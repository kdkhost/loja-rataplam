<?php
namespace App\Services\MercadoPago;

use App\Models\MercadoPagoAction;
use Illuminate\Support\Str;

class MercadoPagoIdempotencyService
{
    public function generateKey(): string
    {
        return Str::uuid()->toString();
    }

    public function generateFingerprint(array $data): string
    {
        // Sort keys to ensure consistent hashing
        ksort($data);
        return hash('sha256', json_encode($data));
    }

    public function initiateAction(array $params): MercadoPagoAction
    {
        $existing = MercadoPagoAction::where('idempotency_key', $params['idempotency_key'])->first();
        if ($existing) {
            return $existing;
        }

        return MercadoPagoAction::create([
            'order_id' => $params['order_id'] ?? null,
            'payment_id' => $params['payment_id'] ?? null,
            'environment' => $params['environment'],
            'action' => $params['action'],
            'requested_amount' => $params['requested_amount'] ?? null,
            'currency' => $params['currency'] ?? null,
            'idempotency_key' => $params['idempotency_key'],
            'request_fingerprint' => $params['request_fingerprint'] ?? null,
            'local_status' => 'processing',
            'performed_by_admin_id' => $params['performed_by_admin_id'] ?? null,
        ]);
    }

    public function completeAction(MercadoPagoAction $action, MercadoPagoResponse $response): void
    {
        $action->local_status = $response->successful ? 'success' : 'failed';
        $action->http_status = $response->httpStatus;
        $action->error_code = $response->errorCode;
        $action->remote_status = $response->data['status'] ?? null;
        $action->mercadopago_operation_id = $response->data['id'] ?? null;
        
        $action->response_summary = [
            'safe_message' => $response->safeMessage,
            'request_id' => $response->requestId,
        ];
        
        $action->save();
    }
}
