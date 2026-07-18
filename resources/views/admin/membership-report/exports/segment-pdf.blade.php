<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('membership_report.segments.'.$segment) }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.meta { color: #6b7280; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background: #eef2ff; text-transform: uppercase; font-size: 9px; }
    </style>
</head>
<body>
    <h1>{{ __('membership_report.title') }} &middot; {{ __('membership_report.segments.'.$segment) }}</h1>
    <p class="meta">
        {{ __('membership_report.messages.report_generated_at') }}: {{ $generatedAt->format('Y-m-d H:i:s') }}
        &middot; {{ __('membership_report.columns.total') }}: {{ number_format($segmentData['total']) }}
    </p>

    @if (in_array($segment, ['free', 'non_renewed']))
        <table>
            <thead>
                <tr>
                    <th>{{ __('membership_report.columns.user') }}</th>
                    <th>{{ __('membership_report.columns.email') }}</th>
                    <th>{{ __('membership_report.columns.joined_at') }}</th>
                    @if ($segment === 'free')
                        <th>{{ __('membership_report.columns.previously_paid') }}</th>
                    @endif
                    <th>{{ __('membership_report.columns.previous_type') }}</th>
                    <th>{{ __('membership_report.columns.downgraded_at') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($segmentData['records'] as $row)
                <tr>
                    <td>{{ $row['user_name'] }}</td>
                    <td>{{ $row['user_email'] }}</td>
                    <td>{{ $row['joined_at'] }}</td>
                    @if ($segment === 'free')
                        <td>{{ $row['previously_paid'] ? __('membership_report.booleans.yes') : __('membership_report.booleans.no') }}</td>
                    @endif
                    <td>{{ ucfirst($row['previous_type'] ?? '-') }}</td>
                    <td>{{ $row['downgraded_at'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('membership_report.messages.empty') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    @else
        <table>
            <thead>
                <tr>
                    <th>{{ __('membership_report.columns.user') }}</th>
                    <th>{{ __('membership_report.columns.email') }}</th>
                    <th>{{ __('membership_report.columns.membership_type') }}</th>
                    <th>{{ __('membership_report.columns.joined_at') }}</th>
                    <th>{{ __('membership_report.columns.started_at') }}</th>
                    <th>{{ __('membership_report.columns.expires_at') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($segmentData['records'] as $row)
                <tr>
                    <td>{{ $row['user_name'] }}</td>
                    <td>{{ $row['user_email'] }}</td>
                    <td>{{ ucfirst($row['membership_type_name']) }}</td>
                    <td>{{ $row['joined_at'] }}</td>
                    <td>{{ $row['started_at'] ?? '-' }}</td>
                    <td>{{ $row['expires_at'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">{{ __('membership_report.messages.empty') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    @endif
</body>
</html>
