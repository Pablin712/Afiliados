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

            {{-- Role change confirmation modal --}}
            @can('edit users')
            <div
                x-data="{
                    visible: false,
                    userId: null,
                    action: '',
                    userName: '',
                    pendingBtn: null,
                    open(d) {
                        this.userId = d.userId;
                        this.action = d.action;
                        this.userName = d.userName;
                        this.pendingBtn = d.btn;
                        this.visible = true;
                    },
                    close() {
                        this.visible = false;
                        if (this.pendingBtn) { this.pendingBtn.disabled = false; this.pendingBtn = null; }
                    },
                    confirm() {
                        this.visible = false;
                        window._doRoleChange(this.userId, this.action);
                    }
                }"
                @open-role-confirm.window="open($event.detail)"
                @keydown.escape.window="close()"
            >
                <div
                    x-show="visible"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="display: none;"
                >
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="close()"></div>

                    <div
                        x-show="visible"
                        x-transition:enter="ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="relative bg-white dark:bg-graphite-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-graphite-700 z-10"
                        style="width: 100%; max-width: 380px;"
                    >
                        <div class="h-1 w-full bg-purple-500 rounded-t-2xl"></div>
                        <div class="p-5 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100" x-text="action === 'make_teacher' ? 'Asignar rol Teacher' : 'Quitar rol Teacher'"></p>
                                    <p class="text-sm text-gray-500 dark:text-graphite-400 mt-0.5 truncate">
                                        Usuario: <span class="font-medium text-gray-700 dark:text-graphite-300" x-text="userName"></span>
                                    </p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-graphite-400" x-text="action === 'make_teacher' ? 'El usuario podrá programar y gestionar sus propias clases.' : 'El usuario perderá acceso a programar clases.'"></p>
                            <div class="flex items-center justify-end gap-2 pt-1">
                                <button
                                    type="button"
                                    @click="close()"
                                    class="px-4 py-2 text-sm font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 dark:text-graphite-300 dark:bg-graphite-800 dark:hover:bg-graphite-700 transition-colors"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="button"
                                    @click="confirm()"
                                    class="px-4 py-2 text-sm font-semibold rounded-lg text-white bg-purple-600 hover:bg-purple-500 transition-colors"
                                >
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const updatePatternUrl     = @json(route('admin.users.update-sponsor', ['user' => '__UID__']));
                    const updateRolePatternUrl = @json(route('admin.users.update-role', ['user' => '__UID__']));
                    const csrfToken            = document.querySelector('meta[name="csrf-token"]').content;
                    const sponsorSelect        = document.getElementById('sponsor-select');

                    window.toggleTeacherRole = function (userId, action, btn) {
                        btn.disabled = true;
                        const userName = btn.closest('tr')?.querySelector('td:nth-child(2) span:first-child')?.textContent?.trim() ?? '';
                        window.dispatchEvent(new CustomEvent('open-role-confirm', {
                            detail: { userId, action, userName, btn }
                        }));
                    };

                    window._doRoleChange = function (userId, action) {
                        fetch(updateRolePatternUrl.replace('__UID__', String(userId)), {
                            method:  'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept':       'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ action }),
                        })
                        .then(async r => {
                            const data = await r.json();
                            if (!r.ok) {
                                window.dispatchEvent(new CustomEvent('open-role-confirm', {
                                    detail: { userId, action, userName: '', btn: null }
                                }));
                                return;
                            }
                            window.location.reload();
                        })
                        .catch(() => window.location.reload());
                    };

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
