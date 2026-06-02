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
        @if($canEdit ?? false)
            <td class="px-4 sm:px-6 py-3 text-sm">
                <form method="POST" action="{{ route('memberships.update', ['id' => $record->id]) }}" class="space-y-2 min-w-[280px]">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-2">
                        <select name="membership_type_id" class="w-full border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 text-gray-900 dark:text-graphite-100 rounded-md px-2 py-1.5 text-xs">
                            @foreach($membershipTypes as $type)
                                <option value="{{ $type->id }}" @selected((int) ($record->membership_type_id ?? 0) === (int) $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="status" class="w-full border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 text-gray-900 dark:text-graphite-100 rounded-md px-2 py-1.5 text-xs">
                            @foreach($statusOptions as $statusValue)
                                <option value="{{ $statusValue }}" @selected((string) ($record->status ?? '') === (string) $statusValue)>
                                    {{ __('memberships.statuses.'.$statusValue) }}
                                </option>
                            @endforeach
                        </select>

                        <input
                            type="datetime-local"
                            name="started_at"
                            value="{{ optional($record->started_at)->format('Y-m-d\TH:i') }}"
                            class="w-full border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 text-gray-900 dark:text-graphite-100 rounded-md px-2 py-1.5 text-xs"
                        >

                        <input
                            type="datetime-local"
                            name="expires_at"
                            value="{{ optional($record->expires_at)->format('Y-m-d\TH:i') }}"
                            class="w-full border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 text-gray-900 dark:text-graphite-100 rounded-md px-2 py-1.5 text-xs"
                        >

                        <button type="submit" class="inline-flex items-center justify-center px-2.5 py-1.5 bg-indigo-600 border border-indigo-600 rounded-md font-semibold text-[10px] text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('memberships.buttons.save_membership') }}
                        </button>
                    </div>
                </form>
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="{{ ($canEdit ?? false) ? 8 : 7 }}" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('memberships.messages.empty') }}
        </td>
    </tr>
@endforelse
