<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->title }} - {{ __('Maintainance') }}</title>
    <link rel="shortcut icon" href="{{ url('/core/public/storage/images/'.$setting->favicon) }}" type="image/x-icon">
    <style>
        :root {
            --primary: {{ $setting->primary_color ?: '#177dff' }};
            --text: #1f2937;
            --muted: #6b7280;
            --bg: #f6f8fb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .maintenance-shell {
            width: min(920px, 100%);
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(31, 41, 55, .14);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .maintenance-media {
            background: color-mix(in srgb, var(--primary) 12%, #ffffff);
            min-height: 370px;
            overflow: hidden;
        }
        .maintenance-media img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        .maintenance-content {
            padding: 42px;
        }
        .maintenance-kicker {
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .08em;
            margin-bottom: 10px;
        }
        h1 {
            margin: 0 0 14px;
            font-size: 32px;
            line-height: 1.15;
        }
        .maintenance-text {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.75;
        }
        .maintenance-countdown {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 26px;
        }
        .maintenance-countdown div {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 8px;
            text-align: center;
        }
        .maintenance-countdown strong {
            display: block;
            color: var(--primary);
            font-size: 24px;
        }
        .maintenance-countdown span {
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
        }
        .maintenance-device {
            margin-top: 24px;
            border-top: 1px solid #e5e7eb;
            padding-top: 16px;
            color: var(--muted);
            font-size: 12px;
            word-break: break-all;
        }
        .maintenance-device code {
            display: block;
            margin-top: 6px;
            color: var(--text);
            background: #f3f4f6;
            border-radius: 6px;
            padding: 10px;
        }
        @media (max-width: 767px) {
            .maintenance-shell { grid-template-columns: 1fr; }
            .maintenance-media { min-height: 260px; }
            .maintenance-content { padding: 28px; }
            h1 { font-size: 26px; }
        }
    </style>
</head>
<body>
    <main class="maintenance-shell">
        <section class="maintenance-media">
            <img src="{{ $setting->maintainance_image ? url('/core/public/storage/images/'.$setting->maintainance_image) : url('/core/public/storage/images/'.$setting->logo) }}" alt="{{ $setting->title }}">
        </section>
        <section class="maintenance-content">
            <div class="maintenance-kicker">{{ __('Maintainance') }}</div>
            <h1>Estamos preparando melhorias.</h1>
            <div class="maintenance-text">{!! nl2br(e($setting->maintainance_text ?: 'Voltaremos em breve.')) !!}</div>
            @if ($setting->maintainance_release_at)
                <div class="maintenance-countdown" data-release="{{ \Carbon\Carbon::parse($setting->maintainance_release_at)->toIso8601String() }}">
                    <div><strong data-days>0</strong><span>Dias</span></div>
                    <div><strong data-hours>0</strong><span>Horas</span></div>
                    <div><strong data-minutes>0</strong><span>Min</span></div>
                    <div><strong data-seconds>0</strong><span>Seg</span></div>
                </div>
            @endif
            @if (request()->cookie('maintenance_device_token'))
                <div class="maintenance-device">
                    Código deste dispositivo para liberação:
                    <code>{{ request()->cookie('maintenance_device_token') }}</code>
                </div>
            @endif
        </section>
    </main>
    <script>
        (function () {
            var box = document.querySelector('.maintenance-countdown');
            if (!box) return;
            var release = new Date(box.dataset.release).getTime();
            function tick() {
                var diff = Math.max(0, release - Date.now());
                var days = Math.floor(diff / 86400000);
                var hours = Math.floor((diff % 86400000) / 3600000);
                var minutes = Math.floor((diff % 3600000) / 60000);
                var seconds = Math.floor((diff % 60000) / 1000);
                box.querySelector('[data-days]').textContent = days;
                box.querySelector('[data-hours]').textContent = hours;
                box.querySelector('[data-minutes]').textContent = minutes;
                box.querySelector('[data-seconds]').textContent = seconds;
                if (diff <= 0) window.location.reload();
            }
            tick();
            setInterval(tick, 1000);
        })();
    </script>
</body>
</html>
