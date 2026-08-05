<?php

namespace App\Http\Controllers\Payment;

use App\Helpers\PriceHelper;
use App\Helpers\EmailHelper;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Setting;
use App\Models\ShippingService;
use App\Models\State;
use App\Services\MercadoPago\MercadoPagoCheckoutInput;
use App\Services\MercadoPago\MercadoPagoLegacyClient;
use App\Services\MercadoPago\MercadoPagoOrderSideEffects;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Log;

/**
 * Compatibilidade explicita com o checkout existente antes do PR #2.
 * Este controller nunca consulta mercadopago_settings nem o client v2.
 */
class MercadopagoLegacyController extends Controller
{
    public function __construct(
        protected MercadoPagoLegacyClient $legacyClient,
        protected MercadoPagoCheckoutInput $checkoutInput,
        protected MercadoPagoOrderSideEffects $orderSideEffects
    ) {}

    public function store(Request $request)
    {
        $this->checkoutInput->validate($request);
        PriceHelper::checkCheckout($request);

        $currency = $this->checkoutInput->activeCurrency();
        $settings = $this->mercadoPagoSettings();
        $paymentType = $this->checkoutInput->paymentType($request, $settings);

        if (!$paymentType) {
            return $this->checkoutInput->cancel('Nenhuma forma de pagamento do Mercado Pago está ativa.');
        }

        if ($paymentType === 'pix' && $currency->name !== 'BRL') {
            return $this->checkoutInput->cancel('Pix está disponível apenas para pagamentos em Real (BRL).');
        }

        if ($paymentType === 'credit_card' && !in_array($currency->name, ['USD', 'NGN', 'BRL'], true)) {
            return $this->checkoutInput->cancel('Moeda não suportada pelo Mercado Pago.');
        }

        if ($paymentType === 'credit_card') {
            $this->checkoutInput->validateCreditCard($request);
        } else {
            $this->checkoutInput->validatePix($request);
        }

        $checkout = $this->checkoutAmounts($request, $settings, $paymentType);

        try {
            [$payment, $pixExpiration] = $this->createPayment($request, $settings, $checkout, $paymentType);
        } catch (Throwable $exception) {
            Log::warning('Falha ao criar pagamento Mercado Pago legado.', [
                'exception_class' => get_class($exception),
            ]);

            return $this->checkoutInput->cancel('Não foi possível iniciar o pagamento pelo Mercado Pago.');
        }

        if (!$payment || !$payment->id) {
            return $this->checkoutInput->cancel($this->paymentFailureMessage($payment));
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

        return $this->checkoutInput->cancel($this->paymentFailureMessage($payment));
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
        $this->legacyClient->configure($settings['token']);
        $payment = $this->legacyClient->newPayment();
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

        $this->legacyClient->savePayment($payment);

        return [$payment, $pixExpiration];
    }

    protected function payer(Request $request): array
    {
        $billing = Session::get('billing_address', []);

        return [
            'email' => $request->input('bill_email') ?: ($billing['bill_email'] ?? EmailHelper::getEmail()),
            'first_name' => $request->input('bill_first_name') ?: ($billing['bill_first_name'] ?? ''),
            'last_name' => $request->input('bill_last_name') ?: ($billing['bill_last_name'] ?? ''),
            'identification' => [
                'type' => $request->input('docType', 'CPF'),
                'number' => preg_replace('/\D+/', '', (string) $request->input('docNumber')),
            ],
        ];
    }

    protected function createOrder(Request $request, array $checkout, object $payment, string $paymentStatus, array $paymentDetails): Order
    {
        $user = auth()->user();
        $order = Order::create([
            'state' => $request['state_id'] ? json_encode(State::findOrFail($request['state_id']), true) : null,
            'cart' => json_encode($checkout['cart'], true),
            'discount' => json_encode($checkout['discount'], true),
            'shipping' => json_encode($checkout['shipping'], true),
            'tax' => $checkout['total_tax'], 'state_price' => $checkout['state_price'],
            'gateway_fee' => $checkout['gateway_fee'],
            'shipping_info' => json_encode(Session::get('shipping_address'), true),
            'billing_info' => json_encode(Session::get('billing_address'), true),
            'payment_method' => $paymentDetails['mercadopago']['payment_type'] === 'pix' ? 'Mercado Pago - Pix' : 'Mercado Pago - Cartão de crédito',
            'txnid' => $payment->id, 'user_id' => $user?->id ?? 0,
            'payment_status' => $paymentStatus, 'payment_details' => json_encode($paymentDetails, JSON_UNESCAPED_UNICODE),
            'order_status' => 'Pending', 'transaction_number' => Str::random(10),
            'currency_sign' => PriceHelper::setCurrencySign(), 'currency_value' => PriceHelper::setCurrencyValue(),
        ]);
        $order->transaction_number = 'ORD-' . Carbon::now()->format('Ymd') . '-' . $order->id;
        $order->save();
        $this->orderSideEffects->register($order, $checkout);

        return $order;
    }

    protected function paymentDetails(object $payment, string $paymentType, array $checkout, ?Carbon $pixExpiration = null): array
    {
        $transactionData = data_get($payment, 'point_of_interaction.transaction_data');

        return ['mercadopago' => [
            'payment_id' => $payment->id, 'payment_type' => $paymentType,
            'payment_method_id' => $payment->payment_method_id,
            'payment_type_id' => $payment->payment_type_id,
            'status' => $payment->status, 'status_detail' => $payment->status_detail,
            'transaction_amount' => $checkout['total_amount'], 'gateway_fee' => $checkout['gateway_fee'],
            'qr_code' => data_get($transactionData, 'qr_code'),
            'qr_code_base64' => data_get($transactionData, 'qr_code_base64'),
            'ticket_url' => data_get($transactionData, 'ticket_url'),
            'expires_at' => $pixExpiration?->toDateTimeString(),
            'created_at' => Carbon::now()->toDateTimeString(),
        ]];
    }

    protected function paymentFailureMessage(?object $payment): string
    {
        if ($payment && isset($payment->error->causes[0]->description)) return $payment->error->causes[0]->description;
        if ($payment && $payment->status_detail) return 'Pagamento não aprovado: ' . $payment->status_detail;

        return 'Pagamento não aprovado pelo Mercado Pago.';
    }

    protected function finishCheckout(\App\Models\Order $order)
    {
        Session::put('order_id', $order->id);
        Session::forget('cart');
        Session::forget('discount');
        Session::forget('coupon');
    }

    public function webhook(Request $request)
    {
        $paymentId = data_get($request->all(), 'data.id')
            ?: $request->input('id')
            ?: $request->query('id')
            ?: $request->query('data_id');

        if (!$paymentId) {
            return response()->json(['status' => 'ignored']);
        }

        try {
            $settings = $this->mercadoPagoSettings();
            $this->legacyClient->configure($settings['token']);
            $payment = $this->legacyClient->findPayment((string) $paymentId);
        } catch (Throwable $exception) {
            Log::warning('Falha ao consultar webhook Mercado Pago legado.', [
                'payment_id' => $paymentId,
                'exception_class' => get_class($exception),
            ]);
            return response()->json(['status' => 'error'], 500);
        }

        if (!$payment) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $order = Order::where('txnid', (string) $payment->id)->first();
        if (!$order && $payment->external_reference) {
            $order = Order::where('transaction_number', $payment->external_reference)->first();
        }
        if (!$order) {
            return response()->json(['status' => 'order_not_found'], 404);
        }

        $details = json_decode($order->payment_details, true) ?: [];
        $details['mercadopago']['status'] = $payment->status;
        $details['mercadopago']['status_detail'] = $payment->status_detail;
        $details['mercadopago']['updated_at'] = Carbon::now()->toDateTimeString();
        $order->payment_details = json_encode($details, JSON_UNESCAPED_UNICODE);
        if ($payment->status === 'approved') {
            $order->payment_status = 'Paid';
        }
        $order->save();

        return response()->json(['status' => 'ok']);
    }

    protected function mercadoPagoSettings(): array
    {
        $data = PaymentSetting::whereUniqueKeyword('mercadopago')->firstOrFail();

        return array_merge([
            'public_key' => '', 'token' => '', 'check_sandbox' => 1,
            'pix_enabled' => 1, 'credit_card_enabled' => 1, 'debit_card_enabled' => 0,
            'pix_expiration_minutes' => 30, 'fee_pass_to_customer' => 0,
            'fee_percent' => 0, 'fee_fixed' => 0, 'max_installments' => 1,
        ], $data->convertJsonData() ?: []);
    }
}
