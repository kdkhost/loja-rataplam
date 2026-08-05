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
            // Campos mínimos para replay seguro de Pix
            $table->text('pix_qr_code')->nullable()->after('response_summary');
            $table->text('pix_qr_code_base64')->nullable()->after('pix_qr_code');
            $table->string('pix_ticket_url', 2048)->nullable()->after('pix_qr_code_base64');
            $table->timestamp('pix_expiration_date')->nullable()->after('pix_ticket_url');
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
            $table->dropColumn(['pix_qr_code', 'pix_qr_code_base64', 'pix_ticket_url', 'pix_expiration_date']);
        });
    }
};
