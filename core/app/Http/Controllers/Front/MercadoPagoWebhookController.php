<?php
namespace App\Http\Controllers\Front;

use App\Exceptions\MercadoPagoApiException;
use App\Services\MercadoPago\MercadoPagoWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController
{
    protected MercadoPagoWebhookService $webhookService;

    public function __construct(MercadoPagoWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Processa notificação de webhook do Mercado Pago
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $notification = $request->all();

            // Validar estrutura mínima
            if (!isset($notification['data']['id'])) {
                Log::warning('Webhook: Estrutura inválida');
                return response()->json(['error' => 'Invalid structure'], 400);
            }

            // Processar notificação
            $result = $this->webhookService->handleNotification($notification);

            return response()->json([
                'status' => 'processed',
                'payment_id' => $result['payment_id'] ?? null,
            ], 200);

        } catch (MercadoPagoApiException $e) {
            Log::error('Webhook: Erro ao processar notificação', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 422);
        } catch (\Exception $e) {
            Log::error('Webhook: Erro inesperado', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Internal error'], 500);
        }
    }
}
