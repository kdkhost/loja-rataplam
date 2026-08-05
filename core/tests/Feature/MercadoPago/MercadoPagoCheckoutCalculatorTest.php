<?php

namespace Tests\Feature\MercadoPago;

use App\Models\Currency;
use App\Models\PromoCode;
use App\Models\ShippingService;
use App\Models\State;
use App\Services\MercadoPago\MercadoPagoCheckoutCalculator;
use Illuminate\Support\Facades\DB;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoCheckoutCalculatorTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        DB::table('currencies')->insert(['id' => 1, 'name' => 'BRL', 'value' => '1.00000000']);
        DB::table('items')->insert([
            ['id' => 1, 'name' => 'A', 'discount_price' => '10.50'],
            ['id' => 2, 'name' => 'B', 'discount_price' => '0.20'],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_multiple_items_and_quantity_use_integer_minor_units(): void
    {
        $result = $this->calculate([
            '1-a' => ['qty' => 3, 'options_id' => [], 'main_price' => '99999.99'],
            '2-b' => ['qty' => 1, 'options_id' => []],
        ]);

        $this->assertSame(3170, $result['subtotal']);
        $this->assertSame(3170, $result['totalMinor']);
        $this->assertSame('31.70', $result['totalDecimal']);
    }

    public function test_discount_shipping_state_and_fee_are_exact(): void
    {
        DB::table('shipping_services')->insert(['id' => 1, 'title' => 'Entrega', 'price' => '5.00']);
        DB::table('promo_codes')->insert(['id' => 1, 'discount' => '10.0000', 'type' => 'percentage']);
        DB::table('states')->insert(['id' => 1, 'price' => '2.00', 'type' => 'fixed']);

        $result = $this->calculate(
            ['1-a' => ['qty' => 2, 'options_id' => []]],
            ShippingService::find(1),
            PromoCode::find(1),
            State::find(1),
            ['fee_pass_to_customer' => 1, 'fee_percent' => '10.0000', 'fee_fixed' => '1.00']
        );

        $this->assertSame(210, $result['discountMinor']);
        $this->assertSame(500, $result['shippingMinor']);
        $this->assertSame(200, $result['stateMinor']);
        $this->assertSame(359, $result['feeMinor']);
        $this->assertSame('29.49', $result['totalDecimal']);
    }

    public function test_option_price_is_loaded_from_database(): void
    {
        DB::table('attribute_options')->insert(['id' => 7, 'name' => 'Extra', 'price' => '0.10']);
        $result = $this->calculate([
            '2-b' => ['qty' => 1, 'options_id' => [7], 'attribute_price' => '5000.00'],
        ]);

        $this->assertSame('0.30', $result['totalDecimal']);
    }

    public function test_zero_total_is_rejected(): void
    {
        DB::table('items')->where('id', 1)->update(['discount_price' => '0.00']);
        $this->expectException(\InvalidArgumentException::class);
        $this->calculate(['1-a' => ['qty' => 1, 'options_id' => []]]);
    }

    private function calculate(
        array $cart,
        ?ShippingService $shipping = null,
        ?PromoCode $coupon = null,
        ?State $state = null,
        array $settings = []
    ): array {
        return app(MercadoPagoCheckoutCalculator::class)->calculate(
            $cart,
            $shipping,
            $coupon,
            $state,
            Currency::findOrFail(1),
            $settings
        );
    }
}
