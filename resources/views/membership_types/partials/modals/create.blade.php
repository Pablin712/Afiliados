@can('create membership_types')
<x-modal name="membership-type-create-modal" focusable>
    <form method="POST" action="{{ route('membership-types.store') }}" class="p-6 space-y-4">
        @csrf

        <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
            {{ __('membership_types.forms.create_title') }}
        </h3>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="50" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.affiliates_required') }}</label>
                <input type="number" name="affiliates_required" value="{{ old('affiliates_required', 0) }}" min="0" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.cost') }}</label>
                <input type="number" step="0.01" name="cost" value="{{ old('cost', 0) }}" min="0" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('membership_types.columns.profit') }}</label>
                <input type="number" step="0.01" name="profit" value="{{ old('profit', 0) }}" min="0" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
            <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'membership-type-create-modal')">{{ __('membership_types.buttons.cancel') }}</x-secondary-button>
            <x-primary-button>{{ __('membership_types.buttons.create') }}</x-primary-button>
        </div>
    </form>
</x-modal>
@endcan
