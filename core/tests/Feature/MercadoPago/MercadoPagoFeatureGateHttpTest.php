<?php

namespace Tests\Feature\MercadoPago;

use App\Http\Controllers\Payment\MercadopagoLegacyController;
use App\Http\Controllers\Payment\MercadopagoV2Controller;
use App\Models\MercadoPagoSetting;
use App\Models\User;
use Mockery;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoFeatureGateHttpTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        $this->withoutMiddleware([\App\Http\Middleware\Maintainance::class, \App\Http\Middleware\Localization::class]);
        User::create(['email'=>'gate@example.test','password'=>bcrypt('synthetic')]);
    }
    protected function tearDown(): void { $this->dropMercadoPagoTestSchema(); parent::tearDown(); }

    public function test_missing_gate_dispatches_explicitly_to_legacy(): void
    {
        [$legacy] = $this->bindControllers(legacyCalls: 1, v2Calls: 0);
        $this->actingAs(User::first())->post('/mercadopago/submit')->assertOk()->assertJson(['flow'=>'legacy']);
        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_false_gate_preserves_legacy_and_ignores_legacy_token_for_v2(): void
    {
        $this->setting();
        \DB::table('payment_settings')->insert(['unique_keyword'=>'mercadopago','information'=>json_encode(['token'=>'legacy-token']),'status'=>1]);
        $this->bindControllers(legacyCalls: 1, v2Calls: 0);
        $this->actingAs(User::first())->post('/mercadopago/submit')->assertOk()->assertJson(['flow'=>'legacy']);
        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_true_but_incomplete_gate_fails_closed_before_any_flow(): void
    {
        $this->setting(['sandbox_enabled'=>true,'sandbox_access_token'=>null]);
        $this->bindControllers(legacyCalls: 0, v2Calls: 0);
        $this->actingAs(User::first())->post('/mercadopago/submit')->assertStatus(503);
        $this->assertDatabaseCount('mercadopago_actions', 0);
    }

    public function test_true_and_ready_gate_dispatches_to_v2(): void
    {
        $this->setting(['sandbox_enabled'=>true]);
        $this->bindControllers(legacyCalls: 0, v2Calls: 1);
        $this->actingAs(User::first())->post('/mercadopago/submit')->assertOk()->assertJson(['flow'=>'v2']);
    }

    private function bindControllers(int $legacyCalls, int $v2Calls): array
    {
        $legacy=Mockery::mock(MercadopagoLegacyController::class);
        $legacy->shouldReceive('store')->times($legacyCalls)->andReturn(response()->json(['flow'=>'legacy']));
        $v2=Mockery::mock(MercadopagoV2Controller::class);
        $v2->shouldReceive('store')->times($v2Calls)->andReturn(response()->json(['flow'=>'v2']));
        $this->app->instance(MercadopagoLegacyController::class,$legacy);
        $this->app->instance(MercadopagoV2Controller::class,$v2);
        return [$legacy,$v2];
    }

    private function setting(array $overrides=[]): void
    {
        MercadoPagoSetting::create(array_merge([
            'mode'=>'sandbox','sandbox_enabled'=>false,'production_enabled'=>false,
            'sandbox_public_key'=>'TEST-public','sandbox_access_token'=>'synthetic-token',
            'sandbox_collector_id'=>'collector-test','sandbox_webhook_secret'=>'synthetic-secret',
            'pix_enabled'=>true,'credit_card_enabled'=>true,
        ],$overrides));
    }
}
