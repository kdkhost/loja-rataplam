<?php

namespace App\Repositories\Back;

use App\{
    Helpers\ImageHelper,
    Models\PaymentSetting
};

class PaymentSettingRepository
{

    /**
     * Show the data for updating resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment()
    {
        $bank = PaymentSetting::whereUniqueKeyword('bank')->first();
        $data['bank'] = $bank;

        $paypal = PaymentSetting::whereUniqueKeyword('paypal')->first();
        $data['paypalData'] = $paypal->convertJsonData();
        $data['paypal'] = $paypal;


        $molly = PaymentSetting::whereUniqueKeyword('mollie')->first();
        $data['mollyData'] = $molly->convertJsonData();
        $data['molly'] = $molly;

        $stripe = PaymentSetting::whereUniqueKeyword('stripe')->first();
        $data['stripeData'] = $stripe->convertJsonData();
        $data['stripe'] = $stripe;

        $paytm = PaymentSetting::whereUniqueKeyword('paytm')->first();
        $data['paytmData'] = $paytm->convertJsonData();
        $data['paytm'] = $paytm;

        $sslcommerz = PaymentSetting::whereUniqueKeyword('sslcommerz')->first();
        $data['sslcommerzData'] = $sslcommerz->convertJsonData();
        $data['sslcommerz'] = $sslcommerz;

        $mercadopago = PaymentSetting::whereUniqueKeyword('mercadopago')->first();
        $data['mercadopagoData'] = $mercadopago->convertJsonData();
        $data['mercadopago'] = $mercadopago;

        $authorize = PaymentSetting::whereUniqueKeyword('authorize')->first();
        $data['authorizeData'] = $authorize->convertJsonData();
        $data['authorize'] = $authorize;

        $flutterwave = PaymentSetting::whereUniqueKeyword('flutterwave')->first();
        $data['flutterwaveData'] = $flutterwave->convertJsonData();
        $data['flutterwave'] = $flutterwave;

        $razorpay = PaymentSetting::whereUniqueKeyword('razorpay')->first();
        $data['razorpayData'] = $razorpay->convertJsonData();
        $data['razorpay'] = $razorpay;

        $paystack = PaymentSetting::whereUniqueKeyword('paystack')->first();
        $data['paystackData'] = $paystack->convertJsonData();
        $data['paystack'] = $paystack;

        $paytabs = PaymentSetting::whereUniqueKeyword('paytabs')->first();
        
        $data['paytabsData'] = $paytabs->convertJsonData();
        $data['paytabs'] = $paytabs;
     
        $cod = PaymentSetting::whereUniqueKeyword('cod')->first();
        $data['cod'] = $cod;

        return $data;
    }

    /**
     * Update setting.
     *
     * @param  \App\Http\Requests\PaymentSettingRequest  $request
     * @return void
     */

    public function update($request)
    {

        $input = $request->all();
        $pay_data = PaymentSetting::whereUniqueKeyword($input['unique_keyword'])->first();

        if ($file = $request->file('photo')) {
            $input['photo'] = ImageHelper::handleUpdatedUploadedImage($file,'images',$pay_data,'images/','photo');
        }

       
        
        if($request->has('pkey')){

            $info_data = $input['pkey'];

            if($pay_data->unique_keyword == 'mollie'){
                $paydata = $pay_data->convertJsonData();
                $prev = $paydata['key'];
            }

           

            $checkboxFields = ['check_sandbox'];

            if ($pay_data->unique_keyword == 'paytm') {
                $checkboxFields[] = 'paytm_mode';
            }

            if ($pay_data->unique_keyword == 'mercadopago') {
                $checkboxFields = array_merge($checkboxFields, [
                    'pix_enabled',
                    'credit_card_enabled',
                    'fee_pass_to_customer',
                ]);
            }

            foreach ($checkboxFields as $checkboxField) {
                if (array_key_exists($checkboxField, $info_data)) {
                    $info_data[$checkboxField] = 1;
                } elseif (strpos((string) $pay_data->information, $checkboxField) !== false || in_array($checkboxField, ['pix_enabled', 'credit_card_enabled', 'fee_pass_to_customer'], true)) {
                    $info_data[$checkboxField] = 0;
                }
            }

            if ($pay_data->unique_keyword == 'mercadopago') {
                $info_data['debit_card_enabled'] = 0;
                $info_data['pix_expiration_minutes'] = max(5, min(4320, (int) ($info_data['pix_expiration_minutes'] ?? 30)));
                $info_data['fee_percent'] = max(0, min(100, (float) str_replace(',', '.', $info_data['fee_percent'] ?? 0)));
                $info_data['fee_fixed'] = max(0, (float) str_replace(',', '.', $info_data['fee_fixed'] ?? 0));
                $info_data['max_installments'] = max(1, min(12, (int) ($info_data['max_installments'] ?? 1)));
            }

            
        
            $input['information'] = json_encode($info_data);

        }

        if($request->has('status')){
            $input['status'] = 1;
        }else{

            $input['status'] = 0;
        }
        
 
        $pay_data->update($input);

        if($pay_data->unique_keyword == 'mollie'){
            $paydata = $pay_data->convertJsonData();
            $this->setEnv('MOLLIE_KEY',$input['pkey']['key'],$prev);
        }
    }

    private function setEnv($key, $value,$prev)
    {

        file_put_contents(app()->environmentFilePath(), str_replace(
            $key . '=' . $prev,
            $key . '=' . $value,
            file_get_contents(app()->environmentFilePath())
        ));

    }

}
