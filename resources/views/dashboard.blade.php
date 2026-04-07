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
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('user.network.index') }}" class="inline-flex items-center justify-center rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700 transition hover:border-amber-400 hover:bg-amber-100 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-300">
                        {{ __('messages.user.dashboard.open_network') }}
                    </a>

                    <a href="https://deriv.com/signup?sidc=7044F2C1-1A0C-496A-986E-570DCAD80FF8&utm_campaign=dynamicworks&utm_medium=affiliate&utm_source=CU17859" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 dark:bg-brand-500 dark:hover:bg-brand-400">
                        {{ __('messages.user.dashboard.create_deriv_account') }}
                    </a>

                    <a href="https://es.gowt.net/ib61404" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-sky-300 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:border-sky-400 hover:bg-sky-100 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-300 dark:hover:border-sky-400 dark:hover:bg-sky-500/20">
                        {{ __('messages.user.dashboard.create_weltrade_account') }}
                    </a>

                    @if($canDownloadScanners)
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-sky-300 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 transition hover:border-sky-400 hover:bg-sky-100 dark:border-sky-500/40 dark:bg-sky-500/10 dark:text-sky-300">
                                    {{ __('messages.user.dashboard.scanner.button') }}
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <button type="button" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-graphite-200 dark:hover:bg-graphite-800" onclick="openScannerDownloadModal('deriv')">
                                    {{ __('messages.user.dashboard.scanner.broker_deriv') }}
                                </button>
                                <button type="button" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-graphite-200 dark:hover:bg-graphite-800" onclick="openScannerDownloadModal('weltrade')">
                                    {{ __('messages.user.dashboard.scanner.broker_weltrade') }}
                                </button>
                                <button type="button" class="block w-full cursor-not-allowed px-4 py-2 text-left text-sm text-gray-400 dark:text-graphite-500" disabled>
                                    {{ __('messages.user.dashboard.scanner.broker_vantage') }}
                                </button>
                            </x-slot>
                        </x-dropdown>
                    @endif
                </div>
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
                                    <a href="https://deriv.com/signup?sidc=7044F2C1-1A0C-496A-986E-570DCAD80FF8&utm_campaign=dynamicworks&utm_medium=affiliate&utm_source=CU17859" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-400 hover:text-brand-700 dark:border-graphite-800 dark:text-graphite-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                        <span>{{ __('messages.user.dashboard.create_deriv_account') }}</span>
                                        <span>{{ __('messages.user.dashboard.external_link_badge') }}</span>
                                    </a>
                                    <a href="https://es.gowt.net/ib61404" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-sky-400 hover:text-sky-700 dark:border-graphite-800 dark:text-graphite-200 dark:hover:border-sky-500 dark:hover:text-sky-300">
                                        <span>{{ __('messages.user.dashboard.create_weltrade_account') }}</span>
                                        <span>{{ __('messages.user.dashboard.external_link_badge') }}</span>
                                    </a>
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
                    @unless($isAdmin)
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.user.dashboard.recent_affiliates_title') }}</h3>
                                <p class="text-sm text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.recent_affiliates_description') }}</p>
                            </div>
                            <a href="{{ route('user.network.index') }}" class="text-sm font-semibold text-amber-600 hover:text-amber-500 dark:text-amber-400">{{ __('messages.user.dashboard.view_network') }}</a>
                        </div>
                    @endunless

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

    @if(! $isAdmin && $canDownloadScanners)
        <x-modal name="scanner-download-modal" focusable maxWidth="md">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">
                    {{ __('messages.user.dashboard.scanner.modal_title') }}
                </h3>
                <p class="mt-2 text-sm text-gray-600 dark:text-graphite-300">
                    {{ __('messages.user.dashboard.scanner.modal_description') }}
                    <span id="scanner-selected-broker" class="font-semibold"></span>
                </p>

                <form id="scanner-download-form" class="mt-5 space-y-4">
                    <input type="hidden" id="scanner-broker" name="broker" value="">

                    <div>
                        <x-input-label for="scanner-account-id" :value="__('messages.user.dashboard.scanner.account_id_label')" />
                        <x-text-input
                            id="scanner-account-id"
                            name="account_id"
                            type="text"
                            class="mt-1 block w-full"
                            maxlength="8"
                            inputmode="numeric"
                            pattern="[0-9]{8}"
                            required
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.user.dashboard.scanner.account_id_hint') }}</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'scanner-download-modal' }))">
                            {{ __('messages.cancel') }}
                        </x-secondary-button>
                        <x-primary-button id="scanner-download-submit" type="submit">
                            {{ __('messages.user.dashboard.scanner.download_now') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>

        @once
            @push('scripts')
                <script>
                    (function () {
                        const brokerLabels = {
                            deriv: @json(__('messages.user.dashboard.scanner.broker_deriv')),
                            weltrade: @json(__('messages.user.dashboard.scanner.broker_weltrade')),
                        };

                        const prepareUrl = @json(route('scanners.prepare'));
                        const form = document.getElementById('scanner-download-form');
                        const brokerInput = document.getElementById('scanner-broker');
                        const accountInput = document.getElementById('scanner-account-id');
                        const selectedBrokerLabel = document.getElementById('scanner-selected-broker');
                        const submitButton = document.getElementById('scanner-download-submit');

                        if (!form || !brokerInput || !accountInput || !selectedBrokerLabel || !submitButton) {
                            return;
                        }

                        window.openScannerDownloadModal = function (broker) {
                            brokerInput.value = broker;
                            accountInput.value = '';
                            selectedBrokerLabel.textContent = brokerLabels[broker] ?? broker;
                            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'scanner-download-modal' }));
                            setTimeout(() => accountInput.focus(), 120);
                        };

                        form.addEventListener('submit', async function (event) {
                            event.preventDefault();

                            const broker = brokerInput.value;
                            const accountId = accountInput.value.trim();

                            if (!/^\d{8}$/.test(accountId)) {
                                window.alert(@json(__('messages.user.dashboard.scanner.account_id_invalid')));
                                return;
                            }

                            submitButton.setAttribute('disabled', 'disabled');

                            try {
                                const response = await fetch(prepareUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': @json(csrf_token()),
                                    },
                                    body: JSON.stringify({
                                        broker: broker,
                                        account_id: accountId,
                                    }),
                                });

                                const payload = await response.json();

                                if (!response.ok) {
                                    const message = payload?.message
                                        ?? payload?.errors?.scanner?.[0]
                                        ?? payload?.errors?.account_id?.[0]
                                        ?? @json(__('messages.error'));
                                    throw new Error(message);
                                }

                                for (const download of (payload.downloads ?? [])) {
                                    const fileResponse = await fetch(download.url, {
                                        method: 'GET',
                                        redirect: 'follow',
                                        headers: {
                                            'Accept': 'application/octet-stream,application/json',
                                        },
                                    });

                                    const contentType = (fileResponse.headers.get('Content-Type') ?? '').toLowerCase();

                                    if (fileResponse.redirected || contentType.includes('text/html')) {
                                        throw new Error(@json(__('messages.user.dashboard.scanner.unexpected_html_response')));
                                    }

                                    if (!fileResponse.ok) {
                                        let detail = @json(__('messages.error'));
                                        try {
                                            const errorPayload = await fileResponse.json();
                                            detail = errorPayload?.message
                                                ?? errorPayload?.errors?.scanner?.[0]
                                                ?? detail;
                                        } catch (e) {
                                            // Ignore parse errors and use generic message.
                                        }
                                        throw new Error(detail);
                                    }

                                    if (contentType.includes('application/json')) {
                                        const errorPayload = await fileResponse.json();
                                        throw new Error(errorPayload?.message ?? @json(__('messages.error')));
                                    }

                                    const blob = await fileResponse.blob();
                                    const disposition = fileResponse.headers.get('Content-Disposition') ?? '';
                                    const match = disposition.match(/filename="?([^";]+)"?/i);
                                    const fileName = match?.[1] ?? 'scanner.ex5';

                                    const objectUrl = URL.createObjectURL(blob);
                                    const link = document.createElement('a');
                                    link.href = objectUrl;
                                    link.download = fileName;
                                    document.body.appendChild(link);
                                    link.click();
                                    link.remove();
                                    URL.revokeObjectURL(objectUrl);
                                }

                                window.dispatchEvent(new CustomEvent('close-modal', { detail: 'scanner-download-modal' }));
                            } catch (error) {
                                window.alert(error.message || @json(__('messages.error')));
                            } finally {
                                submitButton.removeAttribute('disabled');
                            }
                        });
                    })();
                </script>
            @endpush
        @endonce
    @endif
</x-app-layout>
