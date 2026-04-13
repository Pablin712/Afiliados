<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - {{ __('messages.error') }}</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f8fb;
            --card: #ffffff;
            --text: #162338;
            --muted: #5d6c85;
            --accent: #0f6fff;
            --accent-soft: #e8f1ff;
            --border: #dce5f2;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(circle at top, #edf3ff 0%, var(--bg) 55%);
            color: var(--text);
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
        }

        .card {
            width: min(560px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 40px rgba(13, 31, 67, 0.08);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent-soft);
            color: var(--accent);
            border-radius: 999px;
            padding: 8px 14px;
            font-weight: 700;
            font-size: 13px;
        }

        h1 {
            margin: 16px 0 10px;
            font-size: 30px;
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
        }

        .actions {
            margin-top: 22px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--accent);
            color: #ffffff;
        }

        .btn-secondary {
            border-color: var(--border);
            color: var(--text);
            background: #ffffff;
        }

        .meta {
            margin-top: 14px;
            font-size: 13px;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="badge">HTTP {{ $statusCode ?? 500 }}</span>
        <h1>{{ __('messages.error') }}</h1>
        <p>{{ __('messages.error_generic') }}</p>

        <div class="actions">
            <a class="btn btn-primary" href="{{ url()->previous() }}">{{ __('messages.back') }}</a>
            <a class="btn btn-secondary" href="{{ route('dashboard') }}">{{ __('messages.nav.dashboard') }}</a>
        </div>

        <p class="meta">{{ config('app.name') }}</p>
    </main>
</body>
</html>
