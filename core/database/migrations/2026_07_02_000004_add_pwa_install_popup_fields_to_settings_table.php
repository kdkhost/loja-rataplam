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
                'pwa_install_popup_enabled' => fn () => $table->tinyInteger('pwa_install_popup_enabled')->default(0)->after('pwa_start_url'),
                'pwa_install_popup_title' => fn () => $table->text('pwa_install_popup_title')->nullable()->after('pwa_install_popup_enabled'),
                'pwa_install_popup_text' => fn () => $table->text('pwa_install_popup_text')->nullable()->after('pwa_install_popup_title'),
                'pwa_install_popup_button_text' => fn () => $table->text('pwa_install_popup_button_text')->nullable()->after('pwa_install_popup_text'),
                'pwa_install_popup_later_text' => fn () => $table->text('pwa_install_popup_later_text')->nullable()->after('pwa_install_popup_button_text'),
                'pwa_install_popup_image' => fn () => $table->text('pwa_install_popup_image')->nullable()->after('pwa_install_popup_later_text'),
                'pwa_install_popup_delay' => fn () => $table->unsignedInteger('pwa_install_popup_delay')->default(3)->after('pwa_install_popup_image'),
                'pwa_icon_192' => fn () => $table->text('pwa_icon_192')->nullable()->after('pwa_install_popup_delay'),
                'pwa_icon_512' => fn () => $table->text('pwa_icon_512')->nullable()->after('pwa_icon_192'),
                'pwa_auto_generate_icons' => fn () => $table->tinyInteger('pwa_auto_generate_icons')->default(1)->after('pwa_icon_512'),
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
                'pwa_install_popup_enabled',
                'pwa_install_popup_title',
                'pwa_install_popup_text',
                'pwa_install_popup_button_text',
                'pwa_install_popup_later_text',
                'pwa_install_popup_image',
                'pwa_install_popup_delay',
                'pwa_icon_192',
                'pwa_icon_512',
                'pwa_auto_generate_icons',
            ] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
