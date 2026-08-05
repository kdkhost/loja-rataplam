<?php
namespace App\Services\MercadoPago;

use App\Exceptions\MercadoPagoApiException;
use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use Illuminate\Support\Facades\Log;

class MercadoPagoPaymentService
{
    protected ?MercadoPagoClient $client;
    protected MercadoPagoConfigResolver $configResolver;
    protected MercadoPagoIdempotencyService $idempotencyService;
    protected MercadoPagoMoney $money;
    protected MercadoPagoFeatureGate $featureGate;

    public function __construct(
        ?MercadoPagoClient $client = null,
        ?MercadoPagoConfigResolver $configResolver = null,
        ?MercadoPagoIdempotencyService $idempotencyService = null,
        ?MercadoPagoMoney $money = null,
        ?MercadoPagoFeatureGate $featureGate = null
    ) {
        $this->configResolver = $configResolver ?? app(MercadoPagoConfigResolver::class);
        $this->idempotencyService = $idempotencyService ?? app(MercadoPagoIdempotencyService::class);
        $this->money = $money ?? new MercadoPagoMoney();
        $this->client = $client;
        $this->featureGate = $featureGate ?? app(MercadoPagoFeatureGate::class);
    }

    protected function getAccessToken(): string
    {
        return $this->configResolver->resolveBackendCredentials()->accessToken;
    }

    protected function ensureClient(): void
    {
        if ($this->client === null) {
            $this->client = new MercadoPagoClient($this->getAccessToken());
        }
    }

    // Setter methods for testing
    public function setClient(?MercadoPagoClient $client): void
    {
        $this->client = $client;
    }

    public function setIdempotencyService(?MercadoPagoIdempotencyService $service): void
    {
        $this->idempotencyService = $service;
    }

    public function setConfigResolver(?MercadoPagoConfigResolver $resolver): void
    {
        $this->configResolver = $resolver;
    }

    /**
     * Cria pagamento Pix no sandbox
     */
    public function createPixPayment(array $orderData): array
    {
        $config = $this->configResolver->resolve();
        $this->assertAuthoritativeGate($config);
        $this->ensureClient();

        if (!$config['pix_enabled']) {
            throw new MercadoPagoApiException('Pagamento Pix não está habilitado.');
        }

        if ($config['mode'] !== 'sandbox') {
            throw new MercadoPagoApiException('Pagamentos Pix só podem ser criados no sandbox nesta fase.');
        }

        // Recalcular valor no servidor
        $amount = $this->calculateOrderAmount($orderData);
        $cents = $this->money->decimalToCents($amount);

        // Gerar chave de idempotência estável
        $idempotencyKey = $this->generateIdempotencyKey('pix', $orderData, $config);

        // Adquirir ação local com ownership
        $acquisition = $this->idempotencyService->acquireAction([
            'idempotency_key' => $idempotencyKey,
            'request_fingerprint' => null,
            'action' => 'create_pix_payment',
            'environment' => $config['mode'],
            'order_id' => $orderData['order_id'] ?? null,
            'payment_id' => null,
            'requested_amount' => $amount,
            'currency' => 'BRL',
        ]);

        $action = $acquisition->action;
        $executionOwner = $acquisition->executionOwner;

        // Se não adquiriu ownership, verificar estado existente
        if (!$acquisition->canExecuteRemoteCall()) {
            return $this->handleExistingAction($acquisition, $action);
        }

        try {
            $payload = [
                'transaction_amount' => $this->money->centsToApiAmount($cents),
                'description' => $this->sanitizeDescription($orderData['description'] ?? 'Pedido'),
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $this->sanitizeEmail($orderData['payer_email'] ?? 'customer@example.com'),
                ],
                'external_reference' => $orderData['order_id'] ?? null,
                'notification_url' => $config['notification_url'] ?? route('front.mercadopago.webhook.v2'),
                'metadata' => [
                    'order_id' => $orderData['order_id'] ?? null,
                ],
            ];

            $this->assertAuthoritativeGate($config);
            $response = $this->client->createPayment($payload, $idempotencyKey);

            if (!$response->successful) {
                $this->idempotencyService->completeAction($action, $response, $executionOwner);
                throw new MercadoPagoApiException($response->safeMessage);
            }

            // Salvar identificadores e resumo seguro
            $expirationDate = $response->data['date_of_expiration'] ?? null;
            // Normalizar formato de data para consistência
            if ($expirationDate) {
                try {
                    $expirationDate = (new \DateTime($expirationDate))->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    $expirationDate = null;
                }
            }

            $paymentData = [
                'payment_id' => $response->data['id'] ?? null,
                'status' => $response->data['status'] ?? null,
                'qr_code' => $response->data['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                'qr_code_base64' => $response->data['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                'ticket_url' => $response->data['point_of_interaction']['transaction_data']['ticket_url'] ?? null,
                'expiration_date' => $expirationDate,
            ];

            // Persistir campos Pix com validação
            $sanitizer = new MercadoPagoPixResponseSanitizer();
            $sanitizedPixData = $sanitizer->sanitize($paymentData);
            $this->persistPixFields($action, $sanitizedPixData);

            $this->idempotencyService->completeAction($action, $response, $executionOwner);

            return array_merge($paymentData, $sanitizedPixData);
        } catch (\Exception $e) {
            $errorResponse = new MercadoPagoResponse(false, 500, ['error' => $e->getMessage()], 'error', $e->getMessage());
            $this->idempotencyService->completeAction($action, $errorResponse, $executionOwner);
            throw $e;
        }
    }

