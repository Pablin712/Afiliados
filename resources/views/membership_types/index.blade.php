<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('membership_types.title') }}
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
                id="membership-types-table"
                :headers="[
                    ['label' => __('membership_types.columns.id'), 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('membership_types.columns.name'), 'type' => 'string', 'sort_by' => 'name'],
                    ['label' => __('membership_types.columns.affiliates_required'), 'type' => 'number', 'sort_by' => 'affiliates_required'],
                    ['label' => __('membership_types.columns.cost'), 'type' => 'number', 'sort_by' => 'cost'],
                    ['label' => __('membership_types.columns.profit'), 'type' => 'number', 'sort_by' => 'profit'],
                    ['label' => __('membership_types.columns.created_at'), 'type' => 'string', 'sort_by' => 'created_at'],
                    ['label' => __('membership_types.columns.actions'), 'type' => 'actions', 'sort_by' => 'id'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('membership-types.index')"
                :csv="false"
                :excel="false"
                :json="false"
                :pdf="false"
                :print="false"
                :table_void="$records->isEmpty()"
            >
                <x-slot name="buttons">
                    <a href="{{ route('memberships.index') }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 dark:bg-graphite-900 dark:border-graphite-700 dark:text-graphite-200 dark:hover:bg-graphite-800 transition ease-in-out duration-150">
                        {{ __('membership_types.buttons.back_to_memberships') }}
                    </a>

                    @can('create membership_types')
                        <x-primary-button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'membership-type-create-modal' }))">
                            {{ __('membership_types.buttons.create') }}
                        </x-primary-button>
                    @endcan
                </x-slot>

                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('membership_types.partials.table-rows', ['records' => $records])
                </tbody>
            </x-enhanced-table>

            @include('membership_types.partials.modals.create')
            @include('membership_types.partials.modals.edit')
            @include('membership_types.partials.modals.delete')
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const updatePattern = @json(route('membership-types.update', ['membershipType' => '__ID__']));
                    const deletePattern = @json(route('membership-types.destroy', ['membershipType' => '__ID__']));

                    window.openMembershipTypeEditModal = function (payload) {
                        const form = document.getElementById('membership-type-edit-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = updatePattern.replace('__ID__', String(payload.id));
                        document.getElementById('membership-type-edit-name').value = payload.name ?? '';
                        document.getElementById('membership-type-edit-affiliates-required').value = payload.affiliates_required ?? 0;
                        document.getElementById('membership-type-edit-cost').value = payload.cost ?? 0;
                        document.getElementById('membership-type-edit-profit').value = payload.profit ?? 0;
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'membership-type-edit-modal' }));
                    };

                    window.openMembershipTypeDeleteModal = function (payload) {
                        const form = document.getElementById('membership-type-delete-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = deletePattern.replace('__ID__', String(payload.id));
                        document.getElementById('membership-type-delete-name').textContent = payload.name ? `(${payload.name})` : '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'membership-type-delete-modal' }));
                    };
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>
