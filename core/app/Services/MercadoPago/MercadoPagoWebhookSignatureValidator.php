<?php

namespace App\Services\MercadoPago;

use App\Exceptions\InvalidMercadoPagoWebhookSignatureException;

final class MercadoPagoWebhookSignatureValidator
{
    private const HASH_PATTERN = '/\A[a-f0-9]{64}\z/';

    public function validate(
        ?string $xSignature,
        ?string $xRequestId,
        ?string $dataId,
        string $webhookSecret
    ): void {
        $requestId = $this->normalize($xRequestId);

        if ($webhookSecret === '') {
            $this->fail('missing_secret', $requestId);
        }

        $signature = $this->normalize($xSignature);
        $dataId = $this->normalize($dataId);

        if ($signature === null) {
            $this->fail('missing_signature', $requestId);
        }

        if ($requestId === null) {
            $this->fail('missing_request_id');
        }

        if ($dataId === null) {
            $this->fail('missing_data_id', $requestId);
        }

        [$timestamp, $receivedHash] = $this->parse($signature);

        if ($timestamp === null) {
            $this->fail('missing_timestamp', $requestId);
        }

        if (!ctype_digit($timestamp)) {
            $this->fail('invalid_timestamp', $requestId);
        }

        if ($receivedHash === null) {
            $this->fail('missing_hash', $requestId);
        }

        $receivedHash = strtolower($receivedHash);
        if (!preg_match(self::HASH_PATTERN, $receivedHash)) {
            $this->fail('invalid_hash', $requestId);
        }

        $manifest = 'id:' . strtolower($dataId)
            . ';request-id:' . $requestId
            . ';ts:' . $timestamp . ';';
        $computedHash = hash_hmac('sha256', $manifest, $webhookSecret);

        if (!hash_equals($computedHash, $receivedHash)) {
            $this->fail('signature_mismatch', $requestId);
        }
    }

    /** @return array{0: ?string, 1: ?string} */
    private function parse(string $signature): array
    {
        $timestamp = null;
        $hash = null;

        foreach (explode(',', $signature) as $component) {
            $parts = explode('=', $component, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = strtolower(trim($parts[0]));
            $value = trim($parts[1]);
            if ($key === '' || $value === '') {
                continue;
            }

            if ($key === 'ts') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $hash = $value;
            }
        }

        return [$timestamp, $hash];
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function fail(string $code, ?string $requestId = null): never
    {
        throw new InvalidMercadoPagoWebhookSignatureException($code, $requestId);
    }
}
