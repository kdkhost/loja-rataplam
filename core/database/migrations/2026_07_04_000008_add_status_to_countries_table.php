<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('countries', 'status')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->boolean('status')->default(1)->after('name');
            });
        }

        DB::table('countries')
            ->where('name', 'like', 'Pais internacional%')
            ->update(['status' => 0]);

        DB::table('countries')
            ->whereIn('name', ['Brasil', 'Brazil'])
            ->update(['status' => 1]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('countries', 'status')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
