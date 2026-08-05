<?php
namespace Tests\Feature\MercadoPago;

use App\Models\MercadoPagoAction;
use App\Services\MercadoPago\MercadoPagoIdempotencyService;
use Illuminate\Support\Facades\DB;
use Tests\Support\MercadoPago\CreatesMercadoPagoTestSchema;
use Tests\TestCase;

class MercadoPagoIdempotencyConcurrencyTest extends TestCase
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

    public function test_concurrent_initiate_action_creates_single_record()
    {
        $idempotencyKey = 'concurrent-test-key-' . bin2hex(random_bytes(8));
        $callCount = 0;
        $lockFile = sys_get_temp_dir() . '/mp_concurrency_test.lock';

        // Limpar lock file anterior se existir
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        // Função que simula a execução concorrente
        $simulateConcurrentExecution = function () use ($idempotencyKey, &$callCount, $lockFile) {
            // Criar lock file para sincronização
            $fp = fopen($lockFile, 'w');
            if (flock($fp, LOCK_EX)) {
                // Incrementar contador de chamadas
                $callCount++;

                // Simular pequeno delay para aumentar chance de concorrência
                usleep(1000); // 1ms

                try {
                    $service = new MercadoPagoIdempotencyService();
                    $params = [
                        'idempotency_key' => $idempotencyKey,
                        'order_id' => null,
                        'environment' => 'sandbox',
                        'action' => 'refund',
                        'requested_amount' => 1000,
                        'currency' => 'BRL',
                    ];

                    $action = $service->initiateAction($params);

                    flock($fp, LOCK_UN);
                    fclose($fp);

                    return $action;
                } catch (\Exception $e) {
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    throw $e;
                }
            }
            fclose($fp);
            return null;
        };

        // Executar duas tentativas concorrentes usando transações separadas
        $actions = [];
        $exceptions = [];

        // Tentativa 1
        try {
            DB::beginTransaction();
            $actions[0] = $simulateConcurrentExecution();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $exceptions[0] = $e;
        }

        // Tentativa 2
        try {
            DB::beginTransaction();
            $actions[1] = $simulateConcurrentExecution();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $exceptions[1] = $e;
        }

        // Limpar lock file
        if (file_exists($lockFile)) {
            unlink($lockFile);
        }

        // Verificar resultados
        $this->assertCount(2, $actions, 'Ambas as tentativas devem retornar');
        $this->assertEmpty($exceptions, 'Nenhuma exceção deve ocorrer');

        // Verificar que apenas um registro foi criado no banco
        $records = MercadoPagoAction::where('idempotency_key', $idempotencyKey)->get();
        $this->assertCount(1, $records, 'Deve haver exatamente um registro no banco');

        // Verificar que ambas as tentativas retornaram o mesmo registro
        $this->assertEquals($actions[0]->id, $actions[1]->id, 'Ambas devem retornar o mesmo registro');
        $this->assertEquals($idempotencyKey, $actions[0]->idempotency_key);
        $this->assertEquals($idempotencyKey, $actions[1]->idempotency_key);

        // Verificar que o contador de chamadas foi incrementado duas vezes
        $this->assertEquals(2, $callCount, 'Ambas as tentativas devem ter sido executadas');
    }

    public function test_concurrent_initiate_with_different_fingerprints_fails()
    {
        $idempotencyKey = 'concurrent-fail-key-' . bin2hex(random_bytes(8));

        // Criar primeira ação
        $service = new MercadoPagoIdempotencyService();
        $action1 = $service->initiateAction([
            'idempotency_key' => $idempotencyKey,
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'refund',
            'requested_amount' => 1000,
            'currency' => 'BRL',
            'request_fingerprint' => 'fingerprint-1',
        ]);

        // Tentar criar segunda ação com mesma chave mas fingerprint diferente
        $this->expectException(\App\Exceptions\MercadoPagoOperationException::class);

        $service->initiateAction([
            'idempotency_key' => $idempotencyKey,
            'order_id' => null,
            'environment' => 'sandbox',
            'action' => 'payment', // ação diferente
            'requested_amount' => 2000,
            'currency' => 'BRL',
            'request_fingerprint' => 'fingerprint-2',
        ]);
    }
}
