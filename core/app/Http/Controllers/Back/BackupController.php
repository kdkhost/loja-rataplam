<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class BackupController extends Controller
{
    private const DATABASE_BACKUP_DIR = 'backups/database';

    /**
     * Constructor Method.
     *
     * Setting Authentication
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    public function index()
    {
        return view('back.backup.index', [
            'databaseBackups' => $this->databaseBackups(),
            'backupPathLabel' => 'storage/app/' . self::DATABASE_BACKUP_DIR,
        ]);
    }

    public function systemBackup()
    {
        return redirect()
            ->route('back.backup.index')
            ->withError('Backup completo do sistema por ZIP esta desativado. Use o Git para o codigo-fonte e esta central para o banco de dados.');
    }

    public function databaseBackup()
    {
        return redirect()->route('back.backup.index');
    }

    public function storeDatabase()
    {
        $this->ensureBackupDirectory();

        $filename = 'database_backup_on_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        $path = $this->databaseBackupDirectory() . DIRECTORY_SEPARATOR . $filename;

        try {
            $this->writeDatabaseDump($path);
        } catch (Throwable $exception) {
            if (is_file($path)) {
                @unlink($path);
            }

            report($exception);

            return redirect()
                ->route('back.backup.index')
                ->withError('Nao foi possivel gerar o backup do banco de dados. Verifique as permissoes da pasta storage.');
        }

        return redirect()
            ->route('back.backup.index')
            ->withSuccess('Backup do banco de dados criado com sucesso.');
    }

    public function download(string $file)
    {
        $path = $this->resolveDatabaseBackupPath($file);

        return response()->download($path, basename($path), [
            'Content-Type' => 'application/sql; charset=UTF-8',
        ]);
    }

    public function destroy(string $file)
    {
        $path = $this->resolveDatabaseBackupPath($file);

        @unlink($path);

        return redirect()
            ->route('back.backup.index')
            ->withSuccess('Backup excluido com sucesso.');
    }

    private function databaseBackups(): array
    {
        $this->ensureBackupDirectory();

        $files = glob($this->databaseBackupDirectory() . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(function ($path) {
            $createdAt = Carbon::createFromTimestamp(filemtime($path))->timezone(config('app.timezone', 'America/Sao_Paulo'));

            return [
                'name' => basename($path),
                'size' => $this->formatBytes(filesize($path)),
                'bytes' => filesize($path),
                'created_at' => $createdAt->format('d/m/Y H:i:s'),
            ];
        }, $files);
    }

    private function writeDatabaseDump(string $path): void
    {
        $pdo = DB::connection()->getPdo();
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Nao foi possivel abrir o arquivo de backup para escrita.');
        }

        try {
            fwrite($handle, "-- Backup Rataplam\n");
            fwrite($handle, '-- Gerado em ' . Carbon::now()->format('d/m/Y H:i:s') . "\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            foreach ($this->tableNames() as $table) {
                $quotedTable = $this->quoteIdentifier($table);
                $createTable = DB::select("SHOW CREATE TABLE {$quotedTable}");
                $createSql = $createTable[0]->{'Create Table'} ?? null;

                if (!$createSql) {
                    continue;
                }

                fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");
                fwrite($handle, $createSql . ";\n\n");

                $statement = $pdo->query("SELECT * FROM {$quotedTable}");

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $columns = array_map(fn ($column) => $this->quoteIdentifier($column), array_keys($row));
                    $values = array_map(fn ($value) => $this->quoteValue($pdo, $value), array_values($row));

                    fwrite($handle, "INSERT INTO {$quotedTable} (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n");
                }

                fwrite($handle, "\n");
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }
    }

    private function tableNames(): array
    {
        return array_map(function ($table) {
            $values = array_values((array) $table);

            return $values[0];
        }, DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'"));
    }

    private function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    private function quoteValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return $pdo->quote((string) $value);
    }

    private function databaseBackupDirectory(): string
    {
        return storage_path('app/' . self::DATABASE_BACKUP_DIR);
    }

    private function ensureBackupDirectory(): void
    {
        $directory = $this->databaseBackupDirectory();

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    private function resolveDatabaseBackupPath(string $file): string
    {
        if (!preg_match('/^[A-Za-z0-9._-]+\.sql$/', $file)) {
            abort(404);
        }

        $path = $this->databaseBackupDirectory() . DIRECTORY_SEPARATOR . $file;

        if (!is_file($path)) {
            abort(404);
        }

        return $path;
    }

    private function formatBytes(int|false $bytes): string
    {
        $bytes = (int) $bytes;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($index = 0; $bytes >= 1024 && $index < count($units) - 1; $index++) {
            $bytes /= 1024;
        }

        return number_format($bytes, $index === 0 ? 0 : 2, ',', '.') . ' ' . $units[$index];
    }
}
