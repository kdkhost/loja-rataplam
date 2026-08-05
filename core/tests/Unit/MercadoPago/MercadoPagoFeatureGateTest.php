<?php

namespace Tests\Unit\MercadoPago;

use App\Models\MercadoPagoSetting;
use App\Models\PaymentSetting;
use App\Services\MercadoPago\MercadoPagoFeatureGate;
use Illuminate\Support\Facades\DB;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoFeatureGateTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected function setUp(): void { parent::setUp(); $this->createMercadoPagoTestSchema(); }
    protected function tearDown(): void { $this->dropMercadoPagoTestSchema(); parent::tearDown(); }

    public function test_missing_record_is_disabled(): void { $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_both_gates_false_are_disabled(): void { $this->setting(); $this->assertNull($this->gate()->requestedEnvironment()); }
    public function test_sandbox_can_be_enabled_independently(): void { $this->setting(['sandbox_enabled'=>true]); $this->assertTrue($this->gate()->isEnabledFor('sandbox')); $this->assertFalse($this->gate()->isEnabledFor('production')); }
    public function test_production_can_be_enabled_independently(): void { $this->setting(['mode'=>'production','production_enabled'=>true]); $this->assertTrue($this->gate()->isEnabledFor('production')); $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_enabled_without_token_fails_closed(): void { $this->setting(['sandbox_enabled'=>true,'sandbox_access_token'=>null]); $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_enabled_without_collector_fails_closed(): void { $this->setting(['sandbox_enabled'=>true,'sandbox_collector_id'=>null]); $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_enabled_without_webhook_secret_fails_closed(): void { $this->setting(['sandbox_enabled'=>true,'sandbox_webhook_secret'=>null]); $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_decrypt_failure_is_sanitized(): void { $s=$this->setting(['sandbox_enabled'=>true]); DB::table('mercadopago_settings')->where('id',$s->id)->update(['sandbox_access_token'=>'invalid']); $this->assertSame(['configuration_unreadable'], $this->gate()->readiness('sandbox')['reasons']); }
    public function test_unknown_environment_is_disabled(): void { $this->assertFalse($this->gate()->isEnabledFor('staging')); }
    public function test_pix_true_does_not_override_false_gate(): void { $this->setting(['pix_enabled'=>true]); $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_card_true_does_not_override_false_gate(): void { $this->setting(['credit_card_enabled'=>true]); $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_complete_configuration_is_ready(): void { $this->setting(['sandbox_enabled'=>true]); $this->assertTrue($this->gate()->readiness('sandbox')['ready']); }
    public function test_legacy_credentials_do_not_enable_new_gate(): void { PaymentSetting::create(['unique_keyword'=>'mercadopago','information'=>json_encode(['token'=>'legacy'])]); $this->assertFalse($this->gate()->isEnabledFor('sandbox')); }
    public function test_legacy_status_does_not_enable_new_gate(): void { PaymentSetting::create(['unique_keyword'=>'mercadopago','status'=>1]); $this->assertNull($this->gate()->requestedEnvironment()); }
    public function test_readiness_never_returns_secrets(): void { $this->setting(['sandbox_enabled'=>true]); $value=json_encode($this->gate()->readiness('sandbox')); $this->assertStringNotContainsString('synthetic-token',$value); $this->assertStringNotContainsString('synthetic-secret',$value); }
    public function test_sandbox_gate_does_not_activate_production(): void { $this->setting(['sandbox_enabled'=>true]); $this->assertFalse($this->gate()->readiness('production')['enabled']); }
    public function test_production_gate_does_not_activate_sandbox(): void { $this->setting(['mode'=>'production','production_enabled'=>true]); $this->assertFalse($this->gate()->readiness('sandbox')['enabled']); }

    private function gate(): MercadoPagoFeatureGate { return new MercadoPagoFeatureGate(); }
    private function setting(array $overrides=[]): MercadoPagoSetting
    {
        return MercadoPagoSetting::create(array_merge([
            'configuration_key'=>'default','mode'=>'sandbox','sandbox_enabled'=>false,'production_enabled'=>false,
            'sandbox_public_key'=>'TEST-public','sandbox_access_token'=>'synthetic-token','sandbox_collector_id'=>'collector-test','sandbox_webhook_secret'=>'synthetic-secret',
            'production_public_key'=>'PROD-public','production_access_token'=>'synthetic-prod-token','production_collector_id'=>'collector-prod','production_webhook_secret'=>'synthetic-prod-secret',
            'pix_enabled'=>true,'credit_card_enabled'=>true,
        ], $overrides));
    }
}
