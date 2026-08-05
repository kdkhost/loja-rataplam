<?php
namespace App\Services\MercadoPago;

use App\Exceptions\InvalidMercadoPagoWebhookSignatureException;

class MercadoPagoWebhookValidator
{
    public function isValid(array $headers, ?string $dataId, string $secret): bool
    {
        $xSignature = $headers['x-signature'][0] ?? ($headers['X-Signature'][0] ?? null);
        $xRequestId = $headers['x-request-id'][0] ?? ($headers['X-Request-Id'][0] ?? null);

        try {
            app(MercadoPagoWebhookSignatureValidator::class)->validate(
                $xSignature,
                $xRequestId,
                $dataId,
                $secret
            );

            return true;
        } catch (InvalidMercadoPagoWebhookSignatureException) {
            return false;
        }
    }
}
