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
        Schema::table('mercadopago_actions', function (Blueprint $table) {
            $table->uuid('execution_owner')->nullable()->after('local_status');
            $table->timestamp('execution_started_at')->nullable()->after('execution_owner');
            $table->timestamp('execution_lease_expires_at')->nullable()->after('execution_started_at');

            // Índices para consultas de lease (nomes curtos para limitação MySQL de 64 caracteres)
            $table->index(['execution_owner', 'execution_lease_expires_at'], 'mp_exec_owner_lease');
            $table->index('execution_lease_expires_at', 'mp_exec_lease_expires');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mercadopago_actions', function (Blueprint $table) {
            $table->dropIndex('mp_exec_owner_lease');
            $table->dropIndex('mp_exec_lease_expires');
            $table->dropColumn(['execution_owner', 'execution_started_at', 'execution_lease_expires_at']);
        });
    }
};
