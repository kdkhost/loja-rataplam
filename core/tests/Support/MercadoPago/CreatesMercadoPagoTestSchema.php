<?php

namespace Tests\Support\MercadoPago;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\Support\MercadoPago\MercadoPagoTestDatabaseGuard;

trait CreatesMercadoPagoTestSchema
{
    /**
     * Create the test schema.
     */
    protected function createMercadoPagoTestSchema(): void
    {
        // Guard: validar configuração de banco de teste antes de criar schema
        MercadoPagoTestDatabaseGuard::validateFromLaravel();
        MercadoPagoTestDatabaseGuard::validateRealConnection();

        // Drop existing tables first to ensure clean state
        Schema::dropIfExists('mercadopago_actions');
        Schema::dropIfExists('mercadopago_settings');
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('extra_settings');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('items');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('shipping_services');
        Schema::dropIfExists('states');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('users');
        Schema::dropIfExists('admins');

        // Create languages table (required by adminlocalize middleware)
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique();
            $table->string('type')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Create menus table (required by system middleware)
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content')->nullable();
            $table->foreignId('language_id')->nullable();
            $table->timestamps();
        });

        // Create settings table (legacy table used by PaymentSetting model)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('unique_keyword')->unique();
            $table->json('credentials')->nullable();
            $table->boolean('status')->default(0);
            $table->text('text')->nullable();
            $table->string('photo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_single_checkout')->default(false);
            $table->boolean('is_maintainance')->default(false);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 3);
            $table->string('sign')->nullable();
            $table->decimal('value', 12, 8)->default(1);
            $table->boolean('is_default')->default(true);
            $table->boolean('status')->default(true);
        });
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('value', 8, 4)->default(0);
            $table->boolean('status')->default(true);
        });
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('discount_price', 12, 2);
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->timestamps();
        });
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_id')->nullable();
            $table->string('name')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->integer('stock')->nullable();
        });
        Schema::create('shipping_services', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->boolean('status')->default(true);
        });
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->decimal('price', 12, 4)->default(0);
            $table->string('type')->default('fixed');
            $table->boolean('status')->default(true);
        });
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('code_name')->nullable();
            $table->decimal('discount', 12, 4)->default(0);
            $table->string('type')->default('amount');
            $table->boolean('status')->default(true);
            $table->integer('no_of_times')->default(1);
            $table->timestamps();
        });

        // Create extra_settings table (used by some legacy code)
        Schema::create('extra_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Create admins table (for mercadopago_actions.admin_id foreign key)
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Create orders table (for mercadopago_actions.order_id foreign key)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('payment_status')->default('pending');
            $table->text('payment_details')->nullable();
            $table->string('state_price')->nullable();
            $table->text('cart')->nullable();
            $table->text('discount')->nullable();
            $table->text('shipping')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('txnid')->nullable();
            $table->string('transaction_number')->nullable();
            $table->string('order_status')->nullable();
            $table->string('gateway_fee')->nullable();
            $table->string('currency_sign')->nullable();
            $table->string('currency_value')->nullable();
            $table->text('shipping_info')->nullable();
            $table->text('billing_info')->nullable();
            $table->text('state')->nullable();
            $table->string('tax')->nullable();
            $table->timestamps();
        });

        // Create payment_settings table (for legacy fallback in MercadoPagoConfigResolver)
        if (!Schema::hasTable('payment_settings')) {
            Schema::create('payment_settings', function (Blueprint $table) {
                $table->id();
                $table->string('unique_keyword')->unique();
                $table->json('credentials')->nullable();
                $table->json('information')->nullable();
                $table->boolean('status')->default(1);
                $table->timestamps();
            });
        }

        // Create mercadopago_settings table
        if (!Schema::hasTable('mercadopago_settings')) {
            Schema::create('mercadopago_settings', function (Blueprint $table) {
                $table->id();
                $table->string('configuration_key')->default('default')->unique();
                $table->string('mode')->default('sandbox');
                $table->text('sandbox_public_key')->nullable();
                $table->text('sandbox_access_token')->nullable();
                $table->string('sandbox_collector_id')->nullable();
                $table->text('production_public_key')->nullable();
                $table->text('production_access_token')->nullable();
                $table->string('production_collector_id')->nullable();
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

        // Create mercadopago_actions table (matching migration)
        if (!Schema::hasTable('mercadopago_actions')) {
            Schema::create('mercadopago_actions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                // No foreign key constraint for testing
                $table->string('payment_id')->nullable();
                $table->string('environment')->nullable();
                $table->string('action');
                $table->decimal('requested_amount', 12, 2)->nullable();
                $table->string('currency', 3)->nullable();
                $table->uuid('idempotency_key')->unique();
                $table->string('request_fingerprint', 64)->nullable();
                $table->string('mercadopago_operation_id')->nullable()->unique('mp_operation_id_unique');
                $table->string('remote_status')->nullable();
                $table->string('local_status')->default('pending');
                $table->uuid('execution_owner')->nullable();
                $table->timestamp('execution_started_at')->nullable();
                $table->timestamp('execution_lease_expires_at')->nullable();
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->json('response_summary')->nullable();
                $table->string('error_code')->nullable();
                $table->unsignedBigInteger('performed_by_admin_id')->nullable();
                $table->text('pix_qr_code')->nullable();
                $table->text('pix_qr_code_base64')->nullable();
                $table->string('pix_ticket_url', 2048)->nullable();
                $table->timestamp('pix_expiration_date')->nullable();
                $table->foreign('performed_by_admin_id')->references('id')->on('admins')->nullOnDelete();
                $table->timestamps();
                $table->index('payment_id');
                $table->index('order_id');
                $table->index('action');
                $table->index('environment');
                $table->index('local_status');
                $table->index('created_at');
                $table->index(['order_id', 'action', 'environment'], 'mp_order_action_environment');
                $table->index(['execution_owner', 'execution_lease_expires_at'], 'mp_exec_owner_lease');
                $table->index('execution_lease_expires_at', 'mp_exec_lease_expires');
            });
        }
    }

    /**
     * Drop the test schema.
     */
    protected function dropMercadoPagoTestSchema(): void
    {
        // Guard: validar configuração de banco de teste antes de remover schema
        MercadoPagoTestDatabaseGuard::validateFromLaravel();
        MercadoPagoTestDatabaseGuard::validateRealConnection();

        Schema::dropIfExists('mercadopago_actions');
        Schema::dropIfExists('mercadopago_settings');
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('extra_settings');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('items');
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('shipping_services');
        Schema::dropIfExists('states');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('promo_codes');
        Schema::dropIfExists('users');
        Schema::dropIfExists('admins');
    }
}
