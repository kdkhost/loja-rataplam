<?php

namespace Tests\Support\MercadoPago;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MercadoPagoDatabaseBootstrap
{
    use CreatesMercadoPagoTestSchema;

    /**
     * Bootstrap the database for Mercado Pago tests.
     * Creates minimal schema and runs only Mercado Pago migrations.
     */
    public static function bootstrap(): void
    {
        $instance = new self();
        $instance->createMercadoPagoTestSchema();

        // Run only Mercado Pago migrations explicitly
        Artisan::call('migrate', [
            '--path' => 'core/database/migrations/2026_07_31_173157_create_mercadopago_settings_table.php',
            '--force' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => 'core/database/migrations/2026_07_31_173158_create_mercadopago_actions_table.php',
            '--force' => true,
        ]);
    }

    /**
     * Clean up the database after Mercado Pago tests.
     */
    public static function cleanup(): void
    {
        $instance = new self();
        $instance->dropMercadoPagoTestSchema();
    }

    /**
     * Refresh the database (drop and recreate).
     */
    public static function refresh(): void
    {
        $instance = new self();
        $instance->dropMercadoPagoTestSchema();
        $instance->createMercadoPagoTestSchema();

        Artisan::call('migrate', [
            '--path' => 'core/database/migrations/2026_07_31_173157_create_mercadopago_settings_table.php',
            '--force' => true,
        ]);

        Artisan::call('migrate', [
            '--path' => 'core/database/migrations/2026_07_31_173158_create_mercadopago_actions_table.php',
            '--force' => true,
        ]);
    }
}
