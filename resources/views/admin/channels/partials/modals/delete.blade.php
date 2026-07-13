@can('delete channels')
    <x-modal name="channel-delete-modal" focusable>
        <form id="channel-delete-form" method="POST" action="{{ route('admin.channels.destroy', ['channel' => '__ID__']) }}" class="p-6 space-y-4">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.channels.forms.delete_title') }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-graphite-300">
                {{ __('messages.admin.channels.messages.confirm_delete_modal') }}
                <span id="channel-delete-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
            </p>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'channel-delete-modal')">{{ __('messages.admin.channels.buttons.cancel') }}</x-secondary-button>
                <x-danger-button>{{ __('messages.admin.channels.buttons.delete') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
@endcan
