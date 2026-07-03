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
                'admin_whatsapp_title' => fn () => $table->text('admin_whatsapp_title')->nullable()->after('admin_whatsapp_enabled'),
                'admin_whatsapp_primary_name' => fn () => $table->text('admin_whatsapp_primary_name')->nullable()->after('admin_whatsapp_phone'),
                'admin_whatsapp_primary_label' => fn () => $table->text('admin_whatsapp_primary_label')->nullable()->after('admin_whatsapp_primary_name'),
                'admin_whatsapp_secondary_enabled' => fn () => $table->tinyInteger('admin_whatsapp_secondary_enabled')->default(0)->after('admin_whatsapp_message'),
                'admin_whatsapp_secondary_name' => fn () => $table->text('admin_whatsapp_secondary_name')->nullable()->after('admin_whatsapp_secondary_enabled'),
                'admin_whatsapp_secondary_phone' => fn () => $table->text('admin_whatsapp_secondary_phone')->nullable()->after('admin_whatsapp_secondary_name'),
                'admin_whatsapp_secondary_label' => fn () => $table->text('admin_whatsapp_secondary_label')->nullable()->after('admin_whatsapp_secondary_phone'),
                'admin_whatsapp_secondary_message' => fn () => $table->text('admin_whatsapp_secondary_message')->nullable()->after('admin_whatsapp_secondary_label'),
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
                'admin_whatsapp_title',
                'admin_whatsapp_primary_name',
                'admin_whatsapp_primary_label',
                'admin_whatsapp_secondary_enabled',
                'admin_whatsapp_secondary_name',
                'admin_whatsapp_secondary_phone',
                'admin_whatsapp_secondary_label',
                'admin_whatsapp_secondary_message',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
