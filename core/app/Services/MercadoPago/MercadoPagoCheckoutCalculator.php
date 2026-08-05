<?php

namespace App\Services\MercadoPago;

use App\Models\AttributeOption;
use App\Models\Currency;
use App\Models\Item;
use App\Models\PromoCode;
use App\Models\ShippingService;
use App\Models\State;

class MercadoPagoCheckoutCalculator
{
    public function __construct(private ?MercadoPagoMoney $money = null)
    {
        $this->money ??= new MercadoPagoMoney();
    }

    public function calculate(array $cart, ?ShippingService $shipping, ?PromoCode $coupon, ?State $state, Currency $currency, array $settings): array
    {
        $subtotal = 0;
        $tax = 0;
        foreach ($cart as $key => $cartItem) {
            $item = Item::query()->with('tax')->findOrFail((int) explode('-', (string) $key, 2)[0]);
            $quantity = filter_var($cartItem['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($quantity === false) {
                throw new \InvalidArgumentException('Quantidade do item invalida.');
            }
            $baseUnit = $this->minorFromDatabase($item->discount_price);
            $unit = $baseUnit;
            foreach (array_unique(array_map('intval', $cartItem['options_id'] ?? [])) as $optionId) {
                $option = AttributeOption::query()->findOrFail($optionId);
                $unit = $this->money->add($unit, $this->minorFromDatabase($option->price));
            }
            $subtotal = $this->money->add($subtotal, $this->money->multiplyQuantity($unit, $quantity));
            if ($item->tax_id && $item->tax && $item->tax->value !== null) {
                $tax = $this->money->add($tax, $this->money->multiplyQuantity(
                    $this->money->percentageOf($baseUnit, (string) $item->tax->value),
                    $quantity
                ));
            }
        }

        $shippingMinor = $shipping ? $this->minorFromDatabase($shipping->price) : 0;
        $beforeDiscount = $this->money->add($subtotal, $tax, $shippingMinor);
        $discountMinor = 0;
        if ($coupon) {
            $discountMinor = $coupon->type === 'amount'
                ? $this->minorFromDatabase($coupon->discount)
                : $this->money->percentageOf($subtotal, (string) $coupon->discount);
        }
        if ($discountMinor > $beforeDiscount) {
            throw new \InvalidArgumentException('Desconto excede o total.');
        }

        $afterDiscount = $beforeDiscount - $discountMinor;
        $stateMinor = $state
            ? ($state->type === 'fixed'
                ? $this->minorFromDatabase($state->price)
                : $this->money->percentageOf($subtotal, (string) $state->price))
            : 0;
        $beforeFee = $this->money->add($afterDiscount, $stateMinor);
        $feeMinor = 0;
        if ((int) ($settings['fee_pass_to_customer'] ?? 0) === 1) {
            $feeMinor = $this->money->add(
                $this->money->percentageOf($beforeFee, (string) ($settings['fee_percent'] ?? '0')),
                $this->minorFromDatabase($settings['fee_fixed'] ?? '0')
            );
        }

        $totalMinor = $this->money->multiplyByDecimal(
            $this->money->add($beforeFee, $feeMinor),
            (string) $currency->value
        );
        if ($totalMinor <= 0) {
            throw new \InvalidArgumentException('Total oficial deve ser maior que zero.');
        }

        return compact('subtotal', 'tax', 'shippingMinor', 'discountMinor', 'stateMinor', 'feeMinor', 'totalMinor') + [
            'totalDecimal' => $this->money->centsToDecimal($totalMinor),
        ];
    }

    private function minorFromDatabase(mixed $value): int
    {
        $decimal = (string) $value;
        if (preg_match('/^\d+\.\d{3,}$/', $decimal)) {
            $trimmed = rtrim($decimal, '0');
            if (str_ends_with($trimmed, '.')) {
                $trimmed .= '00';
            }
            $decimal = $trimmed;
        }

        return $this->money->decimalToCents($decimal);
    }
}
