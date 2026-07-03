<?php

namespace App\Providers;

use Illuminate\{
    Support\ServiceProvider,
    Support\Facades\DB
};
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Paginator::useBootstrap();
        view()->composer('*', function ($settings) {
            $settings->with('setting', DB::table('settings')->find(1));
            $settings->with('extra_settings', DB::table('extra_settings')->find(1));
            // Busca o menu pelo idioma ativo na sessão, com fallback para o primeiro disponível
            $activeLanguageId = session('language', DB::table('languages')->where('is_default', 1)->value('id'));
            $menus = DB::table('menus')->where('language_id', $activeLanguageId)->first();
            if (!$menus) {
                $menus = DB::table('menus')->first();
            }
            $settings->with('menus', $menus);

            if (!session()->has('popup')) {
                view()->share('visit', 1);
            }
            session()->put('popup', 1);
        });
    }

    public function register()
    {
    }
}