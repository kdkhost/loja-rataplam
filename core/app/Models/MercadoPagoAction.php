<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MercadoPagoAction extends Model
{
    protected $table = 'mercadopago_actions';

    protected $fillable = [
        'order_id',
        'payment_id',
        'environment',
        'action',
        'requested_amount',
        'currency',
        'idempotency_key',
        'request_fingerprint',
        'mercadopago_operation_id',
        'remote_status',
        'local_status',
        'http_status',
        'response_summary',
        'error_code',
        'performed_by_admin_id',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'response_summary' => 'array',
    ];

    /**
     * Scope a query to only include successful actions.
     */
    public function scopeSuccessful(Builder $query)
    {
        return $query->where('local_status', 'success');
    }

    /**
     * Scope a query to only include failed actions.
     */
    public function scopeFailed(Builder $query)
    {
        return $query->where('local_status', 'failed');
    }

    /**
     * Scope a query to only include actions for a specific payment.
     */
    public function scopeForPayment(Builder $query, $paymentId)
    {
        return $query->where('payment_id', (string) $paymentId);
    }

    /**
     * Scope a query to only include actions for a specific order.
     */
    public function scopeForOrder(Builder $query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope a query to only include actions with a specific idempotency key.
     */
    public function scopeWithIdempotencyKey(Builder $query, $key)
    {
        return $query->where('idempotency_key', $key);
    }
}
