<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MercadoPagoSettingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'mode' => 'required|in:sandbox,production',
            'webhook_validation_mode' => 'required|in:api_lookup,signed',
            'fee_calculation_mode' => 'required|in:additive,gross_up',
            'pix_expiration_minutes' => 'required|integer|min:5|max:4320',
            'max_installments' => 'required|integer|min:1|max:12',
            'pix_fee_percent' => 'required|numeric|min:0|max:30',
            'pix_fee_fixed' => 'required|numeric|min:0|max:1000',
            'credit_fee_percent' => 'required|numeric|min:0|max:30',
            'credit_fee_fixed' => 'required|numeric|min:0|max:1000',
            'statement_descriptor' => 'nullable|string|max:200|regex:/^[a-zA-Z0-9\s\-_]+$/',
            'sandbox_public_key' => 'nullable|string|max:255',
            'sandbox_access_token' => 'nullable|string|max:255',
            'sandbox_webhook_secret' => 'nullable|string|max:255',
            'production_public_key' => 'nullable|string|max:255',
            'production_access_token' => 'nullable|string|max:255',
            'production_webhook_secret' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mode = $this->input('mode');
            $isActive = $this->input('status') == 1;
            $webhookMode = $this->input('webhook_validation_mode');

            // Regra: gateway ativo em sandbox exige Public Key e Access Token sandbox
            if ($isActive && $mode === 'sandbox') {
                if (empty($this->input('sandbox_public_key')) && empty($this->input('sandbox_access_token'))) {
                    $validator->errors()->add('sandbox_public_key', 'Gateway ativo em sandbox exige Public Key e Access Token de sandbox.');
                }
            }

            // Regra: gateway ativo em produção exige Public Key e Access Token production
            if ($isActive && $mode === 'production') {
                if (empty($this->input('production_public_key')) && empty($this->input('production_access_token'))) {
                    $validator->errors()->add('production_public_key', 'Gateway ativo em produção exige Public Key e Access Token de produção.');
                }
            }

            // Regra: modo signed exige segredo do ambiente selecionado
            if ($webhookMode === 'signed') {
                if ($mode === 'sandbox' && empty($this->input('sandbox_webhook_secret'))) {
                    $validator->errors()->add('sandbox_webhook_secret', 'Modo signed exige segredo do webhook sandbox.');
                }
                if ($mode === 'production' && empty($this->input('production_webhook_secret'))) {
                    $validator->errors()->add('production_webhook_secret', 'Modo signed exige segredo do webhook de produção.');
                }
            }

            // Regra: remoção da credencial ativa deve ser rejeitada
            if ($isActive && $mode === 'sandbox' && $this->has('remove_sandbox_token')) {
                $validator->errors()->add('remove_sandbox_token', 'Não é possível remover a credencial sandbox enquanto o gateway está ativo.');
            }
            if ($isActive && $mode === 'production' && $this->has('remove_production_token')) {
                $validator->errors()->add('remove_production_token', 'Não é possível remover a credencial de produção enquanto o gateway está ativo.');
            }

            // Regra: remoção de segredo usado por signed deve ser rejeitada
            if ($webhookMode === 'signed') {
                if ($mode === 'sandbox' && $this->has('remove_sandbox_secret')) {
                    $validator->errors()->add('remove_sandbox_secret', 'Não é possível remover o segredo sandbox enquanto o modo de validação é signed.');
                }
                if ($mode === 'production' && $this->has('remove_production_secret')) {
                    $validator->errors()->add('remove_production_secret', 'Não é possível remover o segredo de produção enquanto o modo de validação é signed.');
                }
            }
        });
    }
}
