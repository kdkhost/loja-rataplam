<?php
namespace App\Services\MercadoPago;

class MercadoPagoCredentials
{
    public function __construct(
        public readonly string $publicKey,
        public readonly string $accessToken,
        public readonly ?string $webhookSecret = null,
        public readonly string $mode = 'sandbox'
    ) {}
}
