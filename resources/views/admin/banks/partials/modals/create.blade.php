@can('create banks')
    <x-modal name="bank-create-modal" focusable>
        <form method="POST" action="{{ route('admin.banks.store') }}" class="p-6 space-y-4">
            @csrf

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.banks.forms.create_title') }}
            </h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="120" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.owner') }}</label>
                    <input type="text" name="owner" value="{{ old('owner') }}" required maxlength="150" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('owner')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.identification') }}</label>
                    <input type="text" name="identification" value="{{ old('identification') }}" required maxlength="50" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('identification')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.number') }}</label>
                    <input type="text" name="number" value="{{ old('number') }}" required maxlength="80" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('number')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.amount') }}</label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', 0) }}" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('amount')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.detail') }}</label>
                <textarea name="detail" rows="3" maxlength="2000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">{{ old('detail') }}</textarea>
                @error('detail')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'bank-create-modal')">{{ __('messages.admin.banks.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.banks.buttons.create') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
