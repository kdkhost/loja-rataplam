<?php
namespace Tests\Feature\MercadoPago;

use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use App\Services\MercadoPago\MercadoPagoResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MercadoPagoIdempotencyTest extends TestCase
{
    use RefreshDatabase;
    public function test_uuid_is_unique()
    {
        $service = new MercadoPagoIdempotencyService();
        $key1 = $service->generateKey();
        $key2 = $service->generateKey();
        $this->assertNotEquals($key1, $key2);
    }

    public function test_fingerprint_filters_sensitive_fields()
    {
        $service = new MercadoPagoIdempotencyService();
        $data = [
            'order_id' => '123',
            'action' => 'refund',
            'amount_in_cents' => 1000,
            'currency' => 'BRL',
            'access_token' => 'SECRET-TOKEN', // deve ser filtrado
            'webhook_secret' => 'WEBHOOK-SECRET', // deve ser filtrado
        ];

        $fingerprint1 = $service->generateFingerprint($data);
        $fingerprint2 = $service->generateFingerprint([
            'order_id' => '123',
            'action' => 'refund',
            'amount_in_cents' => 1000,
            'currency' => 'BRL',
            'access_token' => 'DIFFERENT-TOKEN', // não deve afetar
        ]);

        $this->assertEquals($fingerprint1, $fingerprint2);
    }

    public function test_fingerprint_sorts_keys_canonically()
    {
        $service = new MercadoPagoIdempotencyService();
        $data1 = ['order_id' => '123', 'action' => 'refund', 'amount_in_cents' => 1000];
        $data2 = ['amount_in_cents' => 1000, 'order_id' => '123', 'action' => 'refund'];

        $fingerprint1 = $service->generateFingerprint($data1);
        $fingerprint2 = $service->generateFingerprint($data2);

        $this->assertEquals($fingerprint1, $fingerprint2);
    }

    public function test_initiate_action_creates_new_record()
    {
        $service = new MercadoPagoIdempotencyService();
        $params = [
            'idempotency_key' => 'test-key-123',
            'order_id' => '123',
            'environment' => 'sandbox',
            'action' => 'refund',
            'requested_amount' => 1000,
            'currency' => 'BRL',
        ];

        $action = $service->initiateAction($params);

        $this->assertInstanceOf(MercadoPagoAction::class, $action->refresh());
        $this->assertEquals('test-key-123', $action->idempotency_key);
        $this->assertEquals('processing', $action->local_status);
    }

    public function test_initiate_action_returns_existing_on_duplicate_key()
    {
        MercadoPagoAction::create([
            'idempotency_key' => 'duplicate-key',
            'local_status' => 'success',
        ]);

        $service = new MercadoPagoIdempotencyService();
        $params = [
            'idempotency_key' => 'duplicate-key',
            'order_id' => '456',
            'environment' => 'sandbox',
            'action' => 'refund',
        ];

        $action = $service->initiateAction($params);

        $this->assertEquals('success', $action->local_status);
        $this->assertEquals('duplicate-key', $action->idempotency_key);
    }

    public function test_complete_action_updates_status()
    {
        $action = MercadoPagoAction::create([
            'local_status' => 'processing',
        ]);

        $response = new MercadoPagoResponse(
            successful: true,
            httpStatus: 200,
            data: ['status' => 'approved', 'id' => 'mp-123'],
            errorCode: null,
            safeMessage: 'Sucesso',
            requestId: 'req-123',
            retryAfter: null
        );

        $service = new MercadoPagoIdempotencyService();
        $service->completeAction($action, $response);

        $this->assertEquals('success', $action->local_status);
        $this->assertEquals(200, $action->http_status);
        $this->assertEquals('approved', $action->remote_status);
        $this->assertEquals('mp-123', $action->mercadopago_operation_id);
    }

    public function test_complete_action_handles_failure()
    {
        $action = MercadoPagoAction::create([
            'local_status' => 'processing',
        ]);

        $response = new MercadoPagoResponse(
            successful: false,
            httpStatus: 400,
            data: ['status' => 'rejected'],
            errorCode: 'invalid_amount',
            safeMessage: 'Valor inválido',
            requestId: 'req-456',
            retryAfter: null
        );

        $service = new MercadoPagoIdempotencyService();
        $service->completeAction($action, $response);

        $this->assertEquals('failed', $action->local_status);
        $this->assertEquals(400, $action->http_status);
        $this->assertEquals('invalid_amount', $action->error_code);
    }
}
