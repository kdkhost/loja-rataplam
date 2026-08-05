<?php

namespace Tests\Feature\MercadoPago;

use App\Models\Order;
use App\Models\MercadoPagoSetting;
use App\Models\User;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoFeatureGate;
use App\Services\MercadoPago\MercadoPagoLegacyClient;
use App\Services\MercadoPago\MercadoPagoPaymentService;
use App\Services\MercadoPago\MercadoPagoResponse;
use Illuminate\Support\Facades\DB;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoCheckoutHttpTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    private User $user;
    private array $captured = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        $this->withoutMiddleware([
            \App\Http\Middleware\Maintainance::class,
            \App\Http\Middleware\Localization::class,
        ]);
        DB::table('users')->insert(['id' => 20, 'email' => 'customer@example.test', 'password' => bcrypt('synthetic')]);
        DB::table('settings')->insert(['id' => 1, 'unique_keyword' => 'system', 'title' => 'Loja Teste']);
        DB::table('currencies')->insert(['id' => 1, 'name' => 'BRL', 'value' => '1.00000000', 'is_default' => 1]);
        DB::table('items')->insert(['id' => 1, 'name' => 'Produto', 'discount_price' => '10.50']);
        DB::table('shipping_services')->insert(['id' => 1, 'title' => 'Entrega', 'price' => '5.00', 'status' => 1]);
        DB::table('payment_settings')->insert([
            'unique_keyword' => 'mercadopago',
            'information' => json_encode(['pix_enabled' => 1, 'credit_card_enabled' => 0]),
            'status' => 1,
        ]);
        $setting = MercadoPagoSetting::create([
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-public', 'sandbox_access_token' => 'synthetic-token',
            'sandbox_collector_id' => 'collector-test', 'sandbox_webhook_secret' => 'synthetic-secret',
            'pix_enabled' => true, 'credit_card_enabled' => false,
        ]);
        $setting->sandbox_enabled = true;
        $setting->save();
        $legacyClient = $this->createMock(MercadoPagoLegacyClient::class);
        $legacyClient->expects($this->never())->method('configure');
        $legacyClient->expects($this->never())->method('savePayment');
        $this->app->instance(MercadoPagoLegacyClient::class, $legacyClient);
        $this->user = User::findOrFail(20);
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_checkout_submission_requires_authenticated_customer(): void
    {
        $this->post('/mercadopago/submit', [])->assertRedirect('/user/login');
    }

    public function test_authenticated_owner_uses_server_amount_currency_and_key(): void
    {
        $this->seedPendingOrder(20, 'Unpaid', '10.50', 'BRL');
        $this->bindPaymentFlow(1);

        $this->actingAs($this->user)->withSession($this->checkoutSession())->post('/mercadopago/submit', $this->request([
            'amount' => '99999.99',
            'currency' => 'USD',
            'idempotency_key' => 'client-key-must-be-ignored',
        ]))->assertRedirect(route('front.checkout.success'));

        $this->assertSame('10.50', $this->captured['payload']['transaction_amount']);
        $this->assertSame('10', (string) $this->captured['payload']['external_reference']);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $this->captured['key']);
        $this->assertNotSame('client-key-must-be-ignored', $this->captured['key']);
        $this->assertDatabaseHas('orders', ['id' => 10, 'txnid' => 'payment-http', 'payment_status' => 'Unpaid']);
    }

    /** @dataProvider blockedOrders */
    public function test_invalid_ownership_or_order_state_never_calls_remote(int $owner, string $status, string $amount, string $currency): void
    {
        $this->seedPendingOrder($owner, $status, $amount, $currency);
        $this->bindPaymentFlow(0);

        $this->actingAs($this->user)->withSession($this->checkoutSession())
            ->post('/mercadopago/submit', $this->request())
            ->assertStatus(in_array($owner, [20], true) ? ($status === 'Paid' ? 409 : 409) : 403);
    }

    public static function blockedOrders(): array
    {
        return [
            'other owner' => [21, 'Unpaid', '10.50', 'BRL'],
            'paid' => [20, 'Paid', '10.50', 'BRL'],
            'changed total' => [20, 'Unpaid', '10.51', 'BRL'],
            'changed currency' => [20, 'Unpaid', '10.50', 'USD'],
        ];
    }

    public function test_missing_pending_order_never_calls_remote(): void
    {
        $this->bindPaymentFlow(0);
        $session = $this->checkoutSession();
        $session['mercadopago_pending_order_id'] = 999;

        $this->actingAs($this->user)->withSession($session)
            ->post('/mercadopago/submit', $this->request())
            ->assertStatus(403);
    }

    public function test_remote_failure_leaves_order_without_partial_payment_write(): void
    {
        $this->seedPendingOrder(20, 'Unpaid', '10.50', 'BRL');
        $this->bindPaymentFlow(1, false);

        $this->actingAs($this->user)->withSession($this->checkoutSession())
            ->post('/mercadopago/submit', $this->request())
            ->assertRedirect(route('front.checkout.cancle'));

        $this->assertDatabaseHas('orders', ['id' => 10, 'txnid' => null, 'payment_status' => 'Unpaid']);
    }

    public function test_identical_http_retry_reuses_server_operation_without_second_remote_call(): void
    {
        $this->seedPendingOrder(20, 'Unpaid', '10.50', 'BRL');
        $this->bindPaymentFlow(1);

        $this->actingAs($this->user)->withSession($this->checkoutSession())
            ->post('/mercadopago/submit', $this->request())->assertRedirect();
        DB::table('orders')->where('id', 10)->update(['txnid' => null]);
        $this->actingAs($this->user)->withSession($this->checkoutSession())
            ->post('/mercadopago/submit', $this->request())->assertRedirect();

        $this->assertSame(1, DB::table('mercadopago_actions')->where('action', 'create_pix_payment')->count());
    }

    public function test_gate_disabled_between_dispatcher_and_v2_barrier_persists_nothing(): void
    {
        $gate = new class extends MercadoPagoFeatureGate {
            private int $checks = 0;

            public function assertCheckoutEnabled(string $environment): void
            {
                $this->checks++;
                if ($this->checks === 2) {
                    DB::table('mercadopago_settings')->where('configuration_key', 'default')
                        ->update(['sandbox_enabled' => false]);
                }
                parent::assertCheckoutEnabled($environment);
            }
        };
        $this->app->instance(MercadoPagoFeatureGate::class, $gate);
        $this->bindPaymentFlow(0);
        $session = $this->checkoutSession();
        unset($session['mercadopago_pending_order_id']);
        $request = $this->request();
        unset($request['mercadopago_order_id']);

        $this->actingAs($this->user)->withSession($session)
            ->post('/mercadopago/submit', $request)->assertStatus(503);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('mercadopago_actions', 0);
        $this->assertFalse(MercadoPagoSetting::firstOrFail()->sandbox_enabled);
    }

    private function bindPaymentFlow(int $calls, bool $successful = true): void
    {
        $config = $this->createMock(MercadoPagoConfigResolver::class);
        $config->method('resolvePublicConfiguration')->willReturn([
            'mode' => 'sandbox', 'pix_enabled' => true, 'credit_card_enabled' => false,
        ]);
        $config->method('resolve')->willReturn([
            'mode' => 'sandbox', 'pix_enabled' => true, 'credit_card_enabled' => false,
            'notification_url' => 'https://example.test/mercadopago/webhook/v2',
        ]);

        $client = $this->createMock(MercadoPagoClient::class);
        $expectation = $client->expects($this->exactly($calls))->method('createPayment');
        if ($calls > 0) {
            $expectation->willReturnCallback(function (array $payload, string $key) use ($successful): MercadoPagoResponse {
                $this->captured = compact('payload', 'key');
                return new MercadoPagoResponse($successful, $successful ? 201 : 503, $successful ? [
                    'id' => 'payment-http', 'status' => 'pending',
                ] : [], $successful ? null : 'remote_failure', 'Synthetic response');
            });
        }

        $service = new MercadoPagoPaymentService($client, $config);
        $this->app->instance(MercadoPagoConfigResolver::class, $config);
        $this->app->instance(MercadoPagoPaymentService::class, $service);
    }

    private function seedPendingOrder(int $owner, string $status, string $amount, string $currency): void
    {
        DB::table('orders')->insert([
            'id' => 10,
            'user_id' => $owner,
            'payment_status' => $status,
            'payment_details' => json_encode(['mercadopago' => [
                'authoritative_amount' => $amount,
                'currency' => $currency,
                'side_effects_registered' => true,
            ]]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function checkoutSession(): array
    {
        return [
            'mercadopago_pending_order_id' => 10,
            'cart' => ['1-digital' => [
                'qty' => 1, 'options_id' => [], 'main_price' => '99999.99',
                'attribute_price' => '99999.99', 'type' => 'digital', 'item_type' => 'digital',
            ]],
        ];
    }

    private function request(array $overrides = []): array
    {
        return array_merge([
            'mercadopago_payment_type' => 'pix',
            'docType' => 'CPF',
            'docNumber' => '12345678901',
            'mercadopago_order_id' => 10,
        ], $overrides);
    }
}
