<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Str;

class Maintainance
{

    public function handle($request, Closure $next)
    {
        $setting = Setting::first();

        if (!$setting || (int) $setting->is_maintainance !== 1) {
            return $next($request);
        }

        if ($setting->maintainance_release_at && Carbon::parse($setting->maintainance_release_at)->isPast()) {
            $setting->update(['is_maintainance' => 0]);
            return $next($request);
        }

        $deviceToken = $request->cookie('maintenance_device_token');
        if (!$deviceToken) {
            $deviceToken = (string) Str::uuid();
            $request->cookies->set('maintenance_device_token', $deviceToken);
            cookie()->queue(cookie('maintenance_device_token', $deviceToken, 60 * 24 * 365));
        }

        if ($request->is('admin*') || $request->routeIs('front.maintainance')) {
            return $next($request);
        }

        $allowedIps = collect(preg_split('/\r\n|\r|\n|,/', (string) $setting->maintainance_allowed_ips))
            ->map(fn ($ip) => trim($ip))
            ->filter()
            ->all();

        if (in_array($request->ip(), $allowedIps, true)) {
            return $next($request);
        }

        $allowedDevices = collect(preg_split('/\r\n|\r|\n|,/', (string) $setting->maintainance_allowed_devices))
            ->map(fn ($device) => trim($device))
            ->filter()
            ->all();

        if (in_array($deviceToken, $allowedDevices, true)) {
            return $next($request);
        }

        return redirect(route('front.maintainance'));
    }
}
