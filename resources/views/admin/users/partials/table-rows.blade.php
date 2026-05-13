@forelse ($records as $user)
    @php
        $changeOnclick = 'window.openChangeSponsorModal(' . json_encode([
            'id'              => $user->id,
            'name'            => $user->name,
            'currentSponsor'  => $user->sponsor ? [
                'id'   => $user->sponsor->id,
                'text' => $user->sponsor->name . ' (' . ($user->sponsor->affiliate_code ?? '#' . $user->sponsor->id) . ') — ' . $user->sponsor->email,
            ] : null,
        ]) . ')';
        $session = ($activeSessions ?? collect())->get($user->id);
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 dark:text-graphite-400 font-mono">#{{ $user->id }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm font-medium text-gray-900 dark:text-graphite-100">{{ $user->name }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-300">{{ $user->email }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm font-mono text-gray-600 dark:text-graphite-400">{{ $user->affiliate_code ?? '—' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-300">
            @if ($user->sponsor)
                <span class="font-medium">{{ $user->sponsor->name }}</span>
                <span class="text-xs text-gray-400 dark:text-graphite-500 ml-1">({{ $user->sponsor->affiliate_code ?? '#'.$user->sponsor->id }})</span>
            @else
                <span class="text-gray-400 dark:text-graphite-600">—</span>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            @if ($user->membership)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300">
                    {{ $user->membership->membershipType?->name ?? $user->membership->status }}
                </span>
            @else
                <span class="text-gray-400 dark:text-graphite-600">—</span>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">
            {{ $user->created_at?->format('Y-m-d') }}
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            @if ($session)
                <div class="flex flex-col gap-0.5">
                    <span class="inline-flex items-center gap-1">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ __('messages.admin.users.session_online') }}</span>
                    </span>
                    <span class="text-[11px] text-gray-500 dark:text-graphite-400">{{ $session['browser'] }} · {{ $session['os'] }}</span>
                    <span class="text-[11px] text-gray-400 dark:text-graphite-500 font-mono">{{ $session['ip'] }}</span>
                </div>
            @else
                <span class="text-xs text-gray-400 dark:text-graphite-600">{{ __('messages.admin.users.session_offline') }}</span>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            @can('edit users')
                <x-action-icon-button
                    variant="edit"
                    icon="edit"
                    :title="__('messages.admin.users.change_sponsor')"
                    :onclick="$changeOnclick"
                />
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.users.no_users') }}
        </td>
    </tr>
@endforelse
