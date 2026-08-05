<?php

namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoCredentials;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoResponse;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoWebhookCollectorValidationTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected MercadoPagoWebhookService $webhookService;
    protected MercadoPagoConfigResolver $configResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        $this->configResolver = app(MercadoPagoConfigResolver::class);
        // Não instanciar webhookService aqui - será configurado por teste
    }

    /** @test */
    public function conta_correta_aceita()
    {
        $notification = [
            'data' => ['id' => 'payment-123'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-123',
        ];

        $paymentData = [
            'id' => 'payment-123',
            'currency_id' => 'BRL',
            'external_reference' => 'order-123',
            'sandbox' => false,
            'collector_id' => 'collector-456',
            'status' => 'approved',
        ];

        // Mock do config resolver para retornar collector_id
        $mockConfigResolver = $this->createMockConfigResolver('collector-456', 'production');

        // Mock do cliente para retornar pagamento
        $mockClient = $this->createMockClient($paymentData);

        // Mock do validator para passar assinatura
        $mockValidator = $this->createMockValidator(true);

        // Instanciar webhookService com mocks
        $this->webhookService = new MercadoPagoWebhookService(
            $mockClient,
            $mockConfigResolver,
            $mockValidator
        );

        $result = $this->webhookService->handleNotification($notification);

        $this->assertEquals('processed', $result['status']);
        $this->assertEquals('payment-123', $result['payment_id']);
    }

    /** @test */
    public function collector_divergente_rejeitado()
    {
        $notification = [
            'data' => ['id' => 'payment-789'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-789',
        ];

        $paymentData = [
            'id' => 'payment-789',
            'currency_id' => 'BRL',
            'external_reference' => 'order-789',
            'sandbox' => false,
            'collector_id' => 'collector-999',
            'status' => 'approved',
        ];

        // Mock do config resolver para retornar collector_id diferente
        $mockConfigResolver = $this->createMockConfigResolver('collector-456', 'production');

        // Mock do cliente para retornar pagamento
        $mockClient = $this->createMockClient($paymentData);

        // Mock do validator para passar assinatura
        $mockValidator = $this->createMockValidator(true);

        // Instanciar webhookService com mocks
        $this->webhookService = new MercadoPagoWebhookService(
            $mockClient,
            $mockConfigResolver,
            $mockValidator
        );

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Conta recebedora divergente.');

        $this->webhookService->handleNotification($notification);
    }

    /** @test */
    public function collector_ausente_rejeitado()
    {
        $notification = [
            'data' => ['id' => 'payment-456'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-456',
        ];

        $paymentData = [
            'id' => 'payment-456',
            'currency_id' => 'BRL',
            'external_reference' => 'order-456',
            'sandbox' => false,
            // collector_id ausente
            'status' => 'approved',
        ];

        // Mock do config resolver para retornar collector_id
        $mockConfigResolver = $this->createMockConfigResolver('collector-456', 'production');

        // Mock do cliente para retornar pagamento
        $mockClient = $this->createMockClient($paymentData);

        // Mock do validator para passar assinatura
        $mockValidator = $this->createMockValidator(true);

        // Instanciar webhookService com mocks
        $this->webhookService = new MercadoPagoWebhookService(
            $mockClient,
            $mockConfigResolver,
            $mockValidator
        );

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Conta recebedora divergente.');

        $this->webhookService->handleNotification($notification);
    }

    /** @test */
    public function sandbox_com_conta_de_producao_rejeitado()
    {
        $notification = [
            'data' => ['id' => 'payment-111'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-111',
        ];

        $paymentData = [
            'id' => 'payment-111',
            'currency_id' => 'BRL',
            'external_reference' => 'order-111',
            'sandbox' => true,
            'collector_id' => 'collector-prod',
            'status' => 'approved',
        ];

        // Mock do config resolver para retornar collector_id de sandbox
        $mockConfigResolver = $this->createMockConfigResolver('collector-sandbox', 'sandbox');

        // Mock do cliente para retornar pagamento
        $mockClient = $this->createMockClient($paymentData);

        // Mock do validator para passar assinatura
        $mockValidator = $this->createMockValidator(true);

        // Instanciar webhookService com mocks
        $this->webhookService = new MercadoPagoWebhookService(
            $mockClient,
            $mockConfigResolver,
            $mockValidator
        );

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Conta recebedora divergente.');

        $this->webhookService->handleNotification($notification);
    }

    /** @test */
    public function producao_com_conta_sandbox_rejeitado()
    {
        $notification = [
            'data' => ['id' => 'payment-222'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-222',
        ];

        $paymentData = [
            'id' => 'payment-222',
            'currency_id' => 'BRL',
            'external_reference' => 'order-222',
            'sandbox' => false,
            'collector_id' => 'collector-sandbox',
            'status' => 'approved',
        ];

        // Mock do config resolver para retornar collector_id de produção
        $mockConfigResolver = $this->createMockConfigResolver('collector-prod', 'production');

        // Mock do cliente para retornar pagamento
        $mockClient = $this->createMockClient($paymentData);

        // Mock do validator para passar assinatura
        $mockValidator = $this->createMockValidator(true);

        // Instanciar webhookService com mocks
        $this->webhookService = new MercadoPagoWebhookService(
            $mockClient,
            $mockConfigResolver,
            $mockValidator
        );

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Conta recebedora divergente.');

        $this->webhookService->handleNotification($notification);
    }

    /** @test */
    public function pedido_nao_e_marcado_como_pago_em_divergencia()
    {
        $notification = [
            'data' => ['id' => 'payment-333'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-333',
        ];

        $paymentData = [
            'id' => 'payment-333',
            'currency_id' => 'BRL',
            'external_reference' => 'order-333',
            'sandbox' => false,
            'collector_id' => 'collector-wrong',
            'status' => 'approved',
        ];

        // Mock do config resolver para retornar collector_id
        $mockConfigResolver = $this->createMockConfigResolver('collector-correct', 'production');

        // Mock do cliente para retornar pagamento
        $mockClient = $this->createMockClient($paymentData);

        // Mock do validator para passar assinatura
        $mockValidator = $this->createMockValidator(true);

        // Instanciar webhookService com mocks
        $this->webhookService = new MercadoPagoWebhookService(
            $mockClient,
            $mockConfigResolver,
            $mockValidator
        );

        $this->expectException(\App\Exceptions\MercadoPagoApiException::class);
        $this->expectExceptionMessage('Conta recebedora divergente.');

        $this->webhookService->handleNotification($notification);

        // Verificar que nenhum registro de ação foi criado
        $this->assertDatabaseMissing('mercadopago_actions', [
            'payment_id' => 'payment-333',
        ]);
    }

    private function createMockConfigResolver(string $collectorId, string $mode)
    {
        $mock = $this->createMock(MercadoPagoConfigResolver::class);
        $mock->method('resolve')->willReturn([
            'mode' => $mode,
            'sandbox_access_token' => 'test-sandbox-token',
            'production_access_token' => 'test-production-token',
            'sandbox_webhook_secret' => 'test-sandbox-secret',
            'production_webhook_secret' => 'test-production-secret',
        ]);
        $mock->method('resolveBackendCredentials')->willReturn(
            new MercadoPagoCredentials(
                publicKey: 'test-pub-key',
                accessToken: 'test-token',
                webhookSecret: 'test-secret',
                mode: $mode,
                collectorId: $collectorId
            )
        );
        return $mock;
    }

    private function createMockClient(array $paymentData)
    {
        $mock = $this->createMock(MercadoPagoClient::class);
        $mock->method('getPayment')->willReturn(
            new MercadoPagoResponse(true, 200, $paymentData, null, 'Success')
        );
        return $mock;
    }

    private function createMockValidator(bool $isValid)
    {
        $mock = $this->createMock(\App\Services\MercadoPago\MercadoPagoWebhookValidator::class);
        $mock->method('isValid')->willReturn($isValid);
        return $mock;
    }
}
