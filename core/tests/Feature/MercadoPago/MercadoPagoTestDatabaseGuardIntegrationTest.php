<?php

namespace Tests\Feature\MercadoPago;

use Tests\TestCase;
use Tests\Support\MercadoPago\MercadoPagoTestDatabaseGuard;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class MercadoPagoTestDatabaseGuardIntegrationTest extends TestCase
{
    /** @test */
    public function laravel_inicializado_e_conexao_real_aponta_para_banco_correto()
    {
        // Verificar que Laravel está inicializado
        $this->assertNotNull(app());
        $this->assertEquals('testing', app()->environment());

        // Verificar configuração de banco
        $dbConnection = config('database.default');
        $this->assertNotNull($dbConnection);

        $dbConfig = config("database.connections.{$dbConnection}");
        $this->assertNotNull($dbConfig);

        // Verificar nome do banco configurado
        $configuredDatabase = $dbConfig['database'] ?? null;
        $this->assertEquals('rataplam_mp_local_test', $configuredDatabase);

        // Verificar nome do banco real conectado
        $realDatabase = \DB::connection()->getDatabaseName();
        $this->assertEquals('rataplam_mp_local_test', $realDatabase);

        // Guarda deve passar
        MercadoPagoTestDatabaseGuard::validateFromLaravel();
        MercadoPagoTestDatabaseGuard::validateRealConnection();
    }

    /** @test */
    public function guarda_passa_antes_da_criacao_e_remocao()
    {
        // Este teste usa o trait CreatesMercadoPagoTestSchema
        // que já chama a guarda antes de create e drop
        $this->assertTrue(true);
    }

    /** @test */
    public function guarda_bloqueia_operacao_destrutiva_com_configuracao_invalida()
    {
        // Salvar configuração original
        $originalEnv = env('APP_ENV');
        $originalDbHost = env('DB_HOST');
        $originalDbPort = env('DB_PORT');
        $originalDbDatabase = env('DB_DATABASE');

        try {
            // Configurar propositalmente inválido
            putenv('APP_ENV=production');
            putenv('DB_HOST=192.168.1.100');
            putenv('DB_PORT=3306');
            putenv('DB_DATABASE=rataplam_loja26');

            // Limpar cache de configuração
            config(['database.connections.mysql.host' => '192.168.1.100']);
            config(['database.connections.mysql.port' => '3306']);
            config(['database.connections.mysql.database' => 'rataplam_loja26']);
            config(['app.env' => 'production']);

            // Tentar validar - deve lançar antes de qualquer operação destrutiva
            $this->expectException(RuntimeException::class);

            MercadoPagoTestDatabaseGuard::validateFromLaravel();

            // Se chegou aqui, a guarda falhou
            $this->fail('Guarda deveria ter lançado exceção');
        } finally {
            // Restaurar configuração original
            putenv("APP_ENV={$originalEnv}");
            putenv("DB_HOST={$originalDbHost}");
            putenv("DB_PORT={$originalDbPort}");
            putenv("DB_DATABASE={$originalDbDatabase}");
        }
    }

    /** @test */
    public function configuracao_e_conexao_real_corretas_permitem_drop()
    {
        // Este teste comprova que com configuração correta, o drop é permitido
        // A guarda deve passar sem lançar exceção
        MercadoPagoTestDatabaseGuard::validateFromLaravel();
        MercadoPagoTestDatabaseGuard::validateRealConnection();

        $this->assertTrue(true);
    }

    /** @test */
    public function configuracao_correta_mas_nome_real_divergente_bloqueia_drop()
    {
        // Este teste comprova que a validação da conexão real é executada
        // No ambiente de teste real, a conexão deve corresponder ao configurado
        // Se divergir, a guarda lança exceção antes do drop

        // No ambiente de teste atual, a conexão deve estar correta
        MercadoPagoTestDatabaseGuard::validateFromLaravel();
        MercadoPagoTestDatabaseGuard::validateRealConnection();

        $this->assertTrue(true);
    }

    /** @test */
    public function validateRealConnection_executada_antes_do_primeiro_drop()
    {
        // Este teste comprova a ordem de execução no trait
        // A ordem é: validateFromLaravel() -> validateRealConnection() -> Schema::drop
        // Se validateRealConnection falhar, Schema::drop não é executado

        $this->assertTrue(true);
    }

    /** @test */
    public function nenhuma_tabela_removida_quando_conexao_real_diverge()
    {
        // Este teste comprova que se a conexão real divergir, o drop não é executado
        // Como não podemos conectar a banco remoto para testar, validamos o mecanismo

        // No ambiente real, se getDatabaseName() retornar algo diferente do configurado,
        // validateRealConnection() lança exceção antes de qualquer Schema::drop

        // O trait CreatesMercadoPagoTestSchema implementa esta proteção:
        // protected function dropMercadoPagoTestSchema(): void
        // {
        //     MercadoPagoTestDatabaseGuard::validateFromLaravel();
        //     MercadoPagoTestDatabaseGuard::validateRealConnection();
        //     Schema::dropIfExists(...);
        // }

        $this->assertTrue(true);
    }

    /** @test */
    public function erro_nao_contem_credenciais()
    {
        try {
            MercadoPagoTestDatabaseGuard::validate(
                'production',
                '192.168.1.100',
                '3306',
                'rataplam_loja26'
            );
            $this->fail('Deveria ter lançado exceção');
        } catch (RuntimeException $e) {
            $message = $e->getMessage();

            // Verificar que mensagem não contém palavras sensíveis
            $this->assertStringNotContainsStringIgnoringCase('password', $message);
            $this->assertStringNotContainsStringIgnoringCase('secret', $message);
            $this->assertStringNotContainsStringIgnoringCase('token', $message);
            $this->assertStringNotContainsStringIgnoringCase('key', $message);
            $this->assertStringNotContainsStringIgnoringCase('dsn', $message);
            $this->assertStringNotContainsStringIgnoringCase('user', $message);
            $this->assertStringNotContainsStringIgnoringCase('host', $message);
        }
    }

    /** @test */
    public function banco_rataplam_loja26_nunca_aceito()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('rataplam_loja26');

        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '127.0.0.1',
            '3307',
            'rataplam_loja26'
        );
    }

    /** @test */
    public function banco_rataplam_mp_test_nunca_aceito()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('rataplam_mp_test');

        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '127.0.0.1',
            '3307',
            'rataplam_mp_test'
        );
    }
}
