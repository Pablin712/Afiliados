<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                {{ __('messages.admin.financial_dashboard.title') }}
            </h2>
            <form method="GET" action="{{ route('admin.financial-dashboard.index') }}" class="flex flex-wrap gap-2 items-center">
                <input type="date" name="from" value="{{ $from }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                <input type="date" name="to" value="{{ $to }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                <x-secondary-button type="submit">{{ __('messages.admin.financial_dashboard.apply') }}</x-secondary-button>
            </form>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.financial_dashboard.cards.incomes') }}</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-600">${{ number_format($summary['totals']['incomes_total'] ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.financial_dashboard.cards.expenses') }}</p>
                    <p class="mt-1 text-xl font-semibold text-rose-600">${{ number_format($summary['totals']['expenses_total'] ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.financial_dashboard.cards.net') }}</p>
                    <p class="mt-1 text-xl font-semibold text-sky-600">${{ number_format($summary['totals']['net_profit_total'] ?? 0, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.financial_dashboard.cards.pending_profits') }}</p>
                    <p class="mt-1 text-xl font-semibold text-amber-600">${{ number_format($summary['totals']['pending_profits_total_now'] ?? 0, 2) }}</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.financial_dashboard.line_chart_title') }}</h3>
                    <div id="lineChart" class="mt-4 h-[320px]"></div>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.financial_dashboard.memberships_title') }}</h3>
                    <div class="mt-3 space-y-2">
                        @foreach (($summary['membership_totals'] ?? []) as $item)
                            <div class="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-graphite-800">
                                <span class="capitalize">{{ $item['name'] }}</span>
                                <span class="font-semibold">{{ $item['total'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.financial_dashboard.candle_chart_title') }}</h3>
                <div id="candlesChart" class="mt-4 h-[300px]"></div>
            </div>

            <div class="flex flex-wrap gap-2">
                <form method="POST" action="{{ route('admin.financial-dashboard.register-today') }}">
                    @csrf
                    <x-primary-button>{{ __('messages.admin.financial_dashboard.register_today') }}</x-primary-button>
                </form>
                <form method="POST" action="{{ route('admin.financial-dashboard.register-yesterday') }}">
                    @csrf
                    <x-secondary-button>{{ __('messages.admin.financial_dashboard.register_yesterday') }}</x-secondary-button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            (() => {
                const lineSeries = @json($summary['line_series'] ?? []);
                const candleSeries = @json($summary['candles'] ?? []);

                const lineChart = new ApexCharts(document.querySelector('#lineChart'), {
                    chart: { type: 'line', height: 320, toolbar: { show: true } },
                    stroke: { width: [3, 3, 2], curve: 'smooth' },
                    series: [
                        { name: '{{ __('messages.admin.financial_dashboard.cards.incomes') }}', data: lineSeries.map((row) => row.incomes) },
                        { name: '{{ __('messages.admin.financial_dashboard.cards.expenses') }}', data: lineSeries.map((row) => row.expenses) },
                        { name: '{{ __('messages.admin.financial_dashboard.cards.net') }}', data: lineSeries.map((row) => row.net_profit) },
                    ],
                    xaxis: { categories: lineSeries.map((row) => row.date) },
                    colors: ['#059669', '#dc2626', '#0284c7'],
                });

                lineChart.render();

                const candlesChart = new ApexCharts(document.querySelector('#candlesChart'), {
                    chart: { type: 'candlestick', height: 300, toolbar: { show: true } },
                    series: [{
                        data: candleSeries.map((row) => ({
                            x: row.date,
                            y: [Number(row.open), Number(row.high), Number(row.low), Number(row.close)],
                        })),
                    }],
                });

                candlesChart.render();
            })();
        </script>
    @endpush
</x-app-layout>
