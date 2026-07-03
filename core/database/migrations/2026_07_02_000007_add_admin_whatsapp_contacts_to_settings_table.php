<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'admin_whatsapp_contacts')) {
                $table->text('admin_whatsapp_contacts')->nullable()->after('admin_whatsapp_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'admin_whatsapp_contacts')) {
                $table->dropColumn('admin_whatsapp_contacts');
            }
        });
    }
};
