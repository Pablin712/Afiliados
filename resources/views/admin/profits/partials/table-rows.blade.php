@forelse ($records as $profit)
    @php
        $markPaidOnclick = 'openProfitPaidModal(' . json_encode([
            'id' => $profit->id,
            'user' => $profit->user?->name,
            'amount' => (float) $profit->amount,
        ]) . ')';
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">#{{ $profit->id }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            <p class="font-medium text-gray-900 dark:text-graphite-100">{{ $profit->user?->name }}</p>
            <p class="text-xs text-gray-500 dark:text-graphite-400">{{ $profit->user?->email }}</p>
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            @php $userBanks = $profit->user?->userBanks ?? collect(); @endphp
            @forelse ($userBanks as $bank)
                <div class="{{ $loop->first ? '' : 'mt-2 pt-2 border-t border-gray-100 dark:border-graphite-700/50' }}">
                    <p class="font-medium text-gray-900 dark:text-graphite-100">{{ $bank->bank_name }}</p>
                    @if ($bank->type === 'binance')
                        <p class="text-xs text-gray-500 dark:text-graphite-400">ID: {{ $bank->identification }}</p>
                        <p class="text-xs text-gray-500 dark:text-graphite-400">{{ '@'.$bank->owner }}</p>
                    @else
                        <p class="text-xs text-gray-500 dark:text-graphite-400">Cta: {{ $bank->number }}</p>
                        <p class="text-xs text-gray-500 dark:text-graphite-400">{{ $bank->owner }}</p>
                    @endif
                </div>
            @empty
                <span class="text-xs text-gray-400 dark:text-graphite-500">—</span>
            @endforelse
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200 font-semibold">${{ number_format((float) $profit->amount, 2) }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            @if ($profit->state === 'pending')
                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ __('messages.status.pending') }}</span>
            @else
                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('messages.admin.profits.status_made') }}</span>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400">
            <p>{{ optional($profit->created_at)->format('Y-m-d H:i') }}</p>
            @if ($profit->paid_at)
                <p class="text-xs">{{ __('messages.admin.profits.paid_at') }}: {{ optional($profit->paid_at)->format('Y-m-d H:i') }}</p>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
                @if ($profit->state === 'pending')
                    @can('manage profits')
                        <x-action-icon-button
                            variant="approve"
                            icon="approve"
                            :title="__('messages.admin.profits.mark_as_paid')"
                            :onclick="$markPaidOnclick"
                        />
                    @endcan
                @else
                    <span class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.profits.transaction') }} #{{ $profit->transaction_id }}</span>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.table.no_records') }}
        </td>
    </tr>
@endforelse
