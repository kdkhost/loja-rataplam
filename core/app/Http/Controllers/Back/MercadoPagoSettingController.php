<?php
namespace App\Http\Controllers\Back;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\MercadoPagoSettingRequest;
use App\Models\MercadoPagoSetting;
use App\Models\PaymentSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MercadoPagoSettingController extends Controller
{
    public function __construct()
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
            return DB::transaction(function () use ($request) {
                $settings = MercadoPagoSetting::firstOrNew(['configuration_key' => 'default']);

                // Definir explicitamente configuration_key
                $settings->configuration_key = 'default';

                $mode = $request->input('mode');
                $isActive = $request->input('status', false);

                // Preencher campos (exceto configuration_key e photo)
                $settings->fill($request->except([
                    'configuration_key',
                    'photo',
                    'sandbox_access_token',
                    'production_access_token',
                    'sandbox_webhook_secret',
                    'production_webhook_secret',
                    'remove_sandbox_token',
                    'remove_production_token',
                    'remove_sandbox_secret',
                    'remove_production_secret',
                ]));

                // Regra: campo vazio preserva credencial
                if ($request->filled('sandbox_access_token')) {
                    $settings->sandbox_access_token = $request->input('sandbox_access_token');
                } elseif ($request->input('remove_sandbox_token', false)) {
                    $settings->sandbox_access_token = null;
                }

                if ($request->filled('production_access_token')) {
                    $settings->production_access_token = $request->input('production_access_token');
                } elseif ($request->input('remove_production_token', false)) {
                    $settings->production_access_token = null;
                }

                if ($request->filled('sandbox_webhook_secret')) {
                    $settings->sandbox_webhook_secret = $request->input('sandbox_webhook_secret');
                } elseif ($request->input('remove_sandbox_secret', false)) {
                    $settings->sandbox_webhook_secret = null;
                }

                if ($request->filled('production_webhook_secret')) {
                    $settings->production_webhook_secret = $request->input('production_webhook_secret');
                } elseif ($request->input('remove_production_secret', false)) {
                    $settings->production_webhook_secret = null;
                }

                $settings->save();

                // Sincronizar com registro legado payment_settings
                $legacy = PaymentSetting::where('unique_keyword', 'mercadopago')->first();
                if ($legacy) {
                    // Tratar imagem
                    if ($file = $request->file('photo')) {
                        $legacy->photo = ImageHelper::handleUpdatedUploadedImage($file, 'images', $legacy, 'images/', 'photo');
                    }

                    $legacy->name = $request->input('name', 'Mercado Pago');
                    $legacy->status = $request->input('status', false) ? 1 : 0;
                    $legacy->text = $request->input('text', '');
                    $legacy->save();
                }

                return redirect()->back()->withSuccess('Configurações do Mercado Pago atualizadas com sucesso.');
            });
        } catch (\Exception $e) {
            Log::error('Mercado Pago: Erro ao salvar configurações', [
                'operation' => 'update',
                'exception_class' => get_class($e),
                'admin_id' => auth('admin')->id(),
            ]);
            return redirect()->back()->withErrors('Erro ao salvar configurações. Tente novamente.');
        }
    }
}
