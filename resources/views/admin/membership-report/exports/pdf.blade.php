<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('membership_report.title') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 12px; margin: 18px 0 6px; text-transform: uppercase; color: #374151; }
        p.meta { color: #6b7280; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { border: 1px solid #d1d5db; padding: 4px 6px; text-align: left; }
        th { background: #eef2ff; text-transform: uppercase; font-size: 9px; }
        .cards { width: 100%; margin: 10px 0; }
        .cards td { border: 1px solid #d1d5db; padding: 8px; width: 16.66%; text-align: center; }
        .cards .label { font-size: 8px; text-transform: uppercase; color: #6b7280; }
        .cards .value { font-size: 14px; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ __('membership_report.title') }}</h1>
    <p class="meta">
        {{ __('membership_report.messages.report_generated_at') }}: {{ $generatedAt->format('Y-m-d H:i:s') }}
        &middot; {{ $report['range']['from'] }} &rarr; {{ $report['range']['to'] }}
    </p>

    <table class="cards">
        <tr>
            <td>
                <div class="label">{{ __('membership_report.cards.total_users') }}</div>
                <div class="value">{{ number_format($report['totals']['total_users']) }}</div>
            </td>
            <td>
                <div class="label">{{ __('membership_report.cards.new_users') }}</div>
                <div class="value">{{ number_format($report['totals']['new_users']) }}</div>
            </td>
            <td>
                <div class="label">{{ __('membership_report.cards.paying_users') }}</div>
                <div class="value">{{ number_format($report['totals']['paying_users']) }}</div>
            </td>
            <td>
                <div class="label">{{ __('membership_report.cards.free_users') }}</div>
                <div class="value">{{ number_format($report['totals']['free_users']) }}</div>
            </td>
            <td>
                <div class="label">{{ __('membership_report.cards.upgrades') }}</div>
                <div class="value">{{ number_format($report['totals']['upgrades_count']) }}</div>
            </td>
            <td>
                <div class="label">{{ __('membership_report.cards.non_renewed') }}</div>
                <div class="value">{{ number_format($report['totals']['non_renewed_count']) }}</div>
            </td>
        </tr>
    </table>

    <h2>{{ __('membership_report.sections.type_breakdown') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('membership_report.columns.type') }}</th>
                <th class="text-right">{{ __('membership_report.columns.total') }}</th>
                <th class="text-right">{{ __('membership_report.columns.percent') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($report['type_breakdown'] as $row)
            <tr>
                <td>{{ ucfirst($row['name']) }}</td>
                <td class="text-right">{{ number_format($row['total']) }}</td>
                <td class="text-right">{{ number_format($row['percent'], 1) }}%</td>
            </tr>
        @empty
            <tr><td colspan="3">{{ __('membership_report.messages.empty') }}</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>{{ __('membership_report.sections.status_breakdown') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('membership_report.columns.status') }}</th>
                <th class="text-right">{{ __('membership_report.columns.total') }}</th>
                <th class="text-right">{{ __('membership_report.columns.percent') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($report['status_breakdown'] as $row)
            <tr>
                <td>{{ __('membership_report.statuses.'.$row['status']) }}</td>
                <td class="text-right">{{ number_format($row['total']) }}</td>
                <td class="text-right">{{ number_format($row['percent'], 1) }}%</td>
            </tr>
        @empty
            <tr><td colspan="3">{{ __('membership_report.messages.empty') }}</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>{{ __('membership_report.sections.upgrades') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('membership_report.columns.user') }}</th>
                <th>{{ __('membership_report.columns.email') }}</th>
                <th>{{ __('membership_report.columns.membership_type') }}</th>
                <th>{{ __('membership_report.columns.started_at') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($report['upgrades']['records'] as $row)
            <tr>
                <td>{{ $row['user_name'] }}</td>
                <td>{{ $row['user_email'] }}</td>
                <td>{{ ucfirst($row['membership_type_name']) }}</td>
                <td>{{ $row['started_at'] }}</td>
            </tr>
        @empty
            <tr><td colspan="4">{{ __('membership_report.messages.empty') }}</td></tr>
        @endforelse
        </tbody>
    </table>

    <h2>{{ __('membership_report.sections.non_renewed') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('membership_report.columns.user') }}</th>
                <th>{{ __('membership_report.columns.email') }}</th>
                <th>{{ __('membership_report.columns.previous_type') }}</th>
                <th>{{ __('membership_report.columns.downgraded_at') }}</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($report['non_renewed']['records'] as $row)
            <tr>
                <td>{{ $row['user_name'] }}</td>
                <td>{{ $row['user_email'] }}</td>
                <td>{{ ucfirst($row['previous_type']) }}</td>
                <td>{{ $row['downgraded_at'] }}</td>
            </tr>
        @empty
            <tr><td colspan="4">{{ __('membership_report.messages.empty') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
