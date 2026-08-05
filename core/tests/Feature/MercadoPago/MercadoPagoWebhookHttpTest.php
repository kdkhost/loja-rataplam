<?php

namespace Tests\Feature\MercadoPago;

use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoCredentials;
use App\Services\MercadoPago\MercadoPagoResponse;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoWebhookHttpTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    private const SECRET = 'synthetic-webhook-secret';
    private const REQUEST_ID = 'request-http-test';
    private const COLLECTOR_ID = 'collector-http-test';

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

    public function test_only_post_is_accepted(): void
    {
        $this->get('/mercadopago/webhook/v2?data.id=pay-http')->assertStatus(405);
    }

    public function test_missing_signature_is_rejected_before_remote_call_or_write(): void
    {
        $this->bindWebhookDependencies(remoteCalls: 0);

        $this->postJson('/mercadopago/webhook/v2?data.id=pay-http', [], [
            'x-request-id' => self::REQUEST_ID,
        ])->assertStatus(401)->assertExactJson(['error' => 'Unauthorized']);

        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_missing_request_id_is_rejected_before_remote_call_or_write(): void
    {
        $this->bindWebhookDependencies(remoteCalls: 0);

        $this->postJson('/mercadopago/webhook/v2?data.id=pay-http', [], [
            'x-signature' => $this->signature('pay-http'),
        ])->assertStatus(401)->assertExactJson(['error' => 'Unauthorized']);

        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_missing_data_id_is_rejected_before_remote_call_or_write(): void
    {
        $this->bindWebhookDependencies(remoteCalls: 0);

        $this->postJson('/mercadopago/webhook/v2', [], [
            'x-request-id' => self::REQUEST_ID,
            'x-signature' => $this->signature('pay-http'),
        ])->assertStatus(401)->assertExactJson(['error' => 'Unauthorized']);

        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_invalid_signature_is_generic_and_precedes_remote_call_or_write(): void
    {
        Log::spy();
        $this->bindWebhookDependencies(remoteCalls: 0);

        $response = $this->postJson('/mercadopago/webhook/v2?data.id=pay-http', [], [
            'x-request-id' => self::REQUEST_ID,
            'x-signature' => 'ts=1700000000,v1=' . str_repeat('0', 64),
        ]);

        $response->assertStatus(401)->assertExactJson(['error' => 'Unauthorized']);
        $this->assertStringNotContainsString(self::SECRET, $response->getContent());
        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_official_data_dot_query_is_used_and_valid_signature_reaches_service(): void
    {
        $this->seedPayableOperation();
        $this->bindWebhookDependencies($this->remotePayment(), 1);

        $this->signedPost('pay-http')->assertOk()->assertJson([
            'status' => 'processed',
            'payment_id' => 'pay-http',
        ]);

        $this->assertDatabaseHas('orders', ['id' => 10, 'payment_status' => 'Paid']);
        $this->assertDatabaseHas('mercadopago_actions', [
            'action' => 'webhook_notification',
            'payment_id' => 'pay-http',
        ]);
    }

    public function test_legacy_query_is_explicitly_supported_but_conflict_is_rejected(): void
    {
        $this->bindWebhookDependencies(remoteCalls: 0);

        $this->postJson('/mercadopago/webhook/v2?data.id=pay-http&data_id=other', [], [
            'x-request-id' => self::REQUEST_ID,
            'x-signature' => $this->signature('pay-http'),
        ])->assertStatus(401);

        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_body_cannot_override_signed_query_id(): void
    {
        $this->bindWebhookDependencies(remoteCalls: 0);

        $this->postJson('/mercadopago/webhook/v2?data.id=pay-http', [
            'data' => ['id' => 'other-payment'],
        ], [
            'x-request-id' => self::REQUEST_ID,
            'x-signature' => $this->signature('pay-http'),
        ])->assertStatus(401)->assertExactJson(['error' => 'Unauthorized']);

        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_unknown_payment_is_rejected_after_mocked_server_lookup(): void
    {
        $this->bindWebhookDependencies($this->remotePayment(), 1);

        $this->signedPost('pay-http')->assertStatus(422)
            ->assertExactJson(['error' => 'Processing failed']);

        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_remote_client_failure_is_sanitized(): void
    {
        $this->bindWebhookDependencies(
            new MercadoPagoResponse(false, 503, [], 'remote_error', 'synthetic upstream detail'),
            1
        );

        $this->signedPost('pay-http')->assertStatus(422)
            ->assertExactJson(['error' => 'Processing failed']);
        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_duplicate_notification_does_not_duplicate_transition(): void
    {
        $this->seedPayableOperation();
        $this->bindWebhookDependencies($this->remotePayment(), 2);

        $this->signedPost('pay-http')->assertOk()->assertJson(['status' => 'processed']);
        $this->signedPost('pay-http')->assertOk()->assertJson(['status' => 'already_processed']);

        $this->assertSame(1, MercadoPagoAction::query()->where('action', 'webhook_notification')->count());
    }

    private function bindWebhookDependencies(
        ?MercadoPagoResponse $remoteResponse = null,
        int $remoteCalls = 0
    ): void {
        $config = $this->createMock(MercadoPagoConfigResolver::class);
        $config->method('resolve')->willReturn(['mode' => 'sandbox']);
        $config->method('resolveBackendCredentials')->willReturn(new MercadoPagoCredentials(
            publicKey: 'synthetic-public-key',
            accessToken: 'synthetic-access-token',
            webhookSecret: self::SECRET,
            mode: 'sandbox',
            collectorId: self::COLLECTOR_ID
        ));

        $client = $this->createMock(MercadoPagoClient::class);
        $expectation = $client->expects($this->exactly($remoteCalls))->method('getPayment');
        if ($remoteCalls > 0) {
            $expectation->with('pay-http')->willReturn($remoteResponse);
        }

        $service = new MercadoPagoWebhookService($client, $config);
        $this->app->instance(MercadoPagoConfigResolver::class, $config);
        $this->app->instance(MercadoPagoWebhookService::class, $service);
    }

    private function seedPayableOperation(): void
    {
        DB::table('orders')->insert([
            'id' => 10,
            'user_id' => 20,
            'total_amount' => '10.50',
            'payment_status' => 'Unpaid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        MercadoPagoAction::query()->create([
            'order_id' => 10,
            'environment' => 'sandbox',
            'action' => 'create_pix_payment',
            'requested_amount' => '10.50',
            'currency' => 'BRL',
            'idempotency_key' => '123e4567-e89b-52d3-a456-426614174001',
            'mercadopago_operation_id' => 'pay-http',
            'local_status' => 'success',
        ]);
    }

    private function remotePayment(): MercadoPagoResponse
    {
        return new MercadoPagoResponse(true, 200, [
            'id' => 'pay-http',
            'status' => 'approved',
            'status_detail' => 'accredited',
            'external_reference' => '10',
            'currency_id' => 'BRL',
            'sandbox' => true,
            'collector_id' => self::COLLECTOR_ID,
            'transaction_amount' => '10.50',
        ], null, 'Success');
    }

    private function signedPost(string $paymentId)
    {
        return $this->postJson('/mercadopago/webhook/v2?data.id=' . rawurlencode($paymentId), [], [
            'x-request-id' => self::REQUEST_ID,
            'x-signature' => $this->signature($paymentId),
        ]);
    }

    private function signature(string $paymentId): string
    {
        $timestamp = '1700000000';
        $manifest = 'id:' . strtolower($paymentId)
            . ';request-id:' . self::REQUEST_ID
            . ';ts:' . $timestamp . ';';

        return 'ts=' . $timestamp . ',v1=' . hash_hmac('sha256', $manifest, self::SECRET);
    }
}
