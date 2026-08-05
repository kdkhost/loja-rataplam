<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MercadoPagoSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => 'required|in:sandbox,production',
            'sandbox_public_key' => 'nullable|string|max:255',
            'sandbox_access_token' => 'nullable|string|max:255',
            'sandbox_collector_id' => 'nullable|string|max:255|regex:/^[A-Za-z0-9_-]+$/',
            'sandbox_webhook_secret' => 'nullable|string|max:255',
            'production_public_key' => 'nullable|string|max:255',
            'production_access_token' => 'nullable|string|max:255',
            'production_collector_id' => 'nullable|string|max:255|regex:/^[A-Za-z0-9_-]+$/',
            'production_webhook_secret' => 'nullable|string|max:255',
            'remove_sandbox_token' => 'nullable|boolean',
            'remove_sandbox_secret' => 'nullable|boolean',
            'remove_production_token' => 'nullable|boolean',
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
            'statement_descriptor' => 'nullable|string|max:22|regex:/^[a-zA-Z0-9 ]+$/',
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['pix_enabled', 'credit_card_enabled', 'fee_pass_to_customer', 'refund_enabled',
            'partial_refund_enabled', 'cancellation_enabled', 'reconciliation_enabled', 'binary_mode',
            'remove_sandbox_token', 'remove_sandbox_secret', 'remove_production_token',
            'remove_production_secret'] as $field) {
            $this->merge([$field => $this->boolean($field)]);
        }

        if (is_string($this->input('statement_descriptor'))) {
            $this->merge(['statement_descriptor' => preg_replace('/\s+/', ' ', trim($this->input('statement_descriptor')))]);
        }
        foreach (['pix_fee_percent', 'pix_fee_fixed', 'credit_fee_percent', 'credit_fee_fixed'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => str_replace(',', '.', $this->input($field))]);
            }
        }
    }
}
