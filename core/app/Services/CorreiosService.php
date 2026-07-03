<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class CorreiosService
{
    public function quote(array $payload): array
    {
        $setting = Setting::firstOrFail();

        if (!$setting->correios_enabled) {
            return ['success' => false, 'message' => 'Integracao dos Correios desativada.'];
        }

        return $setting->correios_mode === 'paid'
            ? $this->paidQuote($setting, $payload)
            : $this->freeQuote($setting, $payload);
    }

    private function paidQuote(Setting $setting, array $payload): array
    {
        if (!$setting->correios_token) {
            return ['success' => false, 'message' => 'Informe o token da API oficial dos Correios.'];
        }

        $services = $this->services($setting);
        $quotes = [];

        foreach ($services as $service) {
            $response = Http::withToken($setting->correios_token)
                ->acceptJson()
                ->timeout(20)
                ->get('https://api.correios.com.br/preco/v1/nacional/' . $service, [
                    'cepOrigem' => $this->digits($setting->correios_origin_cep),
                    'cepDestino' => $this->digits($payload['destination_cep'] ?? ''),
                    'psObjeto' => $payload['weight'] ?? '1',
                    'comprimento' => $payload['length'] ?? '20',
                    'largura' => $payload['width'] ?? '20',
                    'altura' => $payload['height'] ?? '10',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $quotes[] = [
                    'service' => $service,
                    'price' => $data['pcFinal'] ?? $data['valor'] ?? null,
                    'deadline' => isset($data['prazoEntrega']) ? ((int) $data['prazoEntrega'] + (int) $setting->correios_extra_days) : null,
                    'raw' => $data,
                ];
            }
        }

        return ['success' => count($quotes) > 0, 'quotes' => $quotes, 'message' => count($quotes) ? null : 'Nenhum servico retornado pelos Correios.'];
    }

    private function freeQuote(Setting $setting, array $payload): array
    {
        if (!$setting->correios_free_endpoint) {
            return ['success' => false, 'message' => 'Informe a URL do endpoint gratuito/legado de consulta.'];
        }

        $response = Http::timeout(20)->get($setting->correios_free_endpoint, [
            'cepOrigem' => $this->digits($setting->correios_origin_cep),
            'cepDestino' => $this->digits($payload['destination_cep'] ?? ''),
            'peso' => $payload['weight'] ?? '1',
            'comprimento' => $payload['length'] ?? '20',
            'largura' => $payload['width'] ?? '20',
            'altura' => $payload['height'] ?? '10',
            'servicos' => implode(',', $this->services($setting)),
        ]);

        return [
            'success' => $response->successful(),
            'quotes' => $response->json() ?: [],
            'message' => $response->successful() ? null : 'Endpoint gratuito/legado não respondeu corretamente.',
        ];
    }

    private function services(Setting $setting): array
    {
        return collect(explode(',', (string) $setting->correios_services))
            ->map(fn ($service) => trim($service))
            ->filter()
            ->values()
            ->all() ?: ['03220', '03298'];
    }

    private function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $value);
    }
}
