<?php

namespace App\Console\Commands;

use App\Models\InternalCronTask;
use App\Services\Cron\InternalCronRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class RunInternalCron extends Command
{
    protected $signature = 'internal-cron:run {--task=}';

    protected $description = 'Executa a central interna de cron cadastrada no painel administrativo.';

    public function handle(): int
    {
        app(InternalCronRegistry::class)->ensureDefaults();

        $query = InternalCronTask::query()->where('is_active', 1);

        if ($this->option('task')) {
            $query->where('id', $this->option('task'));
        } else {
            $query->where(function ($builder) {
                $builder->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            });
        }

        $tasks = $query->get();

        foreach ($tasks as $task) {
            try {
                $exitCode = Artisan::call($task->command);
                $task->last_status = $exitCode === 0 ? 'success' : 'error';
                $task->last_output = trim(Artisan::output());
            } catch (Throwable $exception) {
                $task->last_status = 'error';
                $task->last_output = $exception->getMessage();
            }

            $task->last_run_at = now();
            $task->next_run_at = $task->calculateNextRun();
            $task->save();

            $this->line("{$task->name}: {$task->last_status}");
        }

        return self::SUCCESS;
    }
}
