<?php
namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoMoney;
use PHPUnit\Framework\TestCase;

class MercadoPagoMoneyTest extends TestCase
{
    private MercadoPagoMoney $money;

    protected function setUp(): void
    {
        $this->money = new MercadoPagoMoney();
    }

    public function test_decimal_to_cents_zero_point_zero_one()
    {
        $this->assertEquals(1, $this->money->decimalToCents('0.01'));
    }

    public function test_decimal_to_cents_one()
    {
        $this->assertEquals(100, $this->money->decimalToCents('1'));
    }

    public function test_decimal_to_cents_one_point_zero_zero()
    {
        $this->assertEquals(100, $this->money->decimalToCents('1.00'));
    }

    public function test_decimal_to_cents_ten_point_five()
    {
        $this->assertEquals(1050, $this->money->decimalToCents('10.50'));
    }

    public function test_decimal_to_cents_one_hundred_point_zero_zero()
    {
        $this->assertEquals(10000, $this->money->decimalToCents('100.00'));
    }

    public function test_decimal_to_cents_large_value()
    {
        $this->assertEquals(99999999, $this->money->decimalToCents('999999.99'));
    }

    public function test_decimal_to_cents_rejects_empty_string()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('');
    }

    public function test_decimal_to_cents_rejects_internal_spaces()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('1 0.50');
    }

    public function test_decimal_to_cents_rejects_negative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('-10.50');
    }

    public function test_decimal_to_cents_rejects_scientific_notation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('1e5');
    }

    public function test_decimal_to_cents_rejects_more_than_two_decimals()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('10.505');
    }

    public function test_decimal_to_cents_rejects_nan()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('NaN');
    }

    public function test_decimal_to_cents_rejects_inf()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('INF');
    }

    public function test_decimal_to_cents_rejects_non_numeric()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents('abc');
    }

    public function test_cents_to_api_amount_integer()
    {
        $this->assertEquals(100, $this->money->centsToApiAmount(100));
    }

    public function test_cents_to_api_amount_decimal()
    {
        $this->assertEquals(10.50, $this->money->centsToApiAmount(1050));
    }

    public function test_cents_to_api_amount_rejects_negative()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->money->centsToApiAmount(-100);
    }
}
