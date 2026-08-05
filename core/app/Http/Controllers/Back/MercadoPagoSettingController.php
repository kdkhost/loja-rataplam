<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\MercadoPagoSettingRequest;
use App\Models\MercadoPagoSetting;
use App\Services\MercadoPago\MercadoPagoFeatureGate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MercadoPagoSettingController extends Controller
{
    public function __construct(protected MercadoPagoFeatureGate $featureGate)
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function index()
    {
        return redirect()->route('back.setting.payment');
    }

    public function update(MercadoPagoSettingRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $settings = MercadoPagoSetting::firstOrNew(['configuration_key' => 'default']);
                $settings->configuration_key = 'default';
                $settings->fill($request->except([
                    'configuration_key', 'photo', 'status', 'mode',
                    'sandbox_enabled', 'production_enabled',
                    'sandbox_public_key', 'sandbox_access_token', 'production_public_key',
                    'production_access_token', 'sandbox_webhook_secret', 'production_webhook_secret',
                    'remove_sandbox_token', 'remove_production_token',
                    'remove_sandbox_secret', 'remove_production_secret',
                ]));

                foreach (['sandbox', 'production'] as $environment) {
                    foreach (['public_key', 'access_token', 'webhook_secret'] as $field) {
                        $name = $environment . '_' . $field;
                        if ($request->filled($name)) {
                            $settings->{$name} = $request->input($name);
                        }
                    }
                    foreach (['token' => 'access_token', 'secret' => 'webhook_secret'] as $remove => $field) {
                        if ($request->boolean('remove_' . $environment . '_' . $remove)) {
                            $settings->{$environment . '_' . $field} = null;
                        }
                    }
                }

                $settings->save();
            });

            return redirect()->back()->withSuccess('Configurações salvas. A ativação permanece inalterada.');
        } catch (\Throwable $exception) {
            Log::error('Mercado Pago: erro ao salvar configurações', [
                'exception_class' => get_class($exception),
                'admin_id' => auth('admin')->id(),
            ]);
            return redirect()->back()->withErrors('Erro ao salvar configurações. Tente novamente.');
        }
    }

    public function activate(string $environment)
    {
        abort_unless(in_array($environment, ['sandbox', 'production'], true), 404);
        try {
            $this->featureGate->assertConfigurationReady($environment);
            DB::transaction(function () use ($environment) {
                $settings = MercadoPagoSetting::where('configuration_key', 'default')->lockForUpdate()->firstOrFail();
                $settings->mode = $environment;
                $settings->sandbox_enabled = $environment === 'sandbox';
                $settings->production_enabled = $environment === 'production';
                $settings->save();
            });
            return redirect()->back()->withSuccess('Ambiente Mercado Pago ativado explicitamente.');
        } catch (\Throwable $exception) {
            Log::warning('Mercado Pago: ativação rejeitada', [
                'environment' => $environment,
                'exception_class' => get_class($exception),
                'admin_id' => auth('admin')->id(),
            ]);
            return redirect()->back()->withErrors('Ativação indisponível: configuração incompleta ou ilegível.');
        }
    }

    public function deactivate(string $environment)
    {
        abort_unless(in_array($environment, ['sandbox', 'production'], true), 404);
        MercadoPagoSetting::where('configuration_key', 'default')->update([$environment . '_enabled' => false]);
        return redirect()->back()->withSuccess('Ambiente Mercado Pago desativado.');
    }
}
