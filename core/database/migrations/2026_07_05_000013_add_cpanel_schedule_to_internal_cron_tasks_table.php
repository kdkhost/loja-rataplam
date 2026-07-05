<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('internal_cron_tasks')) {
            return;
        }

        Schema::table('internal_cron_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('internal_cron_tasks', 'description')) {
                $table->text('description')->nullable()->after('command');
            }
            if (!Schema::hasColumn('internal_cron_tasks', 'minute')) {
                $table->string('minute', 40)->default('*')->after('frequency');
            }
            if (!Schema::hasColumn('internal_cron_tasks', 'hour')) {
                $table->string('hour', 40)->default('*')->after('minute');
            }
            if (!Schema::hasColumn('internal_cron_tasks', 'day')) {
                $table->string('day', 40)->default('*')->after('hour');
            }
            if (!Schema::hasColumn('internal_cron_tasks', 'month')) {
                $table->string('month', 40)->default('*')->after('day');
            }
            if (!Schema::hasColumn('internal_cron_tasks', 'weekday')) {
                $table->string('weekday', 40)->default('*')->after('month');
            }
            if (!Schema::hasColumn('internal_cron_tasks', 'is_system')) {
                $table->tinyInteger('is_system')->default(0)->after('is_active');
            }
        });

        $map = [
            'every_five_minutes' => ['*/5', '*', '*', '*', '*'],
            'every_ten_minutes' => ['*/10', '*', '*', '*', '*'],
            'every_thirty_minutes' => ['*/30', '*', '*', '*', '*'],
            'hourly' => ['0', '*', '*', '*', '*'],
            'daily' => ['0', '0', '*', '*', '*'],
            'weekly' => ['0', '0', '*', '*', '0'],
            'monthly' => ['0', '0', '1', '*', '*'],
        ];

        foreach (DB::table('internal_cron_tasks')->select('id', 'frequency')->get() as $task) {
            [$minute, $hour, $day, $month, $weekday] = $map[$task->frequency] ?? ['0', '*', '*', '*', '*'];
            DB::table('internal_cron_tasks')->where('id', $task->id)->update(compact('minute', 'hour', 'day', 'month', 'weekday'));
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('internal_cron_tasks')) {
            return;
        }

        Schema::table('internal_cron_tasks', function (Blueprint $table) {
            foreach (['is_system', 'weekday', 'month', 'day', 'hour', 'minute', 'description'] as $column) {
                if (Schema::hasColumn('internal_cron_tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
