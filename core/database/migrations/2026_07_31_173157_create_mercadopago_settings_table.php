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
        Schema::create('mercadopago_settings', function (Blueprint $table) {
            $table->id();
            $table->string('configuration_key')->default('default')->unique();
            $table->string('mode')->default('sandbox');

            $table->text('sandbox_public_key')->nullable();
            $table->text('sandbox_access_token')->nullable();

            $table->text('production_public_key')->nullable();
            $table->text('production_access_token')->nullable();

            $table->text('sandbox_webhook_secret')->nullable();
            $table->text('production_webhook_secret')->nullable();

            $table->string('webhook_validation_mode')->default('api_lookup');

            $table->boolean('pix_enabled')->default(true);
            $table->boolean('credit_card_enabled')->default(true);

            $table->unsignedInteger('pix_expiration_minutes')->default(30);
            $table->unsignedTinyInteger('max_installments')->default(1);

            $table->boolean('fee_pass_to_customer')->default(false);
            $table->string('fee_calculation_mode')->default('additive');

            $table->decimal('pix_fee_percent', 8, 4)->default(0);
            $table->decimal('pix_fee_fixed', 12, 2)->default(0);

            $table->decimal('credit_fee_percent', 8, 4)->default(0);
            $table->decimal('credit_fee_fixed', 12, 2)->default(0);

            $table->boolean('refund_enabled')->default(false);
            $table->boolean('partial_refund_enabled')->default(false);
            $table->boolean('cancellation_enabled')->default(false);
            $table->boolean('reconciliation_enabled')->default(true);

            $table->boolean('binary_mode')->default(true);
            $table->string('statement_descriptor')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mercadopago_settings');
    }
};
