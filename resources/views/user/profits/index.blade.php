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

            {{-- Flash: payout success --}}
            @if (session('payout_success'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300">
                    {{ __('messages.user.profits.payout_requested') }}
                </div>
            @endif

            {{-- Flash: payout error --}}
            @if (session('payout_error') === 'api_error')
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300">
                    {{ __('messages.user.profits.payout_api_error') }}
                </div>
            @endif

            {{-- Stats + Cobrar button --}}
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

            {{-- Cobrar button --}}
            <div class="flex justify-end">
                @if ($pendingTotal <= 0)
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'payout-no-pending-modal' }))"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-500 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-400"
                    >
                        {{ __('messages.user.profits.request_payout_button') }}
                    </button>
                @elseif (! $hasBank)
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'payout-no-bank-modal' }))"
                        class="inline-flex items-center gap-2 rounded-md bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 focus:outline-none"
                    >
                        {{ __('messages.user.profits.request_payout_button') }}
                    </button>
                @else
                    <button
                        type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'payout-confirm-modal' }))"
                        class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none"
                    >
                        {{ __('messages.user.profits.request_payout_button') }}
                    </button>
                @endif
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
                    ['label' => __('messages.user.profits.reason'), 'type' => 'string', 'sort_by' => 'id'],
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

    {{-- Modal: sin ganancias pendientes --}}
    <x-modal name="payout-no-pending-modal" :show="false" maxWidth="sm">
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">
                {{ __('messages.user.profits.no_pending_title') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-graphite-300">
                {{ __('messages.user.profits.no_pending_text') }}
            </p>
            <div class="flex justify-end">
                <x-secondary-button x-on:click.prevent="$dispatch('close')">{{ __('messages.cancel') }}</x-secondary-button>
            </div>
        </div>
    </x-modal>

    {{-- Modal: sin banco asociado --}}
    <x-modal name="payout-no-bank-modal" :show="false" maxWidth="md">
        <div class="p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">
                {{ __('messages.user.profits.no_bank_title') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-graphite-300">
                {{ __('messages.user.profits.no_bank_text') }}
            </p>
            <div class="flex justify-end gap-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close')">{{ __('messages.cancel') }}</x-secondary-button>
                <a
                    href="{{ route('profile.edit') }}#bank-section"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700"
                >
                    {{ __('messages.user.profits.no_bank_add_button') }}
                </a>
            </div>
        </div>
    </x-modal>

    {{-- Modal: confirmar cobro --}}
    <x-modal name="payout-confirm-modal" :show="false" maxWidth="md">
        <form method="POST" action="{{ route('user.profits.request-payout') }}" class="p-6 space-y-4">
            @csrf
            <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">
                {{ __('messages.user.profits.confirm_payout_title') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-graphite-300">
                {{ __('messages.user.profits.confirm_payout_text') }}
                <span class="font-semibold text-emerald-600">${{ number_format($pendingTotal, 2) }}</span>.
            </p>
            <div class="flex justify-end gap-2">
                <x-secondary-button type="button" x-on:click.prevent="$dispatch('close')">{{ __('messages.cancel') }}</x-secondary-button>
                <x-primary-button type="submit">{{ __('messages.user.profits.confirm_payout_button') }}</x-primary-button>
            </div>
        </form>
    </x-modal>

</x-app-layout>
