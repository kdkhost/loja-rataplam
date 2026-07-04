<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Response;

class PwaController extends Controller
{
    public function manifest()
    {
        $setting = Setting::first();

        abort_if(!$setting || !$setting->is_pwa, 404);

        $icon = $setting->pwa_icon ?: $setting->favicon;
        $icon192 = $setting->pwa_icon_192 ?: $icon;
        $icon512 = $setting->pwa_icon_512 ?: $icon;
        $iconType192 = $this->iconMimeType($icon192);
        $iconType512 = $this->iconMimeType($icon512);

        return response()->json([
            'name' => $setting->pwa_name ?: $setting->title,
            'short_name' => $setting->pwa_short_name ?: $setting->title,
            'start_url' => $setting->pwa_start_url ?: '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => $setting->pwa_background_color ?: '#ffffff',
            'theme_color' => $setting->pwa_theme_color ?: $setting->primary_color,
            'icons' => [
                [
                    'src' => url('/core/public/storage/images/' . $icon192),
                    'sizes' => '192x192',
                    'type' => $iconType192,
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => url('/core/public/storage/images/' . $icon512),
                    'sizes' => '512x512',
                    'type' => $iconType512,
                    'purpose' => 'any maskable',
                ],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }

    private function iconMimeType(?string $icon): string
    {
        return match (strtolower(pathinfo((string) $icon, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }

    public function serviceWorker()
    {
        $setting = Setting::first();

        abort_if(!$setting || !$setting->is_pwa, 404);

        $cacheVersion = optional($setting->updated_at)->timestamp ?: time();

        $script = "const CACHE_NAME = 'omnimart-pwa-v{$cacheVersion}';\n"
            . "const OFFLINE_URLS = ['/', '/core/public/storage/images/{$setting->favicon}'];\n"
            . "self.addEventListener('install', event => { event.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(OFFLINE_URLS)).then(() => self.skipWaiting())); });\n"
            . "self.addEventListener('activate', event => { event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))))); self.clients.claim(); });\n"
            . "self.addEventListener('fetch', event => { if (event.request.method !== 'GET') return; event.respondWith(fetch(event.request).then(response => { const copy = response.clone(); caches.open(CACHE_NAME).then(cache => cache.put(event.request, copy)); return response; }).catch(() => caches.match(event.request).then(response => response || caches.match('/')))); });\n";

        return new Response($script, 200, ['Content-Type' => 'application/javascript']);
    }
}
