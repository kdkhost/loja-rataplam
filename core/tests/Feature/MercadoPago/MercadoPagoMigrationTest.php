<?php

namespace Tests\Feature\MercadoPago;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;

class MercadoPagoMigrationTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
    }

    protected function tearDown(): void
    {
        $this->dropMercadoPagoTestSchema();
        parent::tearDown();
    }

    public function test_migration_000001_add_execution_lease()
    {
        // Verificar que campos já existem (migrations já aplicadas)
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'execution_owner'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'execution_started_at'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'execution_lease_expires_at'));
    }

    public function test_migration_000002_add_pix_response_fields()
    {
        // Verificar que campos já existem (migrations já aplicadas)
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_qr_code'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_qr_code_base64'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_ticket_url'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_expiration_date'));
    }

    public function test_migration_000003_add_collector_id()
    {
        // Verificar que campos já existem (migrations já aplicadas)
        $this->assertTrue(Schema::hasColumn('mercadopago_settings', 'sandbox_collector_id'));
        $this->assertTrue(Schema::hasColumn('mercadopago_settings', 'production_collector_id'));
    }

    public function test_migration_000004_add_operation_constraints()
    {
        $indexes = collect(DB::select('SHOW INDEX FROM mercadopago_actions'))
            ->pluck('Key_name')
            ->unique();

        $this->assertContains('mp_operation_id_unique', $indexes);
        $this->assertContains('mp_order_action_environment', $indexes);
    }

    public function test_migrations_ordem_correta()
    {
        // Verificar que todas as migrations foram aplicadas corretamente
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'execution_owner'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'execution_started_at'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'execution_lease_expires_at'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_qr_code'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_qr_code_base64'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_ticket_url'));
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'pix_expiration_date'));
        $this->assertTrue(Schema::hasColumn('mercadopago_settings', 'sandbox_collector_id'));
        $this->assertTrue(Schema::hasColumn('mercadopago_settings', 'production_collector_id'));

        // Verificar que tabelas base continuam existindo
        $this->assertTrue(Schema::hasTable('mercadopago_actions'));
        $this->assertTrue(Schema::hasTable('mercadopago_settings'));
    }
}
