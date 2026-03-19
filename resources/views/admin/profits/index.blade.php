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

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-graphite-800 dark:bg-graphite-900">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-graphite-800 text-left text-xs uppercase text-gray-500 dark:text-graphite-400">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">{{ __('messages.admin.user_label') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.profits.bank') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.profits.amount') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.profits.state') }}</th>
                            <th class="px-4 py-3">{{ __('messages.admin.profits.created_at') }}</th>
                            <th class="px-4 py-3">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                        @forelse ($records as $profit)
                            <tr>
                                <td class="px-4 py-3">#{{ $profit->id }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-graphite-100">{{ $profit->user?->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ $profit->user?->email }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ $profit->userBank?->bank_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-graphite-400">{{ $profit->userBank?->number }}</p>
                                </td>
                                <td class="px-4 py-3 font-semibold">${{ number_format((float) $profit->amount, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if ($profit->state === 'pending')
                                        <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ __('messages.status.pending') }}</span>
                                    @else
                                        <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('messages.admin.profits.status_made') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p>{{ optional($profit->created_at)->format('Y-m-d H:i') }}</p>
                                    @if ($profit->paid_at)
                                        <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.profits.paid_at') }}: {{ optional($profit->paid_at)->format('Y-m-d H:i') }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($profit->state === 'pending')
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-md border border-emerald-500 px-2.5 py-1.5 text-xs font-semibold uppercase tracking-widest text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20"
                                            onclick='openProfitPaidModal(@json([
                                                "id" => $profit->id,
                                                "user" => $profit->user?->name,
                                                "amount" => (float) $profit->amount,
                                            ]))'
                                        >
                                            {{ __('messages.admin.profits.mark_as_paid') }}
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.profits.transaction') }} #{{ $profit->transaction_id }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-graphite-400">{{ __('messages.table.no_records') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $records->links() }}
            </div>
        </div>
    </div>

    <x-modal name="profit-mark-paid-modal" :show="false" maxWidth="md">
        <form id="profit-mark-paid-form" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.profits.modal_title') }}</h3>
                <p id="profit-mark-paid-text" class="mt-1 text-sm text-gray-600 dark:text-graphite-300"></p>
            </div>

            <div>
                <x-input-label for="profit_bank_id" :value="__('messages.admin.profits.select_bank')" />
                <select id="profit_bank_id" name="bank_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    <option value="">{{ __('messages.admin.profits.select_bank_placeholder') }}</option>
                    @foreach ($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->number }}) - ${{ number_format((float) $bank->amount, 2) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="profit_detail" :value="__('messages.admin.profits.detail_optional')" />
                <textarea id="profit_detail" name="detail" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
            </div>

            <div class="flex justify-end gap-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close')">{{ __('messages.cancel') }}</x-secondary-button>
                <x-primary-button type="submit">{{ __('messages.confirm') }}</x-primary-button>
            </div>
        </form>
    </x-modal>

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
