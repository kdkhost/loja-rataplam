<?php

namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use PHPUnit\Framework\TestCase;

class MercadoPagoDeterministicIdempotencyTest extends TestCase
{
    public function test_same_operation_generates_same_valid_uuid(): void
    {
        $service = new MercadoPagoIdempotencyService();
        $operation = [
            'action' => 'create_pix_payment',
            'user_id' => 20,
            'order_id' => 10,
            'authoritative_amount' => '10.50',
        ];

        $first = $service->generateDeterministicKey($operation);
        $second = $service->generateDeterministicKey(array_reverse($operation, true));

        $this->assertSame($first, $second);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $first
        );
    }

    public function test_different_operation_generates_different_key(): void
    {
        $service = new MercadoPagoIdempotencyService();
        $base = ['action' => 'create_pix_payment', 'user_id' => 20, 'order_id' => 10];

        $this->assertNotSame(
            $service->generateDeterministicKey($base),
            $service->generateDeterministicKey([...$base, 'order_id' => 11])
        );
    }
}
