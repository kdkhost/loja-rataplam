<?php
namespace App\Services\MercadoPago;

use App\Exceptions\MercadoPagoApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class MercadoPagoClient
{
    protected Client $client;

    public function __construct(string $accessToken)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.mercadopago.com',
            'timeout' => 20,
            'connect_timeout' => 5,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
    }

    protected function request(string $method, string $uri, array $options = []): MercadoPagoResponse
    {
        try {
            $response = $this->client->request($method, $uri, $options);
            $statusCode = $response->getStatusCode();
            $bodyContents = $response->getBody()->getContents();
            $body = json_decode($bodyContents, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Mercado Pago: Resposta JSON inválida', [
                    'method' => $method,
                    'uri' => $uri,
                    'http_status' => $statusCode,
                    'json_error' => json_last_error_msg(),
                ]);
                throw new MercadoPagoApiException('Resposta inválida do Mercado Pago.');
            }

            if (!is_array($body)) {
                $body = [];
            }

            $requestId = $response->getHeaderLine('x-request-id');
            $retryAfter = $response->hasHeader('Retry-After') ? (int) $response->getHeaderLine('Retry-After') : null;

            if ($statusCode >= 200 && $statusCode < 300) {
                return new MercadoPagoResponse(true, $statusCode, $body, null, 'Sucesso', $requestId, $retryAfter);
            }

            $errorCode = $body['error'] ?? 'unknown_error';
            $safeMessage = 'A operação falhou no Mercado Pago.';

            Log::warning('Mercado Pago API falhou', [
                'method' => $method,
                'uri' => $uri,
                'http_status' => $statusCode,
                'error_code' => $errorCode,
                'request_id' => $requestId,
            ]);

            return new MercadoPagoResponse(false, $statusCode, $body, $errorCode, $safeMessage, $requestId, $retryAfter);
        } catch (RequestException $e) {
            Log::error('Mercado Pago: Erro de requisição', [
                'method' => $method,
                'uri' => $uri,
                'exception' => get_class($e),
            ]);

            throw new MercadoPagoApiException('Não foi possível conectar ao Mercado Pago.');
        } catch (\Exception $e) {
            Log::error('Mercado Pago: Erro inesperado', [
                'method' => $method,
                'uri' => $uri,
                'exception' => get_class($e),
            ]);

            throw new MercadoPagoApiException('Erro inesperado ao comunicar com Mercado Pago.');
        }
    }

    public function createPayment(array $payload, string $idempotencyKey): MercadoPagoResponse
    {
        return $this->request('POST', '/v1/payments', [
            'json' => $payload,
            'headers' => [
                'X-Idempotency-Key' => $idempotencyKey,
            ]
        ]);
    }

    public function getPayment(string $paymentId): MercadoPagoResponse
    {
        return $this->request('GET', "/v1/payments/{$paymentId}");
    }

    public function listRefunds(string $paymentId): MercadoPagoResponse
    {
        return $this->request('GET', "/v1/payments/{$paymentId}/refunds");
    }

    public function refund(string $paymentId, ?string $amount, string $idempotencyKey): MercadoPagoResponse
    {
        $options = [
            'headers' => [
                'X-Idempotency-Key' => $idempotencyKey,
            ]
        ];

        if ($amount !== null) {
            // Converter para float com precisão de 2 casas decimais
            $amountFloat = (float) $amount;
            $options['json'] = ['amount' => round($amountFloat, 2)];
        }

        return $this->request('POST', "/v1/payments/{$paymentId}/refunds", $options);
    }

    public function cancel(string $paymentId): MercadoPagoResponse
    {
        return $this->request('PUT', "/v1/payments/{$paymentId}", [
            'json' => [
                'status' => 'cancelled'
            ]
        ]);
    }

    public function getPaymentMethods(): MercadoPagoResponse
    {
        return $this->request('GET', '/v1/payment_methods');
    }
}
