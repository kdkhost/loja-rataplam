<?php

namespace App\Http\Controllers\Front;

use App\Exceptions\InvalidMercadoPagoWebhookSignatureException;
use App\Exceptions\MercadoPagoApiException;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use App\Services\MercadoPago\MercadoPagoWebhookSignatureValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController
{
    public function __construct(
        protected MercadoPagoWebhookService $webhookService,
        protected MercadoPagoWebhookSignatureValidator $signatureValidator,
        protected MercadoPagoConfigResolver $configResolver
    ) {}

    public function handle(Request $request): JsonResponse
    {
        try {
            $dataId = $this->resolveSignedDataId($request);
            $requestId = $request->header('x-request-id');
            $credentials = $this->configResolver->resolveBackendCredentials();

            $this->signatureValidator->validate(
                $request->header('x-signature'),
                $requestId,
                $dataId,
                $credentials->webhookSecret ?? ''
            );

            $bodyDataId = data_get($request->json()->all(), 'data.id');
            if ($bodyDataId !== null && (string) $bodyDataId !== $dataId) {
                throw new InvalidMercadoPagoWebhookSignatureException(
                    'body_query_mismatch',
                    $this->sanitizeRequestId($requestId)
                );
            }

            $result = $this->webhookService->handleVerifiedNotification($dataId);

            return response()->json([
                'status' => $result['status'] ?? 'processed',
                'payment_id' => $result['payment_id'] ?? null,
            ]);
        } catch (InvalidMercadoPagoWebhookSignatureException $exception) {
            Log::warning('Webhook Mercado Pago rejeitado', [
                'failure_code' => $exception->failureCode(),
                'request_id' => $this->sanitizeRequestId($exception->requestId()),
            ]);

            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (MercadoPagoApiException $exception) {
            Log::error('Webhook Mercado Pago nao processado', [
                'exception_class' => get_class($exception),
            ]);

            return response()->json(['error' => 'Processing failed'], 422);
        } catch (\Throwable $exception) {
            Log::error('Webhook Mercado Pago falhou', [
                'exception_class' => get_class($exception),
            ]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    private function resolveSignedDataId(Request $request): ?string
    {
        $official = null;
        $legacy = null;

        foreach (explode('&', (string) $request->server('QUERY_STRING', '')) as $component) {
            if ($component === '') {
                continue;
            }

            [$rawKey, $rawValue] = array_pad(explode('=', $component, 2), 2, '');
            $key = rawurldecode($rawKey);
            $value = rawurldecode($rawValue);

            if ($key === 'data.id') {
                $official = $value;
            } elseif ($key === 'data_id') {
                $legacy = $value;
            }
        }

        if ($official !== null && $legacy !== null && $official !== $legacy) {
            throw new InvalidMercadoPagoWebhookSignatureException('conflicting_data_id');
        }

        return $official ?? $legacy;
    }

    private function sanitizeRequestId(?string $requestId): ?string
    {
        if ($requestId === null) {
            return null;
        }

        $sanitized = preg_replace('/[^A-Za-z0-9._:-]/', '', $requestId);

        return substr((string) $sanitized, 0, 128) ?: null;
    }
}
