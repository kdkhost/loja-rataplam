<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $payment = DB::table('payment_settings')->where('unique_keyword', 'mercadopago')->first();

        if (!$payment) {
            return;
        }

        $information = json_decode($payment->information, true) ?: [];
        $information = array_merge([
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
        ], $information);

        $information['debit_card_enabled'] = 0;

        DB::table('payment_settings')
            ->where('unique_keyword', 'mercadopago')
            ->update([
                'name' => $payment->name === 'Mercadopago' ? 'Mercado Pago' : $payment->name,
                'text' => 'Pague com Pix ou cartão de crédito pelo Mercado Pago com confirmação segura.',
                'information' => json_encode($information, JSON_UNESCAPED_UNICODE),
            ]);
    }

    public function down()
    {
        $payment = DB::table('payment_settings')->where('unique_keyword', 'mercadopago')->first();

        if (!$payment) {
            return;
        }

        $information = json_decode($payment->information, true) ?: [];

        foreach ([
            'pix_enabled',
            'credit_card_enabled',
            'debit_card_enabled',
            'pix_expiration_minutes',
            'fee_pass_to_customer',
            'fee_percent',
            'fee_fixed',
            'max_installments',
        ] as $key) {
            unset($information[$key]);
        }

        DB::table('payment_settings')
            ->where('unique_keyword', 'mercadopago')
            ->update([
                'information' => json_encode($information, JSON_UNESCAPED_UNICODE),
            ]);
    }
};
