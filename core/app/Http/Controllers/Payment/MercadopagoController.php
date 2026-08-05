<?php

namespace App\Http\Controllers\Payment;

use App\Helpers\EmailHelper;
use App\Helpers\PriceHelper;
use App\Helpers\SmsHelper;
use App\Http\Controllers\Controller;
use App\Jobs\EmailSendJob;
use App\Models\Currency;
use App\Models\Item;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\ShippingService;
use App\Models\State;
use App\Models\TrackOrder;
use App\Services\MercadoPago\MercadoPagoConfigResolver;
use App\Services\MercadoPago\MercadoPagoPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use MercadoPago;
use Throwable;

class MercadopagoController extends Controller
{
    public function __construct(
        protected MercadoPagoPaymentService $paymentService,
        protected MercadoPagoConfigResolver $configResolver
    ) {}

    public function store(Request $request)
    {
        if (!Auth::check()) {
            abort(401);
        }

        $this->validateCheckout($request);
        PriceHelper::checkCheckout($request);

        $currency = $this->activeCurrency();
        $settings = array_merge($this->mercadoPagoSettings(), $this->configResolver->resolvePublicConfiguration());
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

        return $this->processSecureCheckout($request, $checkout, $paymentType);

        try {
            [$payment, $pixExpiration] = $this->createPayment($request, $settings, $checkout, $paymentType);
        } catch (Throwable $exception) {
            Log::warning('Falha ao criar pagamento Mercado Pago.', [
                'message' => $exception->getMessage(),
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

    protected function processSecureCheckout(Request $request, array $checkout, string $paymentType)
    {
        if ($this->activeCurrency()->name !== 'BRL') {
            abort(422, 'Moeda invalida para o Mercado Pago.');
        }

        $authoritativeAmount = number_format((float) $checkout['total_amount'], 2, '.', '');
        $order = $this->resolvePendingOrder($request, $checkout, $paymentType, $authoritativeAmount);

        try {
            $orderData = [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'authoritative_amount' => $authoritativeAmount,
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
            $this->registerOrderSideEffects($order, [
                'cart' => json_decode((string) $order->cart, true) ?: [],
                'discount' => json_decode((string) $order->discount, true) ?: [],
                'total_amount' => $authoritativeAmount,
            ]);
        }
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
            MercadoPago\SDK::setAccessToken($settings['token']);
            $payment = MercadoPago\Payment::find_by_id($paymentId);
        } catch (Throwable $exception) {
            Log::warning('Falha ao consultar webhook Mercado Pago.', [
                'payment_id' => $paymentId,
                'message' => $exception->getMessage(),
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

    protected function validateCheckout(Request $request)
    {
        $state = State::whereStatus(1)->count() != 0 ? 'required' : '';
        $shipping = ShippingService::whereStatus(1)->count() == 0 || PriceHelper::CheckDigital() == true ? 'required' : '';

        if ($request->single_page_checkout == 1) {
            $request->validate([
                'state_id' => $state,
                'shipping_id' => $shipping,
                'bill_first_name' => 'required',
                'bill_last_name' => 'required',
                'bill_email' => 'required|email',
                'bill_phone' => 'required',
                'bill_address1' => 'required',
                'bill_city' => 'required',
                'bill_zip' => 'required',
            ]);

            return;
        }

        $request->validate([
            'state_id' => $state,
            'shipping_id' => $shipping,
        ]);
    }

    protected function validateCreditCardRequest(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'paymentMethodId' => 'required|string',
            'paymentTypeId' => 'nullable|string',
            'docType' => 'required|in:CPF,CNPJ',
            'docNumber' => 'required|string|min:11|max:18',
        ], [
            'token.required' => 'Informe os dados do cartão de crédito.',
            'paymentMethodId.required' => 'Não foi possível identificar a bandeira do cartão.',
            'docType.in' => 'Use CPF ou CNPJ como documento.',
            'docNumber.required' => 'Informe o número do CPF ou CNPJ.',
        ]);

        $method = strtolower((string) $request->paymentMethodId);
        $type = strtolower((string) $request->paymentTypeId);

        if ($type === 'debit_card' || str_starts_with($method, 'deb')) {
            abort(422, 'Cartão de débito não é aceito nesta operação.');
        }
    }

    protected function validatePixRequest(Request $request)
    {
        $request->validate([
            'docType' => 'required|in:CPF,CNPJ',
            'docNumber' => 'required|string|min:11|max:18',
        ], [
            'docType.in' => 'Use CPF ou CNPJ como documento.',
            'docNumber.required' => 'Informe o número do CPF ou CNPJ.',
        ]);
    }

    protected function activeCurrency()
    {
        if (Session::has('currency')) {
            $currency = Currency::find(Session::get('currency'));

            if ($currency) {
                return $currency;
            }
        }

        return Currency::where('is_default', 1)->first();
    }

    protected function mercadoPagoSettings()
    {
        $data = PaymentSetting::whereUniqueKeyword('mercadopago')->firstOrFail();
        $paydata = $data->convertJsonData() ?: [];

        return array_merge([
            'public_key' => '',
            'token' => '',
            'check_sandbox' => 1,
            'pix_enabled' => 1,
            'credit_card_enabled' => 1,
            'debit_card_enabled' => 0,
            'pix_expiration_minutes' => 30,
            'fee_pass_to_customer' => 0,
            'fee_percent' => 0,
            'fee_fixed' => 0,
            'max_installments' => 1,
        ], $paydata);
    }

    protected function resolvePaymentType(Request $request, array $settings)
    {
        $requested = $request->input('mercadopago_payment_type');

        if ($requested === 'pix' && (int) $settings['pix_enabled'] === 1) {
            return 'pix';
        }

        if ($requested === 'credit_card' && (int) $settings['credit_card_enabled'] === 1) {
            return 'credit_card';
        }

        if ((int) $settings['pix_enabled'] === 1) {
            return 'pix';
        }

        if ((int) $settings['credit_card_enabled'] === 1) {
            return 'credit_card';
        }

        return null;
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
        $grandTotal = $grandTotal - ($discount ? $discount['discount'] : 0);
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
        $fee = (($amount * $percent) / 100) + $fixed;

        return round($fee, 2);
    }

    protected function createPayment(Request $request, array $settings, array $checkout, string $paymentType)
    {
        MercadoPago\SDK::setAccessToken($settings['token']);

        $payment = new MercadoPago\Payment();
        $payment->transaction_amount = (float) $checkout['total_amount'];
        $payment->description = Setting::first()->title . ' - Pedido';
        $payment->external_reference = 'RTP-' . Carbon::now()->format('YmdHis') . '-' . Str::upper(Str::random(6));
        $payment->notification_url = route('front.mercadopago.webhook');
        $payment->payer = $this->payer($request);

        $pixExpiration = null;

        if ($paymentType === 'pix') {
            $pixExpiration = Carbon::now('America/Sao_Paulo')->addMinutes((int) $settings['pix_expiration_minutes']);
            $payment->payment_method_id = 'pix';
            $payment->date_of_expiration = $pixExpiration->format('Y-m-d\TH:i:s.000P');
        } else {
            $payment->token = $request->token;
            $payment->installments = max(1, min((int) ($settings['max_installments'] ?? 1), (int) $request->input('installments', 1)));
            $payment->payment_method_id = $request->paymentMethodId;
            $payment->binary_mode = true;
        }

        $payment->save();

        return [$payment, $pixExpiration];
    }

    protected function payer(Request $request)
    {
        $billing = Session::get('billing_address', []);
        $docType = $request->input('docType', 'CPF');
        $docNumber = preg_replace('/\D+/', '', (string) $request->input('docNumber'));

        return [
            'email' => $request->input('bill_email') ?: ($billing['bill_email'] ?? EmailHelper::getEmail()),
            'first_name' => $request->input('bill_first_name') ?: ($billing['bill_first_name'] ?? ''),
            'last_name' => $request->input('bill_last_name') ?: ($billing['bill_last_name'] ?? ''),
            'identification' => [
                'type' => $docType,
                'number' => $docNumber,
            ],
        ];
    }

    protected function createOrder(Request $request, array $checkout, $payment, string $paymentStatus, array $paymentDetails)
    {
        $user = Auth::user();
        $orderData['state'] = $request['state_id'] ? json_encode(State::findOrFail($request['state_id']), true) : null;
        $orderData['cart'] = json_encode($checkout['cart'], true);
        $orderData['discount'] = json_encode($checkout['discount'], true);
        $orderData['shipping'] = json_encode($checkout['shipping'], true);
        $orderData['tax'] = $checkout['total_tax'];
        $orderData['state_price'] = $checkout['state_price'];
        $orderData['gateway_fee'] = $checkout['gateway_fee'];
        $orderData['shipping_info'] = json_encode(Session::get('shipping_address'), true);
        $orderData['billing_info'] = json_encode(Session::get('billing_address'), true);
        $orderData['payment_method'] = $paymentDetails['mercadopago']['payment_type'] === 'pix' ? 'Mercado Pago - Pix' : 'Mercado Pago - Cartão de crédito';
        $orderData['txnid'] = $payment->id;
        $orderData['user_id'] = isset($user) ? $user->id : 0;
        $orderData['payment_status'] = $paymentStatus;
        $orderData['payment_details'] = json_encode($paymentDetails, JSON_UNESCAPED_UNICODE);
        $orderData['order_status'] = 'Pending';
        $orderData['transaction_number'] = Str::random(10);
        $orderData['currency_sign'] = PriceHelper::setCurrencySign();
        $orderData['currency_value'] = PriceHelper::setCurrencyValue();
        $order = Order::create($orderData);

        $order->transaction_number = 'ORD-' . str_pad(Carbon::now()->format('Ymd'), 4, '0000', STR_PAD_LEFT) . '-' . $order->id;
        $order->save();

        $this->registerOrderSideEffects($order, $checkout);

        return $order;
    }

    protected function registerOrderSideEffects(Order $order, array $checkout)
    {
        PriceHelper::Transaction($order->id, $order->transaction_number, EmailHelper::getEmail(), PriceHelper::OrderTotal($order, 'trns'));
        PriceHelper::LicenseQtyDecrese($checkout['cart']);
        PriceHelper::stockDecrese();

        TrackOrder::create([
            'title' => 'Pending',
            'order_id' => $order->id,
        ]);

        Notification::create([
            'order_id' => $order->id,
        ]);

        if (Session::has('copon')) {
            $code = PromoCode::find(Session::get('copon')['code']['id']);
            if ($code) {
                $code->no_of_times--;
                $code->update();
            }
        }

        if ($checkout['discount']) {
            $couponId = $checkout['discount']['code']['id'];
            $coupon = PromoCode::findOrFail($couponId);
            $coupon->no_of_times -= 1;
            $coupon->update();
        }

        $setting = Setting::first();
        if ($setting->is_twilio == 1) {
            $sms = new SmsHelper();
            $userNumber = json_decode($order->billing_info, true)['bill_phone'] ?? null;

            if ($userNumber) {
                $sms->SendSms($userNumber, "'purchase'", $order->transaction_number);
            }
        }

        $this->sendOrderEmail($order, $checkout['total_amount']);
    }

    protected function sendOrderEmail(Order $order, float $totalAmount)
    {
        $user = Auth::user();
        $emailData = [
            'to' => EmailHelper::getEmail(),
            'type' => 'Order',
            'user_name' => isset($user) ? $user->displayName() : Session::get('billing_address')['bill_first_name'],
            'order_cost' => $totalAmount,
            'transaction_number' => $order->transaction_number,
            'site_title' => Setting::first()->title,
        ];

        $setting = Setting::first();

        if ($setting->is_queue_enabled == 1) {
            dispatch(new EmailSendJob($emailData, 'template'));

            return;
        }

        $email = new EmailHelper();
        $email->sendTemplateMail($emailData, 'template');
    }

    protected function paymentDetails($payment, string $paymentType, array $checkout, ?Carbon $pixExpiration = null)
    {
        $transactionData = data_get($payment, 'point_of_interaction.transaction_data');

        return [
            'mercadopago' => [
                'payment_id' => $payment->id,
                'payment_type' => $paymentType,
                'payment_method_id' => $payment->payment_method_id,
                'payment_type_id' => $payment->payment_type_id,
                'status' => $payment->status,
                'status_detail' => $payment->status_detail,
                'transaction_amount' => $checkout['total_amount'],
                'gateway_fee' => $checkout['gateway_fee'],
                'qr_code' => data_get($transactionData, 'qr_code'),
                'qr_code_base64' => data_get($transactionData, 'qr_code_base64'),
                'ticket_url' => data_get($transactionData, 'ticket_url'),
                'expires_at' => $pixExpiration ? $pixExpiration->toDateTimeString() : null,
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
        ];
    }

    protected function paymentFailureMessage($payment)
    {
        if ($payment && isset($payment->error->causes[0]->description)) {
            return $payment->error->causes[0]->description;
        }

        if ($payment && $payment->status_detail) {
            return 'Pagamento não aprovado: ' . $payment->status_detail;
        }

        return 'Pagamento não aprovado pelo Mercado Pago.';
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
