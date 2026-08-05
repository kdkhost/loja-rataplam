<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class MercadoPagoSetting extends Model
{
    protected $table = 'mercadopago_settings';

    protected $fillable = [
        'mode',
        'sandbox_enabled',
        'production_enabled',
        'sandbox_public_key',
        'sandbox_access_token',
        'sandbox_collector_id',
        'production_public_key',
        'production_access_token',
        'production_collector_id',
        'sandbox_webhook_secret',
        'production_webhook_secret',
        'webhook_validation_mode',
        'pix_enabled',
        'credit_card_enabled',
        'pix_expiration_minutes',
        'max_installments',
        'fee_pass_to_customer',
        'fee_calculation_mode',
        'pix_fee_percent',
        'pix_fee_fixed',
        'credit_fee_percent',
        'credit_fee_fixed',
        'refund_enabled',
        'partial_refund_enabled',
        'cancellation_enabled',
        'reconciliation_enabled',
        'binary_mode',
        'statement_descriptor',
    ];

    protected $hidden = [
        'sandbox_access_token',
        'production_access_token',
        'sandbox_webhook_secret',
        'production_webhook_secret',
    ];

    protected $casts = [
        'sandbox_enabled' => 'boolean',
        'production_enabled' => 'boolean',
        'sandbox_access_token' => 'encrypted',
        'production_access_token' => 'encrypted',
        'sandbox_webhook_secret' => 'encrypted',
        'production_webhook_secret' => 'encrypted',
        'pix_enabled' => 'boolean',
        'credit_card_enabled' => 'boolean',
        'fee_pass_to_customer' => 'boolean',
        'refund_enabled' => 'boolean',
        'partial_refund_enabled' => 'boolean',
        'cancellation_enabled' => 'boolean',
        'reconciliation_enabled' => 'boolean',
        'binary_mode' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Proteger o singleton: configuration_key deve ser sempre 'default'
            $model->configuration_key = 'default';
        });
    }
}
