<?php
namespace App\Services\MercadoPago;

use App\Models\MercadoPagoAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MercadoPagoIdempotencyService
{
    // Lease duration: connect timeout (5s) + request timeout (20s) + safety margin (10s) = 35s
    private const LEASE_DURATION_SECONDS = 35;

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

    /**
     * Adquire a tentativa de execução com ownership exclusivo
     */
    public function acquireAction(array $params): MercadoPagoIdempotencyAcquisitionResult
    {
        $idempotencyKey = $params['idempotency_key'];
        $fingerprint = $params['request_fingerprint'] ?? null;
        $action = $params['action'];
        $environment = $params['environment'];
        $orderId = $params['order_id'] ?? null;
        $paymentId = $params['payment_id'] ?? null;

        $executionOwner = Str::uuid()->toString();
        $now = now();
        $leaseExpiresAt = $now->copy()->addSeconds(self::LEASE_DURATION_SECONDS);

        try {
            // Tentativa 1: Criar novo registro com ownership
            $newAction = MercadoPagoAction::create([
                'order_id' => $orderId,
                'payment_id' => $paymentId,
                'environment' => $environment,
                'action' => $action,
                'requested_amount' => $params['requested_amount'] ?? null,
                'currency' => $params['currency'] ?? null,
                'idempotency_key' => $idempotencyKey,
                'request_fingerprint' => $fingerprint,
                'local_status' => 'pending',
                'execution_owner' => $executionOwner,
                'execution_started_at' => $now,
                'execution_lease_expires_at' => $leaseExpiresAt,
                'performed_by_admin_id' => $params['performed_by_admin_id'] ?? null,
            ]);

            return MercadoPagoIdempotencyAcquisitionResult::acquiredNew($newAction, $executionOwner);
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar especificamente violação UNIQUE em idempotency_key
            if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'idempotency_key')) {
                // Recuperar a ação existente
                $existing = MercadoPagoAction::where('idempotency_key', $idempotencyKey)->first();
                if (!$existing) {
                    // Caso raro: registro deletado entre o erro e a consulta
                    throw $e;
                }

                // Verificar compatibilidade da ação existente
                if ($this->isActionIncompatible($existing, $fingerprint, $action, $environment, $orderId, $paymentId)) {
                    throw new \App\Exceptions\MercadoPagoOperationException(
                        'A chave de idempotência já está em uso para uma operação diferente.'
                    );
                }

                // Verificar status existente
                return $this->handleExistingAction($existing, $executionOwner, $leaseExpiresAt);
            }
            // Outros erros de banco não são ocultados
            throw $e;
        }
    }

    /**
     * Lida com ação existente determinando se pode readquirir ownership
     */
    private function handleExistingAction(
        MercadoPagoAction $existing,
        string $executionOwner,
        \Illuminate\Support\Carbon $leaseExpiresAt
    ): MercadoPagoIdempotencyAcquisitionResult {
        // Se já completou, retornar resultado existente
        if ($existing->local_status === 'success') {
            return MercadoPagoIdempotencyAcquisitionResult::existingSuccess($existing);
        }

        if ($existing->local_status === 'failed') {
            return MercadoPagoIdempotencyAcquisitionResult::existingFailed($existing);
        }

        if ($existing->local_status === 'unknown') {
            return MercadoPagoIdempotencyAcquisitionResult::existingUnknown($existing);
        }

        // Se está pending, verificar lease
        if ($existing->local_status === 'pending') {
            // Se lease ainda válido, não pode executar
            if ($existing->execution_lease_expires_at && $existing->execution_lease_expires_at->isFuture()) {
                return MercadoPagoIdempotencyAcquisitionResult::existingInProgress($existing);
            }

            // Lease expirado: tentar readquirir atomicamente
            $updated = MercadoPagoAction::where('id', $existing->id)
                ->where('local_status', 'pending')
                ->where(function ($query) {
                    $query->whereNull('execution_lease_expires_at')
                          ->orWhere('execution_lease_expires_at', '<=', now());
                })
                ->update([
                    'execution_owner' => $executionOwner,
                    'execution_started_at' => now(),
                    'execution_lease_expires_at' => $leaseExpiresAt,
                ]);

            if ($updated > 0) {
                // Conseguiu readquirir
                $existing->refresh();
                return MercadoPagoIdempotencyAcquisitionResult::acquiredStale($existing, $executionOwner);
            }

            // Outro processo readquiriu primeiro
            $existing->refresh();
            return MercadoPagoIdempotencyAcquisitionResult::existingInProgress($existing);
        }

        // Status desconhecido
        return MercadoPagoIdempotencyAcquisitionResult::existingUnknown($existing);
    }

    /**
     * Método legado para compatibilidade
     * @deprecated Use acquireAction() instead
     */
    public function initiateAction(array $params): MercadoPagoAction
    {
        $result = $this->acquireAction($params);
        return $result->action;
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

    public function completeAction(MercadoPagoAction $action, MercadoPagoResponse $response, ?string $executionOwner = null): void
    {
        // Política: não converter success novamente para failed ou pending
        if ($action->local_status === 'success') {
            return;
        }

        // Política: failed não repete automaticamente com a mesma chave
        // (requer nova chave para tentar novamente)
        if ($action->local_status === 'failed') {
            return;
        }

        // Atualizar status se for pending ou unknown
        if (in_array($action->local_status, ['pending', 'unknown'])) {
            // Verificar ownership se fornecido
            if ($executionOwner !== null) {
                $updated = MercadoPagoAction::where('id', $action->id)
                    ->where('execution_owner', $executionOwner)
                    ->update([
                        'local_status' => $response->successful ? 'success' : 'failed',
                        'http_status' => $response->httpStatus,
                        'error_code' => $response->errorCode,
                        'remote_status' => $response->data['status'] ?? null,
                        'mercadopago_operation_id' => $response->data['id'] ?? null,
                        'response_summary' => [
                            'safe_message' => $response->safeMessage,
                            'request_id' => $response->requestId,
                        ],
                    ]);

                if ($updated === 0) {
                    // Perdeu ownership - recarregar e não sobrescrever
                    $action->refresh();
                    return;
                }
            } else {
                // Sem verificação de ownership (compatibilidade)
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

    /**
     * Marca ação como unknown quando timeout ocorre após possível envio
     * Protegido por ownership para evitar sobrescrita por proprietário antigo
     */
    public function markAsUnknown(MercadoPagoAction $action, ?string $executionOwner = null): void
    {
        // Política: não converter success ou failed para unknown
        if ($action->local_status === 'success' || $action->local_status === 'failed') {
            return;
        }

        // Só permite marcar pending como unknown
        if ($action->local_status !== 'pending') {
            return;
        }

        // Verificar ownership se fornecido
        if ($executionOwner !== null) {
            $updated = MercadoPagoAction::where('id', $action->id)
                ->where('execution_owner', $executionOwner)
                ->where('local_status', 'pending')
                ->update(['local_status' => 'unknown']);

            if ($updated === 0) {
                // Perdeu ownership - recarregar e não sobrescrever
                $action->refresh();
                return;
            }
        } else {
            // Sem verificação de ownership (compatibilidade)
            $action->local_status = 'unknown';
            $action->save();
        }
    }
}
