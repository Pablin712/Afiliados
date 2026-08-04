@can('edit landing_content')
    <x-modal name="hero-button-edit-modal" focusable>
        <form id="hero-button-edit-form" method="POST" action="{{ route('admin.hero-buttons.update', ['heroButton' => '__ID__']) }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.hero_buttons.forms.edit_title') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.label_es') }}</label>
                    <input id="hero-button-edit-label-es" type="text" name="label_es" required maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.label_en') }}</label>
                    <input id="hero-button-edit-label-en" type="text" name="label_en" required maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">
                    {{ __('messages.admin.hero_buttons.columns.url') }}
                    <span class="text-xs text-gray-400">{{ __('messages.admin.hero_buttons.hints.url') }}</span>
                </label>
                <input id="hero-button-edit-url" type="text" name="url" required maxlength="500" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.style') }}</label>
                    <select id="hero-button-edit-style" name="style" required class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        @foreach (\App\Models\HeroButton::styleLabels() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.sort_order') }}</label>
                    <input id="hero-button-edit-sort-order" type="number" name="sort_order" min="0" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input id="hero-button-edit-is-active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 dark:border-graphite-700">
                <label for="hero-button-edit-is-active" class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.hero_buttons.columns.is_active') }}</label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'hero-button-edit-modal')">{{ __('messages.admin.hero_buttons.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.landing_content.buttons.save') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
