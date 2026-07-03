<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'correios_enabled' => fn () => $table->tinyInteger('correios_enabled')->default(0)->after('pwa_start_url'),
                'correios_mode' => fn () => $table->string('correios_mode', 20)->default('free')->after('correios_enabled'),
                'correios_origin_cep' => fn () => $table->string('correios_origin_cep', 20)->nullable()->after('correios_mode'),
                'correios_services' => fn () => $table->text('correios_services')->nullable()->after('correios_origin_cep'),
                'correios_company_code' => fn () => $table->text('correios_company_code')->nullable()->after('correios_services'),
                'correios_posting_card' => fn () => $table->text('correios_posting_card')->nullable()->after('correios_company_code'),
                'correios_username' => fn () => $table->text('correios_username')->nullable()->after('correios_posting_card'),
                'correios_password' => fn () => $table->text('correios_password')->nullable()->after('correios_username'),
                'correios_token' => fn () => $table->text('correios_token')->nullable()->after('correios_password'),
                'correios_free_endpoint' => fn () => $table->text('correios_free_endpoint')->nullable()->after('correios_token'),
                'correios_extra_days' => fn () => $table->unsignedInteger('correios_extra_days')->default(0)->after('correios_free_endpoint'),
                'promo_popup_enabled' => fn () => $table->tinyInteger('promo_popup_enabled')->default(0)->after('correios_extra_days'),
                'promo_popup_title' => fn () => $table->text('promo_popup_title')->nullable()->after('promo_popup_enabled'),
                'promo_popup_text' => fn () => $table->text('promo_popup_text')->nullable()->after('promo_popup_title'),
                'promo_popup_button_text' => fn () => $table->text('promo_popup_button_text')->nullable()->after('promo_popup_text'),
                'promo_popup_link' => fn () => $table->text('promo_popup_link')->nullable()->after('promo_popup_button_text'),
                'promo_popup_image' => fn () => $table->text('promo_popup_image')->nullable()->after('promo_popup_link'),
                'promo_popup_delay' => fn () => $table->unsignedInteger('promo_popup_delay')->default(3)->after('promo_popup_image'),
                'exit_popup_enabled' => fn () => $table->tinyInteger('exit_popup_enabled')->default(0)->after('promo_popup_delay'),
                'exit_popup_title' => fn () => $table->text('exit_popup_title')->nullable()->after('exit_popup_enabled'),
                'exit_popup_text' => fn () => $table->text('exit_popup_text')->nullable()->after('exit_popup_title'),
                'exit_popup_coupon' => fn () => $table->text('exit_popup_coupon')->nullable()->after('exit_popup_text'),
                'exit_popup_button_text' => fn () => $table->text('exit_popup_button_text')->nullable()->after('exit_popup_coupon'),
                'exit_popup_link' => fn () => $table->text('exit_popup_link')->nullable()->after('exit_popup_button_text'),
                'admin_whatsapp_enabled' => fn () => $table->tinyInteger('admin_whatsapp_enabled')->default(0)->after('exit_popup_link'),
                'admin_whatsapp_phone' => fn () => $table->text('admin_whatsapp_phone')->nullable()->after('admin_whatsapp_enabled'),
                'admin_whatsapp_message' => fn () => $table->text('admin_whatsapp_message')->nullable()->after('admin_whatsapp_phone'),
                'site_whatsapp_enabled' => fn () => $table->tinyInteger('site_whatsapp_enabled')->default(0)->after('admin_whatsapp_message'),
                'site_whatsapp_phone' => fn () => $table->text('site_whatsapp_phone')->nullable()->after('site_whatsapp_enabled'),
                'site_whatsapp_message' => fn () => $table->text('site_whatsapp_message')->nullable()->after('site_whatsapp_phone'),
                'site_whatsapp_position' => fn () => $table->string('site_whatsapp_position', 20)->default('right')->after('site_whatsapp_message'),
            ];

            foreach ($columns as $column => $definition) {
                if (!Schema::hasColumn('settings', $column)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach ([
                'correios_enabled',
                'correios_mode',
                'correios_origin_cep',
                'correios_services',
                'correios_company_code',
                'correios_posting_card',
                'correios_username',
                'correios_password',
                'correios_token',
                'correios_free_endpoint',
                'correios_extra_days',
                'promo_popup_enabled',
                'promo_popup_title',
                'promo_popup_text',
                'promo_popup_button_text',
                'promo_popup_link',
                'promo_popup_image',
                'promo_popup_delay',
                'exit_popup_enabled',
                'exit_popup_title',
                'exit_popup_text',
                'exit_popup_coupon',
                'exit_popup_button_text',
                'exit_popup_link',
                'admin_whatsapp_enabled',
                'admin_whatsapp_phone',
                'admin_whatsapp_message',
                'site_whatsapp_enabled',
                'site_whatsapp_phone',
                'site_whatsapp_message',
                'site_whatsapp_position',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
