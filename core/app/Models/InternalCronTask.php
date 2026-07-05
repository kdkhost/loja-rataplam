<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InternalCronTask extends Model
{
    protected $fillable = [
        'name',
        'command',
        'description',
        'frequency',
        'minute',
        'hour',
        'day',
        'month',
        'weekday',
        'is_active',
        'is_system',
        'last_run_at',
        'next_run_at',
        'last_status',
        'last_output',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
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
        $candidate = $from->copy()->addMinute()->startOfMinute();

        for ($i = 0; $i < 527040; $i++) {
            if ($this->matchesCronAt($candidate)) {
                return $candidate;
            }
            $candidate->addMinute();
        }

        return $from->copy()->addDay();
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

    public function cronExpression(): string
    {
        return implode(' ', [
            $this->minute ?: '*',
            $this->hour ?: '*',
            $this->day ?: '*',
            $this->month ?: '*',
            $this->weekday ?: '*',
        ]);
    }

    public function scheduleLabel(): string
    {
        return $this->cronExpression();
    }

    private function matchesCronAt(Carbon $time): bool
    {
        return $this->fieldMatches((string) ($this->minute ?: '*'), (int) $time->minute, 0, 59)
            && $this->fieldMatches((string) ($this->hour ?: '*'), (int) $time->hour, 0, 23)
            && $this->fieldMatches((string) ($this->day ?: '*'), (int) $time->day, 1, 31)
            && $this->fieldMatches((string) ($this->month ?: '*'), (int) $time->month, 1, 12)
            && $this->fieldMatches((string) ($this->weekday ?: '*'), (int) $time->dayOfWeek, 0, 6);
    }

    private function fieldMatches(string $expression, int $value, int $min, int $max): bool
    {
        foreach (explode(',', $expression) as $part) {
            $part = trim($part);
            if ($part === '*') {
                return true;
            }

            $step = 1;
            if (str_contains($part, '/')) {
                [$part, $stepValue] = explode('/', $part, 2);
                $step = max(1, (int) $stepValue);
            }

            if ($part === '*') {
                return (($value - $min) % $step) === 0;
            }

            if (str_contains($part, '-')) {
                [$start, $end] = array_map('intval', explode('-', $part, 2));
                if ($value >= $start && $value <= $end && (($value - $start) % $step) === 0) {
                    return true;
                }
                continue;
            }

            if ((int) $part === $value) {
                return true;
            }
        }

        return false;
    }
}
