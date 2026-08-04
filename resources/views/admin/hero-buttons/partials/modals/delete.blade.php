@can('edit landing_content')
    <x-modal name="hero-button-delete-modal" focusable>
        <form id="hero-button-delete-form" method="POST" action="{{ route('admin.hero-buttons.destroy', ['heroButton' => '__ID__']) }}" class="p-6 space-y-4">
            @csrf
            @method('DELETE')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.hero_buttons.forms.delete_title') }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-graphite-300">
                {{ __('messages.admin.hero_buttons.messages.confirm_delete_modal') }}
                <span id="hero-button-delete-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
            </p>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'hero-button-delete-modal')">{{ __('messages.admin.hero_buttons.buttons.cancel') }}</x-secondary-button>
                <x-danger-button>{{ __('messages.admin.hero_buttons.buttons.delete') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
@endcan
