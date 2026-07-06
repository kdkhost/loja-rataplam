<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'exit_popup_mode' => fn () => $table->string('exit_popup_mode', 20)->default('manual')->after('exit_popup_enabled'),
                'exit_popup_coupon_ids' => fn () => $table->text('exit_popup_coupon_ids')->nullable()->after('exit_popup_coupon'),
                'exit_popup_product_ids' => fn () => $table->text('exit_popup_product_ids')->nullable()->after('exit_popup_coupon_ids'),
                'exit_popup_show_random' => fn () => $table->boolean('exit_popup_show_random')->default(false)->after('exit_popup_product_ids'),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('settings', $column)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'exit_popup_mode',
                'exit_popup_coupon_ids',
                'exit_popup_product_ids',
                'exit_popup_show_random',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
