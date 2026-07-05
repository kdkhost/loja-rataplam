<?php

namespace App\Http\Middleware;

use App\Services\Analytics\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class TrackPublicAnalytics
{
    public function handle(Request $request, Closure $next)
    {
        $analytics = app(AnalyticsService::class);
        $visitorUuid = $analytics->visitorUuid($request);
        $response = $next($request);

        if ($analytics->shouldTrackPage($request, $response)) {
            try {
                $analytics->recordPageView($request, $visitorUuid);
                $response->headers->setCookie(new Cookie(
                    'rp_visitor_id',
                    $visitorUuid,
                    now()->addYears(2),
                    '/',
                    null,
                    $request->isSecure(),
                    false,
                    false,
                    'Lax'
                ));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $response;
    }
}
