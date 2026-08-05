<?php

namespace Tests\Support\MercadoPago;

use RuntimeException;

class MercadoPagoTestDatabaseGuard
{
    private const ALLOWED_HOSTS = ['127.0.0.1', 'localhost', '::1'];
    private const REQUIRED_ENV = 'testing';
    private const REQUIRED_DATABASE = 'rataplam_mp_local_test';
    private const REQUIRED_PORT = '3307';
    private const BLOCKED_DATABASES = ['rataplam_loja26', 'rataplam_mp_test'];

    /**
     * Validate that the database configuration is safe for Mercado Pago tests.
     *
     * @param string $appEnv The APP_ENV value
     * @param string|null $dbHost The DB_HOST value
     * @param string|null $dbPort The DB_PORT value
     * @param string|null $dbDatabase The DB_DATABASE value
     * @throws RuntimeException If configuration is unsafe
     */
    public static function validate(
        string $appEnv,
        ?string $dbHost,
        ?string $dbPort,
        ?string $dbDatabase
    ): void {
        self::validateEnvironment($appEnv);
        self::validateDatabase($dbDatabase);
        self::validatePort($dbPort);
        self::validateHost($dbHost);
    }

    /**
     * Validate APP_ENV.
     */
    private static function validateEnvironment(string $appEnv): void
    {
        if ($appEnv !== self::REQUIRED_ENV) {
            throw new RuntimeException(
                sprintf(
                    'APP_ENV deve ser "%s", atual: "%s"',
                    self::REQUIRED_ENV,
                    $appEnv
                )
            );
        }
    }

    /**
     * Validate database name is not blocked and matches required.
     */
    private static function validateDatabase(?string $dbDatabase): void
    {
        if (empty($dbDatabase)) {
            throw new RuntimeException('DB_DATABASE não pode ser vazio');
        }

        if (in_array($dbDatabase, self::BLOCKED_DATABASES, true)) {
            throw new RuntimeException(
                sprintf(
                    'DB_DATABASE "%s" está bloqueado para testes Mercado Pago',
                    $dbDatabase
                )
            );
        }

        if ($dbDatabase !== self::REQUIRED_DATABASE) {
            throw new RuntimeException(
                sprintf(
                    'DB_DATABASE deve ser "%s", atual: "%s"',
                    self::REQUIRED_DATABASE,
                    $dbDatabase
                )
            );
        }
    }

    /**
     * Validate port matches required.
     */
    private static function validatePort(?string $dbPort): void
    {
        if (empty($dbPort)) {
            throw new RuntimeException('DB_PORT não pode ser vazio');
        }

        if ($dbPort !== self::REQUIRED_PORT) {
            throw new RuntimeException(
                sprintf(
                    'DB_PORT deve ser "%s", atual: "%s"',
                    self::REQUIRED_PORT,
                    $dbPort
                )
            );
        }
    }

    /**
     * Validate host is in allowlist.
     */
    private static function validateHost(?string $dbHost): void
    {
        if (empty($dbHost)) {
            throw new RuntimeException('DB_HOST não pode ser vazio');
        }

        if (!in_array($dbHost, self::ALLOWED_HOSTS, true)) {
            throw new RuntimeException(
                sprintf(
                    'DB_HOST "%s" não está na allowlist de hosts locais permitidos. Hosts permitidos: %s',
                    $dbHost,
                    implode(', ', self::ALLOWED_HOSTS)
                )
            );
        }
    }

    /**
     * Validate using Laravel config after bootstrap.
     */
    public static function validateFromLaravel(): void
    {
        $appEnv = app()->environment();
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");

        if (!$dbConfig) {
            throw new RuntimeException('Configuração de banco não encontrada');
        }

        $dbHost = $dbConfig['host'] ?? null;
        $dbPort = $dbConfig['port'] ?? null;
        $dbDatabase = $dbConfig['database'] ?? null;

        self::validate($appEnv, $dbHost, $dbPort, $dbDatabase);
    }

    /**
     * Validate that the real connection matches the configured database.
     */
    public static function validateRealConnection(): void
    {
        $dbConnection = config('database.default');
        $dbConfig = config("database.connections.{$dbConnection}");
        $configuredDatabase = $dbConfig['database'] ?? null;

        if ($configuredDatabase !== self::REQUIRED_DATABASE) {
            throw new RuntimeException(
                sprintf(
                    'Banco configurado "%s" não corresponde ao banco de teste esperado "%s"',
                    $configuredDatabase,
                    self::REQUIRED_DATABASE
                )
            );
        }

        // Get real database name from connection
        $realDatabase = \DB::connection()->getDatabaseName();

        if ($realDatabase !== self::REQUIRED_DATABASE) {
            throw new RuntimeException(
                sprintf(
                    'Banco real conectado "%s" não corresponde ao banco de teste esperado "%s"',
                    $realDatabase,
                    self::REQUIRED_DATABASE
                )
            );
        }
    }
}
