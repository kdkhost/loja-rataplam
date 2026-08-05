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
        Schema::create('mercadopago_actions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();

            $table->string('payment_id')->nullable();
            $table->string('environment');
            $table->string('action');
            $table->decimal('requested_amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();

            $table->uuid('idempotency_key')->unique();
            $table->string('request_fingerprint', 64)->nullable();

            $table->string('mercadopago_operation_id')->nullable();
            $table->string('remote_status')->nullable();

            $table->string('local_status')->default('pending');
            $table->unsignedSmallInteger('http_status')->nullable();

            $table->json('response_summary')->nullable();
            $table->string('error_code')->nullable();

            // Administradores usam a tabela admins no core
            $table->unsignedBigInteger('performed_by_admin_id')->nullable();
            // Assumindo admins table, ser├í necess├írio verificar se ├® admins mesmo, usaremos foreign ap├│s confirmar.
            // O projeto usa admins table com increment id
            $table->foreign('performed_by_admin_id')->references('id')->on('admins')->nullOnDelete();

            $table->timestamps();

            $table->index('payment_id');
            $table->index('order_id');
            $table->index('action');
            $table->index('environment');
            $table->index('local_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mercadopago_actions');
    }
};
