<?php
namespace App\Services\MercadoPago;

use App\Models\MercadoPagoSetting;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Log;

class MercadoPagoConfigResolver
{
    public function resolvePublicConfiguration(): array
    {
        $settings = MercadoPagoSetting::where('configuration_key', 'default')->first();

        if ($settings) {
            return [
                'mode' => $settings->mode,
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

        // Fallback legado - configuração pública
        $legacy = PaymentSetting::where('unique_keyword', 'mercadopago')->first();
        if ($legacy) {
            $info = (array) json_decode($legacy->information, true);
            $isSandbox = isset($info['check_sandbox']) && $info['check_sandbox'] == 1;

            return [
                'mode' => $isSandbox ? 'sandbox' : 'production',
                'webhook_validation_mode' => 'api_lookup',
                'pix_enabled' => isset($info['pix_enabled']) && $info['pix_enabled'] == 1,
                'credit_card_enabled' => isset($info['credit_card_enabled']) && $info['credit_card_enabled'] == 1,
                'pix_expiration_minutes' => $info['pix_expiration_minutes'] ?? 30,
                'max_installments' => $info['max_installments'] ?? 1,
                'fee_pass_to_customer' => isset($info['fee_pass_to_customer']) && $info['fee_pass_to_customer'] == 1,
                'fee_calculation_mode' => 'additive',
                'pix_fee_percent' => $info['fee_percent'] ?? 0,
                'pix_fee_fixed' => $info['fee_fixed'] ?? 0,
                'credit_fee_percent' => $info['fee_percent'] ?? 0,
                'credit_fee_fixed' => $info['fee_fixed'] ?? 0,
                'refund_enabled' => false,
                'partial_refund_enabled' => false,
                'cancellation_enabled' => false,
                'reconciliation_enabled' => true,
                'binary_mode' => true,
                'statement_descriptor' => null,
                'sandbox_token_configured' => !empty($info['public_key']) && !empty($info['token']),
                'production_token_configured' => false,
                'sandbox_secret_configured' => false,
                'production_secret_configured' => false,
                'is_legacy' => true,
            ];
        }

        return [];
    }

    public function resolveBackendCredentials(): MercadoPagoCredentials
    {
        $settings = MercadoPagoSetting::where('configuration_key', 'default')->first();

        if ($settings) {
            $mode = $settings->mode;
            $pubKey = $mode === 'production' ? $settings->production_public_key : $settings->sandbox_public_key;
            $token = $mode === 'production' ? $settings->production_access_token : $settings->sandbox_access_token;
            $secret = $mode === 'production' ? $settings->production_webhook_secret : $settings->sandbox_webhook_secret;

            return new MercadoPagoCredentials(
                publicKey: $pubKey,
                accessToken: $token,
                webhookSecret: $secret,
                mode: $mode
            );
        }

        // Fallback legado - credenciais backend
        $legacy = PaymentSetting::where('unique_keyword', 'mercadopago')->first();
        if ($legacy) {
            Log::info('Mercado Pago: Utilizando fallback de configuração legada para credenciais.');
            $info = (array) json_decode($legacy->information, true);
            $isSandbox = isset($info['check_sandbox']) && $info['check_sandbox'] == 1;

            return new MercadoPagoCredentials(
                publicKey: $info['public_key'] ?? '',
                accessToken: $info['token'] ?? '',
                webhookSecret: null,
                mode: $isSandbox ? 'sandbox' : 'production'
            );
        }

        throw new \RuntimeException('Mercado Pago: Nenhuma configuração encontrada.');
    }

    public function resolveAdminConfiguration(): array
    {
        $settings = MercadoPagoSetting::where('configuration_key', 'default')->first();

        if ($settings) {
            return [
                'mode' => $settings->mode,
                'sandbox_public_key' => $settings->sandbox_public_key,
                'production_public_key' => $settings->production_public_key,
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

        // Fallback legado - configuração administrativa
        $legacy = PaymentSetting::where('unique_keyword', 'mercadopago')->first();
        if ($legacy) {
            $info = (array) json_decode($legacy->information, true);
            $isSandbox = isset($info['check_sandbox']) && $info['check_sandbox'] == 1;

            return [
                'mode' => $isSandbox ? 'sandbox' : 'production',
                'sandbox_public_key' => $isSandbox ? ($info['public_key'] ?? null) : null,
                'production_public_key' => !$isSandbox ? ($info['public_key'] ?? null) : null,
                'webhook_validation_mode' => 'api_lookup',
                'pix_enabled' => isset($info['pix_enabled']) && $info['pix_enabled'] == 1,
                'credit_card_enabled' => isset($info['credit_card_enabled']) && $info['credit_card_enabled'] == 1,
                'pix_expiration_minutes' => $info['pix_expiration_minutes'] ?? 30,
                'max_installments' => $info['max_installments'] ?? 1,
                'fee_pass_to_customer' => isset($info['fee_pass_to_customer']) && $info['fee_pass_to_customer'] == 1,
                'fee_calculation_mode' => 'additive',
                'pix_fee_percent' => $info['fee_percent'] ?? 0,
                'pix_fee_fixed' => $info['fee_fixed'] ?? 0,
                'credit_fee_percent' => $info['fee_percent'] ?? 0,
                'credit_fee_fixed' => $info['fee_fixed'] ?? 0,
                'refund_enabled' => false,
                'partial_refund_enabled' => false,
                'cancellation_enabled' => false,
                'reconciliation_enabled' => true,
                'binary_mode' => true,
                'statement_descriptor' => null,
                'sandbox_token_configured' => $isSandbox && !empty($info['public_key']) && !empty($info['token']),
                'production_token_configured' => !$isSandbox && !empty($info['public_key']) && !empty($info['token']),
                'sandbox_secret_configured' => false,
                'production_secret_configured' => false,
                'is_legacy' => true,
            ];
        }

        return [];
    }

    // Método legado para compatibilidade
    public function resolve(): array
    {
        $publicConfig = $this->resolvePublicConfiguration();
        $adminConfig = $this->resolveAdminConfiguration();

        return array_merge($publicConfig, [
            'sandbox_public_key' => $adminConfig['sandbox_public_key'] ?? null,
            'production_public_key' => $adminConfig['production_public_key'] ?? null,
        ]);
    }
}
