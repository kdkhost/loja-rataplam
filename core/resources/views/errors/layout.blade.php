<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $setting->title ?? config('app.name') }} - @yield('code')</title>
    <link rel="icon" href="{{ isset($setting) ? url('/core/public/storage/images/'.$setting->favicon) : '' }}">
    <style>
        :root {
            --primary: {{ $setting->primary_color ?? '#177dff' }};
            --bg: #f6f8fb;
            --text: #1f2937;
            --muted: #6b7280;
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
        .error-card {
            width: min(720px, 100%);
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 24px 70px rgba(31, 41, 55, .14);
            padding: 42px;
            text-align: center;
        }
        .error-code {
            color: var(--primary);
            font-size: 72px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 12px;
        }
        h1 { margin: 0 0 12px; font-size: 28px; }
        p { margin: 0 auto 24px; color: var(--muted); max-width: 520px; line-height: 1.7; }
        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 18px;
            border-radius: 6px;
            background: var(--primary);
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="error-card">
        <div class="error-code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <a href="{{ route('front.index') }}">{{ __('Back Home') }}</a>
    </main>
</body>
</html>
