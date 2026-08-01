<?php
namespace App\Http\Controllers\Back;

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
        return DB::transaction(function () use ($request) {
            try {
                $settings = MercadoPagoSetting::firstOrNew(['configuration_key' => 'default']);
                $mode = $request->input('mode');
                $isActive = $request->input('status') == 1;

                // Regra: credencial ativa não pode ser removida enquanto gateway estiver ativo
                if ($isActive && $mode === 'sandbox' && $request->has('remove_sandbox_token')) {
                    return redirect()->back()->withErrors('Não é possível remover a credencial sandbox enquanto o gateway está ativo.');
                }

                if ($isActive && $mode === 'production' && $request->has('remove_production_token')) {
                    return redirect()->back()->withErrors('Não é possível remover a credencial de produção enquanto o gateway está ativo.');
                }

                // Regra: segredo ativo não pode ser removido no modo signed
                if ($request->input('webhook_validation_mode') === 'signed') {
                    if ($mode === 'sandbox' && $request->has('remove_sandbox_secret')) {
                        return redirect()->back()->withErrors('Não é possível remover o segredo sandbox enquanto o modo de validação é signed.');
                    }
                    if ($mode === 'production' && $request->has('remove_production_secret')) {
                        return redirect()->back()->withErrors('Não é possível remover o segredo de produção enquanto o modo de validação é signed.');
                    }
                }

                // Regra: troca para produção exige credenciais de produção
                if ($mode === 'production' && $isActive) {
                    if (empty($settings->production_public_key) && empty($request->input('production_public_key'))) {
                        return redirect()->back()->withErrors('Ativar em produção exige a Public Key de produção.');
                    }
                    if (empty($settings->production_access_token) && empty($request->input('production_access_token'))) {
                        return redirect()->back()->withErrors('Ativar em produção exige o Access Token de produção.');
                    }
                }

                // Regra: troca para sandbox exige credenciais sandbox
                if ($mode === 'sandbox' && $isActive) {
                    if (empty($settings->sandbox_public_key) && empty($request->input('sandbox_public_key'))) {
                        return redirect()->back()->withErrors('Ativar em sandbox exige a Public Key de sandbox.');
                    }
                    if (empty($settings->sandbox_access_token) && empty($request->input('sandbox_access_token'))) {
                        return redirect()->back()->withErrors('Ativar em sandbox exige o Access Token de sandbox.');
                    }
                }

                // Preencher campos
                $settings->fill($request->except([
                    'sandbox_access_token',
                    'production_access_token',
                    'sandbox_webhook_secret',
                    'production_webhook_secret',
                    'remove_sandbox_token',
                    'remove_production_token',
                    'remove_sandbox_secret',
                    'remove_production_secret',
                    'pix_enabled',
                    'credit_card_enabled',
                    'fee_pass_to_customer',
                    'refund_enabled',
                    'partial_refund_enabled',
                    'cancellation_enabled',
                    'reconciliation_enabled',
                    'binary_mode',
                ]));

                $settings->pix_enabled = $request->has('pix_enabled');
                $settings->credit_card_enabled = $request->has('credit_card_enabled');
                $settings->fee_pass_to_customer = $request->has('fee_pass_to_customer');
                $settings->refund_enabled = $request->has('refund_enabled');
                $settings->partial_refund_enabled = $request->has('partial_refund_enabled');
                $settings->cancellation_enabled = $request->has('cancellation_enabled');
                $settings->reconciliation_enabled = $request->has('reconciliation_enabled');
                $settings->binary_mode = $request->has('binary_mode');

                // Regra: campo vazio preserva credencial
                if ($request->filled('sandbox_access_token')) {
                    $settings->sandbox_access_token = $request->input('sandbox_access_token');
                } elseif ($request->has('remove_sandbox_token')) {
                    $settings->sandbox_access_token = null;
                }

                if ($request->filled('production_access_token')) {
                    $settings->production_access_token = $request->input('production_access_token');
                } elseif ($request->has('remove_production_token')) {
                    $settings->production_access_token = null;
                }

                if ($request->filled('sandbox_webhook_secret')) {
                    $settings->sandbox_webhook_secret = $request->input('sandbox_webhook_secret');
                } elseif ($request->has('remove_sandbox_secret')) {
                    $settings->sandbox_webhook_secret = null;
                }

                if ($request->filled('production_webhook_secret')) {
                    $settings->production_webhook_secret = $request->input('production_webhook_secret');
                } elseif ($request->has('remove_production_secret')) {
                    $settings->production_webhook_secret = null;
                }

                $settings->save();

                // Sincronizar com registro legado payment_settings
                $legacy = PaymentSetting::where('unique_keyword', 'mercadopago')->first();
                if ($legacy) {
                    $legacy->name = $request->input('name', 'Mercado Pago');
                    $legacy->status = $request->input('status', 0);
                    $legacy->text = $request->input('text', '');
                    $legacy->save();
                }

                return redirect()->back()->withSuccess('Configurações do Mercado Pago atualizadas com sucesso.');
            } catch (\Exception $e) {
                Log::error('Mercado Pago: Erro ao salvar configurações', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'admin_id' => auth('admin')->id(),
                ]);
                throw $e;
            }
        });
    }
}
