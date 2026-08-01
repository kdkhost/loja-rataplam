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
        $fingerprint = $params['request_fingerprint'] ?? null;
        $action = $params['action'];
        $environment = $params['environment'];
        $orderId = $params['order_id'] ?? null;
        $paymentId = $params['payment_id'] ?? null;

        try {
            return MercadoPagoAction::create([
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'environment' => $environment,
                'action' => $action,
                'requested_amount' => $params['requested_amount'] ?? null,
                'currency' => $params['currency'] ?? null,
                'idempotency_key' => $idempotencyKey,
                'request_fingerprint' => $fingerprint,
                'local_status' => 'processing',
                'performed_by_admin_id' => $params['performed_by_admin_id'] ?? null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar especificamente violação UNIQUE em idempotency_key
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'idempotency_key')) {
                // Recuperar a ação que venceu a corrida
                $existing = MercadoPagoAction::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    // Verificar compatibilidade da ação existente
                    if ($this->isActionIncompatible($existing, $fingerprint, $action, $environment, $orderId, $paymentId)) {
                        throw new \App\Exceptions\MercadoPagoOperationException(
                            'A chave de idempotência já está em uso para uma operação diferente.'
                        );
                    }
                    return $existing;
                }
            }
            // Outros erros de banco não são ocultados
            throw $e;
        }
    }

    private function isActionIncompatible(
        MercadoPagoAction $existing,
        ?string $newFingerprint,
        string $newAction,
        string $newEnvironment,
        ?string $newOrderId,
        ?string $newPaymentId
    ): bool {
        // Comparar fingerprint se ambos existirem
        if ($existing->request_fingerprint && $newFingerprint && $existing->request_fingerprint !== $newFingerprint) {
            return true;
        }

        // Comparar action
        if ($existing->action !== $newAction) {
            return true;
        }

        // Comparar environment
        if ($existing->environment !== $newEnvironment) {
            return true;
        }

        // Comparar order_id se ambos existirem
        if ($existing->order_id && $newOrderId && $existing->order_id !== $newOrderId) {
            return true;
        }

        // Comparar payment_id se ambos existirem
        if ($existing->payment_id && $newPaymentId && $existing->payment_id !== $newPaymentId) {
            return true;
        }

        return false;
    }

    public function completeAction(MercadoPagoAction $action, MercadoPagoResponse $response): void
    {
        // Política: não converter success novamente para failed ou processing
        if ($action->local_status === 'success') {
            return;
        }

        // Política: processing não pode ser convertido para failed automaticamente
        // (requer intervenção manual ou nova tentativa com chave diferente)
        if ($action->local_status === 'processing' && !$response->successful) {
            return;
        }

        // Política: failed não repete automaticamente com a mesma chave
        // (requer nova chave para tentar novamente)
        if ($action->local_status === 'failed') {
            return;
        }

        // Atualizar status apenas se for processing
        if ($action->local_status === 'processing') {
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
}
