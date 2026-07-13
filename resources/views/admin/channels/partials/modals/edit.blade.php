@can('edit channels')
    <x-modal name="channel-edit-modal" focusable>
        <form id="channel-edit-form" method="POST" action="{{ route('admin.channels.update', ['channel' => '__ID__']) }}" class="p-6 space-y-4" x-data="{ type: 'telegram' }">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.channels.forms.edit_title') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.type') }}</label>
                    <select id="channel-edit-type" name="type" x-model="type" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        @foreach (\App\Models\Channel::types() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.purpose') }}</label>
                    <select id="channel-edit-purpose" name="purpose" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        @foreach (\App\Models\Channel::purposes() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.name') }}</label>
                <input id="channel-edit-name" type="text" name="name" required maxlength="120" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">
                    {{ __('messages.admin.channels.columns.chat_id') }}
                    <span class="text-xs text-gray-400" x-text="type === 'whatsapp' ? '{{ __('messages.admin.channels.hints.whatsapp_group_jid') }}' : '{{ __('messages.admin.channels.hints.telegram_chat_id') }}'"></span>
                </label>
                <input id="channel-edit-chat-id" type="text" name="chat_id" required maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
            </div>

            <div x-show="type === 'telegram'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.bot_token') }}</label>
                <input id="channel-edit-bot-token" type="text" name="bot_token" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
            </div>

            <div x-show="type === 'whatsapp'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.instance_name') }}</label>
                    <input id="channel-edit-instance-name" type="text" name="instance_name" maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.server_url') }}</label>
                    <input id="channel-edit-server-url" type="text" name="server_url" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.api_key') }}</label>
                    <input id="channel-edit-api-key" type="text" name="api_key" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.notes') }}</label>
                <textarea id="channel-edit-notes" name="notes" rows="2" maxlength="1000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="channel-edit-is-active" name="is_active" value="1" class="rounded border-gray-300 dark:border-graphite-700">
                <label for="channel-edit-is-active" class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.channels.columns.is_active') }}</label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'channel-edit-modal')">{{ __('messages.admin.channels.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.channels.buttons.update') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
