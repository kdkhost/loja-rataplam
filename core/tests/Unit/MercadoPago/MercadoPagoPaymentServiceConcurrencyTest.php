<?php
namespace Tests\Unit\MercadoPago;

use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoFeatureGate;
use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use App\Services\MercadoPago\MercadoPagoPaymentService;
use App\Services\MercadoPago\MercadoPagoResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoPaymentServiceConcurrencyTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_concurrent_payment_service_calls_remote_client_only_once()
    {
        // Contador atômico para chamadas ao cliente
        $counter = new class {
            public int $callCount = 0;
            public ?\Fiber $suspensionPoint = null;
        };

        // Fake cliente que incrementa contador e suspende Fiber
        $fakeClient = new class($counter) extends MercadoPagoClient {
            private $counter;

            public function __construct($counter)
            {
                parent::__construct('fake-token');
                $this->counter = $counter;
            }

            public function createPayment(array $payload, string $idempotencyKey): MercadoPagoResponse
            {
                $this->counter->callCount++;

                // Suspender Fiber para permitir que outra Fiber execute
                if (\Fiber::getCurrent() !== null) {
                    $this->counter->suspensionPoint = \Fiber::getCurrent();
                    \Fiber::suspend();
                }

                return new MercadoPagoResponse(
                    successful: true,
                    httpStatus: 200,
                    data: [
                        'id' => 'pay-123',
                        'status' => 'approved',
                        'point_of_interaction' => [
                            'transaction_data' => [
                                'qr_code' => 'qr-code-string',
                                'qr_code_base64' => 'base64-string',
                                'ticket_url' => 'https://example.com/ticket',
                            ]
                        ]
                    ],
                    errorCode: null,
                    safeMessage: 'Sucesso',
                    requestId: 'req-123'
                );
            }
        };

        $mockConfigResolver = $this->createMockConfigResolver();

        // Mock do serviço de idempotência para controlar aquisição
        $idempotencyService = $this->createMock(MercadoPagoIdempotencyService::class);

        // Criar ação mockada
        $actionMock = MercadoPagoAction::create([
            'idempotency_key' => 'fixed-test-key',
            'local_status' => 'pending',
            'action' => 'create_pix_payment',
            'environment' => 'sandbox',
            'execution_owner' => 'owner-uuid',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->addSeconds(35),
        ]);

        // Primeira chamada: adquire ownership
        $acquisitionNew = \App\Services\MercadoPago\MercadoPagoIdempotencyAcquisitionResult::acquiredNew($actionMock, 'owner-uuid');

        // Segunda chamada: encontra em progresso
        $acquisitionInProgress = \App\Services\MercadoPago\MercadoPagoIdempotencyAcquisitionResult::existingInProgress($actionMock);

        // Configurar mock para retornar aquisição nova na primeira chamada
        // e em progresso na segunda
        $callCount = 0;
        $idempotencyService->method('acquireAction')->willReturnCallback(function () use ($acquisitionNew, $acquisitionInProgress, &$callCount) {
            $callCount++;
            return $callCount === 1 ? $acquisitionNew : $acquisitionInProgress;
        });

        $idempotencyService->method('completeAction');

        $service = new MercadoPagoPaymentService($fakeClient, $mockConfigResolver, $idempotencyService, null, $this->enabledGate());

        $orderData = [
            'order_id' => null,
            'amount' => '10.50',
            'authoritative_amount' => '10.50',
            'description' => 'Test Order',
            'payer_email' => 'customer@example.com',
        ];

        // Fiber A: primeira execução
        $fiberA = new \Fiber(function () use ($service, $orderData) {
            return $service->createPixPayment($orderData);
        });

        // Fiber B: segunda execução concorrente
        $fiberB = new \Fiber(function () use ($service, $orderData) {
            try {
                return $service->createPixPayment($orderData);
            } catch (\Exception $e) {
                return ['error' => $e->getMessage()];
            }
        });

        // Iniciar Fiber A
        $fiberA->start();

        // Enquanto Fiber A está suspensa (aguardando resposta do cliente),
        // iniciar Fiber B com mesma idempotency key
        $fiberB->start();

        // Retomar Fiber A para completar
        $fiberA->resume();

        // Verificar resultados
        $this->assertEquals(1, $counter->callCount, 'Deve haver exatamente uma chamada ao MercadoPagoClient');

        // Verificar que Fiber A retornou sucesso
        $resultA = $fiberA->getReturn();
        $this->assertIsArray($resultA);
        $this->assertEquals('pay-123', $resultA['payment_id']);

        // Fiber B deve ter recebido erro de processamento
        $resultB = $fiberB->getReturn();
        $this->assertIsArray($resultB);
        $this->assertArrayHasKey('error', $resultB);
        $this->assertStringContainsString('processamento', $resultB['error']);
    }

    public function test_existing_success_returns_cached_result()
    {
        // Criar ação já success
        MercadoPagoAction::create([
            'idempotency_key' => 'test-key-success',
            'local_status' => 'success',
            'action' => 'create_pix_payment',
            'environment' => 'sandbox',
            'mercadopago_operation_id' => 'pay-existing',
            'remote_status' => 'approved',
        ]);

        $counter = new class {
            public int $callCount = 0;
        };

        $fakeClient = new class($counter) extends MercadoPagoClient {
            private $counter;

            public function __construct($counter)
            {
                parent::__construct('fake-token');
                $this->counter = $counter;
            }

            public function createPayment(array $payload, string $idempotencyKey): MercadoPagoResponse
            {
                $this->counter->callCount++;
                throw new \Exception('Cliente não deve ser chamado');
            }
        };

        $mockConfigResolver = $this->createMockConfigResolver();
        $idempotencyService = app(MercadoPagoIdempotencyService::class);
        $service = new MercadoPagoPaymentService($fakeClient, $mockConfigResolver, $idempotencyService, null, $this->enabledGate());

        // Sobrescrever geração de chave para usar a chave existente
        $idempotencyService = $this->createMock(MercadoPagoIdempotencyService::class);
        $action = MercadoPagoAction::where('idempotency_key', 'test-key-success')->first();
        $acquisition = \App\Services\MercadoPago\MercadoPagoIdempotencyAcquisitionResult::existingSuccess($action);
        $idempotencyService->method('acquireAction')->willReturn($acquisition);

        $service = new MercadoPagoPaymentService($fakeClient, $mockConfigResolver, $idempotencyService, null, $this->enabledGate());

        $result = $service->createPixPayment([
            'order_id' => null,
            'amount' => '10.50',
            'authoritative_amount' => '10.50',
            'description' => 'Test Order',
            'payer_email' => 'customer@example.com',
        ]);

        $this->assertEquals(0, $counter->callCount, 'Cliente não deve ser chamado para success');
        $this->assertEquals('pay-existing', $result['payment_id']);
        $this->assertTrue($result['from_cache'] ?? false, 'Deve indicar que veio do cache');
    }

    protected function createMockConfigResolver()
    {
        $mock = $this->createMock(MercadoPagoConfigResolver::class);
        $mock->expects($this->any())
             ->method('resolve')
             ->willReturn([
                'mode' => 'sandbox',
                'pix_enabled' => true,
                'sandbox_access_token' => 'fake-token',
                'production_access_token' => 'prod-token',
                'notification_url' => 'https://example.com/webhook',
            ]);
        return $mock;
    }

    protected function enabledGate(): MercadoPagoFeatureGate
    {
        $gate = $this->createMock(MercadoPagoFeatureGate::class);
        $gate->method('assertCheckoutEnabled');

        return $gate;
    }
}
