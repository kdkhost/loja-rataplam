<?php

namespace Tests\Unit\MercadoPago;

use App\Http\Controllers\Payment\MercadopagoLegacyController;
use App\Http\Controllers\Payment\MercadopagoV2Controller;
use App\Services\MercadoPago\MercadoPagoCheckoutControllerFactory;
use Illuminate\Contracts\Container\Container;
use PHPUnit\Framework\TestCase;

class MercadoPagoCheckoutControllerFactoryTest extends TestCase
{
    public function test_constructor_does_not_resolve_a_controller(): void
    {
        $container = $this->createMock(Container::class);
        $container->expects($this->never())->method('make');

        new MercadoPagoCheckoutControllerFactory($container);
    }

    public function test_legacy_resolves_only_legacy_controller(): void
    {
        $legacy = $this->createMock(MercadopagoLegacyController::class);
        $container = $this->createMock(Container::class);
        $container->expects($this->once())->method('make')
            ->with(MercadopagoLegacyController::class)
            ->willReturn($legacy);

        $this->assertSame($legacy, (new MercadoPagoCheckoutControllerFactory($container))->legacy());
    }

    public function test_v2_resolves_only_v2_controller(): void
    {
        $v2 = $this->createMock(MercadopagoV2Controller::class);
        $container = $this->createMock(Container::class);
        $container->expects($this->once())->method('make')
            ->with(MercadopagoV2Controller::class)
            ->willReturn($v2);

        $this->assertSame($v2, (new MercadoPagoCheckoutControllerFactory($container))->v2());
    }

    public function test_repeated_calls_follow_container_resolution_semantics(): void
    {
        $first = $this->createMock(MercadopagoV2Controller::class);
        $second = $this->createMock(MercadopagoV2Controller::class);
        $container = $this->createMock(Container::class);
        $container->expects($this->exactly(2))->method('make')
            ->with(MercadopagoV2Controller::class)
            ->willReturnOnConsecutiveCalls($first, $second);
        $factory = new MercadoPagoCheckoutControllerFactory($container);

        $this->assertSame($first, $factory->v2());
        $this->assertSame($second, $factory->v2());
    }
}
