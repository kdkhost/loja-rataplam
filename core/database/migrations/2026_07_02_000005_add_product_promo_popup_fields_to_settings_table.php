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
                'promo_popup_mode' => fn () => $table->string('promo_popup_mode', 20)->default('manual')->after('promo_popup_enabled'),
                'promo_popup_item_id' => fn () => $table->unsignedBigInteger('promo_popup_item_id')->nullable()->after('promo_popup_mode'),
                'promo_popup_campaign_type' => fn () => $table->string('promo_popup_campaign_type', 40)->nullable()->after('promo_popup_item_id'),
                'promo_popup_badge' => fn () => $table->text('promo_popup_badge')->nullable()->after('promo_popup_campaign_type'),
                'promo_popup_starts_at' => fn () => $table->dateTime('promo_popup_starts_at')->nullable()->after('promo_popup_delay'),
                'promo_popup_ends_at' => fn () => $table->dateTime('promo_popup_ends_at')->nullable()->after('promo_popup_starts_at'),
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
                'promo_popup_mode',
                'promo_popup_item_id',
                'promo_popup_campaign_type',
                'promo_popup_badge',
                'promo_popup_starts_at',
                'promo_popup_ends_at',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
