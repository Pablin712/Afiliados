@can('delete membership_types')
<x-modal name="membership-type-delete-modal" focusable>
    <form id="membership-type-delete-form" method="POST" action="{{ route('membership-types.destroy', ['membershipType' => '__ID__']) }}" class="p-6 space-y-4">
        @csrf
        @method('DELETE')

        <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
            {{ __('membership_types.forms.delete_title') }}
        </h3>

        <p class="text-sm text-gray-600 dark:text-graphite-300">
            {{ __('membership_types.messages.confirm_delete_modal') }}
            <span id="membership-type-delete-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
        </p>

        <div class="flex justify-end gap-2 pt-2">
            <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'membership-type-delete-modal')">{{ __('membership_types.buttons.cancel') }}</x-secondary-button>
            <x-danger-button>{{ __('membership_types.buttons.delete') }}</x-danger-button>
        </div>
    </form>
</x-modal>
@endcan
