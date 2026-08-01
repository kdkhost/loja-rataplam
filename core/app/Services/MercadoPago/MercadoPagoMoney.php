<?php
namespace App\Services\MercadoPago;

class MercadoPagoMoney
{
    /**
     * Converte valor decimal para centavos (inteiros)
     *
     * @param string $amount Valor decimal (ex: "0.01", "1", "1.00", "10.50", "100.00", "999999.99")
     * @return int Valor em centavos
     * @throws \InvalidArgumentException Se o valor for inválido
     */
    public function decimalToCents(string $amount): int
    {
        // Rejeitar string vazia
        if ($amount === '') {
            throw new \InvalidArgumentException('Valor não pode ser vazio.');
        }

        // Rejeitar espaços internos
        if (trim($amount) !== $amount) {
            throw new \InvalidArgumentException('Valor não pode conder espaços.');
        }

        // Validar formato: ^\d+(\.\d{1,2})?$
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $amount)) {
            throw new \InvalidArgumentException('Valor deve estar no formato decimal válido (ex: "1.00", "10.50").');
        }

        // Separar parte inteira e decimal
        $parts = explode('.', $amount);
        $integerPart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '00';

        // Preencher parte decimal para duas casas
        if (strlen($decimalPart) === 1) {
            $decimalPart .= '0';
        } elseif (strlen($decimalPart) === 0) {
            $decimalPart = '00';
        }

        // Validar overflow da parte inteira antes da conversão
        // O valor máximo seguro para multiplicação por 100 é PHP_INT_MAX / 100
        $maxSafeInteger = (int) floor(PHP_INT_MAX / 100);
        if (strlen($integerPart) > strlen((string) $maxSafeInteger)) {
            throw new \InvalidArgumentException('Valor excede o limite máximo.');
        }

        // Validar overflow numérico antes da conversão
        $integerValue = (int) $integerPart;
        if ($integerValue > $maxSafeInteger) {
            throw new \InvalidArgumentException('Valor excede o limite máximo.');
        }

        // Calcular centavos usando operações inteiras
        $integerCents = $integerValue * 100;
        $decimalCents = (int) $decimalPart;
        $cents = $integerCents + $decimalCents;

        // Validar overflow final (soma)
        if ($cents > PHP_INT_MAX) {
            throw new \InvalidArgumentException('Valor excede o limite máximo.');
        }

        return $cents;
    }

    /**
     * Converte centavos para valor numérico para API
     *
     * @param int $cents Valor em centavos
     * @return int|float Valor numérico (int se inteiro, float se decimal)
     * @throws \InvalidArgumentException Se o valor for inválido
     */
    public function centsToApiAmount(int $cents): int|float
    {
        // Rejeitar valor negativo
        if ($cents < 0) {
            throw new \InvalidArgumentException('Valor não pode ser negativo.');
        }

        // Converter para decimal
        $decimal = $cents / 100;

        // Retornar int se for inteiro, float se tiver casas decimais
        if ($decimal == (int) $decimal) {
            return (int) $decimal;
        }

        return $decimal;
    }
}
