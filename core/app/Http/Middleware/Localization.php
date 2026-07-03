<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
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
        if(Session::has('language')){
            $language = Language::where('status', 1)->find(Session::get('language'));
        }else{
            $language = Language::whereType('Website')->where('status', 1)->where('is_default',1)->first();
        }
        if(!$language){
            Session::forget('language');
            $language = Language::whereType('Website')->where('status', 1)->where('is_default',1)->first()
                ?: Language::whereType('Website')->where('status', 1)->first();
        }
        if ($language) {
            App::setLocale($language->name ?: pathinfo($language->file, PATHINFO_FILENAME));
        }
        return $next($request);
    }
}
