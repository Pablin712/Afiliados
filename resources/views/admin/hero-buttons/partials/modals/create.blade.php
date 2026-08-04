@can('edit landing_content')
    <x-modal name="hero-button-create-modal" focusable>
        <form method="POST" action="{{ route('admin.hero-buttons.store') }}" class="p-6 space-y-4">
            @csrf

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.hero_buttons.forms.create_title') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.label_es') }}</label>
                    <input type="text" name="label_es" value="{{ old('label_es') }}" required maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('label_es')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.label_en') }}</label>
                    <input type="text" name="label_en" value="{{ old('label_en') }}" required maxlength="100" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('label_en')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">
                    {{ __('messages.admin.hero_buttons.columns.url') }}
                    <span class="text-xs text-gray-400">{{ __('messages.admin.hero_buttons.hints.url') }}</span>
                </label>
                <input type="text" name="url" value="{{ old('url') }}" required maxlength="500" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 font-mono">
                @error('url')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @include('admin.hero-buttons.partials.style-picker', ['selected' => old('style', 'primary')])
            @error('style')
                <p class="-mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.sort_order') }}</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                @error('sort_order')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" id="hero-button-create-is-active" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 dark:border-graphite-700">
                <label for="hero-button-create-is-active" class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.hero_buttons.columns.is_active') }}</label>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'hero-button-create-modal')">{{ __('messages.admin.hero_buttons.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.hero_buttons.buttons.create') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
