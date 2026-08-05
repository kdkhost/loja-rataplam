<?php
namespace Tests\Unit\MercadoPago;

use App\Exceptions\MercadoPagoApiException;
use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoIdempotencyAcquisitionResult;
use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use App\Services\MercadoPago\MercadoPagoMoney;
use App\Services\MercadoPago\MercadoPagoPaymentService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoPaymentServiceTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    private MercadoPagoPaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        $this->service = new MercadoPagoPaymentService();
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_create_pix_payment_sandbox()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(201, [], json_encode([
                'id' => 'pay-123',
                'status' => 'pending',
                'point_of_interaction' => [
                    'transaction_data' => [
                        'qr_code' => 'qr-code-string',
                        'qr_code_base64' => 'base64-string',
                        'ticket_url' => 'https://ticket.url',
                    ],
                ],
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockGuzzleClient = new Client(['handler' => $handlerStack]);
        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'pix_enabled' => true,
            'sandbox_access_token' => 'fake-token',
            'production_access_token' => 'prod-token',
            'notification_url' => 'https://example.com/webhook',
        ]);

        $mockIdempotencyService = $this->createMockIdempotencyService();
        $mockMercadoPagoClient = new MercadoPagoClient('fake-token', $mockGuzzleClient);

        $service = new MercadoPagoPaymentService($mockMercadoPagoClient, $mockConfigResolver, $mockIdempotencyService);

        $result = $service->createPixPayment([
            'order_id' => 'order-123',
            'amount' => '99999.99',
            'authoritative_amount' => '10.50',
            'description' => 'Test Order',
            'payer_email' => 'customer@example.com',
        ]);

        $this->assertEquals('pay-123', $result['payment_id']);
        $this->assertEquals('pending', $result['status']);
        $this->assertEquals('qr-code-string', $result['qr_code']);

        $request = $container[0]['request'];
        $this->assertEquals('Bearer fake-token', $request->getHeaderLine('Authorization'));
        $this->assertNotEmpty($request->getHeaderLine('X-Idempotency-Key'));
        $this->assertSame(10.5, json_decode((string) $request->getBody(), true)['transaction_amount']);
    }

    public function test_create_pix_payment_rejects_production_mode()
    {
        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'production',
            'sandbox_access_token' => 'fake-token',
            'production_access_token' => 'prod-token',
            'pix_enabled' => true,
            'credit_card_enabled' => true,
            'max_installments' => 12,
        ]);

        $mockMercadoPagoClient = new MercadoPagoClient('prod-token');
        $service = new MercadoPagoPaymentService($mockMercadoPagoClient, $mockConfigResolver);

        $this->expectException(MercadoPagoApiException::class);
        $this->expectExceptionMessage('sandbox');

        $service->createPixPayment([
            'order_id' => 'order-123',
            'amount' => '10.50',
            'authoritative_amount' => '10.50',
        ]);
    }

    public function test_create_card_payment_sandbox()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(201, [], json_encode([
                'id' => 'pay-456',
                'status' => 'approved',
                'status_detail' => 'accredited',
                'transaction_details' => [
                    'external_resource_url' => 'https://external.url',
                ],
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        $mockGuzzleClient = new Client(['handler' => $handlerStack]);
        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
            'production_access_token' => 'prod-token',
            'credit_card_enabled' => true,
            'max_installments' => 12,
            'notification_url' => 'https://example.com/webhook',
        ]);

        $mockIdempotencyService = $this->createMockIdempotencyService();
        $mockMercadoPagoClient = new MercadoPagoClient('fake-token', $mockGuzzleClient);

        $service = new MercadoPagoPaymentService($mockMercadoPagoClient, $mockConfigResolver, $mockIdempotencyService);

        $result = $service->createCardPayment(
            [
                'order_id' => 'order-456',
                'amount' => '100.00',
                'authoritative_amount' => '100.00',
                'description' => 'Test Order',
                'payer_email' => 'customer@example.com',
                'installments' => 3,
            ],
            [
                'payment_method_id' => 'visa',
                'token' => 'card-token-123',
                'identification_type' => 'CPF',
                'identification_number' => '12345678900',
            ]
        );

        $this->assertEquals('pay-456', $result['payment_id']);
        $this->assertEquals('approved', $result['status']);

        $request = $container[0]['request'];
        $body = json_decode((string) $request->getBody(), true);
        $this->assertEquals('visa', $body['payment_method_id']);
        $this->assertEquals(3, $body['installments']);
    }

    public function test_create_card_payment_validates_installments()
    {
        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
            'production_access_token' => 'prod-token',
            'credit_card_enabled' => true,
            'max_installments' => 1,
        ]);

        $mockMercadoPagoClient = new MercadoPagoClient('fake-token');
        $service = new MercadoPagoPaymentService($mockMercadoPagoClient, $mockConfigResolver);

        $this->expectException(MercadoPagoApiException::class);
        $this->expectExceptionMessage('parcelas');

        $service->createCardPayment(
            [
                'order_id' => 'order-123',
                'amount' => '100.00',
                'authoritative_amount' => '100.00',
                'installments' => 12,
            ],
            ['payment_method_id' => 'visa']
        );
    }

    public function test_get_payment_from_api()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'id' => 'pay-789',
                'status' => 'approved',
                'external_reference' => 'order-789',
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockGuzzleClient = new Client(['handler' => $handlerStack]);

        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
            'production_access_token' => 'prod-token',
        ]);

        $mockMercadoPagoClient = new MercadoPagoClient('fake-token', $mockGuzzleClient);

        $service = new MercadoPagoPaymentService($mockMercadoPagoClient, $mockConfigResolver);

        $result = $service->getPayment('pay-789');

        $this->assertEquals('pay-789', $result['id']);
        $this->assertEquals('approved', $result['status']);
    }

    protected function createMockConfigResolver(array $config)
    {
        $mock = $this->createMock(MercadoPagoConfigResolver::class);
        $mock->expects($this->any())
             ->method('resolve')
             ->willReturn($config);
        return $mock;
    }

    protected function createMockIdempotencyService()
    {
        $mock = $this->createMock(MercadoPagoIdempotencyService::class);

        $actionMock = $this->createMock(MercadoPagoAction::class);
        $actionMock->id = 1;

        // Create acquisition result for the new API
        $acquisitionResult = MercadoPagoIdempotencyAcquisitionResult::acquiredNew($actionMock, 'test-owner-uuid');

        $mock->method('acquireAction')->willReturn($acquisitionResult);
        $mock->method('generateDeterministicKey')
            ->willReturn('123e4567-e89b-52d3-a456-426614174000');
        $mock->method('completeAction');
        return $mock;
    }

    protected function createDefaultConfig(): array
    {
        return [
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
            'production_access_token' => 'prod-token',
            'pix_enabled' => true,
            'credit_card_enabled' => true,
            'max_installments' => 12,
        ];
    }
}
