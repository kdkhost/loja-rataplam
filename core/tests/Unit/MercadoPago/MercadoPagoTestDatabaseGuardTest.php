<?php

namespace Tests\Unit\MercadoPago;

use Tests\TestCase;
use Tests\Support\MercadoPago\MercadoPagoTestDatabaseGuard;
use RuntimeException;

class MercadoPagoTestDatabaseGuardTest extends TestCase
{
    /** @test */
    public function configuracao_correta_aceita()
    {
        // Não deve lançar exceção
        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '127.0.0.1',
            '3307',
            'rataplam_mp_local_test'
        );

        $this->assertTrue(true);
    }

    /** @test */
    public function app_env_diferente_de_testing_rejeita()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('APP_ENV deve ser "testing"');

        MercadoPagoTestDatabaseGuard::validate(
            'production',
            '127.0.0.1',
            '3307',
            'rataplam_mp_local_test'
        );
    }

    /** @test */
    public function banco_rataplam_loja26_rejeita()
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
    public function banco_rataplam_mp_test_rejeita()
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

    /** @test */
    public function banco_vazio_rejeita()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_DATABASE não pode ser vazio');

        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '127.0.0.1',
            '3307',
            ''
        );
    }

    /** @test */
    public function porta_diferente_de_3307_rejeita()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB_PORT deve ser "3307"');

        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '127.0.0.1',
            '3306',
            'rataplam_mp_local_test'
        );
    }

    /** @test */
    public function host_remoto_rejeita()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('não está na allowlist');

        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '192.168.1.100',
            '3307',
            'rataplam_mp_local_test'
        );
    }

    /** @test */
    public function localhost_aceita()
    {
        // Não deve lançar exceção
        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            'localhost',
            '3307',
            'rataplam_mp_local_test'
        );

        $this->assertTrue(true);
    }

    /** @test */
    public function ip_127001_aceita()
    {
        // Não deve lançar exceção
        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '127.0.0.1',
            '3307',
            'rataplam_mp_local_test'
        );

        $this->assertTrue(true);
    }

    /** @test */
    public function ipv6_loopback_aceita()
    {
        // Não deve lançar exceção
        MercadoPagoTestDatabaseGuard::validate(
            'testing',
            '::1',
            '3307',
            'rataplam_mp_local_test'
        );

        $this->assertTrue(true);
    }

    /** @test */
    public function mensagem_nao_contem_senha_ou_app_key()
    {
        try {
            MercadoPagoTestDatabaseGuard::validate(
                'production',
                '127.0.0.1',
                '3307',
                'rataplam_mp_local_test'
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
        }
    }
}
