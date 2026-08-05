<?php

namespace App\Http\Controllers\Payment;

use App\Helpers\PriceHelper;
use App\Models\Item;
use App\Models\ShippingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use MercadoPago;
use Throwable;
use Illuminate\Support\Facades\Log;

/**
 * Compatibilidade explicita com o checkout existente antes do PR #2.
 * Este controller nunca consulta mercadopago_settings nem o client v2.
 */
class MercadopagoLegacyController extends MercadopagoV2Controller
{
    public function store(Request $request)
    {
        $this->validateCheckout($request);
        PriceHelper::checkCheckout($request);

        $currency = $this->activeCurrency();
        $settings = $this->mercadoPagoSettings();
        $paymentType = $this->resolvePaymentType($request, $settings);

        if (!$paymentType) {
            return $this->cancelWithMessage('Nenhuma forma de pagamento do Mercado Pago está ativa.');
        }

        if ($paymentType === 'pix' && $currency->name !== 'BRL') {
            return $this->cancelWithMessage('Pix está disponível apenas para pagamentos em Real (BRL).');
        }

        if ($paymentType === 'credit_card' && !in_array($currency->name, ['USD', 'NGN', 'BRL'], true)) {
            return $this->cancelWithMessage('Moeda não suportada pelo Mercado Pago.');
        }

        if ($paymentType === 'credit_card') {
            $this->validateCreditCardRequest($request);
        } else {
            $this->validatePixRequest($request);
        }

        $checkout = $this->checkoutAmounts($request, $settings, $paymentType);

        try {
            [$payment, $pixExpiration] = $this->createPayment($request, $settings, $checkout, $paymentType);
        } catch (Throwable $exception) {
            Log::warning('Falha ao criar pagamento Mercado Pago legado.', [
                'exception_class' => get_class($exception),
            ]);

            return $this->cancelWithMessage('Não foi possível iniciar o pagamento pelo Mercado Pago.');
        }

        if (!$payment || !$payment->id) {
            return $this->cancelWithMessage($this->paymentFailureMessage($payment));
        }

        if ($paymentType === 'pix') {
            $order = $this->createOrder($request, $checkout, $payment, 'Unpaid', $this->paymentDetails($payment, $paymentType, $checkout, $pixExpiration));
            $this->finishCheckout($order);

            return redirect()->route('front.checkout.success');
        }

        if ($payment->status === 'approved') {
            $order = $this->createOrder($request, $checkout, $payment, 'Paid', $this->paymentDetails($payment, $paymentType, $checkout));
            $this->finishCheckout($order);

            return redirect()->route('front.checkout.success');
        }

        return $this->cancelWithMessage($this->paymentFailureMessage($payment));
    }

    protected function checkoutAmounts(Request $request, array $settings, string $paymentType)
    {
        $cart = Session::get('cart');
        $totalTax = 0;
        $cartTotal = 0;
        $total = 0;
        $optionPrice = 0;

        foreach ($cart as $key => $items) {
            $total += $items['main_price'] * $items['qty'];
            $optionPrice += $items['attribute_price'];
            $cartTotal = $total + $optionPrice;
            $item = Item::findOrFail($key);

            if ($item->tax) {
                $totalTax += $item::taxCalculate($item) * $items['qty'];
            }
        }

        $shipping = PriceHelper::Digital() ? ShippingService::findOrFail($request['shipping_id']) : null;
        $discount = Session::has('coupon') ? Session::get('coupon') : [];
        $statePrice = PriceHelper::StatePrce($request->state_id, $cartTotal);
        $grandTotal = ($cartTotal + ($shipping ? $shipping->price : 0)) + $totalTax;
        $grandTotal -= $discount ? $discount['discount'] : 0;
        $grandTotal += $statePrice;
        $gatewayFee = $this->gatewayFee($grandTotal, $settings, $paymentType);
        $chargeTotal = $grandTotal + $gatewayFee;

        return [
            'cart' => $cart,
            'discount' => $discount,
            'shipping' => $shipping,
            'total_tax' => $totalTax,
            'cart_total' => $cartTotal,
            'state_price' => $statePrice,
            'gateway_fee' => $gatewayFee,
            'grand_total' => $grandTotal,
            'charge_total' => $chargeTotal,
            'total_amount' => PriceHelper::setConvertPrice($chargeTotal),
        ];
    }

    protected function gatewayFee(float $amount, array $settings, string $paymentType)
    {
        if ((int) ($settings['fee_pass_to_customer'] ?? 0) !== 1) {
            return 0;
        }

        $percent = max(0, (float) ($settings['fee_percent'] ?? 0));
        $fixed = max(0, (float) ($settings['fee_fixed'] ?? 0));

        return round((($amount * $percent) / 100) + $fixed, 2);
    }

    protected function createPayment(Request $request, array $settings, array $checkout, string $paymentType)
    {
        MercadoPago\SDK::setAccessToken($settings['token']);

        $payment = new MercadoPago\Payment();
        $payment->transaction_amount = (float) $checkout['total_amount'];
        $payment->description = \App\Models\Setting::first()->title . ' - Pedido';
        $payment->external_reference = 'RTP-' . Carbon::now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        $payment->notification_url = route('front.mercadopago.webhook');
        $payment->payer = $this->payer($request);

        $pixExpiration = null;
        if ($paymentType === 'pix') {
            $payment->payment_method_id = 'pix';
            $pixExpiration = Carbon::now('America/Sao_Paulo')->addMinutes((int) ($settings['pix_expiration_minutes'] ?? 30));
            $payment->date_of_expiration = $pixExpiration->copy()->utc()->format('Y-m-d\TH:i:s.000\Z');
        } else {
            $payment->token = $request->token;
            $payment->payment_method_id = $request->paymentMethodId;
            $payment->installments = max(1, min((int) ($settings['max_installments'] ?? 1), (int) $request->input('installments', 1)));
            $payment->binary_mode = true;
        }

        $payment->save();

        return [$payment, $pixExpiration];
    }

    protected function finishCheckout(\App\Models\Order $order)
    {
        Session::put('order_id', $order->id);
        Session::forget('cart');
        Session::forget('discount');
        Session::forget('coupon');
    }
}
