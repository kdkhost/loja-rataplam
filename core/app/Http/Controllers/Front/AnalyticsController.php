<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function store(Request $request, AnalyticsService $analytics)
    {
        try {
            $analytics->recordEvent($request, $analytics->visitorUuid($request));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json(['status' => true]);
    }
}
