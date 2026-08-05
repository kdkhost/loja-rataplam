<?php
namespace Tests\Unit\MercadoPago;

use App\Exceptions\MercadoPagoApiException;
use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use App\Services\MercadoPago\MercadoPagoWebhookValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoWebhookServiceTest extends TestCase
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

    public function test_handle_notification_valid_signature()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'id' => 'pay-123',
                'status' => 'approved',
                'external_reference' => 'order-123',
                'currency_id' => 'BRL',
                'sandbox' => true,
                'collector_id' => 'collector-test',
                'transaction_amount' => 10.50,
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockGuzzleClient = new Client(['handler' => $handlerStack]);

        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
            'sandbox_webhook_secret' => 'test-secret',
        ]);

        $mockValidator = $this->createMockValidator(true);
        $mockIdempotencyService = $this->createMockIdempotencyService();
        $mockMercadoPagoClient = new MercadoPagoClient('fake-token', $mockGuzzleClient);

        $service = new MercadoPagoWebhookService($mockMercadoPagoClient, $mockConfigResolver, $mockValidator, $mockIdempotencyService);

        $notification = [
            'data' => ['id' => 'pay-123'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-123',
        ];

        $result = $service->handleNotification($notification);

        $this->assertEquals('processed', $result['status']);
        $this->assertEquals('pay-123', $result['payment_id']);
    }

    public function test_handle_notification_without_payment_id()
    {
        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
        ]);
        $mockValidator = $this->createMockValidator(true);
        $mockIdempotencyService = $this->createMockIdempotencyService();

        $service = new MercadoPagoWebhookService(null, $mockConfigResolver, $mockValidator, $mockIdempotencyService);

        $this->expectException(MercadoPagoApiException::class);
        $this->expectExceptionMessage('ID do pagamento');

        $service->handleNotification(['data' => []]);
    }

    public function test_handle_notification_invalid_signature()
    {
        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
            'sandbox_webhook_secret' => 'test-secret',
        ]);

        $mockValidator = $this->createMockValidator(false);
        $mockIdempotencyService = $this->createMockIdempotencyService();

        $service = new MercadoPagoWebhookService(null, $mockConfigResolver, $mockValidator, $mockIdempotencyService);

        $notification = [
            'data' => ['id' => 'pay-123'],
            'signature' => [
                'ts' => time(),
                'v1' => 'invalid-signature',
            ],
        ];

        $this->expectException(MercadoPagoApiException::class);
        $this->expectExceptionMessage('Assinatura');

        $service->handleNotification($notification);
    }

    public function test_handle_notification_invalid_currency()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'id' => 'pay-123',
                'status' => 'approved',
                'currency_id' => 'USD',
                'external_reference' => 'order-123',
            ])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $mockGuzzleClient = new Client(['handler' => $handlerStack]);

        $mockConfigResolver = $this->createMockConfigResolver([
            'mode' => 'sandbox',
            'sandbox_access_token' => 'fake-token',
            'sandbox_webhook_secret' => 'test-secret',
        ]);

        $mockValidator = $this->createMockValidator(true);
        $mockIdempotencyService = $this->createMockIdempotencyService();
        $mockMercadoPagoClient = new MercadoPagoClient('fake-token', $mockGuzzleClient);

        $service = new MercadoPagoWebhookService($mockMercadoPagoClient, $mockConfigResolver, $mockValidator, $mockIdempotencyService);

        $notification = [
            'data' => ['id' => 'pay-123'],
            'signature' => [
                'ts' => time(),
                'v1' => 'valid-signature',
            ],
            'request_id' => 'req-123',
        ];

        $this->expectException(MercadoPagoApiException::class);
        $this->expectExceptionMessage('Moeda');

        $service->handleNotification($notification);
    }

    protected function createMockConfigResolver(array $config)
    {
        $mock = $this->createMock(MercadoPagoConfigResolver::class);
        $mock->method('resolve')->willReturn($config);

        // Mock resolveBackendCredentials para retornar credenciais com collectorId
        $credentials = new \App\Services\MercadoPago\MercadoPagoCredentials(
            publicKey: 'test-pub-key',
            accessToken: $config['sandbox_access_token'] ?? 'fake-token',
            webhookSecret: $config['sandbox_webhook_secret'] ?? null,
            mode: 'sandbox',
            collectorId: 'collector-test'
        );
        $mock->method('resolveBackendCredentials')->willReturn($credentials);

        return $mock;
    }

    protected function createMockValidator(bool $isValid)
    {
        $mock = $this->createMock(MercadoPagoWebhookValidator::class);
        $mock->method('isValid')->willReturn($isValid);
        return $mock;
    }

    protected function createMockIdempotencyService()
    {
        $mock = $this->createMock(MercadoPagoIdempotencyService::class);

        $actionMock = $this->createMock(MercadoPagoAction::class);
        $actionMock->id = 1;

        $mock->method('generateDeterministicKey')
            ->willReturn('123e4567-e89b-52d3-a456-426614174002');
        $mock->method('generateFingerprint')->willReturn(str_repeat('a', 64));
        $mock->method('acquireAction')->willReturn(
            \App\Services\MercadoPago\MercadoPagoIdempotencyAcquisitionResult::acquiredNew(
                $actionMock,
                'test-owner-uuid'
            )
        );
        $mock->method('completeAction');
        return $mock;
    }
}
