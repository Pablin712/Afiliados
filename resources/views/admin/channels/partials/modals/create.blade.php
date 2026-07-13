@can('create channels')
    <x-modal name="channel-create-modal" focusable>
        <form method="POST" action="{{ route('admin.channels.store') }}" class="p-6 space-y-4" x-data="{ type: '{{ old('type', 'telegram') }}' }">
            @csrf

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.channels.forms.create_title') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.type') }}</label>
                    <select name="type" x-model="type" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        @foreach (\App\Models\Channel::types() as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'telegram') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.purpose') }}</label>
                    <select name="purpose" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        @foreach (\App\Models\Channel::purposes() as $value => $label)
                            <option value="{{ $value }}" @selected(old('purpose', 'general') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('purpose')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="120" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">
                    {{ __('messages.admin.channels.columns.chat_id') }}
                    <span class="text-xs text-gray-400" x-text="type === 'whatsapp' ? '{{ __('messages.admin.channels.hints.whatsapp_group_jid') }}' : '{{ __('messages.admin.channels.hints.telegram_chat_id') }}'"></span>
                </label>
                <input type="text" name="chat_id" value="{{ old('chat_id') }}" required maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
                @error('chat_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="type === 'telegram'" x-cloak>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.bot_token') }}</label>
                <input type="text" name="bot_token" value="{{ old('bot_token') }}" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
                @error('bot_token')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div x-show="type === 'whatsapp'" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.instance_name') }}</label>
                    <input type="text" name="instance_name" value="{{ old('instance_name') }}" maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('instance_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.server_url') }}</label>
                    <input type="text" name="server_url" value="{{ old('server_url') }}" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('server_url')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.api_key') }}</label>
                    <input type="text" name="api_key" value="{{ old('api_key') }}" maxlength="255" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
                    @error('api_key')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.channels.columns.notes') }}</label>
                <textarea name="notes" rows="2" maxlength="1000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="channel-create-is-active" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 dark:border-graphite-700">
                <label for="channel-create-is-active" class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.channels.columns.is_active') }}</label>
            </div>

            <div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="channel-create-is-exclusive" name="is_exclusive" value="1" @checked(old('is_exclusive', false)) class="rounded border-gray-300 dark:border-graphite-700">
                    <label for="channel-create-is-exclusive" class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.channels.columns.is_exclusive') }}</label>
                </div>
                <p class="mt-1 text-xs text-gray-400">{{ __('messages.admin.channels.hints.is_exclusive') }}</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'channel-create-modal')">{{ __('messages.admin.channels.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.channels.buttons.create') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
