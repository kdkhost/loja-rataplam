<?php

namespace App\Services\MercadoPago;

use App\Models\MercadoPagoSetting;
use Illuminate\Support\Facades\DB;

class MercadoPagoActivationService
{
    public function __construct(protected MercadoPagoFeatureGate $featureGate) {}

    public function activate(string $environment): void
    {
        $this->validateEnvironment($environment);

        DB::transaction(function () use ($environment): void {
            $setting = $this->lockedSetting();
            $this->featureGate->assertConfigurationReadyFor($setting, $environment);
            $setting->{$environment . '_enabled'} = true;
            $setting->mode = $environment;
            $setting->save();
        });
    }

    public function deactivate(string $environment): void
    {
        $this->validateEnvironment($environment);

        DB::transaction(function () use ($environment): void {
            $setting = $this->lockedSetting();
            $setting->{$environment . '_enabled'} = false;
            $setting->save();
        });
    }

    private function lockedSetting(): MercadoPagoSetting
    {
        return MercadoPagoSetting::where('configuration_key', 'default')->lockForUpdate()->firstOrFail();
    }

    private function validateEnvironment(string $environment): void
    {
        abort_unless(in_array($environment, ['sandbox', 'production'], true), 404);
    }
}
