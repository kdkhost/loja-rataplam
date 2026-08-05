<?php

namespace Tests\Feature\MercadoPago;

use App\Models\User;
use App\Services\MercadoPago\MercadoPagoLegacyClient;
use Illuminate\Support\Facades\DB;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoLegacyCheckoutHttpTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    private ?object $capturedPayment = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        $this->withoutMiddleware([
            \App\Http\Middleware\Maintainance::class,
            \App\Http\Middleware\Localization::class,
        ]);
        DB::table('users')->insert(['id'=>30,'email'=>'legacy@example.test','password'=>bcrypt('synthetic')]);
        DB::table('settings')->insert(['id'=>1,'unique_keyword'=>'system','title'=>'Loja Teste']);
        DB::table('currencies')->insert(['id'=>1,'name'=>'BRL','value'=>'1.00000000','is_default'=>1]);
        DB::table('items')->insert(['id'=>1,'name'=>'Produto','discount_price'=>'10.50']);
        DB::table('shipping_services')->insert(['id'=>1,'title'=>'Digital','price'=>0,'status'=>1]);
        DB::table('payment_settings')->insert([
            'unique_keyword'=>'mercadopago',
            'information'=>json_encode([
                'token'=>'legacy-synthetic-token','pix_enabled'=>0,'credit_card_enabled'=>1,'max_installments'=>3,
                'fee_pass_to_customer'=>0,'pix_expiration_minutes'=>30,
            ]),
            'status'=>1,
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_real_legacy_route_uses_only_legacy_sdk_boundary(): void
    {
        $client = $this->createMock(MercadoPagoLegacyClient::class);
        $client->expects($this->once())->method('configure')->with('legacy-synthetic-token');
        $payment = new \stdClass();
        $client->expects($this->once())->method('newPayment')->willReturn($payment);
        $client->expects($this->once())->method('savePayment')->willReturnCallback(function (object $payment): void {
            $this->capturedPayment = $payment;
            $payment->id = 'legacy-payment';
            $payment->status = 'rejected';
            $payment->status_detail = 'cc_rejected_other_reason';
        });
        $this->app->instance(MercadoPagoLegacyClient::class, $client);

        $response = $this->actingAs(User::findOrFail(30))->withSession([
            'cart'=>[1=>['qty'=>1,'main_price'=>'10.50','attribute_price'=>0,'options_id'=>[],'type'=>'digital','item_type'=>'digital']],
        ])->post('/mercadopago/submit', [
            'mercadopago_payment_type'=>'credit_card','docType'=>'CPF','docNumber'=>'12345678901','shipping_id'=>1,
            'token'=>'synthetic-card-token','paymentMethodId'=>'visa','paymentTypeId'=>'credit_card','installments'=>2,
        ]);

        $response->assertRedirect(route('front.checkout.cancle'));
        $this->assertSame(10.5, $this->capturedPayment->transaction_amount);
        $this->assertSame('visa', $this->capturedPayment->payment_method_id);
        $this->assertDatabaseCount('mercadopago_actions', 0);
        $this->assertDatabaseCount('orders', 0);
    }
}
