<?php

namespace App\Http\Controllers\Payment;

use App\Helpers\EmailHelper;
use App\Helpers\PriceHelper;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\ShippingService;
use App\Models\State;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoCheckoutCalculator;
use App\Services\MercadoPago\MercadoPagoCheckoutInput;
use App\Services\MercadoPago\MercadoPagoFeatureGate;
use App\Services\MercadoPago\MercadoPagoMoney;
use App\Services\MercadoPago\MercadoPagoOrderSideEffects;
use App\Services\MercadoPago\MercadoPagoPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

class MercadopagoV2Controller extends Controller
{
    public function __construct(
        protected MercadoPagoPaymentService $paymentService,
        protected MercadoPagoConfigResolver $configResolver,
        protected MercadoPagoCheckoutCalculator $checkoutCalculator,
        protected MercadoPagoMoney $money,
        protected MercadoPagoCheckoutInput $checkoutInput,
        protected MercadoPagoOrderSideEffects $orderSideEffects,
        protected MercadoPagoFeatureGate $featureGate
    ) {}

    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $this->checkoutInput->validate($request);
        PriceHelper::checkCheckout($request);

        $currency = $this->checkoutInput->activeCurrency();
        $settings = $this->configResolver->resolvePublicConfiguration();
        $paymentType = $this->checkoutInput->paymentType($request, $settings);

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
            $this->checkoutInput->validateCreditCard($request);
        } else {
            $this->checkoutInput->validatePix($request);
        }

        $checkout = $this->checkoutAmounts($request, $settings, $paymentType);

        return $this->processSecureCheckout($request, $checkout, $paymentType);
    }

    protected function processSecureCheckout(Request $request, array $checkout, string $paymentType)
    {
        if ($this->checkoutInput->activeCurrency()->name !== 'BRL') {
            abort(422, 'Moeda invalida para o Mercado Pago.');
        }

        $authoritativeAmount = $this->money->centsToDecimal($checkout['total_minor']);
        $environment = $this->configResolver->resolve()['mode'];
        try {
            $this->featureGate->assertCheckoutEnabled($environment);
        } catch (Throwable) {
            abort(503, 'Gateway temporariamente indisponivel.');
        }
        $order = $this->resolvePendingOrder($request, $checkout, $paymentType, $authoritativeAmount);

        try {
            $orderData = [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'authoritative_amount' => $authoritativeAmount,
                'currency' => 'BRL',
                'description' => Setting::first()->title . ' - Pedido ' . $order->id,
                'payer_email' => $request->input('bill_email') ?: EmailHelper::getEmail(),
                'installments' => (int) $request->input('installments', 1),
            ];

            $paymentData = $paymentType === 'pix'
                ? $this->paymentService->createPixPayment($orderData)
                : $this->paymentService->createCardPayment($orderData, [
                    'token' => $request->input('token'),
                    'payment_method_id' => $request->input('paymentMethodId'),
                    'installments' => (int) $request->input('installments', 1),
                    'identification_type' => $request->input('docType'),
                    'identification_number' => preg_replace('/\D+/', '', (string) $request->input('docNumber')),
                ]);
        } catch (Throwable $exception) {
            Log::warning('Falha ao criar pagamento Mercado Pago.', [
                'exception_class' => get_class($exception),
                'order_id' => $order->id,
            ]);

            return $this->cancelWithMessage('Nao foi possivel iniciar o pagamento pelo Mercado Pago.');
        }

        if (empty($paymentData['payment_id'])) {
            return $this->cancelWithMessage('Nao foi possivel identificar o pagamento criado.');
        }

        $this->persistPaymentOnOrder($order, $paymentData, $paymentType, $authoritativeAmount);
        $this->finishCheckout($order);

        return redirect()->route('front.checkout.success');
    }

    protected function resolvePendingOrder(
        Request $request,
        array $checkout,
        string $paymentType,
        string $authoritativeAmount
    ): Order {
        $pendingId = Session::get('mercadopago_pending_order_id');
        if ($request->filled('mercadopago_order_id')
            && (string) $request->input('mercadopago_order_id') !== (string) $pendingId) {
            abort(403);
        }

        if ($pendingId) {
            $pending = Order::find($pendingId);
            if (!$pending || (int) $pending->user_id !== (int) Auth::id()) {
                abort(403);
            }
            if ($pending->payment_status === 'Paid') {
                abort(409, 'Pedido ja pago.');
            }

            $details = json_decode((string) $pending->payment_details, true) ?: [];
            if (($details['mercadopago']['authoritative_amount'] ?? null) !== $authoritativeAmount
                || ($details['mercadopago']['currency'] ?? null) !== 'BRL') {
                abort(409, 'O total oficial do pedido foi alterado.');
            }

            return $pending;
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'state' => $request['state_id'] ? json_encode(State::findOrFail($request['state_id']), true) : null,
            'cart' => json_encode($checkout['cart'], true),
            'discount' => json_encode($checkout['discount'], true),
            'shipping' => json_encode($checkout['shipping'], true),
            'tax' => $checkout['total_tax'],
            'state_price' => $checkout['state_price'],
            'gateway_fee' => $checkout['gateway_fee'],
            'shipping_info' => json_encode(Session::get('shipping_address'), true),
            'billing_info' => json_encode(Session::get('billing_address'), true),
            'payment_method' => $paymentType === 'pix' ? 'Mercado Pago - Pix' : 'Mercado Pago - Cartao de credito',
            'txnid' => null,
            'transaction_number' => 'MP-PENDING-' . Str::uuid(),
            'order_status' => 'Pending',
            'payment_status' => 'Unpaid',
            'payment_details' => json_encode([
                'mercadopago' => [
                    'payment_type' => $paymentType,
                    'authoritative_amount' => $authoritativeAmount,
                    'currency' => 'BRL',
                    'side_effects_registered' => false,
                ],
            ], JSON_UNESCAPED_UNICODE),
            'currency_sign' => PriceHelper::setCurrencySign(),
            'currency_value' => PriceHelper::setCurrencyValue(),
        ]);
        $order->transaction_number = 'ORD-' . Carbon::now()->format('Ymd') . '-' . $order->id;
        $order->save();
        Session::put('mercadopago_pending_order_id', $order->id);

        return $order;
    }

    protected function persistPaymentOnOrder(
        Order $order,
        array $paymentData,
        string $paymentType,
        string $authoritativeAmount
    ): void {
        $details = json_decode((string) $order->payment_details, true) ?: [];
        $alreadyRegistered = (bool) data_get($details, 'mercadopago.side_effects_registered', false);
        $details['mercadopago'] = array_merge($details['mercadopago'] ?? [], [
            'payment_id' => (string) $paymentData['payment_id'],
            'payment_type' => $paymentType,
            'status' => $paymentData['status'] ?? null,
            'authoritative_amount' => $authoritativeAmount,
            'currency' => 'BRL',
            'qr_code' => $paymentData['qr_code'] ?? null,
            'qr_code_base64' => $paymentData['qr_code_base64'] ?? null,
            'ticket_url' => $paymentData['ticket_url'] ?? null,
            'expires_at' => $paymentData['expiration_date'] ?? null,
            'side_effects_registered' => true,
        ]);
        $order->txnid = (string) $paymentData['payment_id'];
        $order->payment_status = ($paymentData['status'] ?? null) === 'approved' ? 'Paid' : 'Unpaid';
        $order->payment_details = json_encode($details, JSON_UNESCAPED_UNICODE);
        $order->save();

        if (!$alreadyRegistered) {
            $this->orderSideEffects->register($order, [
                'cart' => json_decode((string) $order->cart, true) ?: [],
                'discount' => json_decode((string) $order->discount, true) ?: [],
                'total_amount' => $authoritativeAmount,
            ]);
        }
    }

    protected function checkoutAmounts(Request $request, array $settings, string $paymentType)
    {
        $cart = Session::get('cart');
        $shipping = PriceHelper::Digital() ? ShippingService::findOrFail($request['shipping_id']) : null;
        $discount = Session::has('coupon') ? Session::get('coupon') : [];
        $couponId = data_get($discount, 'code.id');
        $coupon = $couponId ? PromoCode::findOrFail($couponId) : null;
        $state = $request->state_id ? State::findOrFail($request->state_id) : null;
        $calculated = $this->checkoutCalculator->calculate(
            $cart,
            $shipping,
            $coupon,
            $state,
            $this->checkoutInput->activeCurrency(),
            $settings
        );

        return [
            'cart' => $cart,
            'discount' => $discount,
            'shipping' => $shipping,
            'total_tax' => $this->money->centsToDecimal($calculated['tax']),
            'cart_total' => $this->money->centsToDecimal($calculated['subtotal']),
            'state_price' => $this->money->centsToDecimal($calculated['stateMinor']),
            'gateway_fee' => $this->money->centsToDecimal($calculated['feeMinor']),
            'grand_total' => $calculated['totalDecimal'],
            'charge_total' => $calculated['totalDecimal'],
            'total_amount' => $calculated['totalDecimal'],
            'total_minor' => $calculated['totalMinor'],
        ];
    }

    protected function finishCheckout(Order $order)
    {
        Session::put('order_id', $order->id);
        Session::forget('cart');
        Session::forget('discount');
        Session::forget('coupon');
        Session::forget('mercadopago_pending_order_id');
    }

    protected function cancelWithMessage(string $message)
    {
        Session::put('message', $message);

        return redirect()->route('front.checkout.cancle');
    }
}
