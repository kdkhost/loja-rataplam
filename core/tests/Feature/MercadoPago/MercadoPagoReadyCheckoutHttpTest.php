<?php

namespace Tests\Feature\MercadoPago;

use App\Http\Controllers\Payment\MercadopagoLegacyController;
use App\Http\Controllers\Payment\MercadopagoV2Controller;
use App\Models\MercadoPagoSetting;
use App\Models\User;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoLegacyClient;
use App\Services\MercadoPago\MercadoPagoResponse;
use App\Services\MercadoPago\MercadoPagoSettingRepository;
use Illuminate\Support\Facades\DB;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoReadyCheckoutHttpTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        $this->withoutMiddleware([
            \App\Http\Middleware\Maintainance::class,
            \App\Http\Middleware\Localization::class,
        ]);

        DB::table('users')->insert(['id' => 40, 'email' => 'ready-v2@example.test', 'password' => bcrypt('synthetic')]);
        DB::table('settings')->insert(['id' => 1, 'unique_keyword' => 'system', 'title' => 'Loja Teste']);
        DB::table('currencies')->insert(['id' => 1, 'name' => 'BRL', 'value' => '1.00000000', 'is_default' => 1]);
        DB::table('items')->insert(['id' => 1, 'name' => 'Produto', 'discount_price' => '10.50']);
        DB::table('shipping_services')->insert(['id' => 1, 'title' => 'Entrega', 'price' => '5.00', 'status' => 1]);
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_ready_v2_resolves_only_v2_path_with_real_persisted_configuration(): void
    {
        $legacyToken = 'LEGACY-test-token-distinct';
        $v2Token = 'TEST-v2-access-token-distinct';
        DB::table('payment_settings')->insert([
            'unique_keyword' => 'mercadopago',
            'information' => json_encode(['token' => $legacyToken, 'pix_enabled' => 1, 'credit_card_enabled' => 0]),
            'status' => 1,
        ]);
        $setting = MercadoPagoSetting::create([
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-v2-public-key',
            'sandbox_access_token' => $v2Token,
            'sandbox_collector_id' => 'collector-v2-test',
            'sandbox_webhook_secret' => 'TEST-v2-webhook-secret',
            'pix_enabled' => true,
            'credit_card_enabled' => false,
        ]);
        $setting->sandbox_enabled = true;
        $setting->production_enabled = false;
        $setting->save();
        DB::table('orders')->insert([
            'id' => 40,
            'user_id' => 40,
            'payment_status' => 'Unpaid',
            'payment_details' => json_encode(['mercadopago' => [
                'authoritative_amount' => '10.50', 'currency' => 'BRL', 'side_effects_registered' => true,
            ]]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $resolutions = ['legacy_controller' => 0, 'legacy_client' => 0, 'v2_controller' => 0, 'v2_client' => 0, 'resolver' => 0, 'repository' => 0];
        $this->observeResolution(MercadopagoLegacyController::class, $resolutions, 'legacy_controller');
        $this->observeResolution(MercadoPagoLegacyClient::class, $resolutions, 'legacy_client');
        $this->observeResolution(MercadopagoV2Controller::class, $resolutions, 'v2_controller');
        $this->observeResolution(MercadoPagoClient::class, $resolutions, 'v2_client');
        $this->observeResolution(MercadoPagoConfigResolver::class, $resolutions, 'resolver');
        $this->observeResolution(MercadoPagoSettingRepository::class, $resolutions, 'repository');

        $captured = [];
        $client = $this->createMock(MercadoPagoClient::class);
        $client->expects($this->once())->method('createPayment')
            ->willReturnCallback(function (array $payload, string $idempotencyKey) use (&$captured): MercadoPagoResponse {
                $captured = compact('payload', 'idempotencyKey');
                return new MercadoPagoResponse(true, 201, ['id' => 'payment-ready-v2', 'status' => 'pending'], null, 'Synthetic response');
            });
        $this->app->instance(MercadoPagoClient::class, $client);

        $response = $this->actingAs(User::findOrFail(40))->withSession([
            'mercadopago_pending_order_id' => 40,
            'cart' => ['1-digital' => [
                'qty' => 1, 'options_id' => [], 'main_price' => '99999.99',
                'attribute_price' => '99999.99', 'type' => 'digital', 'item_type' => 'digital',
            ]],
        ])->post('/mercadopago/submit', [
            'mercadopago_payment_type' => 'pix', 'docType' => 'CPF', 'docNumber' => '12345678901',
            'mercadopago_order_id' => 40, 'amount' => '99999.99', 'currency' => 'USD',
            'idempotency_key' => 'client-key-must-be-ignored',
        ]);

        $response->assertRedirect(route('front.checkout.success'));
        $response->assertSessionMissing('error');
        $this->assertSame(0, $resolutions['legacy_controller']);
        $this->assertSame(0, $resolutions['legacy_client']);
        $this->assertSame(1, $resolutions['v2_controller']);
        // A instância fake é entregue diretamente pelo binding; a chamada à
        // fronteira é verificada pela expectativa createPayment() acima.
        $this->assertSame(0, $resolutions['v2_client']);
        $this->assertGreaterThan(0, $resolutions['resolver']);
        $this->assertGreaterThan(0, $resolutions['repository']);

        $credentials = $this->app->make(MercadoPagoConfigResolver::class)->resolveBackendCredentials();
        $this->assertSame('sandbox', $credentials->mode);
        $this->assertSame('collector-v2-test', $credentials->collectorId);
        $this->assertSame($v2Token, $credentials->accessToken);
        $this->assertNotSame($legacyToken, $credentials->accessToken);
        $this->assertSame('10.50', $captured['payload']['transaction_amount']);
        $this->assertSame('40', (string) $captured['payload']['external_reference']);
        $this->assertSame('pix', $captured['payload']['payment_method_id']);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $captured['idempotencyKey']);
        $this->assertNotSame('client-key-must-be-ignored', $captured['idempotencyKey']);
        $this->assertDatabaseHas('mercadopago_actions', [
            'order_id' => 40, 'action' => 'create_pix_payment', 'environment' => 'sandbox',
            'currency' => 'BRL', 'requested_amount' => '10.50', 'local_status' => 'success',
        ]);
        $this->assertSame(1, DB::table('mercadopago_actions')->count());
        $this->assertDatabaseHas('orders', ['id' => 40, 'txnid' => 'payment-ready-v2', 'payment_status' => 'Unpaid']);
        $this->assertDatabaseHas('payment_settings', ['unique_keyword' => 'mercadopago', 'status' => 1]);
        $this->assertStringNotContainsString($legacyToken, $response->getContent());
        $this->assertStringNotContainsString($v2Token, $response->getContent());
    }

    private function observeResolution(string $abstract, array &$counts, string $key): void
    {
        $this->app->resolving($abstract, function () use (&$counts, $key): void {
            $counts[$key]++;
        });
    }
}
