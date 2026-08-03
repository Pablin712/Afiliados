@can('edit landing_content')
    <x-modal name="testimonial-edit-modal" focusable>
        <form id="testimonial-edit-form" method="POST" action="{{ route('admin.testimonials.update', ['testimonial' => '__ID__']) }}" class="p-6 space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.testimonials.forms.edit_title') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.name_es') }}</label>
                    <input id="testimonial-edit-name-es" type="text" name="name_es" required maxlength="150" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('name_es')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.name_en') }}</label>
                    <input id="testimonial-edit-name-en" type="text" name="name_en" required maxlength="150" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('name_en')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.quote_es') }}</label>
                <textarea id="testimonial-edit-quote-es" name="quote_es" rows="2" required maxlength="2000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
                @error('quote_es')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.quote_en') }}</label>
                <textarea id="testimonial-edit-quote-en" name="quote_en" rows="2" required maxlength="2000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
                @error('quote_en')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.photo') }}</label>
                <img id="testimonial-edit-current-photo" src="" alt="" class="h-16 w-16 rounded-md object-cover border border-gray-200 dark:border-graphite-700 hidden mb-2">
                <input id="testimonial-edit-photo" type="file" name="photo" accept="image/*" class="w-full text-sm text-gray-700 dark:text-graphite-200">
                <p class="mt-1 text-xs text-gray-400">{{ __('messages.admin.testimonials.forms.photo_hint_edit') }}</p>
                @error('photo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.sort_order') }}</label>
                    <input id="testimonial-edit-sort-order" type="number" name="sort_order" min="0" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('sort_order')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end pb-1">
                    <div class="flex items-center gap-2">
                        <input id="testimonial-edit-is-active" type="checkbox" name="is_active" value="1" class="rounded border-gray-300 dark:border-graphite-700">
                        <label for="testimonial-edit-is-active" class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.testimonials.columns.is_active') }}</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'testimonial-edit-modal')">{{ __('messages.admin.testimonials.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.landing_content.buttons.save') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
