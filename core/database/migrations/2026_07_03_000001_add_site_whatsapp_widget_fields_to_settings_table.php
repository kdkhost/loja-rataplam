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
                'site_whatsapp_attendant_name' => fn () => $table->text('site_whatsapp_attendant_name')->nullable()->after('site_whatsapp_phone'),
                'site_whatsapp_attendant_photo' => fn () => $table->text('site_whatsapp_attendant_photo')->nullable()->after('site_whatsapp_attendant_name'),
                'site_whatsapp_support_message' => fn () => $table->text('site_whatsapp_support_message')->nullable()->after('site_whatsapp_attendant_photo'),
                'site_whatsapp_offline_message' => fn () => $table->text('site_whatsapp_offline_message')->nullable()->after('site_whatsapp_support_message'),
                'site_whatsapp_working_days' => fn () => $table->text('site_whatsapp_working_days')->nullable()->after('site_whatsapp_offline_message'),
                'site_whatsapp_working_start' => fn () => $table->string('site_whatsapp_working_start', 10)->nullable()->after('site_whatsapp_working_days'),
                'site_whatsapp_working_end' => fn () => $table->string('site_whatsapp_working_end', 10)->nullable()->after('site_whatsapp_working_start'),
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
                'site_whatsapp_attendant_name',
                'site_whatsapp_attendant_photo',
                'site_whatsapp_support_message',
                'site_whatsapp_offline_message',
                'site_whatsapp_working_days',
                'site_whatsapp_working_start',
                'site_whatsapp_working_end',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
