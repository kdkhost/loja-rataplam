<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'photo'  => 'mimes:jpeg,jpg,png,svg'
        ];

        if ($this->input('unique_keyword') === 'mercadopago') {
            $rules = array_merge($rules, [
                'pkey.public_key' => 'required|string|max:255',
                'pkey.token' => 'required|string|max:500',
                'pkey.pix_expiration_minutes' => 'nullable|integer|min:5|max:4320',
                'pkey.fee_percent' => ['nullable', 'regex:/^\d+([,.]\d{1,2})?$/'],
                'pkey.fee_fixed' => ['nullable', 'regex:/^\d+([,.]\d{1,2})?$/'],
                'pkey.max_installments' => 'nullable|integer|min:1|max:12',
            ]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'photo.mimes' => __('Image type must be jpg,jpeg,png,svg.'),
            'pkey.public_key.required' => 'Informe a chave pública do Mercado Pago.',
            'pkey.token.required' => 'Informe o access token do Mercado Pago.',
            'pkey.pix_expiration_minutes.integer' => 'O tempo de expiração do Pix deve ser informado em minutos.',
            'pkey.pix_expiration_minutes.min' => 'O Pix precisa expirar em no mínimo 5 minutos.',
            'pkey.pix_expiration_minutes.max' => 'O Pix pode expirar em no máximo 4320 minutos.',
            'pkey.fee_percent.regex' => 'A taxa percentual deve ser numérica e pode usar vírgula.',
            'pkey.fee_fixed.regex' => 'A taxa fixa deve ser numérica e pode usar vírgula.',
            'pkey.max_installments.integer' => 'A quantidade de parcelas deve ser um número inteiro.',
        ];
    }

}
