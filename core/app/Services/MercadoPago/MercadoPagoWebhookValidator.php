<?php
namespace App\Services\MercadoPago;

class MercadoPagoWebhookValidator
{
    public function isValid(array $headers, ?string $dataId, string $secret): bool
    {
        if (empty($secret) || empty($dataId)) {
            return false;
        }

        $xSignature = $headers['x-signature'][0] ?? ($headers['X-Signature'][0] ?? null);
        $xRequestId = $headers['x-request-id'][0] ?? ($headers['X-Request-Id'][0] ?? null);

        if (!$xSignature || !$xRequestId) {
            return false;
        }

        $parts = explode(',', $xSignature);
        $ts = null;
        $hash = null;

        foreach ($parts as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) === 2) {
                if ($kv[0] === 'ts') $ts = $kv[1];
                if ($kv[0] === 'v1') $hash = $kv[1];
            }
        }

        if (!$ts || !$hash || !is_numeric($ts)) {
            return false;
        }

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $expectedHash = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expectedHash, $hash);
    }
}
