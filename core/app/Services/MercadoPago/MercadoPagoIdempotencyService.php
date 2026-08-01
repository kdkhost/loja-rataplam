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
        // Allowlist de campos não sensíveis
        $allowedFields = [
            'order_id',
            'action',
            'payment_id',
            'environment',
            'amount_in_cents',
            'currency',
        ];

        // Filtrar apenas campos permitidos
        $filtered = array_intersect_key($data, array_flip($allowedFields));

        // Ordenar recursivamente arrays e objetos
        $canonical = $this->canonicalize($filtered);

        return hash('sha256', json_encode($canonical));
    }

    private function canonicalize($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $canonical = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $canonical[$key] = $this->canonicalize($value);
            } else {
                $canonical[$key] = $value;
            }
        }

        // Ordenar por chave
        ksort($canonical);
        return $canonical;
    }

    public function initiateAction(array $params): MercadoPagoAction
    {
        $idempotencyKey = $params['idempotency_key'];

        try {
            return MercadoPagoAction::create([
                'order_id' => $params['order_id'] ?? null,
                'payment_id' => $params['payment_id'] ?? null,
                'environment' => $params['environment'],
                'action' => $params['action'],
                'requested_amount' => $params['requested_amount'] ?? null,
                'currency' => $params['currency'] ?? null,
                'idempotency_key' => $idempotencyKey,
                'request_fingerprint' => $params['request_fingerprint'] ?? null,
                'local_status' => 'processing',
                'performed_by_admin_id' => $params['performed_by_admin_id'] ?? null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar especificamente violação UNIQUE em idempotency_key
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'idempotency_key')) {
                // Recuperar a ação que venceu a corrida
                $existing = MercadoPagoAction::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    return $existing;
                }
            }
            // Outros erros de banco não são ocultados
            throw $e;
        }
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
