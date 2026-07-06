<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'announcement_mode')) {
                $table->string('announcement_mode', 20)->default('manual')->nullable()->after('announcement_ends_at');
            }
            if (!Schema::hasColumn('settings', 'announcement_coupon_ids')) {
                $table->text('announcement_coupon_ids')->nullable()->after('announcement_mode');
            }
            if (!Schema::hasColumn('settings', 'announcement_product_ids')) {
                $table->text('announcement_product_ids')->nullable()->after('announcement_coupon_ids');
            }
            if (!Schema::hasColumn('settings', 'announcement_show_random')) {
                $table->boolean('announcement_show_random')->default(false)->nullable()->after('announcement_product_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'announcement_mode',
                'announcement_coupon_ids',
                'announcement_product_ids',
                'announcement_show_random',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
