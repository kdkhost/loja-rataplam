<?php

namespace App\Services\MercadoPago;

use InvalidArgumentException;

class MercadoPagoMoney
{
    public function decimalToCents(string $amount): int
    {
        if ($amount === '' || trim($amount) !== $amount || !preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            throw new InvalidArgumentException('Valor decimal invalido.');
        }

        [$integerPart, $decimalPart] = array_pad(explode('.', $amount, 2), 2, '');
        $decimalPart = str_pad($decimalPart, 2, '0');
        $normalized = ltrim($integerPart, '0') ?: '0';
        $maxInteger = (string) intdiv(PHP_INT_MAX, 100);
        if (strlen($normalized) > strlen($maxInteger)
            || (strlen($normalized) === strlen($maxInteger) && $normalized > $maxInteger)
            || ($normalized === $maxInteger && (int) $decimalPart > PHP_INT_MAX % 100)) {
            throw new InvalidArgumentException('Valor excede o limite maximo.');
        }

        return ((int) $normalized * 100) + (int) $decimalPart;
    }

    public function centsToDecimal(int $cents): string
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Valor nao pode ser negativo.');
        }

        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function centsToApiAmount(int $cents): string
    {
        return $this->centsToDecimal($cents);
    }

    public function add(int ...$amounts): int
    {
        $total = 0;
        foreach ($amounts as $amount) {
            if (($amount > 0 && $total > PHP_INT_MAX - $amount)
                || ($amount < 0 && $total < PHP_INT_MIN - $amount)) {
                throw new InvalidArgumentException('Soma monetaria excede o limite.');
            }
            $total += $amount;
        }

        return $total;
    }

    public function multiplyQuantity(int $unitCents, int $quantity): int
    {
        if ($unitCents < 0 || $quantity < 1 || ($unitCents > 0 && $quantity > intdiv(PHP_INT_MAX, $unitCents))) {
            throw new InvalidArgumentException('Multiplicacao monetaria invalida.');
        }

        return $unitCents * $quantity;
    }

    public function percentageOf(int $amountCents, string $percentage): int
    {
        if (!preg_match('/^\d+(\.\d{1,4})?$/', $percentage)) {
            throw new InvalidArgumentException('Percentual invalido.');
        }

        [$whole, $fraction] = array_pad(explode('.', $percentage, 2), 2, '');
        $scaled = ((int) $whole * 10000) + (int) str_pad($fraction, 4, '0');
        if ($scaled > 1000000 || ($amountCents > 0 && $scaled > intdiv(PHP_INT_MAX, $amountCents))) {
            throw new InvalidArgumentException('Percentual excede o limite.');
        }

        return intdiv(($amountCents * $scaled) + 500000, 1000000);
    }

    public function multiplyByDecimal(int $amountCents, string $multiplier): int
    {
        if (!preg_match('/^\d+(\.\d{1,8})?$/', $multiplier)) {
            throw new InvalidArgumentException('Multiplicador monetario invalido.');
        }

        [$whole, $fraction] = array_pad(explode('.', $multiplier, 2), 2, '');
        $scaled = ((int) $whole * 100000000) + (int) str_pad($fraction, 8, '0');
        if ($amountCents > 0 && $scaled > intdiv(PHP_INT_MAX, $amountCents)) {
            throw new InvalidArgumentException('Conversao monetaria excede o limite.');
        }

        return intdiv(($amountCents * $scaled) + 50000000, 100000000);
    }
}
