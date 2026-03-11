<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('memberships.title') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px; }
        th { background: #eef2ff; text-transform: uppercase; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ __('memberships.title') }}</h1>
    <p>{{ __('memberships.messages.report_generated_at') }}: {{ $generatedAt->format('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('memberships.columns.id') }}</th>
                <th>{{ __('memberships.columns.user') }}</th>
                <th>{{ __('memberships.columns.membership_type') }}</th>
                <th>{{ __('memberships.columns.status') }}</th>
                <th>{{ __('memberships.columns.started_at') }}</th>
                <th>{{ __('memberships.columns.expires_at') }}</th>
                <th>{{ __('memberships.columns.created_at') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($records as $record)
            <tr>
                <td>{{ $record->id ?? '-' }}</td>
                <td>{{ $record->user_name ?? '-' }}</td>
                <td>{{ $record->membership_type_name ?? '-' }}</td>
                <td>{{ __('memberships.statuses.'.($record->status ?? 'pending_payment')) }}</td>
                <td>{{ optional($record->started_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                <td>{{ optional($record->expires_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
                <td>{{ optional($record->created_at)->format('Y-m-d H:i:s') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
