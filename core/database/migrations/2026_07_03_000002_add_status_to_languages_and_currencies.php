<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('languages', 'status')) {
            Schema::table('languages', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('is_default');
            });
        }

        if (!Schema::hasColumn('currencies', 'status')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->boolean('status')->default(true)->after('is_default');
            });
        }

        DB::table('languages')->update(['status' => 0]);
        DB::table('languages')
            ->whereIn(DB::raw('LOWER(language)'), ['english', 'portugues', 'português', 'portuguese'])
            ->update(['status' => 1]);

        DB::table('currencies')->update(['status' => 0]);
        DB::table('currencies')
            ->whereIn(DB::raw('UPPER(name)'), ['BRL', 'USD'])
            ->update(['status' => 1]);

        DB::table('languages')
            ->where('type', 'Website')
            ->where('id', '!=', DB::table('languages')->where('type', 'Website')->where('is_default', 1)->value('id'))
            ->update(['is_default' => 0]);

        DB::table('languages')
            ->where('type', 'Dashboard')
            ->where('id', '!=', DB::table('languages')->where('type', 'Dashboard')->where('is_default', 1)->value('id'))
            ->update(['is_default' => 0]);

        DB::table('currencies')
            ->where('id', '!=', DB::table('currencies')->where('is_default', 1)->value('id'))
            ->update(['is_default' => 0]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('languages', 'status')) {
            Schema::table('languages', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('currencies', 'status')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
