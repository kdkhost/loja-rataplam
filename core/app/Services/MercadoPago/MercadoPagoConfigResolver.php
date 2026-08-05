<?php

namespace App\Services\MercadoPago;

use App\Exceptions\MercadoPagoConfigurationException;

class MercadoPagoConfigResolver
{
    protected MercadoPagoSettingRepository $settings;

    public function __construct(?MercadoPagoSettingRepository $settings = null)
    {
        $this->settings = $settings ?? new MercadoPagoSettingRepository();
    }

    public function resolvePublicConfiguration(): array
    {
        $settings = $this->settings->current();
        if (!$settings) {
            return $this->disabledConfiguration();
        }

        return [
            'mode' => $settings->mode,
            'sandbox_enabled' => $settings->sandbox_enabled,
            'production_enabled' => $settings->production_enabled,
            'webhook_validation_mode' => $settings->webhook_validation_mode,
            'pix_enabled' => $settings->pix_enabled,
            'credit_card_enabled' => $settings->credit_card_enabled,
            'pix_expiration_minutes' => $settings->pix_expiration_minutes,
            'max_installments' => $settings->max_installments,
            'fee_pass_to_customer' => $settings->fee_pass_to_customer,
            'fee_calculation_mode' => $settings->fee_calculation_mode,
            'pix_fee_percent' => $settings->pix_fee_percent,
            'pix_fee_fixed' => $settings->pix_fee_fixed,
            'credit_fee_percent' => $settings->credit_fee_percent,
            'credit_fee_fixed' => $settings->credit_fee_fixed,
            'refund_enabled' => $settings->refund_enabled,
            'partial_refund_enabled' => $settings->partial_refund_enabled,
            'cancellation_enabled' => $settings->cancellation_enabled,
            'reconciliation_enabled' => $settings->reconciliation_enabled,
            'binary_mode' => $settings->binary_mode,
            'statement_descriptor' => $settings->statement_descriptor,
            'sandbox_token_configured' => !empty($settings->sandbox_public_key) && !empty($settings->sandbox_access_token),
            'production_token_configured' => !empty($settings->production_public_key) && !empty($settings->production_access_token),
            'sandbox_secret_configured' => !empty($settings->sandbox_webhook_secret),
            'production_secret_configured' => !empty($settings->production_webhook_secret),
            'is_legacy' => false,
        ];
    }

    public function resolveBackendCredentials(): MercadoPagoCredentials
    {
        $settings = $this->settings->current();
        if (!$settings || !in_array($settings->mode, ['sandbox', 'production'], true)) {
            throw new MercadoPagoConfigurationException('Configuração Mercado Pago indisponível.');
        }

        $prefix = $settings->mode . '_';
        $publicKey = $settings->{$prefix . 'public_key'};
        $accessToken = $settings->{$prefix . 'access_token'};
        if (empty($publicKey) || empty($accessToken)) {
            throw new MercadoPagoConfigurationException('Credenciais Mercado Pago indisponíveis.');
        }

        return new MercadoPagoCredentials(
            publicKey: $publicKey,
            accessToken: $accessToken,
            webhookSecret: $settings->{$prefix . 'webhook_secret'},
            mode: $settings->mode,
            collectorId: $settings->{$prefix . 'collector_id'}
        );
    }

    public function resolveAdminConfiguration(): array
    {
        $settings = $this->settings->current();
        if (!$settings) {
            return $this->disabledConfiguration();
        }

        return array_merge($this->resolvePublicConfiguration(), [
            'sandbox_public_key' => $settings->sandbox_public_key,
            'sandbox_collector_id' => $settings->sandbox_collector_id,
            'production_public_key' => $settings->production_public_key,
            'production_collector_id' => $settings->production_collector_id,
        ]);
    }

    public function resolve(): array
    {
        return $this->resolveAdminConfiguration();
    }

    private function disabledConfiguration(): array
    {
        return [
            'mode' => 'sandbox', 'sandbox_enabled' => false, 'production_enabled' => false,
            'pix_enabled' => false, 'credit_card_enabled' => false,
            'sandbox_token_configured' => false, 'production_token_configured' => false,
            'sandbox_secret_configured' => false, 'production_secret_configured' => false,
            'is_legacy' => false,
        ];
    }
}
