<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                {{ __('membership_report.title') }}
            </h2>
            <form method="GET" action="{{ route('admin.membership-report.index') }}" class="flex flex-wrap gap-2 items-center">
                <select name="segment" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @foreach (\App\Services\MembershipReportService::SEGMENTS as $segmentOption)
                        <option value="{{ $segmentOption }}" @selected($segment === $segmentOption)>
                            {{ __('membership_report.segments.'.$segmentOption) }}
                        </option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ $from }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                <input type="date" name="to" value="{{ $to }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                <x-secondary-button type="submit">{{ __('membership_report.apply') }}</x-secondary-button>
                @if ($segment !== 'all')
                    <a href="{{ route('admin.membership-report.index', ['from' => $from, 'to' => $to, 'segment' => $segment, 'export' => 'csv']) }}"
                       class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition">
                        {{ __('membership_report.export_csv') }}
                    </a>
                    <a href="{{ route('admin.membership-report.index', ['from' => $from, 'to' => $to, 'segment' => $segment, 'export' => 'excel']) }}"
                       class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition">
                        {{ __('membership_report.export_excel') }}
                    </a>
                    <a href="{{ route('admin.membership-report.index', ['from' => $from, 'to' => $to, 'segment' => $segment, 'export' => 'json']) }}"
                       class="inline-flex items-center px-3 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition">
                        {{ __('membership_report.export_json') }}
                    </a>
                @endif
                <a href="{{ route('admin.membership-report.index', ['from' => $from, 'to' => $to, 'segment' => $segment, 'export' => 'pdf']) }}"
                   class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition">
                    {{ __('membership_report.export_pdf') }}
                </a>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <p class="text-sm text-gray-500 dark:text-graphite-400">{{ __('membership_report.subtitle') }}</p>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.cards.total_users') }}</p>
                    <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-graphite-100">{{ number_format($report['totals']['total_users']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.cards.new_users') }}</p>
                    <p class="mt-1 text-xl font-semibold text-sky-600">{{ number_format($report['totals']['new_users']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.cards.paying_users') }}</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-600">{{ number_format($report['totals']['paying_users']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.cards.free_users') }}</p>
                    <p class="mt-1 text-xl font-semibold text-gray-600 dark:text-graphite-300">{{ number_format($report['totals']['free_users']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.cards.upgrades') }}</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-600">{{ number_format($report['totals']['upgrades_count']) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.cards.non_renewed') }}</p>
                    <p class="mt-1 text-xl font-semibold text-rose-600">{{ number_format($report['totals']['non_renewed_count']) }}</p>
                </div>
            </div>

            @if ($segment === 'all')
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('membership_report.sections.type_breakdown') }}</h3>
                    <div id="typeChart" class="mt-4" style="min-height: {{ max(220, count($report['type_breakdown']) * 40) }}px"></div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('membership_report.sections.status_breakdown') }}</h3>
                    <table class="mt-3 w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase text-gray-500 dark:text-graphite-400">
                                <th class="pb-2">{{ __('membership_report.columns.status') }}</th>
                                <th class="pb-2 text-right">{{ __('membership_report.columns.total') }}</th>
                                <th class="pb-2 text-right">{{ __('membership_report.columns.percent') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['status_breakdown'] as $row)
                                <tr class="border-t border-gray-100 dark:border-graphite-800">
                                    <td class="py-2">{{ __('membership_report.statuses.'.$row['status']) }}</td>
                                    <td class="py-2 text-right font-medium">{{ number_format($row['total']) }}</td>
                                    <td class="py-2 text-right text-gray-500 dark:text-graphite-400">{{ number_format($row['percent'], 1) }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-4 text-center text-gray-500 dark:text-graphite-400">{{ __('membership_report.messages.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('membership_report.sections.upgrades') }}</h3>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase text-gray-500 dark:text-graphite-400">
                                <th class="pb-2">{{ __('membership_report.columns.user') }}</th>
                                <th class="pb-2">{{ __('membership_report.columns.email') }}</th>
                                <th class="pb-2">{{ __('membership_report.columns.membership_type') }}</th>
                                <th class="pb-2">{{ __('membership_report.columns.started_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['upgrades']['records'] as $row)
                                <tr class="border-t border-gray-100 dark:border-graphite-800">
                                    <td class="py-2">{{ $row['user_name'] }}</td>
                                    <td class="py-2 text-gray-500 dark:text-graphite-400">{{ $row['user_email'] }}</td>
                                    <td class="py-2 capitalize">{{ $row['membership_type_name'] }}</td>
                                    <td class="py-2">{{ $row['started_at'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-500 dark:text-graphite-400">{{ __('membership_report.messages.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('membership_report.sections.non_renewed') }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.messages.non_renewed_hint') }}</p>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase text-gray-500 dark:text-graphite-400">
                                <th class="pb-2">{{ __('membership_report.columns.user') }}</th>
                                <th class="pb-2">{{ __('membership_report.columns.email') }}</th>
                                <th class="pb-2">{{ __('membership_report.columns.previous_type') }}</th>
                                <th class="pb-2">{{ __('membership_report.columns.downgraded_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['non_renewed']['records'] as $row)
                                <tr class="border-t border-gray-100 dark:border-graphite-800">
                                    <td class="py-2">{{ $row['user_name'] }}</td>
                                    <td class="py-2 text-gray-500 dark:text-graphite-400">{{ $row['user_email'] }}</td>
                                    <td class="py-2 capitalize">{{ $row['previous_type'] }}</td>
                                    <td class="py-2">{{ $row['downgraded_at'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="py-4 text-center text-gray-500 dark:text-graphite-400">{{ __('membership_report.messages.empty') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('membership_report.segments.'.$segment) }}</h3>
                    <span class="text-xs text-gray-500 dark:text-graphite-400">{{ number_format($segmentData['total']) }}</span>
                </div>
                @if ($segment === 'non_renewed')
                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('membership_report.messages.non_renewed_segment_hint') }}</p>
                @endif
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm">
                        @if (in_array($segment, ['free', 'non_renewed']))
                            <thead>
                                <tr class="text-left text-xs uppercase text-gray-500 dark:text-graphite-400">
                                    <th class="pb-2">{{ __('membership_report.columns.user') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.email') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.joined_at') }}</th>
                                    @if ($segment === 'free')
                                        <th class="pb-2">{{ __('membership_report.columns.previously_paid') }}</th>
                                    @endif
                                    <th class="pb-2">{{ __('membership_report.columns.previous_type') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.downgraded_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($segmentData['records'] as $row)
                                    <tr class="border-t border-gray-100 dark:border-graphite-800">
                                        <td class="py-2">{{ $row['user_name'] }}</td>
                                        <td class="py-2 text-gray-500 dark:text-graphite-400">{{ $row['user_email'] }}</td>
                                        <td class="py-2">{{ $row['joined_at'] }}</td>
                                        @if ($segment === 'free')
                                            <td class="py-2">{{ $row['previously_paid'] ? __('membership_report.booleans.yes') : __('membership_report.booleans.no') }}</td>
                                        @endif
                                        <td class="py-2 capitalize">{{ $row['previous_type'] ?? '—' }}</td>
                                        <td class="py-2">{{ $row['downgraded_at'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-4 text-center text-gray-500 dark:text-graphite-400">{{ __('membership_report.messages.empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        @else
                            <thead>
                                <tr class="text-left text-xs uppercase text-gray-500 dark:text-graphite-400">
                                    <th class="pb-2">{{ __('membership_report.columns.user') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.email') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.membership_type') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.joined_at') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.started_at') }}</th>
                                    <th class="pb-2">{{ __('membership_report.columns.expires_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($segmentData['records'] as $row)
                                    <tr class="border-t border-gray-100 dark:border-graphite-800">
                                        <td class="py-2">{{ $row['user_name'] }}</td>
                                        <td class="py-2 text-gray-500 dark:text-graphite-400">{{ $row['user_email'] }}</td>
                                        <td class="py-2 capitalize">{{ $row['membership_type_name'] }}</td>
                                        <td class="py-2">{{ $row['joined_at'] }}</td>
                                        <td class="py-2">{{ $row['started_at'] ?? '—' }}</td>
                                        <td class="py-2">{{ $row['expires_at'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="py-4 text-center text-gray-500 dark:text-graphite-400">{{ __('membership_report.messages.empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        @endif
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if ($segment === 'all')
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
                (() => {
                    const typeBreakdown = @json($report['type_breakdown']);

                    const typeChart = new ApexCharts(document.querySelector('#typeChart'), {
                        chart: { type: 'bar', height: {{ max(220, count($report['type_breakdown']) * 40) }}, toolbar: { show: false } },
                        plotOptions: { bar: { horizontal: true, borderRadius: 4, distributed: false } },
                        dataLabels: { enabled: true },
                        series: [{ name: '{{ __('membership_report.columns.total') }}', data: typeBreakdown.map((row) => row.total) }],
                        xaxis: { categories: typeBreakdown.map((row) => row.name.charAt(0).toUpperCase() + row.name.slice(1)) },
                        colors: ['#2a78d6'],
                        grid: { borderColor: '#e1e0d9' },
                    });

                    typeChart.render();
                })();
            </script>
        @endpush
    @endif
</x-app-layout>
