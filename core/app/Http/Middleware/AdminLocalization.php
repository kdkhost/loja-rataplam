<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class AdminLocalization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $language = Language::whereType('Dashboard')->where('status', 1)->where('is_default', 1)->first()
            ?: Language::whereType('Dashboard')->where('status', 1)->first();

        if ($language) {
            $locale = $language->name ?: pathinfo($language->file, PATHINFO_FILENAME);
            App::setLocale($locale);
            Session::put('admin_language', $language->id);
        }

        return $next($request);
    }
}
