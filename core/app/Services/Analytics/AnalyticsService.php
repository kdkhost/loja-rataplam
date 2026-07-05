<?php

namespace App\Services\Analytics;

use App\Helpers\PriceHelper;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsPageView;
use App\Models\Item;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AnalyticsService
{
    public function recordPageView(Request $request, string $visitorUuid): void
    {
        if (!$this->tablesReady() || $this->isBot($request->userAgent())) {
            return;
        }

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
        $item = $this->resolveItem($routeName, (string) $request->route('slug'));
        $referrer = (string) $request->headers->get('referer', '');
        $referrerHost = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;

        AnalyticsPageView::create([
            'visitor_uuid' => $visitorUuid,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'user_id' => Auth::check() ? Auth::id() : null,
            'item_id' => $item ? $item->id : null,
            'route_name' => $routeName,
            'page_type' => $this->pageType($routeName, $request->path()),
            'page_title' => $this->pageTitle($routeName, $request->path(), $item),
            'path' => Str::limit('/' . ltrim($request->path(), '/'), 500, ''),
            'full_url' => $request->fullUrl(),
            'query_string' => $request->getQueryString(),
            'referrer' => $referrer ?: null,
            'referrer_host' => $referrerHost && $referrerHost !== $request->getHost() ? Str::limit($referrerHost, 255, '') : null,
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_content' => $request->query('utm_content'),
            'utm_term' => $request->query('utm_term'),
            'device_type' => $this->deviceType($request->userAgent()),
            'browser' => $this->browser($request->userAgent()),
            'platform' => $this->platform($request->userAgent()),
            'ip_hash' => hash('sha256', (string) $request->ip() . '|' . config('app.key')),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'is_bot' => false,
        ]);
    }

    public function recordEvent(Request $request, string $visitorUuid): void
    {
        if (!$this->tablesReady() || $this->isBot($request->userAgent())) {
            return;
        }

        $payload = $request->validate([
            'event_name' => 'required|string|max:100',
            'event_category' => 'nullable|string|max:80',
            'item_id' => 'nullable|integer|exists:items,id',
            'page_type' => 'nullable|string|max:60',
            'path' => 'nullable|string|max:500',
            'value' => 'nullable|numeric',
            'metadata' => 'nullable|array',
        ]);

        AnalyticsEvent::create([
            'visitor_uuid' => $visitorUuid,
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'user_id' => Auth::check() ? Auth::id() : null,
            'item_id' => $payload['item_id'] ?? null,
            'event_name' => Str::slug($payload['event_name'], '_'),
            'event_category' => $payload['event_category'] ?? null,
            'page_type' => $payload['page_type'] ?? null,
            'path' => isset($payload['path']) ? Str::limit($payload['path'], 500, '') : null,
            'value' => $payload['value'] ?? null,
            'metadata' => $payload['metadata'] ?? null,
        ]);
    }

    public function dashboardData(): array
    {
        if (!$this->tablesReady()) {
            return $this->emptyDashboardData();
        }

        $today = now()->startOfDay();
        $monthStart = now()->subDays(29)->startOfDay();

        return [
            'updated_at' => now()->format('d/m/Y H:i:s'),
            'cards' => [
                'views_today' => AnalyticsPageView::where('created_at', '>=', $today)->count(),
                'visitors_today' => AnalyticsPageView::where('created_at', '>=', $today)->distinct('visitor_uuid')->count('visitor_uuid'),
                'online_now' => AnalyticsPageView::where('created_at', '>=', now()->subMinutes(5))->distinct('visitor_uuid')->count('visitor_uuid'),
                'product_views_today' => AnalyticsPageView::where('created_at', '>=', $today)->whereNotNull('item_id')->count(),
                'events_today' => AnalyticsEvent::where('created_at', '>=', $today)->count(),
                'seo_average' => (int) round(Item::whereStatus(1)->avg('seo_score') ?: 0),
                'seo_attention' => Item::whereStatus(1)->where('seo_score', '<', 70)->count(),
            ],
            'sales_chart' => $this->salesChart(),
            'earnings_chart' => $this->earningsChart(),
            'views_chart' => $this->viewsChart($monthStart),
            'devices_chart' => $this->simpleGroupChart('device_type', $monthStart, 'Nao identificado'),
            'referrers_chart' => $this->referrersChart($monthStart),
            'top_products_chart' => $this->topProductsChart($monthStart),
            'top_pages' => $this->topPages($monthStart),
            'seo_chart' => $this->seoBuckets(),
        ];
    }

    public function visitorUuid(Request $request): string
    {
        $uuid = (string) $request->cookies->get('rp_visitor_id');

        return preg_match('/^[a-zA-Z0-9-]{16,80}$/', $uuid) ? $uuid : (string) Str::uuid();
    }

    public function shouldTrackPage(Request $request, $response): bool
    {
        if (!$request->isMethod('GET') || $request->ajax() || $request->expectsJson()) {
            return false;
        }

        if (method_exists($response, 'getStatusCode') && $response->getStatusCode() >= 400) {
            return false;
        }

        $path = trim($request->path(), '/');
        if ($path === '') {
            return true;
        }

        if (preg_match('#^(admin|user|auth|cache|run|updater|analytics/event)#i', $path)) {
            return false;
        }

        if (preg_match('/\.(css|js|png|jpe?g|gif|svg|webp|ico|woff2?|ttf|map|xml|txt|json)$/i', $path)) {
            return false;
        }

        return true;
    }

    private function tablesReady(): bool
    {
        return Schema::hasTable('analytics_page_views') && Schema::hasTable('analytics_events');
    }

    private function salesChart(): array
    {
        $labels = [];
        $sales = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');
            $sales[] = Order::where('order_status', 'Delivered')->whereDate('created_at', $date->toDateString())->count();
        }

        return compact('labels', 'sales');
    }

    private function earningsChart(): array
    {
        $labels = [];
        $earnings = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $total = 0;
            $orders = Order::where('order_status', 'Delivered')->whereDate('created_at', $date->toDateString())->get();
            foreach ($orders as $order) {
                $total += (float) PriceHelper::OrderTotalChart($order);
            }
            $labels[] = $date->format('d/m');
            $earnings[] = round($total, 2);
        }

        return compact('labels', 'earnings');
    }

    private function viewsChart(Carbon $start): array
    {
        $raw = AnalyticsPageView::selectRaw('DATE(created_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_uuid) as visitors')
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $views = [];
        $visitors = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();
            $labels[] = $date->format('d/m');
            $views[] = (int) optional($raw->get($key))->views;
            $visitors[] = (int) optional($raw->get($key))->visitors;
        }

        return compact('labels', 'views', 'visitors');
    }

    private function simpleGroupChart(string $column, Carbon $start, string $fallback): array
    {
        $rows = AnalyticsPageView::selectRaw("COALESCE(NULLIF({$column}, ''), ?) as label, COUNT(*) as total", [$fallback])
            ->where('created_at', '>=', $start)
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->values(),
            'data' => $rows->pluck('total')->map(fn ($value) => (int) $value)->values(),
        ];
    }

    private function referrersChart(Carbon $start): array
    {
        $rows = AnalyticsPageView::selectRaw("COALESCE(NULLIF(referrer_host, ''), 'Direto') as label, COUNT(*) as total")
            ->where('created_at', '>=', $start)
            ->groupBy('label')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->values(),
            'data' => $rows->pluck('total')->map(fn ($value) => (int) $value)->values(),
        ];
    }

    private function topProductsChart(Carbon $start): array
    {
        $rows = AnalyticsPageView::query()
            ->selectRaw('item_id, COUNT(*) as views, COUNT(DISTINCT visitor_uuid) as visitors')
            ->where('created_at', '>=', $start)
            ->whereNotNull('item_id')
            ->groupBy('item_id')
            ->orderByDesc('views')
            ->limit(10)
            ->with('item')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->item_id,
                    'name' => $row->item->name ?: 'Produto removido',
                    'url' => $row->item && $row->item->slug ? route('front.product', $row->item->slug) : null,
                    'views' => (int) $row->views,
                    'visitors' => (int) $row->visitors,
                ];
            });

        return [
            'labels' => $rows->pluck('name')->map(fn ($name) => Str::limit($name, 26))->values(),
            'data' => $rows->pluck('views')->values(),
            'rows' => $rows->values(),
        ];
    }

    private function topPages(Carbon $start): array
    {
        return AnalyticsPageView::selectRaw('path, page_title, page_type, COUNT(*) as views, COUNT(DISTINCT visitor_uuid) as visitors')
            ->where('created_at', '>=', $start)
            ->groupBy('path', 'page_title', 'page_type')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'path' => $row->path,
                    'title' => $row->page_title ?: $row->path,
                    'type' => $row->page_type,
                    'views' => (int) $row->views,
                    'visitors' => (int) $row->visitors,
                ];
            })
            ->values()
            ->all();
    }

    private function seoBuckets(): array
    {
        $excellent = Item::whereStatus(1)->where('seo_score', '>=', 85)->count();
        $good = Item::whereStatus(1)->whereBetween('seo_score', [70, 84])->count();
        $attention = Item::whereStatus(1)->whereBetween('seo_score', [40, 69])->count();
        $critical = Item::whereStatus(1)->where('seo_score', '<', 40)->count();

        return [
            'labels' => ['Excelente', 'Bom', 'Ajustar', 'Critico'],
            'data' => [$excellent, $good, $attention, $critical],
        ];
    }

    private function emptyDashboardData(): array
    {
        $labels = [];
        for ($i = 29; $i >= 0; $i--) {
            $labels[] = now()->subDays($i)->format('d/m');
        }

        return [
            'updated_at' => now()->format('d/m/Y H:i:s'),
            'cards' => [
                'views_today' => 0,
                'visitors_today' => 0,
                'online_now' => 0,
                'product_views_today' => 0,
                'events_today' => 0,
                'seo_average' => 0,
                'seo_attention' => 0,
            ],
            'sales_chart' => $this->salesChart(),
            'earnings_chart' => $this->earningsChart(),
            'views_chart' => ['labels' => $labels, 'views' => array_fill(0, 30, 0), 'visitors' => array_fill(0, 30, 0)],
            'devices_chart' => ['labels' => [], 'data' => []],
            'referrers_chart' => ['labels' => [], 'data' => []],
            'top_products_chart' => ['labels' => [], 'data' => [], 'rows' => []],
            'top_pages' => [],
            'seo_chart' => ['labels' => ['Excelente', 'Bom', 'Ajustar', 'Critico'], 'data' => [0, 0, 0, 0]],
        ];
    }

    private function resolveItem(?string $routeName, string $slug): ?Item
    {
        if ($routeName !== 'front.product' || $slug === '') {
            return null;
        }

        return Item::whereSlug($slug)->select('id', 'name', 'slug')->first();
    }

    private function pageType(?string $routeName, string $path): string
    {
        if ($routeName === 'front.product') return 'produto';
        if ($routeName === 'front.catalog') return 'loja';
        if (Str::startsWith((string) $routeName, 'front.checkout')) return 'checkout';
        if ($routeName === 'front.cart') return 'carrinho';
        if (Str::contains((string) $routeName, 'blog')) return 'blog';
        if (Str::contains((string) $routeName, 'faq')) return 'faq';
        if ($routeName === 'front.index') return 'inicio';

        return $path ?: 'pagina';
    }

    private function pageTitle(?string $routeName, string $path, ?Item $item): string
    {
        if ($item) return $item->name;
        if ($routeName === 'front.index') return 'Inicio';
        if ($routeName === 'front.catalog') return 'Loja';
        if ($routeName === 'front.cart') return 'Carrinho';

        return Str::title(str_replace(['-', '/'], [' ', ' / '], $path ?: 'inicio'));
    }

    private function isBot(?string $userAgent): bool
    {
        return $userAgent && preg_match('/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|whatsapp|telegram|preview|monitoring/i', $userAgent);
    }

    private function deviceType(?string $userAgent): string
    {
        $userAgent = Str::lower((string) $userAgent);
        if (Str::contains($userAgent, ['ipad', 'tablet'])) return 'tablet';
        if (Str::contains($userAgent, ['mobile', 'android', 'iphone', 'ipod'])) return 'celular';

        return 'desktop';
    }

    private function browser(?string $userAgent): string
    {
        $userAgent = Str::lower((string) $userAgent);
        if (Str::contains($userAgent, 'edg/')) return 'Edge';
        if (Str::contains($userAgent, 'opr/')) return 'Opera';
        if (Str::contains($userAgent, 'firefox')) return 'Firefox';
        if (Str::contains($userAgent, 'chrome')) return 'Chrome';
        if (Str::contains($userAgent, 'safari')) return 'Safari';

        return 'Outro';
    }

    private function platform(?string $userAgent): string
    {
        $userAgent = Str::lower((string) $userAgent);
        if (Str::contains($userAgent, 'windows')) return 'Windows';
        if (Str::contains($userAgent, 'android')) return 'Android';
        if (Str::contains($userAgent, ['iphone', 'ipad', 'ios'])) return 'iOS';
        if (Str::contains($userAgent, ['macintosh', 'mac os'])) return 'macOS';
        if (Str::contains($userAgent, 'linux')) return 'Linux';

        return 'Outro';
    }
}
