<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.admin.profits.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.profits.pending_total') }}</p>
                    <p class="mt-1 text-xl font-semibold text-amber-600">${{ number_format($pendingTotal, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.profits.paid_total') }}</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-600">${{ number_format($paidTotal, 2) }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                <form method="GET" action="{{ route('admin.profits.index') }}" class="grid gap-2 md:grid-cols-5">
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('messages.table.search_placeholder') }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    <select name="state" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        <option value="">{{ __('messages.admin.profits.all_states') }}</option>
                        <option value="pending" @selected($filters['state'] === 'pending')>{{ __('messages.status.pending') }}</option>
                        <option value="made" @selected($filters['state'] === 'made')>{{ __('messages.admin.profits.status_made') }}</option>
                    </select>
                    <input type="date" name="from" value="{{ $filters['from'] }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    <input type="date" name="to" value="{{ $filters['to'] }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    <div class="flex gap-2">
                        <x-secondary-button type="submit">{{ __('messages.admin.profits.apply_filters') }}</x-secondary-button>
                        <a href="{{ route('admin.profits.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 dark:border-graphite-700 dark:text-graphite-200">
                            {{ __('messages.admin.profits.clear_filters') }}
                        </a>
                    </div>
                </form>
            </div>

            <x-enhanced-table
                id="profits-table"
                :headers="[
                    ['label' => 'ID', 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('messages.admin.user_label'), 'type' => 'string', 'sort_by' => 'user_name'],
                    ['label' => __('messages.admin.profits.bank'), 'type' => 'string', 'sort_by' => 'bank_name'],
                    ['label' => __('messages.admin.profits.amount'), 'type' => 'number', 'sort_by' => 'amount'],
                    ['label' => __('messages.admin.profits.state'), 'type' => 'string', 'sort_by' => 'state'],
                    ['label' => __('messages.admin.profits.created_at'), 'type' => 'string', 'sort_by' => 'created_at'],
                    ['label' => __('messages.actions'), 'type' => 'actions', 'sort_by' => 'id'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('admin.profits.index')"
                :extraParams="[
                    'state' => $filters['state'] ?? '',
                    'from' => $filters['from'] ?? '',
                    'to' => $filters['to'] ?? '',
                ]"
                :csv="false"
                :excel="false"
                :json="false"
                :pdf="false"
                :print="false"
                :table_void="$records->isEmpty()"
            >
                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('admin.profits.partials.table-rows', ['records' => $records->items()])
                </tbody>
            </x-enhanced-table>
        </div>
    </div>

    @include('admin.profits.partials.modals.mark-paid')

    @push('scripts')
        <script>
            (() => {
                const pattern = @json(route('admin.profits.mark-as-paid', ['profit' => '__ID__']));
                const text = document.getElementById('profit-mark-paid-text');
                const form = document.getElementById('profit-mark-paid-form');

                window.openProfitPaidModal = (payload) => {
                    form.action = pattern.replace('__ID__', String(payload.id));
                    text.textContent = `{{ __('messages.admin.profits.modal_text') }} ${payload.user} ($${Number(payload.amount).toFixed(2)})`;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'profit-mark-paid-modal' }));
                };
            })();
        </script>
    @endpush
</x-app-layout>
