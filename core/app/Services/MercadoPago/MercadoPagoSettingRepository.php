<?php

namespace App\Services\MercadoPago;

use App\Models\MercadoPagoSetting;

class MercadoPagoSettingRepository
{
    public function current(): ?MercadoPagoSetting
    {
        return MercadoPagoSetting::where('configuration_key', 'default')->first();
    }
}
