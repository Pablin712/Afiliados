<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.admin.banks.title') }}
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
                id="admin-banks-table"
                :headers="[
                    ['label' => __('messages.admin.banks.columns.id'), 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('messages.admin.banks.columns.name'), 'type' => 'string', 'sort_by' => 'name'],
                    ['label' => __('messages.admin.banks.columns.owner'), 'type' => 'string', 'sort_by' => 'owner'],
                    ['label' => __('messages.admin.banks.columns.identification'), 'type' => 'string', 'sort_by' => 'identification'],
                    ['label' => __('messages.admin.banks.columns.number'), 'type' => 'string', 'sort_by' => 'number'],
                    ['label' => __('messages.admin.banks.columns.amount'), 'type' => 'number', 'sort_by' => 'amount'],
                    ['label' => __('messages.admin.banks.columns.created_at'), 'type' => 'string', 'sort_by' => 'created_at'],
                    ['label' => __('messages.admin.banks.columns.actions'), 'type' => 'actions', 'sort_by' => 'id'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('admin.banks.index')"
                :csv="false"
                :excel="false"
                :json="false"
                :pdf="false"
                :print="false"
                :table_void="$records->isEmpty()"
            >
                <x-slot name="buttons">
                    @can('create banks')
                        <x-primary-button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'bank-create-modal' }))">
                            {{ __('messages.admin.banks.buttons.create') }}
                        </x-primary-button>
                    @endcan
                </x-slot>

                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('admin.banks.partials.table-rows', ['records' => $records->items()])
                </tbody>
            </x-enhanced-table>

            @include('admin.banks.partials.modals.create')
            @include('admin.banks.partials.modals.edit')
            @include('admin.banks.partials.modals.delete')
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const updatePattern = @json(route('admin.banks.update', ['bank' => '__ID__']));
                    const deletePattern = @json(route('admin.banks.destroy', ['bank' => '__ID__']));

                    window.openBankEditModal = function (payload) {
                        const form = document.getElementById('bank-edit-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = updatePattern.replace('__ID__', String(payload.id));
                        document.getElementById('bank-edit-name').value = payload.name ?? '';
                        document.getElementById('bank-edit-owner').value = payload.owner ?? '';
                        document.getElementById('bank-edit-identification').value = payload.identification ?? '';
                        document.getElementById('bank-edit-number').value = payload.number ?? '';
                        document.getElementById('bank-edit-amount').value = payload.amount ?? '0.00';
                        document.getElementById('bank-edit-detail').value = payload.detail ?? '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'bank-edit-modal' }));
                    };

                    window.openBankDeleteModal = function (payload) {
                        const form = document.getElementById('bank-delete-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = deletePattern.replace('__ID__', String(payload.id));
                        document.getElementById('bank-delete-name').textContent = payload.name ? `(${payload.name})` : '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'bank-delete-modal' }));
                    };
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>
