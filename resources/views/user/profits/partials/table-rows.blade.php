@forelse ($records as $profit)
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">#{{ $profit->id }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            <p class="font-medium text-gray-900 dark:text-graphite-100">{{ $profit->sourceUser?->name ?? __('messages.user.profits.system_origin') }}</p>
            <p class="text-xs text-gray-500 dark:text-graphite-400">{{ $profit->sourceUser?->email ?? $profit->detail }}</p>
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            @if ($profit->source_payment_id)
                <span class="font-medium">#{{ $profit->source_payment_id }}</span>
            @else
                <span class="text-gray-500 dark:text-graphite-400">-</span>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">
            @if ($profit->source_level)
                {{ __('messages.user.profits.level_badge', ['level' => $profit->source_level]) }}
            @else
                -
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm font-semibold text-gray-700 dark:text-graphite-200">${{ number_format((float) $profit->amount, 2) }}</td>
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
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.table.no_records') }}
        </td>
    </tr>
@endforelse
