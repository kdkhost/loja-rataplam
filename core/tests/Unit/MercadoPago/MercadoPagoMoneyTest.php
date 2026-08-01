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
        $this->assertEquals(1, $this->money->centsToApiAmount(100));
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

    public function test_decimal_to_cents_max_safe_value()
    {
        // Usar um valor seguro que sabemos que não causa overflow
        // Em sistemas 64-bit, PHP_INT_MAX é 9223372036854775807
        // PHP_INT_MAX / 100 = 92233720368547758
        // Vamos usar um valor um pouco menor para garantir segurança
        $safeValue = '92233720368547750.99';
        $expectedCents = 9223372036854775099;
        $this->assertEquals($expectedCents, $this->money->decimalToCents($safeValue));
    }

    public function test_decimal_to_cents_rejects_one_cent_above_limit()
    {
        $maxSafeInteger = (int) floor(PHP_INT_MAX / 100);
        $aboveLimit = ($maxSafeInteger + 1) . '.00';
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents($aboveLimit);
    }

    public function test_decimal_to_cents_rejects_hundreds_of_digits()
    {
        $hugeNumber = str_repeat('9', 500) . '.99';
        $this->expectException(\InvalidArgumentException::class);
        $this->money->decimalToCents($hugeNumber);
    }

    public function test_decimal_to_cents_handles_leading_zeros()
    {
        $this->assertEquals(100, $this->money->decimalToCents('000001.00'));
        $this->assertEquals(1050, $this->money->decimalToCents('000010.50'));
        $this->assertEquals(1, $this->money->decimalToCents('000000.01'));
    }

    public function test_decimal_to_cents_no_silent_overflow()
    {
        // Testar que não há conversão silenciosa por overflow
        $maxSafeInteger = (int) floor(PHP_INT_MAX / 100);
        $overflowValue = ($maxSafeInteger + 1000) . '.00';

        try {
            $this->money->decimalToCents($overflowValue);
            $this->fail('Deveria ter lançado InvalidArgumentException por overflow');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('excede o limite', $e->getMessage());
        }
    }
}
