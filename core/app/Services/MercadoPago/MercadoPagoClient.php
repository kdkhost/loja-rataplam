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
            $body = json_decode($response->getBody()->getContents(), true) ?: [];

            $requestId = $response->getHeaderLine('x-request-id');
            $retryAfter = $response->hasHeader('Retry-After') ? (int) $response->getHeaderLine('Retry-After') : null;

            if ($statusCode >= 200 && $statusCode < 300) {
                return new MercadoPagoResponse(true, $statusCode, $body, null, 'Sucesso', $requestId, $retryAfter);
            }

            $errorCode = $body['error'] ?? 'unknown_error';
            $safeMessage = 'A operação falhou no Mercado Pago.';

            Log::warning('Mercado Pago API falhou.', [
                'type' => $method . ' ' . $uri,
                'exception' => 'MercadoPagoApiException',
                'http_status' => $statusCode,
                'error_code' => $errorCode,
                'request_id' => $requestId,
            ]);

            return new MercadoPagoResponse(false, $statusCode, $body, $errorCode, $safeMessage, $requestId, $retryAfter);
        } catch (RequestException $e) {
            Log::error('Mercado Pago Request Exception.', [
                'type' => $method . ' ' . $uri,
                'exception' => get_class($e),
                'message' => 'Timeout ou erro de rede',
            ]);

            throw new MercadoPagoApiException('Não foi possível conectar ao Mercado Pago.');
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
            $options['json'] = ['amount' => (float) number_format((float) $amount, 2, '.', '')];
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
