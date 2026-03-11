@can('edit membership_types')
<x-modal name="membership-type-edit-modal" focusable>
    <form id="membership-type-edit-form" method="POST" action="{{ route('membership-types.update', ['membershipType' => '__ID__']) }}" class="p-6 space-y-4">
        @csrf
        @method('PUT')

        <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
            {{ __('membership_types.forms.edit_title') }}
        </h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.name') }}</label>
            <input id="membership-type-edit-name" type="text" name="name" required maxlength="50" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.affiliates_required') }}</label>
                <input id="membership-type-edit-affiliates-required" type="number" name="affiliates_required" min="0" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.cost') }}</label>
                <input id="membership-type-edit-cost" type="number" step="0.01" name="cost" min="0" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.profit') }}</label>
                <input id="membership-type-edit-profit" type="number" step="0.01" name="profit" min="0" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'membership-type-edit-modal')">{{ __('membership_types.buttons.cancel') }}</x-secondary-button>
            <x-primary-button>{{ __('membership_types.buttons.update') }}</x-primary-button>
        </div>
    </form>
</x-modal>
@endcan
