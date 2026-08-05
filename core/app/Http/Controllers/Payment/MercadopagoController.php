<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\MercadoPago\MercadoPagoCheckoutControllerFactory;
use App\Services\MercadoPago\MercadoPagoFeatureGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class MercadopagoController extends Controller
{
    public function __construct(
        protected MercadoPagoCheckoutControllerFactory $controllerFactory,
        protected MercadoPagoFeatureGate $featureGate
    ) {}

    public function store(Request $request)
    {
        $environment = $this->featureGate->requestedEnvironment();

        if ($environment === null) {
            return $this->controllerFactory->legacy()->store($request);
        }

        try {
            $this->featureGate->assertCheckoutEnabled($environment);
        } catch (Throwable) {
            abort(503, 'Gateway temporariamente indisponivel.');
        }

        if (!Auth::check()) {
            abort(401);
        }

        return $this->controllerFactory->v2()->store($request);
    }
}
