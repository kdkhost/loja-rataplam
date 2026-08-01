<?php
namespace App\Services\MercadoPago;
class MercadoPagoResponse
{
    public bool $successful;
    public ?int $httpStatus;
    public array $data;
    public ?string $errorCode;
    public ?string $safeMessage;
    public ?string $requestId;
    public ?int $retryAfter;

    public function __construct(
        bool $successful,
        ?int $httpStatus,
        array $data = [],
        ?string $errorCode = null,
        ?string $safeMessage = null,
        ?string $requestId = null,
        ?int $retryAfter = null
    ) {
        $this->successful = $successful;
        $this->httpStatus = $httpStatus;
        $this->data = $data;
        $this->errorCode = $errorCode;
        $this->safeMessage = $safeMessage;
        $this->requestId = $requestId;
        $this->retryAfter = $retryAfter;
    }
}
