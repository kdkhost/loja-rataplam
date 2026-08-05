<?php

namespace App\Services\MercadoPago;

use App\Exceptions\MercadoPagoConfigurationException;

class MercadoPagoFeatureGate
{
    protected MercadoPagoSettingRepository $settings;

    public function __construct(?MercadoPagoSettingRepository $settings = null)
    {
        $this->settings = $settings ?? new MercadoPagoSettingRepository();
    }

    public function requestedEnvironment(): ?string
    {
        try {
            $setting = $this->settings->current();
            $environment = $setting?->mode;

            if (!in_array($environment, ['sandbox', 'production'], true)) {
                return null;
            }

            return (bool) $setting->{$environment . '_enabled'} ? $environment : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function isEnabledFor(string $environment): bool
    {
        return $this->readiness($environment)['ready'];
    }

    public function assertCheckoutEnabled(string $environment): void
    {
        $this->assertReady($environment, true);
    }

    public function assertWebhookEnabled(string $environment): void
    {
        $this->assertReady($environment, true);
    }

    public function assertConfigurationReady(string $environment): void
    {
        $this->assertReady($environment, false);
    }

    public function readiness(string $environment): array
    {
        return $this->evaluate($environment, true);
    }

    private function assertReady(string $environment, bool $requireGate): void
    {
        if (!$this->evaluate($environment, $requireGate)['ready']) {
            throw new MercadoPagoConfigurationException('Integração Mercado Pago indisponível.');
        }
    }

    private function evaluate(string $environment, bool $requireGate): array
    {
        $result = ['environment' => $environment, 'enabled' => false, 'ready' => false, 'reasons' => []];
        if (!in_array($environment, ['sandbox', 'production'], true)) {
            $result['reasons'][] = 'unknown_environment';
            return $result;
        }

        try {
            $setting = $this->settings->current();
            if (!$setting) {
                $result['reasons'][] = 'missing_configuration';
                return $result;
            }

            $result['enabled'] = (bool) $setting->{$environment . '_enabled'};
            if ($requireGate && !$result['enabled']) {
                $result['reasons'][] = 'gate_disabled';
            }
            if ($requireGate && $setting->mode !== $environment) {
                $result['reasons'][] = 'environment_not_selected';
            }

            foreach (['public_key', 'access_token', 'collector_id', 'webhook_secret'] as $field) {
                if (empty($setting->{$environment . '_' . $field})) {
                    $result['reasons'][] = 'missing_' . $field;
                }
            }
            if (!$setting->pix_enabled && !$setting->credit_card_enabled) {
                $result['reasons'][] = 'no_payment_method';
            }

            $result['ready'] = $result['reasons'] === [];
            return $result;
        } catch (\Throwable) {
            $result['reasons'] = ['configuration_unreadable'];
            return $result;
        }
    }
}