    /**
     * Cria pagamento com cartão no sandbox
     */
    public function createCardPayment(array $orderData, array $cardData): array
    {
        $config = $this->configResolver->resolve();
        $this->assertAuthoritativeGate($config);
        $this->ensureClient();

        if (!$config['credit_card_enabled']) {
            throw new MercadoPagoApiException('Pagamento com cartão não está habilitado.');
        }

        if ($config['mode'] !== 'sandbox') {
            throw new MercadoPagoApiException('Pagamentos com cartão só podem ser criados no sandbox nesta fase.');
        }

        // Recalcular valor no servidor
        $amount = $this->calculateOrderAmount($orderData);
        $cents = $this->money->decimalToCents($amount);

        // Validar parcelas
        $installments = $orderData['installments'] ?? 1;
        $maxInstallments = $config['max_installments'] ?? 1;

        if ($installments < 1 || $installments > $maxInstallments) {
            throw new MercadoPagoApiException('Número de parcelas inválido.');
        }

        // Gerar chave de idempotência estável
        $idempotencyKey = $this->generateIdempotencyKey('card', $orderData, $config, $cardData);

        // Adquirir ação local com ownership
        $acquisition = $this->idempotencyService->acquireAction([
            'idempotency_key' => $idempotencyKey,
            'request_fingerprint' => null,
            'action' => 'create_card_payment',
            'environment' => $config['mode'],
            'order_id' => $orderData['order_id'] ?? null,
            'payment_id' => null,
            'requested_amount' => $amount,
            'currency' => 'BRL',
        ]);

        $action = $acquisition->action;
        $executionOwner = $acquisition->executionOwner;

        // Se não adquiriu ownership, verificar estado existente
        if (!$acquisition->canExecuteRemoteCall()) {
            return $this->handleExistingAction($acquisition, $action);
        }

        try {
            $payload = [
                'transaction_amount' => $this->money->centsToApiAmount($cents),
                'description' => $this->sanitizeDescription($orderData['description'] ?? 'Pedido'),
                'payment_method_id' => $cardData['payment_method_id'],
                'payer' => [
                    'email' => $this->sanitizeEmail($orderData['payer_email'] ?? 'customer@example.com'),
                    'identification' => [
                        'type' => $cardData['identification_type'] ?? 'CPF',
                        'number' => $cardData['identification_number'] ?? '',
                    ],
                ],
                'installments' => $installments,
                'external_reference' => $orderData['order_id'] ?? null,
                'notification_url' => $config['notification_url'] ?? route('front.mercadopago.webhook.v2'),
                'metadata' => [
                    'order_id' => $orderData['order_id'] ?? null,
                ],
                'token' => $cardData['token'] ?? null,
            ];

            $this->assertAuthoritativeGate($config);
            $response = $this->client->createPayment($payload, $idempotencyKey);

            if (!$response->successful) {
                $this->idempotencyService->completeAction($action, $response, $executionOwner);
                throw new MercadoPagoApiException($response->safeMessage);
            }

            // Salvar identificadores e resumo seguro
            $paymentData = [
                'payment_id' => $response->data['id'] ?? null,
                'status' => $response->data['status'] ?? null,
            ];

            $this->idempotencyService->completeAction($action, $response, $executionOwner);

            return $paymentData;
        } catch (\Exception $e) {
            $errorResponse = new MercadoPagoResponse(false, 500, ['error' => $e->getMessage()], 'error', $e->getMessage());
            $this->idempotencyService->completeAction($action, $errorResponse, $executionOwner);
            throw $e;
        }
    }

