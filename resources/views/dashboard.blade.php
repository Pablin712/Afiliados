<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                    {{ __('messages.dashboard') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">
                    {{ $isAdmin ? __('messages.user.dashboard.admin_subtitle') : __('messages.user.dashboard.subtitle') }}
                </p>
            </div>

            @unless($isAdmin)
                <a href="{{ route('user.network.index') }}" class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-400 hover:bg-amber-100 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
                    {{ __('messages.user.dashboard.open_network') }}
                </a>
            @endunless
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.16),_transparent_32%),radial-gradient(circle_at_right,_rgba(14,165,233,0.12),_transparent_28%)]"></div>
                <div class="relative grid gap-6 p-6 lg:grid-cols-[1.3fr_0.7fr] lg:p-8">
                    <div class="space-y-4">
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">
                            {{ __('messages.user.dashboard.badge') }}
                        </span>
                        <div>
                            <h3 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-graphite-100">
                                {{ __('messages.user.dashboard.welcome', ['name' => $user->name]) }}
                            </h3>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-600 dark:text-graphite-300">
                                {{ __('messages.user.dashboard.description') }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            @if ($isAdmin)
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.admin_kpis.users_total') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-graphite-100">{{ $adminKpis['users_total'] ?? 0 }}</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.admin_kpis.customers_total') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-sky-600">{{ $adminKpis['customers_total'] ?? 0 }}</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.admin_kpis.approved_payments_month') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-emerald-600">{{ $adminKpis['approved_payments_month'] ?? 0 }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.admin_kpis.net_month', ['amount' => number_format((float) ($adminKpis['net_month'] ?? 0), 2)]) }}</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.admin_kpis.pending_profits_total') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-amber-600">${{ number_format((float) ($adminKpis['pending_profits_total'] ?? 0), 2) }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.current_membership') }}: {{ $currentMembership }}</p>
                                </div>
                            @else
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.network_affiliates') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-graphite-100">{{ $networkAffiliatesCount }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.direct_affiliates', ['count' => $directAffiliatesCount]) }}</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.monthly_profits') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-emerald-600">${{ number_format($monthlyProfitsAmount, 2) }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.monthly_payments', ['count' => $monthlyApprovedPaymentsCount]) }}</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.pending_profits') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-amber-600">${{ number_format($pendingProfitsAmount, 2) }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.paid_profits', ['amount' => number_format($paidProfitsAmount, 2)]) }}</p>
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-white/90 p-4 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                                    <p class="text-xs uppercase tracking-[0.16em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.current_membership') }}</p>
                                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-graphite-100">{{ $currentMembership }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.kpis.levels', ['level2' => $levelTwoAffiliatesCount, 'level3' => $levelThreeAffiliatesCount]) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div class="rounded-2xl border border-gray-200 bg-white/90 p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.sponsor_title') }}</p>
                            @if ($sponsor)
                                <p class="mt-3 text-xl font-semibold text-gray-900 dark:text-graphite-100">{{ $sponsor->name }}</p>
                                <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">{{ $sponsor->email }}</p>
                                <p class="mt-3 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.sponsor_membership', ['membership' => $sponsor->membership?->membershipType?->name ?? 'Free']) }}</p>
                            @else
                                <p class="mt-3 text-sm text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.no_sponsor') }}</p>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-gray-200 bg-white/90 p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/70">
                            <p class="text-xs uppercase tracking-[0.18em] text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.quick_actions') }}</p>
                            <div class="mt-3 grid gap-2">
                                @unless($isAdmin)
                                    <a href="{{ route('user.network.index') }}" class="inline-flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-amber-400 hover:text-amber-700 dark:border-graphite-800 dark:text-graphite-200 dark:hover:border-amber-500 dark:hover:text-amber-300">
                                        <span>{{ __('messages.nav.my_network') }}</span>
                                        <span>{{ $networkAffiliatesCount }}</span>
                                    </a>
                                    <a href="{{ route('user.profits.index') }}" class="inline-flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-emerald-400 hover:text-emerald-700 dark:border-graphite-800 dark:text-graphite-200 dark:hover:border-emerald-500 dark:hover:text-emerald-300">
                                        <span>{{ __('messages.nav.my_profits') }}</span>
                                        <span>${{ number_format($pendingProfitsAmount + $paidProfitsAmount, 2) }}</span>
                                    </a>
                                @endunless
                                <a href="{{ route('plans.index') }}" class="inline-flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-sky-400 hover:text-sky-700 dark:border-graphite-800 dark:text-graphite-200 dark:hover:border-sky-500 dark:hover:text-sky-300">
                                    <span>{{ __('messages.nav.plans') }}</span>
                                    <span>{{ $currentMembership }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                @unless($isAdmin)
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.user.dashboard.recent_profits_title') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.recent_profits_description') }}</p>
                        </div>
                        <a href="{{ route('user.profits.index') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">{{ __('messages.user.dashboard.view_all') }}</a>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($recentProfits as $profit)
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-graphite-800">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">
                                            {{ $profit->sourceUser?->name ?? __('messages.user.dashboard.system_generated') }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-graphite-400">
                                            {{ __('messages.user.dashboard.profit_origin', ['payment' => $profit->source_payment_id ?? '-', 'level' => $profit->source_level ?? '-']) }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-semibold text-emerald-600">${{ number_format((float) $profit->amount, 2) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-graphite-400">{{ optional($profit->created_at)->format('Y-m-d H:i') }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                                    @if ($profit->state === 'pending')
                                        <span class="rounded-full bg-amber-100 px-2 py-1 font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">{{ __('messages.status.pending') }}</span>
                                    @else
                                        <span class="rounded-full bg-emerald-100 px-2 py-1 font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ __('messages.admin.profits.status_made') }}</span>
                                    @endif
                                    <span class="text-gray-500 dark:text-graphite-400">{{ $profit->detail }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-graphite-700 dark:text-graphite-400">
                                {{ __('messages.user.dashboard.no_recent_profits') }}
                            </div>
                        @endforelse
                    </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.user.dashboard.admin_panel_title') }}</h3>
                        <p class="mt-2 text-sm text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.admin_panel_description') }}</p>
                    </div>
                @endunless

                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.user.dashboard.recent_affiliates_title') }}</h3>
                            <p class="text-sm text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.recent_affiliates_description') }}</p>
                        </div>
                        <a href="{{ route('user.network.index') }}" class="text-sm font-semibold text-amber-600 hover:text-amber-500 dark:text-amber-400">{{ __('messages.user.dashboard.view_network') }}</a>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($recentAffiliates as $affiliate)
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-graphite-800">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $affiliate->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-graphite-400">{{ $affiliate->email }}</p>
                                    </div>
                                    <span class="rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/30 dark:text-sky-300">
                                        {{ $affiliate->membership?->membershipType?->name ?? 'Free' }}
                                    </span>
                                </div>
                                <p class="mt-3 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.affiliate_joined', ['date' => optional($affiliate->created_at)->format('Y-m-d H:i')]) }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-graphite-700 dark:text-graphite-400">
                                {{ __('messages.user.dashboard.no_recent_affiliates') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
