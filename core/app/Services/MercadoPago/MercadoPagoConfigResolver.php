<?php
namespace App\Services\MercadoPago;

use App\Models\MercadoPagoSetting;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Log;

class MercadoPagoConfigResolver
{
    public function resolve(): array
    {
        $settings = MercadoPagoSetting::first();

        if ($settings) {
            $mode = $settings->mode;
            $pubKey = $mode === 'production' ? $settings->production_public_key : $settings->sandbox_public_key;
            $token = $mode === 'production' ? $settings->production_access_token : $settings->sandbox_access_token;
            $secret = $mode === 'production' ? $settings->production_webhook_secret : $settings->sandbox_webhook_secret;

            return [
                'mode' => $mode,
                'public_key' => $pubKey,
                'access_token' => $token,
                'webhook_secret' => $secret,
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
                'sandbox_secret_configured' => !empty($settings->sandbox_webhook_secret),
                'production_secret_configured' => !empty($settings->production_webhook_secret),
                'is_legacy' => false,
            ];
        }

        // Fallback legado
        $legacy = PaymentSetting::where('unique_keyword', 'mercadopago')->first();
        if ($legacy) {
            Log::info('Mercado Pago: Utilizando fallback de configuração legada.');
            $info = (array) json_decode($legacy->information, true);
            $isSandbox = isset($info['check_sandbox']) && $info['check_sandbox'] == 1;
            
            return [
                'mode' => $isSandbox ? 'sandbox' : 'production',
                'public_key' => $info['public_key'] ?? null,
                'access_token' => $info['token'] ?? null,
                'webhook_secret' => null,
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
                'sandbox_secret_configured' => false,
                'production_secret_configured' => false,
                'is_legacy' => true,
            ];
        }

        return [];
    }
}
