<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.admin.channels.title') }}
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
                id="admin-channels-table"
                :headers="[
                    ['label' => __('messages.admin.channels.columns.id'), 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('messages.admin.channels.columns.name'), 'type' => 'string', 'sort_by' => 'name'],
                    ['label' => __('messages.admin.channels.columns.type'), 'type' => 'string', 'sort_by' => 'type'],
                    ['label' => __('messages.admin.channels.columns.purpose'), 'type' => 'string', 'sort_by' => 'purpose'],
                    ['label' => __('messages.admin.channels.columns.chat_id'), 'type' => 'string', 'sort_by' => 'chat_id'],
                    ['label' => __('messages.admin.channels.columns.is_exclusive'), 'type' => 'string', 'sort_by' => 'is_exclusive'],
                    ['label' => __('messages.admin.channels.columns.is_active'), 'type' => 'string', 'sort_by' => 'is_active'],
                    ['label' => __('messages.admin.channels.columns.actions'), 'type' => 'actions', 'sort_by' => 'id'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('admin.channels.index')"
                :csv="false"
                :excel="false"
                :json="false"
                :pdf="false"
                :print="false"
                :table_void="$records->isEmpty()"
            >
                <x-slot name="buttons">
                    @can('create channels')
                        <x-primary-button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'channel-create-modal' }))">
                            {{ __('messages.admin.channels.buttons.create') }}
                        </x-primary-button>
                    @endcan
                </x-slot>

                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('admin.channels.partials.table-rows', ['records' => $records->items()])
                </tbody>
            </x-enhanced-table>

            @include('admin.channels.partials.modals.create')
            @include('admin.channels.partials.modals.edit')
            @include('admin.channels.partials.modals.delete')
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const updatePattern = @json(route('admin.channels.update', ['channel' => '__ID__']));
                    const deletePattern = @json(route('admin.channels.destroy', ['channel' => '__ID__']));

                    window.openChannelEditModal = function (payload) {
                        const form = document.getElementById('channel-edit-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = updatePattern.replace('__ID__', String(payload.id));
                        document.getElementById('channel-edit-type').value = payload.type ?? 'telegram';
                        document.getElementById('channel-edit-name').value = payload.name ?? '';
                        document.getElementById('channel-edit-purpose').value = payload.purpose ?? 'general';
                        document.getElementById('channel-edit-is-active').checked = !!payload.is_active;
                        document.getElementById('channel-edit-is-exclusive').checked = !!payload.is_exclusive;
                        document.getElementById('channel-edit-chat-id').value = payload.chat_id ?? '';
                        document.getElementById('channel-edit-bot-token').value = payload.bot_token ?? '';
                        document.getElementById('channel-edit-instance-name').value = payload.instance_name ?? '';
                        document.getElementById('channel-edit-server-url').value = payload.server_url ?? '';
                        document.getElementById('channel-edit-api-key').value = payload.api_key ?? '';
                        document.getElementById('channel-edit-notes').value = payload.notes ?? '';
                        document.getElementById('channel-edit-type').dispatchEvent(new Event('change'));
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'channel-edit-modal' }));
                    };

                    window.openChannelDeleteModal = function (payload) {
                        const form = document.getElementById('channel-delete-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = deletePattern.replace('__ID__', String(payload.id));
                        document.getElementById('channel-delete-name').textContent = payload.name ? `(${payload.name})` : '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'channel-delete-modal' }));
                    };
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>
