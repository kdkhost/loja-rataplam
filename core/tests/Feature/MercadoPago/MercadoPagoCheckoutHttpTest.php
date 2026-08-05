<?php

namespace Tests\Feature\MercadoPago;

use Tests\TestCase;

class MercadoPagoCheckoutHttpTest extends TestCase
{
    public function test_checkout_submission_requires_authenticated_customer(): void
    {
        $this->post('/mercadopago/submit', [
            'mercadopago_payment_type' => 'pix',
            'amount' => '0.01',
        ])->assertRedirect('/user/login');
    }
}
