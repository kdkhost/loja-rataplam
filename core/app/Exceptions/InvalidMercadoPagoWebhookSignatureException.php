<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidMercadoPagoWebhookSignatureException extends RuntimeException
{
    public function __construct(
        private readonly string $failureCode,
        private readonly ?string $requestId = null
    ) {
        parent::__construct('Assinatura do webhook invalida.');
    }

    public function failureCode(): string
    {
        return $this->failureCode;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }
}
