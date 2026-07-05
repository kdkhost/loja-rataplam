@extends('master.back')

@section('styles')
<style>
    .analytics-status {
        color: #6f7a8a;
        font-size: 13px;
    }
    .analytics-table td,
    .analytics-table th {
        vertical-align: middle;
        white-space: nowrap;
    }
    .analytics-table td:first-child {
        min-width: 220px;
        white-space: normal;
    }
</style>
@endsection

@section('content')
@php
    $analyticsDashboard = $analyticsDashboard ?? [];
    $analyticsCards = $analyticsDashboard['cards'] ?? [];
@endphp

<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-body d-sm-flex align-items-center justify-content-between">
            <div>
                <h3 class="mb-1 bc-title"><b>{{ __('Analitico publico') }}</b></h3>
                <span class="analytics-status">{{ __('Ultima atualizacao') }}: <span id="dashboard-updated-at">{{ $analyticsDashboard['updated_at'] ?? now()->format('d/m/Y H:i:s') }}</span></span>
            </div>
            <span class="badge badge-primary mt-3 mt-sm-0">{{ __('Atualizacao automatica a cada 20 segundos') }}</span>
        </div>
    </div>

    @include('alerts.alerts')

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-eye"></i></div></div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Visualizacoes hoje') }}</b></p>
                                <h4 class="card-title" id="analytics-views-today">{{ number_format($analyticsCards['views_today'] ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-users"></i></div></div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Visitantes hoje') }}</b></p>
                                <h4 class="card-title" id="analytics-visitors-today">{{ number_format($analyticsCards['visitors_today'] ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-signal"></i></div></div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Online agora') }}</b></p>
                                <h4 class="card-title" id="analytics-online-now">{{ number_format($analyticsCards['online_now'] ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-secondary bubble-shadow-small"><i class="fas fa-box-open"></i></div></div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Produtos vistos hoje') }}</b></p>
                                <h4 class="card-title" id="analytics-product-views-today">{{ number_format($analyticsCards['product_views_today'] ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-mouse-pointer"></i></div></div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Eventos hoje') }}</b></p>
                                <h4 class="card-title" id="analytics-events-today">{{ number_format($analyticsCards['events_today'] ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-search"></i></div></div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Media SEO') }}</b></p>
                                <h4 class="card-title"><span id="analytics-seo-average">{{ number_format($analyticsCards['seo_average'] ?? 0, 0, ',', '.') }}</span>/100</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-exclamation-triangle"></i></div></div>
                        <div class="col col-stats ml-3 ml-sm-0">
                            <div class="numbers">
                                <p class="mb-0"><b>{{ __('Produtos com SEO baixo') }}</b></p>
                                <h4 class="card-title" id="analytics-seo-attention">{{ number_format($analyticsCards['seo_attention'] ?? 0, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Trafego dos ultimos 30 dias') }}</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="analyticsViewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('SEO dos produtos') }}</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="analyticsSeoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Produtos mais vistos') }}</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="analyticsTopProductsChart"></canvas>
                    </div>
                    <div class="gd-responsive-table mt-3">
                        <table class="table table-bordered analytics-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Produto') }}</th>
                                    <th>{{ __('Views') }}</th>
                                    <th>{{ __('Visitantes') }}</th>
                                </tr>
                            </thead>
                            <tbody id="analytics-top-products-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Dispositivos') }}</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="analyticsDevicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Origens de acesso') }}</div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="analyticsReferrersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{ __('Paginas mais acessadas') }}</div>
                </div>
                <div class="card-body">
                    <div class="gd-responsive-table">
                        <table class="table table-bordered analytics-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Pagina') }}</th>
                                    <th>{{ __('Tipo') }}</th>
                                    <th>{{ __('Views') }}</th>
                                    <th>{{ __('Visitantes') }}</th>
                                </tr>
                            </thead>
                            <tbody id="analytics-top-pages-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var analyticsDashboard = @json($analyticsDashboard ?? []);
    var analyticsCharts = {};
    var analyticsColors = ['#1d7af3', '#31ce36', '#ffad46', '#f3545d', '#6861ce', '#48abf7', '#1572e8', '#8d9498'];

    function analyticsNumber(value) {
        return new Intl.NumberFormat('pt-BR').format(Number(value || 0));
    }

    function analyticsEscape(value) {
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function analyticsLineOptions(showLegend) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            legend: { display: !!showLegend },
            tooltips: {
                bodySpacing: 4,
                mode: 'nearest',
                intersect: 0,
                position: 'nearest',
                xPadding: 10,
                yPadding: 10,
                caretPadding: 10
            },
            layout: { padding: { left: 15, right: 15, top: 15, bottom: 15 } },
            scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] }
        };
    }

    function analyticsDoughnutOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            legend: { position: 'bottom' },
            tooltips: { bodySpacing: 4, xPadding: 10, yPadding: 10 }
        };
    }

    function analyticsCanvas(id) {
        var canvas = document.getElementById(id);
        return canvas ? canvas.getContext('2d') : null;
    }

    function setChartData(chart, labels, datasets) {
        if (!chart) return;
        chart.data.labels = labels || [];
        chart.data.datasets = datasets || [];
        chart.update();
    }

    function createAnalyticsCharts(data) {
        var views = data.views_chart || { labels: [], views: [], visitors: [] };
        var topProducts = data.top_products_chart || { labels: [], data: [] };
        var devices = data.devices_chart || { labels: [], data: [] };
        var referrers = data.referrers_chart || { labels: [], data: [] };
        var seo = data.seo_chart || { labels: [], data: [] };

        analyticsCharts.views = new Chart(analyticsCanvas('analyticsViewsChart'), {
            type: 'line',
            data: {
                labels: views.labels || [],
                datasets: [
                    {
                        label: 'Visualizacoes',
                        borderColor: '#1d7af3',
                        backgroundColor: 'rgba(29,122,243,.08)',
                        pointBackgroundColor: '#1d7af3',
                        fill: true,
                        borderWidth: 2,
                        data: views.views || []
                    },
                    {
                        label: 'Visitantes',
                        borderColor: '#31ce36',
                        backgroundColor: 'rgba(49,206,54,.08)',
                        pointBackgroundColor: '#31ce36',
                        fill: true,
                        borderWidth: 2,
                        data: views.visitors || []
                    }
                ]
            },
            options: analyticsLineOptions(true)
        });

        analyticsCharts.products = new Chart(analyticsCanvas('analyticsTopProductsChart'), {
            type: 'bar',
            data: {
                labels: topProducts.labels || [],
                datasets: [{ label: 'Visualizacoes', backgroundColor: '#1d7af3', borderColor: '#1d7af3', data: topProducts.data || [] }]
            },
            options: analyticsLineOptions(false)
        });

        analyticsCharts.devices = new Chart(analyticsCanvas('analyticsDevicesChart'), {
            type: 'doughnut',
            data: { labels: devices.labels || [], datasets: [{ data: devices.data || [], backgroundColor: analyticsColors }] },
            options: analyticsDoughnutOptions()
        });

        analyticsCharts.referrers = new Chart(analyticsCanvas('analyticsReferrersChart'), {
            type: 'doughnut',
            data: { labels: referrers.labels || [], datasets: [{ data: referrers.data || [], backgroundColor: analyticsColors }] },
            options: analyticsDoughnutOptions()
        });

        analyticsCharts.seo = new Chart(analyticsCanvas('analyticsSeoChart'), {
            type: 'doughnut',
            data: { labels: seo.labels || [], datasets: [{ data: seo.data || [], backgroundColor: ['#31ce36', '#1d7af3', '#ffad46', '#f3545d'] }] },
            options: analyticsDoughnutOptions()
        });
    }

    function renderAnalyticsTables(data) {
        var productsBody = document.getElementById('analytics-top-products-table');
        var pagesBody = document.getElementById('analytics-top-pages-table');
        var products = (data.top_products_chart && data.top_products_chart.rows) ? data.top_products_chart.rows : [];
        var pages = data.top_pages || [];

        productsBody.innerHTML = products.length ? products.map(function (row) {
            return '<tr><td>' + analyticsEscape(row.name) + '</td><td>' + analyticsNumber(row.views) + '</td><td>' + analyticsNumber(row.visitors) + '</td></tr>';
        }).join('') : '<tr><td colspan="3" class="text-center">{{ __('Nenhum dado encontrado') }}</td></tr>';

        pagesBody.innerHTML = pages.length ? pages.map(function (row) {
            return '<tr><td><strong>' + analyticsEscape(row.title) + '</strong><br><small>' + analyticsEscape(row.path) + '</small></td><td>' + analyticsEscape(row.type) + '</td><td>' + analyticsNumber(row.views) + '</td><td>' + analyticsNumber(row.visitors) + '</td></tr>';
        }).join('') : '<tr><td colspan="4" class="text-center">{{ __('Nenhum dado encontrado') }}</td></tr>';
    }

    function updateAnalyticsCards(data) {
        var cards = data.cards || {};
        var mapping = {
            'analytics-views-today': cards.views_today,
            'analytics-visitors-today': cards.visitors_today,
            'analytics-online-now': cards.online_now,
            'analytics-product-views-today': cards.product_views_today,
            'analytics-events-today': cards.events_today,
            'analytics-seo-average': cards.seo_average,
            'analytics-seo-attention': cards.seo_attention
        };

        Object.keys(mapping).forEach(function (id) {
            var element = document.getElementById(id);
            if (element) element.textContent = analyticsNumber(mapping[id]);
        });

        var updatedAt = document.getElementById('dashboard-updated-at');
        if (updatedAt && data.updated_at) updatedAt.textContent = data.updated_at;
    }

    function updateAnalyticsCharts(data) {
        var views = data.views_chart || { labels: [], views: [], visitors: [] };
        var topProducts = data.top_products_chart || { labels: [], data: [] };
        var devices = data.devices_chart || { labels: [], data: [] };
        var referrers = data.referrers_chart || { labels: [], data: [] };
        var seo = data.seo_chart || { labels: [], data: [] };

        setChartData(analyticsCharts.views, views.labels, [
            { label: 'Visualizacoes', borderColor: '#1d7af3', backgroundColor: 'rgba(29,122,243,.08)', pointBackgroundColor: '#1d7af3', fill: true, borderWidth: 2, data: views.views || [] },
            { label: 'Visitantes', borderColor: '#31ce36', backgroundColor: 'rgba(49,206,54,.08)', pointBackgroundColor: '#31ce36', fill: true, borderWidth: 2, data: views.visitors || [] }
        ]);
        setChartData(analyticsCharts.products, topProducts.labels, [{ label: 'Visualizacoes', backgroundColor: '#1d7af3', borderColor: '#1d7af3', data: topProducts.data || [] }]);
        setChartData(analyticsCharts.devices, devices.labels, [{ data: devices.data || [], backgroundColor: analyticsColors }]);
        setChartData(analyticsCharts.referrers, referrers.labels, [{ data: referrers.data || [], backgroundColor: analyticsColors }]);
        setChartData(analyticsCharts.seo, seo.labels, [{ data: seo.data || [], backgroundColor: ['#31ce36', '#1d7af3', '#ffad46', '#f3545d'] }]);
        renderAnalyticsTables(data);
    }

    function refreshAnalytics() {
        fetch('{{ route('back.analytics.realtime') }}', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                updateAnalyticsCards(data);
                updateAnalyticsCharts(data);
            })
            .catch(function () {});
    }

    createAnalyticsCharts(analyticsDashboard);
    updateAnalyticsCards(analyticsDashboard);
    renderAnalyticsTables(analyticsDashboard);
    setInterval(refreshAnalytics, 20000);
</script>
@endsection
