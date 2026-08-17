@can('edit landing_content')
    <x-modal name="testimonial-create-modal" focusable>
        <form method="POST" action="{{ route('admin.testimonials.store') }}" class="p-6 space-y-4" enctype="multipart/form-data">
            @csrf

            <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
                {{ __('messages.admin.testimonials.forms.create_title') }}
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.name_es') }}</label>
                    <input type="text" name="name_es" value="{{ old('name_es') }}" required maxlength="150" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('name_es')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.name_en') }}</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" required maxlength="150" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('name_en')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.quote_es') }}</label>
                <textarea name="quote_es" rows="2" required maxlength="2000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">{{ old('quote_es') }}</textarea>
                @error('quote_es')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.quote_en') }}</label>
                <textarea name="quote_en" rows="2" required maxlength="2000" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">{{ old('quote_en') }}</textarea>
                @error('quote_en')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.forms.media_type_label') }}</label>
                <div class="flex items-center gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-graphite-300">
                        <input type="radio" name="media_type" value="image" checked onchange="window.toggleTestimonialMediaFields(this.form, 'image')" class="border-gray-300 dark:border-graphite-700">
                        {{ __('messages.admin.testimonials.forms.media_type_image') }}
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-graphite-300">
                        <input type="radio" name="media_type" value="video" onchange="window.toggleTestimonialMediaFields(this.form, 'video')" class="border-gray-300 dark:border-graphite-700">
                        {{ __('messages.admin.testimonials.forms.media_type_video') }}
                    </label>
                </div>
                @error('media_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div data-media-field="image">
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.forms.media_type_image') }}</label>
                <input type="file" name="photo" accept="image/*" class="w-full text-sm text-gray-700 dark:text-graphite-200">
                <p class="mt-1 text-xs text-gray-400">{{ __('messages.admin.testimonials.forms.photo_hint_create') }}</p>
                @error('photo')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div data-media-field="video" class="hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.forms.media_type_video') }}</label>
                <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm" class="w-full text-sm text-gray-700 dark:text-graphite-200">
                <p class="mt-1 text-xs text-gray-400">{{ __('messages.admin.testimonials.forms.video_hint_create') }}</p>
                @error('video')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.testimonials.columns.sort_order') }}</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    @error('sort_order')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-end pb-1">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="testimonial-create-is-active" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 dark:border-graphite-700">
                        <label for="testimonial-create-is-active" class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.testimonials.columns.is_active') }}</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-secondary-button x-on:click.prevent="$dispatch('close-modal', 'testimonial-create-modal')">{{ __('messages.admin.testimonials.buttons.cancel') }}</x-secondary-button>
                <x-primary-button>{{ __('messages.admin.testimonials.buttons.create') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
@endcan
