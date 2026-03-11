@forelse ($records as $record)
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $record->id ?? '-' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $record->user_name ?? '-' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $record->membership_type_name ?? '-' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">
                {{ __('memberships.statuses.'.($record->status ?? 'pending_payment')) }}
            </span>
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">{{ optional($record->started_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">{{ optional($record->expires_at)->format('Y-m-d H:i:s') ?? '-' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">{{ optional($record->created_at)->format('Y-m-d H:i:s') }}</td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('memberships.messages.empty') }}
        </td>
    </tr>
@endforelse
