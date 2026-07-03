<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class Security
{
    public function handle($request, Closure $next)
    {  
        if ($request->is('admin')) {
            return $next($request);
        }
        
        return $next($request); 
    }
}
