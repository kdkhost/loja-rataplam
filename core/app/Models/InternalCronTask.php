<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InternalCronTask extends Model
{
    protected $fillable = [
        'name',
        'command',
        'frequency',
        'is_active',
        'last_run_at',
        'next_run_at',
        'last_status',
        'last_output',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function isDue(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return !$this->next_run_at || $this->next_run_at->lte(now());
    }

    public function calculateNextRun(?Carbon $from = null): Carbon
    {
        $from = $from ?: now();

        return match ($this->frequency) {
            'every_five_minutes' => $from->copy()->addMinutes(5),
            'every_ten_minutes' => $from->copy()->addMinutes(10),
            'every_thirty_minutes' => $from->copy()->addMinutes(30),
            'daily' => $from->copy()->addDay(),
            'weekly' => $from->copy()->addWeek(),
            'monthly' => $from->copy()->addMonthNoOverflow(),
            default => $from->copy()->addHour(),
        };
    }

    public static function frequencies(): array
    {
        return [
            'every_five_minutes' => 'A cada 5 minutos',
            'every_ten_minutes' => 'A cada 10 minutos',
            'every_thirty_minutes' => 'A cada 30 minutos',
            'hourly' => 'A cada hora',
            'daily' => 'Diario',
            'weekly' => 'Semanal',
            'monthly' => 'Mensal',
        ];
    }
}
