@forelse ($groupedRecords as $row)
    @php
        $markAllOnclick = 'openProfitAllPaidModal(' . json_encode([
            'userId'      => $row->user_id,
            'user'        => $row->user_name,
            'totalAmount' => (float) $row->total_amount,
            'count'       => (int) $row->profits_count,
        ]) . ')';
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            <p class="font-medium text-gray-900 dark:text-graphite-100">{{ $row->user_name }}</p>
            <p class="text-xs text-gray-500 dark:text-graphite-400">{{ $row->user_email }}</p>
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            @forelse ($row->userBanks as $bank)
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
        <td class="px-4 sm:px-6 py-3 text-sm font-semibold text-amber-600">${{ number_format((float) $row->total_amount, 2) }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $row->profits_count }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            @can('manage profits')
                <x-action-icon-button
                    variant="approve"
                    icon="approve"
                    :title="__('messages.admin.profits.mark_all_as_paid')"
                    :onclick="$markAllOnclick"
                />
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.profits.no_grouped_records') }}
        </td>
    </tr>
@endforelse
