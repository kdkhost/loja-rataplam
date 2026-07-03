<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'maintainance_release_at')) {
                $table->dateTime('maintainance_release_at')->nullable()->after('maintainance_text');
            }
            if (!Schema::hasColumn('settings', 'maintainance_allowed_ips')) {
                $table->text('maintainance_allowed_ips')->nullable()->after('maintainance_release_at');
            }
            if (!Schema::hasColumn('settings', 'maintainance_allowed_devices')) {
                $table->text('maintainance_allowed_devices')->nullable()->after('maintainance_allowed_ips');
            }
            if (!Schema::hasColumn('settings', 'is_pwa')) {
                $table->tinyInteger('is_pwa')->default(0)->after('is_single_checkout');
            }
            if (!Schema::hasColumn('settings', 'pwa_name')) {
                $table->string('pwa_name')->nullable()->after('is_pwa');
            }
            if (!Schema::hasColumn('settings', 'pwa_short_name')) {
                $table->string('pwa_short_name', 30)->nullable()->after('pwa_name');
            }
            if (!Schema::hasColumn('settings', 'pwa_theme_color')) {
                $table->string('pwa_theme_color', 20)->nullable()->after('pwa_short_name');
            }
            if (!Schema::hasColumn('settings', 'pwa_background_color')) {
                $table->string('pwa_background_color', 20)->nullable()->after('pwa_theme_color');
            }
            if (!Schema::hasColumn('settings', 'pwa_icon')) {
                $table->string('pwa_icon')->nullable()->after('pwa_background_color');
            }
            if (!Schema::hasColumn('settings', 'pwa_start_url')) {
                $table->string('pwa_start_url')->default('/')->after('pwa_icon');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'maintainance_release_at',
                'maintainance_allowed_ips',
                'maintainance_allowed_devices',
                'is_pwa',
                'pwa_name',
                'pwa_short_name',
                'pwa_theme_color',
                'pwa_background_color',
                'pwa_icon',
                'pwa_start_url',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
