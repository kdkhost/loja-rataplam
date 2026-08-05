<?php

namespace App\Services\MercadoPago;

use App\Http\Controllers\Payment\MercadopagoLegacyController;
use App\Http\Controllers\Payment\MercadopagoV2Controller;
use Illuminate\Contracts\Container\Container;

final class MercadoPagoCheckoutControllerFactory
{
    public function __construct(private Container $container)
    {
    }

    public function legacy(): MercadopagoLegacyController
    {
        return $this->container->make(MercadopagoLegacyController::class);
    }

    public function v2(): MercadopagoV2Controller
    {
        return $this->container->make(MercadopagoV2Controller::class);
    }
}
