<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'announcement_button_text')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->text('announcement_button_text')->nullable()->after('announcement_link');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'announcement_button_text')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('announcement_button_text');
            });
        }
    }
};
