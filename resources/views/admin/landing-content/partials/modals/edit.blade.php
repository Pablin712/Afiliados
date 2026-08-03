@can('edit landing_content')
    <x-modal name="landing-content-edit-modal" focusable>
        <form id="landing-content-edit-form" method="POST" action="{{ route('admin.landing-content.update', ['key' => '__KEY__']) }}" class="p-6 space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.landing_content.forms.edit_title') }}
            </h3>

            <div id="landing-content-edit-bilingual" class="space-y-3 hidden">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.landing_content.forms.value_es_label') }}</label>
                    <textarea id="landing-content-edit-es" name="value_es" rows="3" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
                    @error('value_es')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.landing_content.forms.value_en_label') }}</label>
                    <textarea id="landing-content-edit-en" name="value_en" rows="3" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
                    @error('value_en')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div id="landing-content-edit-single" class="hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.landing_content.forms.value_label') }}</label>
                <textarea id="landing-content-edit-value" name="value" rows="3" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
                @error('value')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="landing-content-edit-image" class="space-y-2 hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.landing_content.forms.current_image_label') }}</label>
                <img id="landing-content-edit-current-image" src="" alt="" class="h-24 w-24 rounded-md object-cover border border-gray-200 dark:border-graphite-700 hidden">

                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1 mt-2">{{ __('messages.admin.landing_content.forms.image_label') }}</label>
                <input id="landing-content-edit-file" type="file" name="image" accept="image/*" class="w-full text-sm text-gray-700 dark:text-graphite-200">
                @error('image')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'landing-content-edit-modal')">{{ __('messages.admin.landing_content.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.landing_content.buttons.save') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
