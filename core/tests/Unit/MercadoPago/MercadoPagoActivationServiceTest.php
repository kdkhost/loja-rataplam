<?php

namespace Tests\Unit\MercadoPago;

use App\Exceptions\MercadoPagoConfigurationException;
use App\Models\MercadoPagoSetting;
use App\Services\MercadoPago\MercadoPagoActivationService;
use Illuminate\Support\Facades\DB;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoActivationServiceTest extends TestCase
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

    public function test_gates_are_not_mass_assignable(): void
    {
        $setting = MercadoPagoSetting::create([
            'mode' => 'sandbox',
            'sandbox_enabled' => true,
            'production_enabled' => true,
        ]);

        $this->assertNotTrue($setting->sandbox_enabled);
        $this->assertNotTrue($setting->production_enabled);
    }

    public function test_stale_sandbox_configuration_is_reloaded_and_rejected(): void
    {
        $setting = $this->readySetting();
        $stale = MercadoPagoSetting::findOrFail($setting->id);
        DB::table('mercadopago_settings')->where('id', $setting->id)->update(['sandbox_collector_id' => null]);

        $this->assertNotNull($stale->sandbox_collector_id);
        $this->expectException(MercadoPagoConfigurationException::class);
        try {
            $this->service()->activate('sandbox');
        } finally {
            $this->assertFalse($setting->fresh()->sandbox_enabled);
        }
    }

    public function test_stale_production_configuration_is_reloaded_and_rejected(): void
    {
        $setting = $this->readySetting();
        $stale = MercadoPagoSetting::findOrFail($setting->id);
        DB::table('mercadopago_settings')->where('id', $setting->id)->update(['production_webhook_secret' => null]);

        $this->assertNotNull($stale->production_webhook_secret);
        $this->expectException(MercadoPagoConfigurationException::class);
        try {
            $this->service()->activate('production');
        } finally {
            $this->assertFalse($setting->fresh()->production_enabled);
        }
    }

    public function test_repeated_activation_and_deactivation_are_idempotent(): void
    {
        $setting = $this->readySetting();
        $service = $this->service();
        $service->activate('sandbox');
        $service->activate('sandbox');
        $this->assertTrue($setting->fresh()->sandbox_enabled);

        $service->deactivate('sandbox');
        $service->deactivate('sandbox');
        $this->assertFalse($setting->fresh()->sandbox_enabled);
        $this->assertNotNull($setting->fresh()->sandbox_access_token);
    }

    public function test_each_environment_preserves_the_other_gate(): void
    {
        $setting = $this->readySetting();
        $setting->production_enabled = true;
        $setting->save();

        $this->service()->activate('sandbox');
        $this->assertTrue($setting->fresh()->production_enabled);

        $this->service()->deactivate('production');
        $this->service()->activate('production');
        $this->assertTrue($setting->fresh()->sandbox_enabled);
    }

    public function test_decrypt_failure_rolls_back_activation(): void
    {
        $setting = $this->readySetting();
        DB::table('mercadopago_settings')->where('id', $setting->id)->update(['production_access_token' => 'invalid']);

        $this->expectException(MercadoPagoConfigurationException::class);
        try {
            $this->service()->activate('production');
        } finally {
            $this->assertFalse($setting->fresh()->production_enabled);
        }
    }

    private function service(): MercadoPagoActivationService
    {
        return app(MercadoPagoActivationService::class);
    }

    private function readySetting(): MercadoPagoSetting
    {
        return MercadoPagoSetting::create([
            'configuration_key' => 'default',
            'mode' => 'sandbox',
            'sandbox_public_key' => 'TEST-public',
            'sandbox_access_token' => 'synthetic-token',
            'sandbox_collector_id' => 'collector-test',
            'sandbox_webhook_secret' => 'synthetic-secret',
            'production_public_key' => 'PROD-public',
            'production_access_token' => 'synthetic-prod-token',
            'production_collector_id' => 'collector-prod',
            'production_webhook_secret' => 'synthetic-prod-secret',
            'pix_enabled' => true,
            'credit_card_enabled' => true,
        ]);
    }
}
