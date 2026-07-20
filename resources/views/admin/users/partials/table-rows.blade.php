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
        <td class="px-4 sm:px-6 py-3 text-sm font-medium text-gray-900 dark:text-graphite-100">
            <div class="flex items-center gap-2">
                <span>{{ $user->name }}</span>
                @if ($user->hasRole('teacher'))
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 uppercase tracking-wide">Teacher</span>
                @endif
                @if ($user->hasRole('admin'))
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300 uppercase tracking-wide">Admin</span>
                @endif
            </div>
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-300">{{ $user->email }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 font-mono whitespace-nowrap">{{ $user->phone ?? '—' }}</td>
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
                @php
                    $membershipStatusColors = [
                        'active' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                        'free' => 'bg-gray-100 text-gray-600 dark:bg-graphite-800 dark:text-graphite-300',
                        'expired' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
                        'pending_payment' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                    ];
                    $membershipStatusColor = $membershipStatusColors[$user->membership->status] ?? $membershipStatusColors['free'];
                @endphp
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-50 text-brand-700 dark:bg-brand-900/30 dark:text-brand-300 capitalize">
                            {{ $user->membership->membershipType?->name ?? '—' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase {{ $membershipStatusColor }}">
                            {{ __('membership_report.statuses.'.$user->membership->status) }}
                        </span>
                    </div>
                    @if ($user->membership->expires_at)
                        <span class="text-[11px] text-gray-400 dark:text-graphite-500 font-mono whitespace-nowrap">
                            {{ __('messages.admin.users.membership_expires_at') }}: {{ $user->membership->expires_at->format('Y-m-d') }}
                        </span>
                    @endif
                </div>
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
            <div class="flex items-center gap-1">
                @can('edit users')
                    <x-action-icon-button
                        variant="edit"
                        icon="edit"
                        :title="__('messages.admin.users.change_sponsor')"
                        :onclick="$changeOnclick"
                    />
                    @if (! $user->hasRole('admin'))
                        @if ($user->hasRole('teacher'))
                            <button
                                type="button"
                                title="Quitar rol Teacher"
                                onclick="window.toggleTeacherRole({{ $user->id }}, 'remove_teacher', this)"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-md text-purple-600 hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-900/30 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                            </button>
                        @else
                            <button
                                type="button"
                                title="Hacer Teacher"
                                onclick="window.toggleTeacherRole({{ $user->id }}, 'make_teacher', this)"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-md text-gray-400 hover:text-purple-600 hover:bg-purple-50 dark:hover:text-purple-400 dark:hover:bg-purple-900/30 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                            </button>
                        @endif
                    @endif
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.users.no_users') }}
        </td>
    </tr>
@endforelse
