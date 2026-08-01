<?php
namespace App\Http\Requests;

use App\Models\MercadoPagoSetting;
use Illuminate\Foundation\Http\FormRequest;

class MercadoPagoSettingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'nullable|boolean',
            'name' => 'nullable|string|max:255',
            'photo' => 'nullable|image',
            'text' => 'nullable|string',
            'mode' => 'required|in:sandbox,production',
            'sandbox_public_key' => 'nullable|string|max:255',
            'sandbox_access_token' => 'nullable|string|max:255',
            'remove_sandbox_token' => 'nullable|boolean',
            'sandbox_webhook_secret' => 'nullable|string|max:255',
            'remove_sandbox_secret' => 'nullable|boolean',
            'production_public_key' => 'nullable|string|max:255',
            'production_access_token' => 'nullable|string|max:255',
            'remove_production_token' => 'nullable|boolean',
            'production_webhook_secret' => 'nullable|string|max:255',
            'remove_production_secret' => 'nullable|boolean',
            'webhook_validation_mode' => 'required|in:api_lookup,signed',
            'pix_enabled' => 'nullable|boolean',
            'credit_card_enabled' => 'nullable|boolean',
            'pix_expiration_minutes' => 'nullable|integer|min:5|max:4320',
            'max_installments' => 'nullable|integer|min:1|max:12',
            'fee_pass_to_customer' => 'nullable|boolean',
            'fee_calculation_mode' => 'required|in:additive,gross_up',
            'pix_fee_percent' => 'nullable|numeric|min:0|max:30',
            'pix_fee_fixed' => 'nullable|numeric|min:0|max:1000',
            'credit_fee_percent' => 'nullable|numeric|min:0|max:30',
            'credit_fee_fixed' => 'nullable|numeric|min:0|max:1000',
            'refund_enabled' => 'nullable|boolean',
            'partial_refund_enabled' => 'nullable|boolean',
            'cancellation_enabled' => 'nullable|boolean',
            'reconciliation_enabled' => 'nullable|boolean',
            'binary_mode' => 'nullable|boolean',
            'statement_descriptor' => 'nullable|string|max:22|ascii',
        ];
    }

    public function messages()
    {
        return [
            'mode.required' => 'O ambiente é obrigatório.',
            'mode.in' => 'O ambiente deve ser sandbox ou produção.',
            'webhook_validation_mode.required' => 'O modo de validação do webhook é obrigatório.',
            'webhook_validation_mode.in' => 'O modo de validação deve ser API lookup ou Assinatura + API lookup.',
            'pix_expiration_minutes.min' => 'A expiração do Pix deve ser de no mínimo 5 minutos.',
            'pix_expiration_minutes.max' => 'A expiração do Pix deve ser de no máximo 4320 minutos (72 horas).',
            'max_installments.min' => 'O número mínimo de parcelas é 1.',
            'max_installments.max' => 'O número máximo de parcelas é 12.',
            'pix_fee_percent.min' => 'A taxa percentual Pix não pode ser negativa.',
            'pix_fee_percent.max' => 'A taxa percentual Pix não pode exceder 30%.',
            'credit_fee_percent.min' => 'A taxa percentual Crédito não pode ser negativa.',
            'credit_fee_percent.max' => 'A taxa percentual Crédito não pode exceder 30%.',
            'statement_descriptor.ascii' => 'O descritor deve conter apenas caracteres ASCII.',
            'statement_descriptor.max' => 'O descritor não pode exceder 22 caracteres.',
        ];
    }

    protected function prepareForValidation()
    {
        // Normalizar checkboxes para boolean
        $checkboxFields = [
            'status', 'pix_enabled', 'credit_card_enabled', 'fee_pass_to_customer',
            'refund_enabled', 'partial_refund_enabled', 'cancellation_enabled',
            'reconciliation_enabled', 'binary_mode',
            'remove_sandbox_token', 'remove_sandbox_secret',
            'remove_production_token', 'remove_production_secret',
        ];

        foreach ($checkboxFields as $field) {
            if ($this->has($field)) {
                $this->merge([$field => (bool) $this->input($field)]);
            } else {
                $this->merge([$field => false]);
            }
        }

        // Normalizar statement_descriptor: trim e espaços internos
        if ($this->has('statement_descriptor')) {
            $descriptor = $this->input('statement_descriptor');
            if (is_string($descriptor)) {
                $descriptor = trim($descriptor);
                $descriptor = preg_replace('/\s+/', ' ', $descriptor);
                $this->merge(['statement_descriptor' => $descriptor]);
            }
        }

        // Normalizar valores decimais: vírgula para ponto
        $decimalFields = [
            'pix_fee_percent', 'pix_fee_fixed',
            'credit_fee_percent', 'credit_fee_fixed',
        ];

        foreach ($decimalFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if (is_string($value)) {
                    $value = str_replace(',', '.', $value);
                    $this->merge([$field => $value]);
                }
            }
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $settings = MercadoPagoSetting::where('configuration_key', 'default')->first();
            $mode = $this->input('mode');
            $isActive = $this->input('status', false);
            $webhookMode = $this->input('webhook_validation_mode');

            // Calcular valores efetivos das credenciais
            $sandboxPublicKey = $this->getEffectiveValue(
                $this->input('sandbox_public_key'),
                $settings?->sandbox_public_key
            );
            $sandboxAccessToken = $this->getEffectiveValue(
                $this->input('sandbox_access_token'),
                $settings?->sandbox_access_token,
                $this->input('remove_sandbox_token')
            );
            $sandboxWebhookSecret = $this->getEffectiveValue(
                $this->input('sandbox_webhook_secret'),
                $settings?->sandbox_webhook_secret,
                $this->input('remove_sandbox_secret')
            );

            $productionPublicKey = $this->getEffectiveValue(
                $this->input('production_public_key'),
                $settings?->production_public_key
            );
            $productionAccessToken = $this->getEffectiveValue(
                $this->input('production_access_token'),
                $settings?->production_access_token,
                $this->input('remove_production_token')
            );
            $productionWebhookSecret = $this->getEffectiveValue(
                $this->input('production_webhook_secret'),
                $settings?->production_webhook_secret,
                $this->input('remove_production_secret')
            );

            // Validação individual: gateway ativo em sandbox exige Public Key
            if ($isActive && $mode === 'sandbox' && empty($sandboxPublicKey)) {
                $validator->errors()->add('sandbox_public_key', 'Gateway ativo em sandbox exige Public Key.');
            }

            // Validação individual: gateway ativo em sandbox exige Access Token
            if ($isActive && $mode === 'sandbox' && empty($sandboxAccessToken)) {
                $validator->errors()->add('sandbox_access_token', 'Gateway ativo em sandbox exige Access Token.');
            }

            // Validação individual: gateway ativo em produção exige Public Key
            if ($isActive && $mode === 'production' && empty($productionPublicKey)) {
                $validator->errors()->add('production_public_key', 'Gateway ativo em produção exige Public Key.');
            }

            // Validação individual: gateway ativo em produção exige Access Token
            if ($isActive && $mode === 'production' && empty($productionAccessToken)) {
                $validator->errors()->add('production_access_token', 'Gateway ativo em produção exige Access Token.');
            }

            // Modo signed exige segredo efetivo do ambiente selecionado
            if ($webhookMode === 'signed') {
                if ($mode === 'sandbox' && empty($sandboxWebhookSecret)) {
                    $validator->errors()->add('sandbox_webhook_secret', 'Modo signed exige segredo do webhook sandbox.');
                }
                if ($mode === 'production' && empty($productionWebhookSecret)) {
                    $validator->errors()->add('production_webhook_secret', 'Modo signed exige segredo do webhook de produção.');
                }
            }

            // Remoção da credencial ativa deve ser rejeitada
            if ($isActive && $mode === 'sandbox' && $this->input('remove_sandbox_token')) {
                $validator->errors()->add('remove_sandbox_token', 'Não é possível remover a credencial sandbox enquanto o gateway está ativo.');
            }
            if ($isActive && $mode === 'production' && $this->input('remove_production_token')) {
                $validator->errors()->add('remove_production_token', 'Não é possível remover a credencial de produção enquanto o gateway está ativo.');
            }

            // Remoção do segredo ativo em signed deve ser rejeitada
            if ($webhookMode === 'signed') {
                if ($mode === 'sandbox' && $this->input('remove_sandbox_secret')) {
                    $validator->errors()->add('remove_sandbox_secret', 'Não é possível remover o segredo sandbox enquanto o modo de validação é signed.');
                }
                if ($mode === 'production' && $this->input('remove_production_secret')) {
                    $validator->errors()->add('remove_production_secret', 'Não é possível remover o segredo de produção enquanto o modo de validação é signed.');
                }
            }
        });
    }

    private function getEffectiveValue(?string $newValue, ?string $persistedValue, ?bool $shouldRemove = null): ?string
    {
        // Se remoção foi solicitada, retorna null
        if ($shouldRemove === true) {
            return null;
        }

        // Se novo valor foi preenchido, usa o novo valor
        if ($newValue !== null && $newValue !== '') {
            return $newValue;
        }

        // Caso contrário, preserva o valor persistido
        return $persistedValue;
    }
}
