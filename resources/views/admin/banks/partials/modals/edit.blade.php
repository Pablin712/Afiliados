@can('edit banks')
    <x-modal name="bank-edit-modal" focusable>
        <form id="bank-edit-form" method="POST" action="{{ route('admin.banks.update', ['bank' => '__ID__']) }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.banks.forms.edit_title') }}
            </h3>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.name') }}</label>
                <input id="bank-edit-name" type="text" name="name" required maxlength="120" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.owner') }}</label>
                    <input id="bank-edit-owner" type="text" name="owner" required maxlength="150" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.identification') }}</label>
                    <input id="bank-edit-identification" type="text" name="identification" required maxlength="50" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.number') }}</label>
                    <input id="bank-edit-number" type="text" name="number" required maxlength="80" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.amount') }}</label>
                    <input id="bank-edit-amount" type="number" step="0.01" min="0" name="amount" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.banks.columns.detail') }}</label>
                <textarea id="bank-edit-detail" name="detail" rows="3" maxlength="2000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'bank-edit-modal')">{{ __('messages.admin.banks.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.banks.buttons.update') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
