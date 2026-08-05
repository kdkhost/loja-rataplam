<?php

namespace App\Services\MercadoPago;

use MercadoPago;

class MercadoPagoLegacyClient
{
    public function configure(string $accessToken): void
    {
        MercadoPago\SDK::setAccessToken($accessToken);
    }

    public function newPayment(): object
    {
        return new MercadoPago\Payment();
    }

    public function savePayment(object $payment): void
    {
        $payment->save();
    }

    public function findPayment(string $paymentId): ?object
    {
        return MercadoPago\Payment::find_by_id($paymentId);
    }
}
