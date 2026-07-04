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
        unset($information['paytm_mode']);
        $information['debit_card_enabled'] = 0;

        DB::table('payment_settings')
            ->where('unique_keyword', 'mercadopago')
            ->update([
                'information' => json_encode($information, JSON_UNESCAPED_UNICODE),
            ]);
    }

    public function down()
    {
        //
    }
};