    /**
     * Consulta pagamento na API do Mercado Pago
     */
    public function getPayment(string $paymentId): array
    {
        $this->ensureClient();

        $response = $this->client->getPayment($paymentId);

        if (!$response->successful) {
            throw new MercadoPagoApiException($response->safeMessage);
        }

        return $response->data;
    }

    /**
     * Lida com ação existente quando não adquiriu ownership
     */
    protected function handleExistingAction(
        MercadoPagoIdempotencyAcquisitionResult $acquisition,
        \App\Models\MercadoPagoAction $action
    ): array {
        switch ($acquisition->reason) {
            case MercadoPagoIdempotencyAcquisitionResult::REASON_EXISTING_SUCCESS:
                // Retornar resultado persistido com campos Pix
                return [
                    'payment_id' => $action->mercadopago_operation_id,
                    'status' => $action->remote_status,
                    'qr_code' => $action->pix_qr_code,
                    'qr_code_base64' => $action->pix_qr_code_base64,
                    'ticket_url' => $action->pix_ticket_url,
                    'expiration_date' => $action->pix_expiration_date,
                    'from_cache' => true,
                ];

            case MercadoPagoIdempotencyAcquisitionResult::REASON_EXISTING_FAILED:
                // Retornar falha controlada
                throw new MercadoPagoApiException('A operação falhou anteriormente. Use uma nova chave de idempotência para tentar novamente.');

            case MercadoPagoIdempotencyAcquisitionResult::REASON_EXISTING_IN_PROGRESS:
                // Retornar estado de processamento
                throw new MercadoPagoApiException('A operação já está em processamento. Aguarde o resultado.');

            case MercadoPagoIdempotencyAcquisitionResult::REASON_EXISTING_UNKNOWN:
                // Requer reconciliação
                throw new MercadoPagoApiException('O estado da operação é desconhecido. Reconciliação necessária.');

            default:
                throw new MercadoPagoApiException('Estado de idempotência inválido.');
        }
    }

    /**
     * Recalcula valor do pedido no servidor
     */
    protected function calculateOrderAmount(array $orderData): string
    {
        // Em produção, isso consultaria o banco de dados
        // Para sandbox, usamos o valor fornecido com validação
        if (!array_key_exists('authoritative_amount', $orderData)) {
            throw new MercadoPagoApiException('Valor autoritativo do pedido ausente.');
        }

        $amount = (string) $orderData['authoritative_amount'];

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            throw new MercadoPagoApiException('Valor do pedido inválido.');
        }

        return $amount;
    }

    protected function assertAuthoritativeGate(array $config): void
    {
        $environment = $config['mode'] ?? '';
        $this->featureGate->assertCheckoutEnabled($environment);
    }

    /**
     * Sanitiza descrição
     */
    protected function sanitizeDescription(string $description): string
    {
        return mb_substr(strip_tags($description), 0, 255);
    }

    /**
     * Sanitiza e-mail
     */
    protected function sanitizeEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new MercadoPagoApiException('E-mail inválido.');
        }

        return $email;
    }

    /**
     * Persiste campos Pix já validados
     */
    protected function persistPixFields(\App\Models\MercadoPagoAction $action, array $sanitizedPixData): void
    {
        if (!empty($sanitizedPixData['qr_code'])) {
            $action->pix_qr_code = $sanitizedPixData['qr_code'];
        }

        if (!empty($sanitizedPixData['qr_code_base64'])) {
            $action->pix_qr_code_base64 = $sanitizedPixData['qr_code_base64'];
        }

        if (!empty($sanitizedPixData['ticket_url'])) {
            $action->pix_ticket_url = $sanitizedPixData['ticket_url'];
        }

        if (!empty($sanitizedPixData['expiration_date'])) {
            $action->pix_expiration_date = $sanitizedPixData['expiration_date'];
        }

        $action->save();
    }

    /**
     * Gera chave de idempotência estável
     */
    protected function generateIdempotencyKey(string $type, array $orderData, array $config, ?array $cardData = null): string
    {
        $amountMinor = $this->money->decimalToCents((string) ($orderData['authoritative_amount'] ?? ''));
        $currency = (string) ($orderData['currency'] ?? 'BRL');
        $components = [
            'version' => 2,
            'action' => $type,
            'order_id' => $orderData['order_id'] ?? '',
            'user_id' => $orderData['user_id'] ?? '',
            'environment' => $config['mode'] ?? '',
            'currency' => $currency,
            'amount_minor' => $amountMinor,
        ];

        if ($cardData !== null) {
            $components['payment_method_id'] = $cardData['payment_method_id'] ?? '';
            $components['installments'] = $cardData['installments'] ?? '1';
        }

        return $this->idempotencyService->generateDeterministicKey($components);
    }
}
