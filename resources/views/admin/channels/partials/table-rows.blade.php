@forelse ($records as $channel)
    @php
        $editOnclick = 'window.openChannelEditModal(' . json_encode([
            'id' => $channel->id,
            'type' => $channel->type,
            'name' => $channel->name,
            'purpose' => $channel->purpose,
            'is_active' => (bool) $channel->is_active,
            'chat_id' => $channel->chat_id,
            'bot_token' => $channel->bot_token,
            'instance_name' => $channel->instance_name,
            'server_url' => $channel->server_url,
            'api_key' => $channel->api_key,
            'notes' => $channel->notes,
        ]) . ')';

        $deleteOnclick = 'window.openChannelDeleteModal(' . json_encode([
            'id' => $channel->id,
            'name' => $channel->name,
        ]) . ')';

        $purposeLabels = \App\Models\Channel::purposes();
        $typeLabels = \App\Models\Channel::types();
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $channel->id }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $channel->name }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $typeLabels[$channel->type] ?? $channel->type }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $purposeLabels[$channel->purpose] ?? $channel->purpose }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200 font-mono">{{ $channel->chat_id }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            @if ($channel->is_active)
                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300">{{ __('messages.admin.channels.status.active') }}</span>
            @else
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.admin.channels.status.inactive') }}</span>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
                @can('edit channels')
                    <x-action-icon-button
                        variant="edit"
                        icon="edit"
                        :title="__('messages.admin.channels.buttons.edit')"
                        :onclick="$editOnclick"
                    />
                @endcan

                @can('delete channels')
                    <x-action-icon-button
                        variant="delete"
                        icon="delete"
                        :title="__('messages.admin.channels.buttons.delete')"
                        :onclick="$deleteOnclick"
                    />
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.channels.messages.empty') }}
        </td>
    </tr>
@endforelse
