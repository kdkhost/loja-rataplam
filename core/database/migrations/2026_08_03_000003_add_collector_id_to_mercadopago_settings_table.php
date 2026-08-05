<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mercadopago_settings', function (Blueprint $table) {
            $table->string('sandbox_collector_id')->nullable()->after('sandbox_access_token');
            $table->string('production_collector_id')->nullable()->after('production_access_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mercadopago_settings', function (Blueprint $table) {
            $table->dropColumn(['sandbox_collector_id', 'production_collector_id']);
        });
    }
};
