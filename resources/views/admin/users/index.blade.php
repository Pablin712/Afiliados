<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.admin.users.title') }}
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
                id="admin-users-table"
                :headers="[
                    ['label' => 'ID',                                          'type' => 'number',  'sort_by' => 'id'],
                    ['label' => __('messages.name'),                           'type' => 'string',  'sort_by' => 'name'],
                    ['label' => __('messages.email'),                          'type' => 'string',  'sort_by' => 'email'],
                    ['label' => __('messages.phone'),                          'type' => 'string',  'sort_by' => 'phone'],
                    ['label' => __('messages.admin.users.affiliate_code'),     'type' => 'string',  'sort_by' => 'affiliate_code'],
                    ['label' => __('messages.admin.users.sponsor'),            'type' => 'string',  'sort_by' => 'sponsor'],
                    ['label' => __('messages.admin.users.membership'),         'type' => 'string',  'sort_by' => 'membership'],
                    ['label' => __('messages.admin.registered_at'),            'type' => 'string',  'sort_by' => 'created_at'],
                    ['label' => __('messages.admin.users.active_session'),     'type' => 'string',  'sort_by' => 'id'],
                    ['label' => __('messages.actions'),                        'type' => 'actions', 'sort_by' => 'id'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('admin.users.index')"
                :csv="false"
                :excel="true"
                :json="false"
                :pdf="false"
                :print="false"
                :table_void="$records->isEmpty()"
            >
                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('admin.users.partials.table-rows', ['records' => $records->items(), 'activeSessions' => $activeSessions])
                </tbody>
            </x-enhanced-table>

            {{-- Change Sponsor Modal --}}
            @can('edit users')
            <x-modal name="change-sponsor-modal" focusable>
                <div id="change-sponsor-modal-panel">
                    <form method="POST" id="change-sponsor-form" action="" class="p-6 space-y-5">
                        @csrf

                        <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                            {{ __('messages.admin.users.change_sponsor_title') }}
                        </h3>

                        <p class="text-sm text-gray-600 dark:text-graphite-300">
                            {{ __('messages.admin.users.changing_sponsor_for') }}:
                            <span id="change-sponsor-user-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
                        </p>

                        <div class="space-y-1">
                            <label for="sponsor-select" class="block text-sm font-medium text-gray-700 dark:text-graphite-200">
                                {{ __('messages.admin.users.new_sponsor_label') }}
                            </label>
                            <x-searchable-select
                                id="sponsor-select"
                                name="sponsor_id"
                                :options="$sponsors"
                                :allow-clear="false"
                                :dropdown-parent="'#change-sponsor-modal-panel'"
                                :placeholder="__('messages.admin.users.search_sponsor_placeholder')"
                                class="w-full"
                                required
                            />
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'change-sponsor-modal' }))">
                                {{ __('messages.admin.cancel') }}
                            </x-secondary-button>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-brand-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('messages.admin.users.save_sponsor') }}
                            </button>
                        </div>
                    </form>
                </div>
            </x-modal>
            @endcan
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const updatePatternUrl = @json(route('admin.users.update-sponsor', ['user' => '__UID__']));
                    const sponsorSelect    = document.getElementById('sponsor-select');

                    window.openChangeSponsorModal = function (payload) {
                        if (!payload || !payload.id) return;

                        // Set form action
                        const form = document.getElementById('change-sponsor-form');
                        form.action = updatePatternUrl.replace('__UID__', String(payload.id));

                        // Set user name label
                        document.getElementById('change-sponsor-user-name').textContent = payload.name ?? '';

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'change-sponsor-modal' }));

                        if (!sponsorSelect) {
                            return;
                        }

                        document.dispatchEvent(new CustomEvent('searchable-select:init', {
                            detail: { root: document.getElementById('change-sponsor-modal-panel') }
                        }));

                        window.setTimeout(function () {
                            const tomSelect = sponsorSelect.tomselect;
                            const sponsorId = payload.currentSponsor?.id ? String(payload.currentSponsor.id) : '';

                            if (tomSelect) {
                                tomSelect.clear(true);

                                if (sponsorId !== '') {
                                    tomSelect.setValue(sponsorId, true);
                                }

                                tomSelect.focus();
                                tomSelect.setTextboxValue('');
                                tomSelect.refreshOptions(false);
                                tomSelect.open();
                                return;
                            }

                            sponsorSelect.value = sponsorId;
                            sponsorSelect.focus();
                        }, 75);

                    };
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>
