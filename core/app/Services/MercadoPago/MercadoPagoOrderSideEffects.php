<?php

namespace App\Services\MercadoPago;

use App\Helpers\EmailHelper;
use App\Helpers\PriceHelper;
use App\Helpers\SmsHelper;
use App\Jobs\EmailSendJob;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\Setting;
use App\Models\TrackOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MercadoPagoOrderSideEffects
{
    public function register(Order $order, array $checkout): void
    {
        PriceHelper::Transaction($order->id, $order->transaction_number, EmailHelper::getEmail(), PriceHelper::OrderTotal($order, 'trns'));
        PriceHelper::LicenseQtyDecrese($checkout['cart']);
        PriceHelper::stockDecrese();
        TrackOrder::create(['title' => 'Pending', 'order_id' => $order->id]);
        Notification::create(['order_id' => $order->id]);

        if (Session::has('copon')) {
            $code = PromoCode::find(Session::get('copon')['code']['id']);
            if ($code) { $code->no_of_times--; $code->update(); }
        }
        if ($checkout['discount']) {
            $coupon = PromoCode::findOrFail($checkout['discount']['code']['id']);
            $coupon->no_of_times -= 1;
            $coupon->update();
        }

        $setting = Setting::first();
        if ($setting->is_twilio == 1) {
            $number = json_decode($order->billing_info, true)['bill_phone'] ?? null;
            if ($number) (new SmsHelper())->SendSms($number, "'purchase'", $order->transaction_number);
        }

        $this->sendEmail($order, $checkout['total_amount']);
    }

    private function sendEmail(Order $order, string $totalAmount): void
    {
        $user = Auth::user();
        $data = [
            'to' => EmailHelper::getEmail(), 'type' => 'Order',
            'user_name' => $user ? $user->displayName() : Session::get('billing_address')['bill_first_name'],
            'order_cost' => $totalAmount, 'transaction_number' => $order->transaction_number,
            'site_title' => Setting::first()->title,
        ];
        if (Setting::first()->is_queue_enabled == 1) {
            dispatch(new EmailSendJob($data, 'template'));
            return;
        }
        (new EmailHelper())->sendTemplateMail($data, 'template');
    }
}
