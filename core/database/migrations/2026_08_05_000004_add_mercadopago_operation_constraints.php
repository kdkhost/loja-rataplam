<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mercadopago_actions', function (Blueprint $table): void {
            $table->unique('mercadopago_operation_id', 'mp_operation_id_unique');
            $table->index(
                ['order_id', 'action', 'environment'],
                'mp_order_action_environment'
            );
        });
    }

    public function down(): void
    {
        Schema::table('mercadopago_actions', function (Blueprint $table): void {
            $table->dropUnique('mp_operation_id_unique');
            $table->dropIndex('mp_order_action_environment');
        });
    }
};
