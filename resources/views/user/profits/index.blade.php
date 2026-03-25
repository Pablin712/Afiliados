<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                {{ __('messages.user.profits.title') }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-graphite-400">
                {{ __('messages.user.profits.description') }}
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-4 sm:px-6 lg:px-8">
            <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.profits.pending_total') }}</p>
                    <p class="mt-1 text-xl font-semibold text-amber-600">${{ number_format($pendingTotal, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.profits.paid_total') }}</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-600">${{ number_format($paidTotal, 2) }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.profits.month_total') }}</p>
                    <p class="mt-1 text-xl font-semibold text-sky-600">${{ number_format($monthTotal, 2) }}</p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                <form method="GET" action="{{ route('user.profits.index') }}" class="grid gap-2 md:grid-cols-4">
                    <select name="state" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        <option value="">{{ __('messages.user.profits.all_states') }}</option>
                        <option value="pending" @selected($filters['state'] === 'pending')>{{ __('messages.status.pending') }}</option>
                        <option value="made" @selected($filters['state'] === 'made')>{{ __('messages.admin.profits.status_made') }}</option>
                    </select>
                    <input type="date" name="from" value="{{ $filters['from'] }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    <input type="date" name="to" value="{{ $filters['to'] }}" class="rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    <div class="flex gap-2">
                        <x-secondary-button type="submit">{{ __('messages.user.profits.apply_filters') }}</x-secondary-button>
                        <a href="{{ route('user.profits.index') }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 dark:border-graphite-700 dark:text-graphite-200">
                            {{ __('messages.user.profits.clear_filters') }}
                        </a>
                    </div>
                </form>
            </div>

            <x-enhanced-table
                id="user-profits-table"
                :headers="[
                    ['label' => 'ID', 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('messages.user.profits.source_user'), 'type' => 'string', 'sort_by' => 'source_user_name'],
                    ['label' => __('messages.user.profits.source_payment'), 'type' => 'number', 'sort_by' => 'source_payment_id'],
                    ['label' => __('messages.user.profits.source_level'), 'type' => 'number', 'sort_by' => 'source_level'],
                    ['label' => __('messages.user.profits.amount'), 'type' => 'number', 'sort_by' => 'amount'],
                    ['label' => __('messages.user.profits.state'), 'type' => 'string', 'sort_by' => 'state'],
                    ['label' => __('messages.user.profits.created_at'), 'type' => 'string', 'sort_by' => 'created_at'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('user.profits.index')"
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
                    @include('user.profits.partials.table-rows', ['records' => $records->items()])
                </tbody>
            </x-enhanced-table>
        </div>
    </div>
</x-app-layout>
