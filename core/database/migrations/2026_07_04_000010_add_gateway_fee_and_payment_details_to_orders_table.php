<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'gateway_fee')) {
                $table->decimal('gateway_fee', 12, 2)->default(0)->after('state_price');
            }

            if (!Schema::hasColumn('orders', 'payment_details')) {
                $table->longText('payment_details')->nullable()->after('payment_status');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_details')) {
                $table->dropColumn('payment_details');
            }

            if (Schema::hasColumn('orders', 'gateway_fee')) {
                $table->dropColumn('gateway_fee');
            }
        });
    }
};
