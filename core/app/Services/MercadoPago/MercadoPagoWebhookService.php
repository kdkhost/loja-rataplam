<?php

namespace App\Services\MercadoPago;

use App\Exceptions\MercadoPagoApiException;
use App\Exceptions\MercadoPagoOperationException;
use App\Models\MercadoPagoAction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookService
{
    protected ?MercadoPagoClient $client;

    public function __construct(
        ?MercadoPagoClient $client = null,
        protected ?MercadoPagoConfigResolver $configResolver = null,
        protected ?MercadoPagoWebhookValidator $validator = null,
        protected ?MercadoPagoIdempotencyService $idempotencyService = null,
        protected ?MercadoPagoMoney $money = null
    ) {
        $this->client = $client;
        $this->configResolver ??= app(MercadoPagoConfigResolver::class);
        $this->validator ??= app(MercadoPagoWebhookValidator::class);
        $this->idempotencyService ??= app(MercadoPagoIdempotencyService::class);
        $this->money ??= app(MercadoPagoMoney::class);
    }

    public function setClient(?MercadoPagoClient $client): void
    {
        $this->client = $client;
    }

    public function setConfigResolver(?MercadoPagoConfigResolver $resolver): void
    {
        $this->configResolver = $resolver;
    }

    public function setValidator(?MercadoPagoWebhookValidator $validator): void
    {
        $this->validator = $validator;
    }

    /**
     * Compatibilidade interna para os testes anteriores. Rotas HTTP devem usar
     * handleVerifiedNotification(), depois da validacao dos headers oficiais.
     */
    public function handleNotification(array $notification): array
    {
        $paymentId = isset($notification['data']['id']) ? (string) $notification['data']['id'] : '';
        if ($paymentId === '') {
            throw new MercadoPagoApiException('ID do pagamento nao fornecido.');
        }

        $config = $this->configResolver->resolve();
        $secret = $config['mode'] === 'production'
            ? ($config['production_webhook_secret'] ?? null)
            : ($config['sandbox_webhook_secret'] ?? null);

        if (!empty($secret)) {
            $signature = $notification['signature'] ?? [];
            $headers = [
                'x-signature' => ['ts=' . ($signature['ts'] ?? '') . ',v1=' . ($signature['v1'] ?? '')],
                'x-request-id' => [$notification['request_id'] ?? null],
            ];
            if (!$this->validator->isValid($headers, $paymentId, $secret)) {
                throw new MercadoPagoApiException('Assinatura do webhook invalida.');
            }
        }

        return $this->processVerifiedPayment($paymentId, false);
    }

    public function handleVerifiedNotification(string $paymentId): array
    {
        if ($paymentId === '') {
            throw new MercadoPagoApiException('ID do pagamento nao fornecido.');
        }

        return $this->processVerifiedPayment($paymentId, true);
    }

    private function processVerifiedPayment(string $paymentId, bool $enforceLocalOrder): array
    {
        $config = $this->configResolver->resolve();
        $paymentData = $this->getPaymentFromApi($paymentId);
        $this->validatePaymentData($paymentData, $config);

        $creationAction = $enforceLocalOrder
            ? $this->validateLocalPayment($paymentData, $config)
            : null;

        $idempotencyKey = $this->idempotencyService->generateDeterministicKey([
            'action' => 'webhook_notification',
            'environment' => $config['mode'],
            'payment_id' => (string) $paymentData['id'],
            'remote_status' => (string) ($paymentData['status'] ?? ''),
        ]);
        $fingerprint = $this->idempotencyService->generateFingerprint([
            'action' => 'webhook_notification',
            'environment' => $config['mode'],
            'payment_id' => (string) $paymentData['id'],
            'order_id' => $creationAction?->order_id ?? ($paymentData['external_reference'] ?? null),
        ]);

        $acquisition = $this->idempotencyService->acquireAction([
            'idempotency_key' => $idempotencyKey,
            'request_fingerprint' => $fingerprint,
            'action' => 'webhook_notification',
            'environment' => $config['mode'],
            'order_id' => $creationAction?->order_id,
            'payment_id' => (string) $paymentData['id'],
            'requested_amount' => $creationAction?->requested_amount,
            'currency' => $paymentData['currency_id'] ?? null,
        ]);

        if (!$acquisition->canExecuteRemoteCall()) {
            return [
                'status' => 'already_processed',
                'payment_id' => (string) $paymentData['id'],
            ];
        }

        if ($enforceLocalOrder) {
            $this->applyOrderTransition($creationAction, $paymentData);
        }

        $response = new MercadoPagoResponse(true, 200, [
            'id' => (string) $paymentData['id'],
            'status' => (string) ($paymentData['status'] ?? ''),
        ], null, 'Success');
        $this->idempotencyService->completeAction(
            $acquisition->action,
            $response,
            $acquisition->executionOwner
        );

        Log::info('Webhook Mercado Pago processado', [
            'payment_id' => (string) $paymentData['id'],
            'remote_status' => (string) ($paymentData['status'] ?? ''),
        ]);

        return [
            'status' => 'processed',
            'payment_id' => (string) $paymentData['id'],
            'payment_status' => (string) ($paymentData['status'] ?? ''),
        ];
    }

    private function ensureClient(): void
    {
        if ($this->client === null) {
            $credentials = $this->configResolver->resolveBackendCredentials();
            $this->client = new MercadoPagoClient($credentials->accessToken);
        }
    }

    private function getPaymentFromApi(string $paymentId): array
    {
        $this->ensureClient();
        $response = $this->client->getPayment($paymentId);

        if (!$response->successful) {
            throw new MercadoPagoApiException('Falha ao consultar pagamento na API.');
        }

        return $response->data;
    }

    private function validatePaymentData(array $paymentData, array $config): void
    {
        if (!isset($paymentData['id']) || (string) $paymentData['id'] === '') {
            throw new MercadoPagoApiException('Pagamento remoto sem identificador.');
        }

        if (($paymentData['currency_id'] ?? null) !== 'BRL') {
            throw new MercadoPagoApiException('Moeda invalida.');
        }

        if (empty($paymentData['external_reference'])) {
            throw new MercadoPagoApiException('External reference ausente.');
        }

        $remoteMode = !empty($paymentData['sandbox']) ? 'sandbox' : 'production';
        if ($remoteMode !== ($config['mode'] ?? null)) {
            throw new MercadoPagoApiException('Ambiente inconsistente.');
        }

        $credentials = $this->configResolver->resolveBackendCredentials();
        if (empty($credentials->collectorId)) {
            throw new MercadoPagoApiException('Conta recebedora nao configurada.');
        }

        if (!isset($paymentData['collector_id']) || (string) $paymentData['collector_id'] !== (string) $credentials->collectorId) {
            throw new MercadoPagoApiException('Conta recebedora divergente.');
        }
    }

    private function validateLocalPayment(array $paymentData, array $config): MercadoPagoAction
    {
        $paymentId = (string) $paymentData['id'];
        $action = MercadoPagoAction::query()
            ->whereIn('action', ['create_pix_payment', 'create_card_payment'])
            ->where('mercadopago_operation_id', $paymentId)
            ->first();

        if (!$action || !$action->order_id) {
            throw new MercadoPagoApiException('Pagamento remoto desconhecido.');
        }

        if ($action->environment !== ($config['mode'] ?? null)) {
            throw new MercadoPagoApiException('Ambiente da operacao divergente.');
        }

        if ((string) $paymentData['external_reference'] !== (string) $action->order_id) {
            throw new MercadoPagoApiException('External reference divergente.');
        }

        if (($action->currency ?? 'BRL') !== ($paymentData['currency_id'] ?? null)) {
            throw new MercadoPagoApiException('Moeda da operacao divergente.');
        }

        $remoteAmount = $this->normalizeRemoteAmount($paymentData['transaction_amount'] ?? null);
        $expectedCents = $this->money->decimalToCents((string) $action->requested_amount);
        $remoteCents = $this->money->decimalToCents($remoteAmount);
        if ($remoteCents !== $expectedCents) {
            throw new MercadoPagoApiException('Valor do pagamento divergente.');
        }

        $otherAction = MercadoPagoAction::query()
            ->whereIn('action', ['create_pix_payment', 'create_card_payment'])
            ->where('mercadopago_operation_id', $paymentId)
            ->where('id', '!=', $action->id)
            ->exists();
        if ($otherAction) {
            throw new MercadoPagoApiException('Pagamento vinculado a outra operacao.');
        }

        return $action;
    }

    private function applyOrderTransition(MercadoPagoAction $action, array $paymentData): void
    {
        $status = (string) ($paymentData['status'] ?? '');
        $allowed = ['approved', 'pending', 'in_process', 'rejected', 'cancelled', 'refunded', 'charged_back'];
        if (!in_array($status, $allowed, true)) {
            throw new MercadoPagoApiException('Status remoto nao permitido.');
        }

        DB::transaction(function () use ($action, $status, $paymentData): void {
            $order = Order::query()->lockForUpdate()->find($action->order_id);
            if (!$order) {
                throw new MercadoPagoApiException('Pedido local inexistente.');
            }

            if ($status === 'approved') {
                $order->payment_status = 'Paid';
            } elseif (in_array($status, ['refunded', 'charged_back'], true)) {
                $order->payment_status = 'Unpaid';
            }

            $details = json_decode((string) $order->payment_details, true) ?: [];
            $details['mercadopago'] = array_merge($details['mercadopago'] ?? [], [
                'payment_id' => (string) $paymentData['id'],
                'status' => $status,
                'status_detail' => $paymentData['status_detail'] ?? null,
            ]);
            $order->payment_details = json_encode($details, JSON_UNESCAPED_UNICODE);
            $order->save();
        });
    }

    private function normalizeRemoteAmount(mixed $amount): string
    {
        if (is_int($amount) || is_string($amount)) {
            return (string) $amount;
        }

        if (is_float($amount)) {
            $encoded = json_encode($amount, JSON_PRESERVE_ZERO_FRACTION);
            if (is_string($encoded)) {
                return $encoded;
            }
        }

        throw new MercadoPagoApiException('Valor remoto invalido.');
    }
}
