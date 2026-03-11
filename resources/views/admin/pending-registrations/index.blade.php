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
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>