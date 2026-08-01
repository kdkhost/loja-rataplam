<?php
namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Http\Requests\MercadoPagoSettingRequest;
use App\Models\MercadoPagoSetting;
use Illuminate\Support\Facades\DB;

class MercadoPagoSettingController extends Controller
{
    public function update(MercadoPagoSettingRequest $request)
    {
        DB::beginTransaction();
        try {
            $settings = MercadoPagoSetting::first() ?: new MercadoPagoSetting();
            
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
            DB::commit();

            return redirect()->back()->withSuccess('Configurações do Mercado Pago atualizadas com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('Erro ao salvar as configurações.');
        }
    }
}
