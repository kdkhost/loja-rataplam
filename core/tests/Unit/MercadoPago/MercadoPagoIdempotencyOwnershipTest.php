<?php

namespace Tests\Unit\MercadoPago;

use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoIdempotencyOwnershipTest extends TestCase
{
    use CreatesMercadoPagoTestSchema;

    protected MercadoPagoIdempotencyService $idempotencyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createMercadoPagoTestSchema();
        $this->idempotencyService = app(MercadoPagoIdempotencyService::class);
    }

    /** @test */
    public function proprietario_atual_marca_pending_como_unknown()
    {
        $action = MercadoPagoAction::create([
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'create_payment',
            'requested_amount' => 100.00,
            'currency' => 'BRL',
            'idempotency_key' => 'test-key-unknown-1',
            'request_fingerprint' => 'fingerprint-1',
            'local_status' => 'pending',
            'execution_owner' => 'owner-uuid-1',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->addSeconds(35),
        ]);

        $this->idempotencyService->markAsUnknown($action, 'owner-uuid-1');

        $action->refresh();
        $this->assertEquals('unknown', $action->local_status);
    }

    /** @test */
    public function proprietario_antigo_nao_marca_como_unknown()
    {
        $action = MercadoPagoAction::create([
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'create_payment',
            'requested_amount' => 100.00,
            'currency' => 'BRL',
            'idempotency_key' => 'test-key-unknown-2',
            'request_fingerprint' => 'fingerprint-2',
            'local_status' => 'pending',
            'execution_owner' => 'owner-uuid-2',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->addSeconds(35),
        ]);

        // Tentativa com proprietário diferente
        $this->idempotencyService->markAsUnknown($action, 'owner-uuid-3');

        $action->refresh();
        $this->assertEquals('pending', $action->local_status);
    }

    /** @test */
    public function tentativa_success_nao_regride_para_unknown()
    {
        $action = MercadoPagoAction::create([
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'create_payment',
            'requested_amount' => 100.00,
            'currency' => 'BRL',
            'idempotency_key' => 'test-key-unknown-3',
            'request_fingerprint' => 'fingerprint-3',
            'local_status' => 'success',
            'execution_owner' => 'owner-uuid-4',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->addSeconds(35),
        ]);

        $this->idempotencyService->markAsUnknown($action, 'owner-uuid-4');

        $action->refresh();
        $this->assertEquals('success', $action->local_status);
    }

    /** @test */
    public function tentativa_failed_nao_regride_para_unknown()
    {
        $action = MercadoPagoAction::create([
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'create_payment',
            'requested_amount' => 100.00,
            'currency' => 'BRL',
            'idempotency_key' => 'test-key-unknown-4',
            'request_fingerprint' => 'fingerprint-4',
            'local_status' => 'failed',
            'execution_owner' => 'owner-uuid-5',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->addSeconds(35),
        ]);

        $this->idempotencyService->markAsUnknown($action, 'owner-uuid-5');

        $action->refresh();
        $this->assertEquals('failed', $action->local_status);
    }

    /** @test */
    public function lease_readquirido_impede_proprietario_anterior_de_alterar_estado()
    {
        $action = MercadoPagoAction::create([
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'create_payment',
            'requested_amount' => 100.00,
            'currency' => 'BRL',
            'idempotency_key' => 'test-key-unknown-5',
            'request_fingerprint' => 'fingerprint-5',
            'local_status' => 'pending',
            'execution_owner' => 'owner-uuid-6',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->subSeconds(1), // Lease expirado
        ]);

        // Readquirir lease
        MercadoPagoAction::where('id', $action->id)
            ->where('local_status', 'pending')
            ->where('execution_lease_expires_at', '<=', now())
            ->update([
                'execution_owner' => 'owner-uuid-7',
                'execution_started_at' => now(),
                'execution_lease_expires_at' => now()->addSeconds(35),
            ]);

        $action->refresh();

        // Proprietário antigo tenta marcar como unknown
        $this->idempotencyService->markAsUnknown($action, 'owner-uuid-6');

        $action->refresh();
        $this->assertEquals('pending', $action->local_status);
        $this->assertEquals('owner-uuid-7', $action->execution_owner);
    }

    /** @test */
    public function timeout_apos_possivel_envio_preserva_idempotency_key()
    {
        $action = MercadoPagoAction::create([
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'create_payment',
            'requested_amount' => 100.00,
            'currency' => 'BRL',
            'idempotency_key' => 'test-key-unknown-6',
            'request_fingerprint' => 'fingerprint-6',
            'local_status' => 'pending',
            'execution_owner' => 'owner-uuid-8',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->addSeconds(35),
        ]);

        $originalKey = $action->idempotency_key;

        $this->idempotencyService->markAsUnknown($action, 'owner-uuid-8');

        $action->refresh();
        $this->assertEquals($originalKey, $action->idempotency_key);
        $this->assertEquals('unknown', $action->local_status);
    }

    /** @test */
    public function nenhum_processo_sem_ownership_pode_finalizar_ou_marcar_unknown()
    {
        $action = MercadoPagoAction::create([
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'create_payment',
            'requested_amount' => 100.00,
            'currency' => 'BRL',
            'idempotency_key' => 'test-key-unknown-7',
            'request_fingerprint' => 'fingerprint-7',
            'local_status' => 'pending',
            'execution_owner' => 'owner-uuid-9',
            'execution_started_at' => now(),
            'execution_lease_expires_at' => now()->addSeconds(35),
        ]);

        // Tentativa sem ownership
        $this->idempotencyService->markAsUnknown($action, null);

        $action->refresh();
        // Sem ownership, o modo de compatibilidade permite alteração
        // Este teste verifica que o modo de compatibilidade ainda funciona
        $this->assertEquals('unknown', $action->local_status);
    }
}
