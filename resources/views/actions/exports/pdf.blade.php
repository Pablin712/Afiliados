<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('messages.pdf.audit_report') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { margin: 0 0 8px 0; font-size: 18px; }
        .meta { margin-bottom: 12px; color: #374151; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; vertical-align: top; }
        th { background: #eef2ff; font-size: 9px; text-transform: uppercase; }
        td { font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ __('messages.pdf.audit_report') }}</h1>
    <div class="meta">
        {{ __('messages.pdf.generated') }}: {{ $generatedAt->format('Y-m-d H:i:s') }}<br>
        {{ __('messages.pdf.total_records') }}: {{ $records->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('messages.audit.column_id') }}</th>
                <th>{{ __('messages.audit.column_user') }}</th>
                <th>{{ __('messages.audit.column_module') }}</th>
                <th>{{ __('messages.audit.column_action') }}</th>
                <th>{{ __('messages.audit.column_method') }}</th>
                <th>{{ __('messages.audit.column_route') }}</th>
                <th>{{ __('messages.audit.column_url') }}</th>
                <th>{{ __('messages.audit.column_ip') }}</th>
                <th>{{ __('messages.pdf.date') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($records as $action)
            <tr>
                <td>{{ $action->id }}</td>
                <td>{{ $action->user?->name ?? __('messages.audit.system_user') }}</td>
                <td>{{ $action->module }}</td>
                <td>{{ $action->action }}</td>
                <td>{{ $action->method }}</td>
                <td>{{ $action->route }}</td>
                <td>{{ $action->url }}</td>
                <td>{{ $action->ip_address }}</td>
                <td>{{ optional($action->created_at)->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
