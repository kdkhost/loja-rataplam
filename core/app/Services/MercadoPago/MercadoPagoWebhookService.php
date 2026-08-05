<?php
namespace App\Services\MercadoPago;

use App\Exceptions\MercadoPagoApiException;
use App\Services\MercadoPago\MercadoPagoWebhookValidator;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookService
{
    protected MercadoPagoClient $client;
    protected MercadoPagoConfigResolver $configResolver;
    protected MercadoPagoWebhookValidator $validator;
    protected MercadoPagoIdempotencyService $idempotencyService;

    public function __construct(
        ?MercadoPagoClient $client = null,
        ?MercadoPagoConfigResolver $configResolver = null,
        ?MercadoPagoWebhookValidator $validator = null,
        ?MercadoPagoIdempotencyService $idempotencyService = null
    ) {
        $this->configResolver = $configResolver ?? app(MercadoPagoConfigResolver::class);
        $this->validator = $validator ?? app(MercadoPagoWebhookValidator::class);
        $this->idempotencyService = $idempotencyService ?? app(MercadoPagoIdempotencyService::class);

        $config = $this->configResolver->resolve();
        $accessToken = $config['mode'] === 'production'
            ? $config['production_access_token']
            : $config['sandbox_access_token'];

        $this->client = $client ?? new MercadoPagoClient($accessToken);
    }

    // Setter methods for testing
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
     * Processa notificação de webhook do Mercado Pago
     */
    public function handleNotification(array $notification): array
    {
        $config = $this->configResolver->resolve();

        // Extrair identificador da notificação
        $paymentId = $notification['data']['id'] ?? null;

        if (!$paymentId) {
            Log::warning('Webhook: ID do pagamento não fornecido');
            throw new MercadoPagoApiException('ID do pagamento não fornecido.');
        }

        // Validar assinatura se configurada
        $webhookSecret = $config['mode'] === 'production'
            ? $config['production_webhook_secret']
            : $config['sandbox_webhook_secret'];

        if (!empty($webhookSecret)) {
            $this->validateSignature($notification, $webhookSecret);
        }

        // Consultar pagamento na API (não confiar no status do POST)
        $paymentData = $this->getPaymentFromApi($paymentId);

        // Validar dados do pagamento
        $this->validatePaymentData($paymentData, $notification);

        // Processar de forma idempotente
        return $this->processPaymentUpdate($paymentData, $config);
    }

    /**
     * Valida assinatura do webhook
     */
    protected function validateSignature(array $notification, string $webhookSecret): void
    {
        $signature = $notification['signature'] ?? [];
        $ts = $signature['ts'] ?? null;
        $v1 = $signature['v1'] ?? null;
        $requestId = $notification['request_id'] ?? null;
        $dataId = $notification['data']['id'] ?? null;

        if (!$ts || !$v1 || !$dataId) {
            Log::warning('Webhook: Assinatura incompleta');
            throw new MercadoPagoApiException('Assinatura do webhook inválida.');
        }

        // Construir headers no formato esperado pelo validator
        $headers = [
            'x-signature' => ["ts={$ts},v1={$v1}"],
            'x-request-id' => [$requestId],
        ];

        // Validar usando o validator existente
        if (!$this->validator->isValid($headers, $dataId, $webhookSecret)) {
            Log::warning('Webhook: Assinatura inválida');
            throw new MercadoPagoApiException('Assinatura do webhook inválida.');
        }
    }

    /**
     * Consulta pagamento na API do Mercado Pago
     */
    protected function getPaymentFromApi(string $paymentId): array
    {
        $response = $this->client->getPayment($paymentId);

        if (!$response->successful) {
            Log::error('Webhook: Falha ao consultar pagamento na API', [
                'payment_id' => $paymentId,
                'http_status' => $response->httpStatus,
            ]);
            throw new MercadoPagoApiException('Falha ao consultar pagamento na API.');
        }

        return $response->data;
    }

    /**
     * Valida dados do pagamento
     */
    protected function validatePaymentData(array $paymentData, array $notification): void
    {
        // Validar moeda
        if (($paymentData['currency_id'] ?? '') !== 'BRL') {
            Log::warning('Webhook: Moeda inválida', [
                'currency' => $paymentData['currency_id'] ?? null,
            ]);
            throw new MercadoPagoApiException('Moeda inválida.');
        }

        // Validar external_reference
        $externalReference = $paymentData['external_reference'] ?? null;
        if (!$externalReference) {
            Log::warning('Webhook: External reference ausente');
            throw new MercadoPagoApiException('External reference ausente.');
        }

        // Validar ambiente
        $config = $this->configResolver->resolve();
        $expectedMode = $config['mode'];
        $paymentMode = ($paymentData['sandbox'] ?? false) ? 'sandbox' : 'production';

        if ($paymentMode !== $expectedMode) {
            Log::warning('Webhook: Ambiente inconsistente', [
                'expected' => $expectedMode,
                'received' => $paymentMode,
            ]);
            throw new MercadoPagoApiException('Ambiente inconsistente.');
        }

        // Validar conta recebedora (collector_id)
        $this->validateCollectorId($paymentData, $config);
    }

    /**
     * Valida se o pagamento pertence à conta recebedora configurada
     */
    protected function validateCollectorId(array $paymentData, array $config): void
    {
        // Obter collector_id esperado das credenciais
        $credentials = $this->configResolver->resolveBackendCredentials();
        $expectedCollectorId = $credentials->collectorId;

        // Se collector_id não está configurado, não validar (compatibilidade)
        if ($expectedCollectorId === null) {
            Log::warning('Webhook: Collector ID não configurado, pulando validação');
            return;
        }

        // Obter collector_id do pagamento
        $paymentCollectorId = $paymentData['collector_id'] ?? null;

        if ($paymentCollectorId === null) {
            Log::warning('Webhook: Collector ID ausente no pagamento');
            throw new MercadoPagoApiException('Pagamento não possui identificador de conta.');
        }

        // Validar correspondência
        if ($paymentCollectorId !== $expectedCollectorId) {
            Log::warning('Webhook: Conta recebedora divergente', [
                'expected' => $expectedCollectorId,
                'received' => $paymentCollectorId,
            ]);
            throw new MercadoPagoApiException('Pagamento pertence a outra conta.');
        }
    }

    /**
     * Processa atualização do pagamento de forma idempotente
     */
    protected function processPaymentUpdate(array $paymentData, array $config): array
    {
        $paymentId = $paymentData['id'];
        $externalReference = $paymentData['external_reference'];
        $status = $paymentData['status'];
        $statusDetail = $paymentData['status_detail'] ?? null;

        // Gerar fingerprint para idempotência
        $fingerprint = $this->idempotencyService->generateFingerprint([
            'order_id' => $externalReference,
            'payment_id' => $paymentId,
            'environment' => $config['mode'],
            'action' => 'webhook_notification',
        ]);

        // Criar nova ação (o serviço lida com idempotência via idempotency_key)
        $idempotencyKey = $this->idempotencyService->generateKey();

        try {
            $action = $this->idempotencyService->initiateAction([
                'idempotency_key' => $idempotencyKey,
                'request_fingerprint' => $fingerprint,
                'action' => 'webhook_notification',
                'environment' => $config['mode'],
                'order_id' => $externalReference,
                'payment_id' => $paymentId,
                'requested_amount' => $paymentData['transaction_amount'] ?? null,
                'currency' => $paymentData['currency_id'] ?? null,
            ]);
        } catch (\App\Exceptions\MercadoPagoOperationException $e) {
            // Chave de idempotência já em uso - ação duplicada
            Log::info('Webhook: Notificação duplicada ignorada', [
                'payment_id' => $paymentId,
                'external_reference' => $externalReference,
            ]);

            return [
                'status' => 'already_processed',
                'payment_id' => $paymentId,
                'external_reference' => $externalReference,
            ];
        }

        // Atualizar pedido (em produção, isso atualizaria o banco de dados)
        $result = [
            'payment_id' => $paymentId,
            'external_reference' => $externalReference,
            'status' => $status,
            'status_detail' => $statusDetail,
            'transaction_amount' => $paymentData['transaction_amount'] ?? null,
        ];

        // Marcar como completado
        $response = new MercadoPagoResponse(true, 200, $result, null, 'Success');
        $this->idempotencyService->completeAction($action, $response);

        Log::info('Webhook: Notificação processada com sucesso', [
            'payment_id' => $paymentId,
            'external_reference' => $externalReference,
            'status' => $status,
        ]);

        return [
            'status' => 'processed',
            'payment_id' => $paymentId,
            'external_reference' => $externalReference,
            'payment_status' => $status,
        ];
    }
}
