<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('internal_cron_tasks')) {
            Schema::create('internal_cron_tasks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('command');
                $table->string('frequency')->default('hourly');
                $table->tinyInteger('is_active')->default(1);
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->string('last_status')->nullable();
                $table->text('last_output')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_cron_tasks');
    }
};
