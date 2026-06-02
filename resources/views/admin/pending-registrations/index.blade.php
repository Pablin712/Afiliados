<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.admin.pending_registrations_title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Tab switcher --}}
            <div class="border-b border-gray-200 dark:border-graphite-700">
                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                    <button id="tab-btn-pending"
                            onclick="switchTab('pending')"
                            class="tab-btn whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm border-brand-500 text-brand-600 dark:text-brand-400">
                        {{ __('messages.admin.tab_pending') }}
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                            {{ $totalRecords }}
                        </span>
                    </button>
                    <button id="tab-btn-history"
                            onclick="switchTab('history')"
                            class="tab-btn whitespace-nowrap pb-3 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-graphite-400 dark:hover:text-graphite-200">
                        {{ __('messages.admin.tab_history') }}
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-graphite-700 dark:text-graphite-300">
                            {{ $historyTotal }}
                        </span>
                    </button>
                </nav>
            </div>

            {{-- Pending tab --}}
            <div id="tab-panel-pending">
                <x-enhanced-table
                    id="pending-registrations-table"
                    :headers="[
                        ['label' => __('messages.name'),                   'type' => 'string',  'sort_by' => 'name'],
                        ['label' => __('messages.email'),                  'type' => 'string',  'sort_by' => 'email'],
                        ['label' => __('messages.auth.payment_reference'), 'type' => 'string',  'sort_by' => 'number'],
                        ['label' => __('messages.auth.admin_bank'),        'type' => 'string',  'sort_by' => 'bank'],
                        ['label' => __('messages.auth.payment_amount'),    'type' => 'number',  'sort_by' => 'amount'],
                        ['label' => __('messages.auth.payment_receipt'),   'type' => 'actions', 'sort_by' => 'photo'],
                        ['label' => __('messages.admin.registered_at'),    'type' => 'string',  'sort_by' => 'created_at'],
                        ['label' => __('messages.actions'),                'type' => 'actions', 'sort_by' => 'id'],
                    ]"
                    :serverSide="true"
                    :totalRecords="$totalRecords"
                    :searchUrl="route('admin.pending-registrations.index')"
                    :csv="false"
                    :excel="false"
                    :json="false"
                    :pdf="false"
                    :print="false"
                    :table_void="$records->isEmpty()"
                >
                    <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                        @include('admin.pending-registrations.partials.table-rows', ['records' => $records->items()])
                    </tbody>
                </x-enhanced-table>
            </div>

            {{-- History tab --}}
            <div id="tab-panel-history" class="hidden">
                <x-enhanced-table
                    id="payment-history-table"
                    :headers="[
                        ['label' => __('messages.name'),                   'type' => 'string',  'sort_by' => 'name'],
                        ['label' => __('messages.email'),                  'type' => 'string',  'sort_by' => 'email'],
                        ['label' => __('messages.auth.payment_reference'), 'type' => 'string',  'sort_by' => 'number'],
                        ['label' => __('messages.auth.admin_bank'),        'type' => 'string',  'sort_by' => 'bank'],
                        ['label' => __('messages.auth.payment_amount'),    'type' => 'number',  'sort_by' => 'amount'],
                        ['label' => __('messages.auth.payment_receipt'),   'type' => 'actions', 'sort_by' => 'photo'],
                        ['label' => __('messages.admin.payment_state'),    'type' => 'string',  'sort_by' => 'state'],
                        ['label' => __('messages.admin.registered_at'),    'type' => 'string',  'sort_by' => 'created_at'],
                        ['label' => __('messages.admin.reviewed_at'),      'type' => 'string',  'sort_by' => 'reviewed_at'],
                        ['label' => __('messages.admin.reviewed_by'),      'type' => 'string',  'sort_by' => 'reviewer'],
                    ]"
                    :serverSide="true"
                    :totalRecords="$historyTotal"
                    :searchUrl="route('admin.pending-registrations.index')"
                    :extraParams="['tab' => 'history']"
                    :csv="false"
                    :excel="false"
                    :json="false"
                    :pdf="false"
                    :print="false"
                    :table_void="$historyTotal === 0"
                >
                    <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                        {{-- loaded via AJAX by enhanced-table serverSide --}}
                    </tbody>
                </x-enhanced-table>
            </div>

            @include('admin.pending-registrations.partials.modals.approve')
            @include('admin.pending-registrations.partials.modals.reject')
            @include('admin.pending-registrations.partials.modals.receipt')
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const approvePattern = @json(route('admin.pending-registrations.approve', ['payment' => '__ID__']));
                    const rejectPattern  = @json(route('admin.pending-registrations.reject',  ['payment' => '__ID__']));
                    const noReceiptMsg   = @json(__('messages.admin.receipt_no_photo'));

                    window.openApproveModal = function (payload) {
                        const form = document.getElementById('pending-registration-approve-form');
                        if (!form || !payload || !payload.id) return;
                        form.action = approvePattern.replace('__ID__', String(payload.id));
                        document.getElementById('pending-registration-approve-name').textContent = payload.name ?? '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pending-registration-approve-modal' }));
                    };

                    window.openRejectModal = function (payload) {
                        const form = document.getElementById('pending-registration-reject-form');
                        if (!form || !payload || !payload.id) return;
                        form.action = rejectPattern.replace('__ID__', String(payload.id));
                        document.getElementById('pending-registration-reject-name').textContent = payload.name ?? '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pending-registration-reject-modal' }));
                    };

                    window.openReceiptModal = function (payload) {
                        const container = document.getElementById('pending-registration-receipt-container');
                        const nameEl    = document.getElementById('pending-registration-receipt-user-name');
                        if (!container) return;
                        nameEl.textContent = payload.name ?? '';
                        if (payload.photo) {
                            const ext = String(payload.photo).split('.').pop().toLowerCase();
                            if (ext === 'pdf') {
                                container.innerHTML = `<embed src="${payload.photo}" type="application/pdf" class="w-full h-96 rounded-lg" />`;
                            } else {
                                container.innerHTML = `<img src="${payload.photo}" alt="Comprobante" class="max-w-full max-h-[32rem] rounded-lg shadow-md object-contain mx-auto" />`;
                            }
                        } else {
                            container.innerHTML = `<p class="py-8 text-center text-sm text-gray-500 dark:text-graphite-400">${noReceiptMsg}</p>`;
                        }
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'pending-registration-receipt-modal' }));
                    };

                    window.switchTab = function (tab) {
                        const panels  = ['pending', 'history'];
                        const active  = 'border-brand-500 text-brand-600 dark:text-brand-400';
                        const inactive = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-graphite-400 dark:hover:text-graphite-200';

                        panels.forEach(function (t) {
                            const panel = document.getElementById('tab-panel-' + t);
                            const btn   = document.getElementById('tab-btn-' + t);
                            if (!panel || !btn) return;

                            if (t === tab) {
                                panel.classList.remove('hidden');
                                btn.className = btn.className.replace(inactive, '').trim() + ' ' + active;
                            } else {
                                panel.classList.add('hidden');
                                btn.className = btn.className.replace(active, '').trim() + ' ' + inactive;
                            }
                        });
                    };
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>
