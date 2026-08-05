<?php

namespace Tests\Unit\MercadoPago;

use App\Services\MercadoPago\MercadoPagoPaymentService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class MercadoPagoPaymentIdentityTest extends TestCase
{
    private MercadoPagoPaymentService $service;
    private ReflectionMethod $method;

    protected function setUp(): void
    {
        $this->service = new MercadoPagoPaymentService(
            idempotencyService: new \App\Services\MercadoPago\MercadoPagoIdempotencyService()
        );
        $this->method = new ReflectionMethod($this->service, 'generateIdempotencyKey');
    }

    public function test_retry_identico_reutiliza_chave(): void
    {
        $this->assertSame($this->key(), $this->key());
    }

    /** @dataProvider identityChanges */
    public function test_identity_component_change_generates_different_key(array $order, array $config, string $type): void
    {
        $this->assertNotSame($this->key(), $this->key($order, $config, $type));
    }

    public static function identityChanges(): array
    {
        return [
            'environment' => [[], ['mode' => 'production'], 'pix'],
            'currency' => [['currency' => 'USD'], [], 'pix'],
            'amount' => [['authoritative_amount' => '10.51'], [], 'pix'],
            'user' => [['user_id' => 21], [], 'pix'],
            'order' => [['order_id' => 11], [], 'pix'],
            'action' => [[], [], 'card'],
        ];
    }

    public function test_ambiguous_ids_do_not_collide(): void
    {
        $this->assertNotSame(
            $this->key(['user_id' => 1, 'order_id' => 23]),
            $this->key(['user_id' => 12, 'order_id' => 3])
        );
    }

    private function key(array $order = [], array $config = [], string $type = 'pix'): string
    {
        return $this->method->invoke(
            $this->service,
            $type,
            array_merge(['order_id' => 10, 'user_id' => 20, 'authoritative_amount' => '10.50'], $order),
            array_merge(['mode' => 'sandbox'], $config),
            $type === 'card' ? ['payment_method_id' => 'visa', 'installments' => 1] : null
        );
    }
}
