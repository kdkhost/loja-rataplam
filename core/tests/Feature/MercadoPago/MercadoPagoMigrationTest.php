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
        $indexes = collect(DB::select('SHOW INDEX FROM mercadopago_actions'));
        $unique = $indexes->where('Key_name', 'mp_operation_id_unique')->sortBy('Seq_in_index')->values();
        $composite = $indexes->where('Key_name', 'mp_order_action_environment')->sortBy('Seq_in_index')->values();

        $this->assertSame([0], $unique->pluck('Non_unique')->map(fn ($value) => (int) $value)->all());
        $this->assertSame(['mercadopago_operation_id'], $unique->pluck('Column_name')->all());
        $this->assertSame([1, 2, 3], $composite->pluck('Seq_in_index')->map(fn ($value) => (int) $value)->all());
        $this->assertSame(['order_id', 'action', 'environment'], $composite->pluck('Column_name')->all());
        $this->assertSame([1, 1, 1], $composite->pluck('Non_unique')->map(fn ($value) => (int) $value)->all());
    }

    public function test_operation_id_unique_allows_multiple_nulls_and_rejects_duplicate_value(): void
    {
        DB::table('mercadopago_actions')->insert([
            ['action' => 'test', 'idempotency_key' => '123e4567-e89b-52d3-a456-426614174010'],
            ['action' => 'test', 'idempotency_key' => '123e4567-e89b-52d3-a456-426614174011'],
        ]);
        $this->assertSame(2, DB::table('mercadopago_actions')->whereNull('mercadopago_operation_id')->count());

        DB::table('mercadopago_actions')->insert([
            'action' => 'test',
            'idempotency_key' => '123e4567-e89b-52d3-a456-426614174012',
            'mercadopago_operation_id' => 'operation-unique',
        ]);
        $this->expectException(\Illuminate\Database\QueryException::class);
        DB::table('mercadopago_actions')->insert([
            'action' => 'test',
            'idempotency_key' => '123e4567-e89b-52d3-a456-426614174013',
            'mercadopago_operation_id' => 'operation-unique',
        ]);
    }

    public function test_migration_down_removes_only_its_indexes(): void
    {
        $migration = require database_path('migrations/2026_08_05_000004_add_mercadopago_operation_constraints.php');
        $migration->down();
        $indexes = collect(DB::select('SHOW INDEX FROM mercadopago_actions'))->pluck('Key_name');

        $this->assertNotContains('mp_operation_id_unique', $indexes);
        $this->assertNotContains('mp_order_action_environment', $indexes);
        $this->assertContains('mercadopago_actions_idempotency_key_unique', $indexes);
        $this->assertTrue(Schema::hasColumn('mercadopago_actions', 'mercadopago_operation_id'));
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
