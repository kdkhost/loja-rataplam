<?php

namespace App\Services\MercadoPago;

use App\Helpers\PriceHelper;
use App\Models\Currency;
use App\Models\ShippingService;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MercadoPagoCheckoutInput
{
    public function validate(Request $request): void
    {
        $state = State::whereStatus(1)->count() !== 0 ? 'required' : '';
        $shipping = ShippingService::whereStatus(1)->count() === 0 || PriceHelper::CheckDigital() ? 'required' : '';

        if ($request->single_page_checkout == 1) {
            $request->validate([
                'state_id' => $state, 'shipping_id' => $shipping,
                'bill_first_name' => 'required', 'bill_last_name' => 'required',
                'bill_email' => 'required|email', 'bill_phone' => 'required',
                'bill_address1' => 'required', 'bill_city' => 'required', 'bill_zip' => 'required',
            ]);
            return;
        }

        $request->validate(['state_id' => $state, 'shipping_id' => $shipping]);
    }

    public function validateCreditCard(Request $request): void
    {
        $request->validate([
            'token' => 'required|string', 'paymentMethodId' => 'required|string',
            'paymentTypeId' => 'nullable|string', 'docType' => 'required|in:CPF,CNPJ',
            'docNumber' => 'required|string|min:11|max:18',
        ]);

        $method = strtolower((string) $request->paymentMethodId);
        $type = strtolower((string) $request->paymentTypeId);
        if ($type === 'debit_card' || str_starts_with($method, 'deb')) {
            abort(422, 'Cartão de débito não é aceito nesta operação.');
        }
    }

    public function validatePix(Request $request): void
    {
        $request->validate([
            'docType' => 'required|in:CPF,CNPJ',
            'docNumber' => 'required|string|min:11|max:18',
        ]);
    }

    public function activeCurrency(): ?Currency
    {
        if (Session::has('currency')) {
            $currency = Currency::find(Session::get('currency'));
            if ($currency) {
                return $currency;
            }
        }

        return Currency::where('is_default', 1)->first();
    }

    public function paymentType(Request $request, array $settings): ?string
    {
        $requested = $request->input('mercadopago_payment_type');
        if ($requested === 'pix' && (int) $settings['pix_enabled'] === 1) return 'pix';
        if ($requested === 'credit_card' && (int) $settings['credit_card_enabled'] === 1) return 'credit_card';
        if ((int) $settings['pix_enabled'] === 1) return 'pix';
        if ((int) $settings['credit_card_enabled'] === 1) return 'credit_card';

        return null;
    }

    public function cancel(string $message)
    {
        Session::flash('error', $message);
        return redirect()->route('front.checkout.cancle');
    }
}
