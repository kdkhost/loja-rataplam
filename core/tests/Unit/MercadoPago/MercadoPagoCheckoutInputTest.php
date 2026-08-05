<?php

namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoCheckoutInput;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class MercadoPagoCheckoutInputTest extends TestCase
{
    public function test_explicit_pix_is_selected_when_enabled(): void
    {
        $input = new MercadoPagoCheckoutInput();
        $request = Request::create('/', 'POST', ['mercadopago_payment_type' => 'pix']);

        $this->assertSame('pix', $input->paymentType($request, ['pix_enabled'=>1,'credit_card_enabled'=>1]));
    }

    public function test_explicit_card_is_selected_when_enabled(): void
    {
        $input = new MercadoPagoCheckoutInput();
        $request = Request::create('/', 'POST', ['mercadopago_payment_type' => 'credit_card']);

        $this->assertSame('credit_card', $input->paymentType($request, ['pix_enabled'=>1,'credit_card_enabled'=>1]));
    }

    public function test_no_enabled_method_returns_null(): void
    {
        $input = new MercadoPagoCheckoutInput();
        $request = Request::create('/', 'POST');

        $this->assertNull($input->paymentType($request, ['pix_enabled'=>0,'credit_card_enabled'=>0]));
    }
}
