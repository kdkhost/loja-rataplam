<?php

namespace App\Services\Cron;

use App\Models\InternalCronTask;

class InternalCronRegistry
{
    public function ensureDefaults(): void
    {
        foreach ($this->defaultTasks() as $task) {
            InternalCronTask::firstOrCreate(
                ['command' => $task['command']],
                $task
            );
        }
    }

    public function defaultTasks(): array
    {
        return [
            [
                'name' => 'Processar fila do sistema',
                'command' => 'queue:work --stop-when-empty --tries=3',
                'description' => 'Processa e-mails, notificacoes e jobs pendentes sem manter processo residente.',
                'frequency' => 'every_minute',
                'minute' => '*',
                'hour' => '*',
                'day' => '*',
                'month' => '*',
                'weekday' => '*',
                'is_active' => 1,
                'is_system' => 1,
                'next_run_at' => now(),
            ],
            [
                'name' => 'Limpar cache da aplicacao',
                'command' => 'cache:clear',
                'description' => 'Remove cache de aplicacao em horario de menor movimento.',
                'frequency' => 'daily',
                'minute' => '0',
                'hour' => '3',
                'day' => '*',
                'month' => '*',
                'weekday' => '*',
                'is_active' => 1,
                'is_system' => 1,
                'next_run_at' => now(),
            ],
            [
                'name' => 'Limpar cache de views',
                'command' => 'view:clear',
                'description' => 'Garante que alteracoes de Blade sejam carregadas em hospedagem compartilhada.',
                'frequency' => 'daily',
                'minute' => '10',
                'hour' => '3',
                'day' => '*',
                'month' => '*',
                'weekday' => '*',
                'is_active' => 1,
                'is_system' => 1,
                'next_run_at' => now(),
            ],
            [
                'name' => 'Limpar rotas em cache',
                'command' => 'route:clear',
                'description' => 'Evita rotas antigas em ambiente com atualizacoes frequentes.',
                'frequency' => 'weekly',
                'minute' => '20',
                'hour' => '3',
                'day' => '*',
                'month' => '*',
                'weekday' => '0',
                'is_active' => 0,
                'is_system' => 1,
                'next_run_at' => now(),
            ],
            [
                'name' => 'Limpar configuracoes em cache',
                'command' => 'config:clear',
                'description' => 'Mantem configuracoes do painel sincronizadas quando houver cache ativo.',
                'frequency' => 'weekly',
                'minute' => '30',
                'hour' => '3',
                'day' => '*',
                'month' => '*',
                'weekday' => '0',
                'is_active' => 0,
                'is_system' => 1,
                'next_run_at' => now(),
            ],
            [
                'name' => 'Reconstruir link de storage',
                'command' => 'storage:link',
                'description' => 'Opcional para hospedagens que perdem o link publico de storage apos deploy.',
                'frequency' => 'monthly',
                'minute' => '40',
                'hour' => '3',
                'day' => '1',
                'month' => '*',
                'weekday' => '*',
                'is_active' => 0,
                'is_system' => 1,
                'next_run_at' => now(),
            ],
            [
                'name' => 'Recalcular SEO dos produtos',
                'command' => 'seo:recalculate-products',
                'description' => 'Atualiza a pontuacao SEO dos produtos antigos e dos produtos modificados em lote.',
                'frequency' => 'daily',
                'minute' => '50',
                'hour' => '3',
                'day' => '*',
                'month' => '*',
                'weekday' => '*',
                'is_active' => 1,
                'is_system' => 1,
                'next_run_at' => now(),
            ],
        ];
    }

    public function commonSettings(): array
    {
        return [
            '* * * * *' => 'Uma vez por minuto (* * * * *)',
            '*/5 * * * *' => 'Uma vez a cada 5 minutos (*/5 * * * *)',
            '*/10 * * * *' => 'Uma vez a cada 10 minutos (*/10 * * * *)',
            '*/15 * * * *' => 'Uma vez a cada 15 minutos (*/15 * * * *)',
            '*/30 * * * *' => 'Uma vez a cada 30 minutos (*/30 * * * *)',
            '0 * * * *' => 'Uma vez por hora (0 * * * *)',
            '0 0 * * *' => 'Uma vez por dia (0 0 * * *)',
            '0 0 * * 0' => 'Uma vez por semana (0 0 * * 0)',
            '0 0 1 * *' => 'Uma vez por mes (0 0 1 * *)',
        ];
    }

    public function commandOptions(): array
    {
        return [
            'queue:work --stop-when-empty --tries=3' => 'Processar fila do sistema',
            'cache:clear' => 'Limpar cache da aplicacao',
            'view:clear' => 'Limpar cache de views',
            'route:clear' => 'Limpar rotas em cache',
            'config:clear' => 'Limpar configuracoes em cache',
            'optimize:clear' => 'Limpar todos os caches otimizados',
            'storage:link' => 'Reconstruir link de storage',
            'seo:recalculate-products' => 'Recalcular SEO dos produtos',
        ];
    }

    public function selectOptions(): array
    {
        return [
            'minute' => $this->numberOptions(0, 59, [
                '*' => 'Todo minuto (*)',
                '*/5' => 'A cada 5 minutos (*/5)',
                '*/10' => 'A cada 10 minutos (*/10)',
                '*/15' => 'A cada 15 minutos (*/15)',
                '*/30' => 'A cada 30 minutos (*/30)',
            ]),
            'hour' => $this->numberOptions(0, 23, [
                '*' => 'Toda hora (*)',
                '*/2' => 'A cada 2 horas (*/2)',
                '*/4' => 'A cada 4 horas (*/4)',
                '*/6' => 'A cada 6 horas (*/6)',
                '*/12' => 'A cada 12 horas (*/12)',
            ]),
            'day' => $this->numberOptions(1, 31, ['*' => 'Todo dia (*)']),
            'month' => [
                '*' => 'Todo mes (*)',
                '1' => 'Janeiro',
                '2' => 'Fevereiro',
                '3' => 'Marco',
                '4' => 'Abril',
                '5' => 'Maio',
                '6' => 'Junho',
                '7' => 'Julho',
                '8' => 'Agosto',
                '9' => 'Setembro',
                '10' => 'Outubro',
                '11' => 'Novembro',
                '12' => 'Dezembro',
            ],
            'weekday' => [
                '*' => 'Todo dia da semana (*)',
                '0' => 'Domingo',
                '1' => 'Segunda-feira',
                '2' => 'Terca-feira',
                '3' => 'Quarta-feira',
                '4' => 'Quinta-feira',
                '5' => 'Sexta-feira',
                '6' => 'Sabado',
            ],
        ];
    }

    private function numberOptions(int $start, int $end, array $prefix = []): array
    {
        $options = $prefix;
        for ($i = $start; $i <= $end; $i++) {
            $options[(string) $i] = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
        }

        return $options;
    }
}
