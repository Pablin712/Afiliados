<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.admin.landing_content.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-sm text-gray-500 dark:text-graphite-400">
                {{ __('messages.admin.landing_content.preview_hint') }}
            </p>

            <div class="lg:grid lg:grid-cols-[minmax(360px,460px)_minmax(0,1fr)] lg:gap-6 lg:items-start">
                <div class="lg:sticky lg:top-20 mb-6 lg:mb-0">
                    <div class="bg-white dark:bg-graphite-900 rounded-lg shadow border border-gray-200 dark:border-graphite-700 overflow-hidden">
                        <div class="px-4 py-2 flex items-center justify-between gap-2 bg-gray-50 dark:bg-graphite-950 border-b border-gray-200 dark:border-graphite-800">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-graphite-300">
                                {{ __('messages.admin.landing_content.preview_title') }}
                            </p>
                            <div class="flex items-center gap-1">
                                <button type="button" id="lc-preview-locale-es" onclick="window.lcSetPreviewLocale('es')" class="px-2 py-1 text-xs font-semibold rounded-md border border-gray-300 dark:border-graphite-700 text-gray-600 dark:text-graphite-300 hover:text-brand-600 dark:hover:text-brand-400">ES</button>
                                <button type="button" id="lc-preview-locale-en" onclick="window.lcSetPreviewLocale('en')" class="px-2 py-1 text-xs font-semibold rounded-md border border-gray-300 dark:border-graphite-700 text-gray-600 dark:text-graphite-300 hover:text-brand-600 dark:hover:text-brand-400">EN</button>
                                <button type="button" onclick="window.lcReloadPreview()" class="px-2 py-1 text-xs font-semibold rounded-md border border-gray-300 dark:border-graphite-700 text-gray-600 dark:text-graphite-300 hover:text-brand-600 dark:hover:text-brand-400">{{ __('messages.admin.landing_content.preview_reload') }}</button>
                            </div>
                        </div>
                        <iframe
                            id="lc-preview-iframe"
                            src="{{ url('/') }}?preview=1&preview_locale=es"
                            class="w-full border-0"
                            style="height: calc(100vh - 180px);"
                            title="{{ __('messages.admin.landing_content.preview_title') }}"
                        ></iframe>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="space-y-4">
                        @foreach ($fields as $group => $groupFields)
                            <div class="bg-white dark:bg-graphite-900 rounded-lg shadow border border-gray-200 dark:border-graphite-700 overflow-hidden">
                                <div class="px-5 py-3 bg-gray-50 dark:bg-graphite-950 border-b border-gray-200 dark:border-graphite-800">
                                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-graphite-300">
                                        {{ __('messages.admin.landing_content.groups.'.$group) }}
                                    </h3>
                                </div>

                                <div class="divide-y divide-gray-100 dark:divide-graphite-800">
                                    @foreach ($groupFields as $field)
                                        @php
                                            $mode = $field->type === 'image' ? 'image' : ($field->all !== null ? 'single' : 'bilingual');
                                            $preview = $mode === 'bilingual' ? $field->es : ($mode === 'single' ? $field->all : null);
                                            $editPayload = [
                                                'key' => $field->key,
                                                'mode' => $mode,
                                                'label' => $field->key,
                                                'es' => $field->es,
                                                'en' => $field->en,
                                                'value' => $field->all,
                                                'currentImageUrl' => $mode === 'image' && $field->all ? asset('storage/'.$field->all) : null,
                                            ];
                                            $editOnclick = 'window.openLandingContentEditModal(' . json_encode($editPayload) . ')';
                                        @endphp
                                        <div
                                            class="px-5 py-3 flex items-start justify-between gap-4 transition-colors hover:bg-amber-50 dark:hover:bg-amber-900/10"
                                            onmouseenter="window.lcHighlight('{{ $field->key }}')"
                                            onmouseleave="window.lcClearHighlight()"
                                        >
                                            <div class="min-w-0">
                                                <p class="text-xs font-mono text-gray-400 dark:text-graphite-500">{{ $field->key }}</p>
                                                @if ($mode === 'image')
                                                    @if ($field->all)
                                                        <img src="{{ asset('storage/'.$field->all) }}" alt="{{ $field->key }}" class="mt-1 h-16 w-16 rounded-md object-cover border border-gray-200 dark:border-graphite-700">
                                                    @endif
                                                @else
                                                    <p class="mt-1 text-sm text-gray-700 dark:text-graphite-200 line-clamp-2">{{ $preview }}</p>
                                                @endif
                                            </div>

                                            @can('edit landing_content')
                                                <x-primary-button type="button" class="shrink-0" :onclick="$editOnclick">
                                                    {{ __('messages.admin.landing_content.buttons.edit') }}
                                                </x-primary-button>
                                            @endcan
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="bg-white dark:bg-graphite-900 rounded-lg shadow border border-gray-200 dark:border-graphite-700 overflow-hidden">
                        <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3 bg-gray-50 dark:bg-graphite-950 border-b border-gray-200 dark:border-graphite-800">
                            <div>
                                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-graphite-300">
                                    {{ __('messages.admin.landing_content.testimonials_heading') }}
                                </h3>
                                <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.landing_content.testimonials_hint') }}</p>
                            </div>

                            @can('edit landing_content')
                                <x-primary-button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'testimonial-create-modal' }))">
                                    {{ __('messages.admin.testimonials.buttons.create') }}
                                </x-primary-button>
                            @endcan
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-graphite-800">
                                <thead class="bg-gray-50 dark:bg-graphite-950">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.testimonials.columns.photo') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.testimonials.columns.name_es') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.testimonials.columns.name_en') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.testimonials.columns.sort_order') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.testimonials.columns.is_active') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.testimonials.columns.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                                    @forelse ($testimonials as $testimonial)
                                        @php
                                            $editOnclick = 'window.openTestimonialEditModal(' . json_encode([
                                                'id' => $testimonial->id,
                                                'name_es' => $testimonial->name_es,
                                                'name_en' => $testimonial->name_en,
                                                'quote_es' => $testimonial->quote_es,
                                                'quote_en' => $testimonial->quote_en,
                                                'sort_order' => $testimonial->sort_order,
                                                'is_active' => (bool) $testimonial->is_active,
                                                'currentPhotoUrl' => $testimonial->photo_path ? asset('storage/'.$testimonial->photo_path) : null,
                                            ]) . ')';

                                            $deleteOnclick = 'window.openTestimonialDeleteModal(' . json_encode([
                                                'id' => $testimonial->id,
                                                'name' => $testimonial->name_es,
                                            ]) . ')';
                                        @endphp
                                        <tr
                                            class="transition-colors hover:bg-amber-50 dark:hover:bg-amber-900/10"
                                            onmouseenter="window.lcHighlight('{{ $testimonial->id }}')"
                                            onmouseleave="window.lcClearHighlight()"
                                        >
                                            <td class="px-4 py-2">
                                                @if ($testimonial->photo_path)
                                                    <img src="{{ asset('storage/'.$testimonial->photo_path) }}" alt="{{ $testimonial->name_es }}" class="h-12 w-12 rounded-md object-cover border border-gray-200 dark:border-graphite-700">
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-graphite-200">{{ $testimonial->name_es }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-graphite-200">{{ $testimonial->name_en }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-700 dark:text-graphite-200">{{ $testimonial->sort_order }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @if ($testimonial->is_active)
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300">{{ __('messages.admin.testimonials.status.active') }}</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.admin.testimonials.status.inactive') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @can('edit landing_content')
                                                        <x-action-icon-button
                                                            variant="edit"
                                                            icon="edit"
                                                            :title="__('messages.admin.testimonials.buttons.edit')"
                                                            :onclick="$editOnclick"
                                                        />
                                                        <x-action-icon-button
                                                            variant="delete"
                                                            icon="delete"
                                                            :title="__('messages.admin.testimonials.buttons.delete')"
                                                            :onclick="$deleteOnclick"
                                                        />
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
                                                {{ __('messages.admin.testimonials.messages.empty') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.landing-content.partials.modals.edit')
            @include('admin.testimonials.partials.modals.create')
            @include('admin.testimonials.partials.modals.edit')
            @include('admin.testimonials.partials.modals.delete')
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                (function () {
                    const contentUpdatePattern = @json(route('admin.landing-content.update', ['key' => '__KEY__']));
                    const testimonialUpdatePattern = @json(route('admin.testimonials.update', ['testimonial' => '__ID__']));
                    const testimonialDeletePattern = @json(route('admin.testimonials.destroy', ['testimonial' => '__ID__']));

                    window.openLandingContentEditModal = function (payload) {
                        const form = document.getElementById('landing-content-edit-form');
                        if (!form || !payload || !payload.key) {
                            return;
                        }

                        form.action = contentUpdatePattern.replace('__KEY__', encodeURIComponent(payload.key));

                        document.getElementById('landing-content-edit-bilingual').classList.toggle('hidden', payload.mode !== 'bilingual');
                        document.getElementById('landing-content-edit-single').classList.toggle('hidden', payload.mode !== 'single');
                        document.getElementById('landing-content-edit-image').classList.toggle('hidden', payload.mode !== 'image');

                        document.getElementById('landing-content-edit-es').value = payload.es ?? '';
                        document.getElementById('landing-content-edit-en').value = payload.en ?? '';
                        document.getElementById('landing-content-edit-value').value = payload.value ?? '';
                        document.getElementById('landing-content-edit-file').value = '';

                        const preview = document.getElementById('landing-content-edit-current-image');
                        if (payload.currentImageUrl) {
                            preview.src = payload.currentImageUrl;
                            preview.classList.remove('hidden');
                        } else {
                            preview.classList.add('hidden');
                        }

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'landing-content-edit-modal' }));
                    };

                    window.openTestimonialEditModal = function (payload) {
                        const form = document.getElementById('testimonial-edit-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = testimonialUpdatePattern.replace('__ID__', String(payload.id));
                        document.getElementById('testimonial-edit-name-es').value = payload.name_es ?? '';
                        document.getElementById('testimonial-edit-name-en').value = payload.name_en ?? '';
                        document.getElementById('testimonial-edit-quote-es').value = payload.quote_es ?? '';
                        document.getElementById('testimonial-edit-quote-en').value = payload.quote_en ?? '';
                        document.getElementById('testimonial-edit-sort-order').value = payload.sort_order ?? 0;
                        document.getElementById('testimonial-edit-is-active').checked = !!payload.is_active;
                        document.getElementById('testimonial-edit-photo').value = '';

                        const preview = document.getElementById('testimonial-edit-current-photo');
                        if (payload.currentPhotoUrl) {
                            preview.src = payload.currentPhotoUrl;
                            preview.classList.remove('hidden');
                        } else {
                            preview.classList.add('hidden');
                        }

                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'testimonial-edit-modal' }));
                    };

                    window.openTestimonialDeleteModal = function (payload) {
                        const form = document.getElementById('testimonial-delete-form');
                        if (!form || !payload || !payload.id) {
                            return;
                        }

                        form.action = testimonialDeletePattern.replace('__ID__', String(payload.id));
                        document.getElementById('testimonial-delete-name').textContent = payload.name ? `(${payload.name})` : '';
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'testimonial-delete-modal' }));
                    };

                    // --- Live preview hover-highlight ---
                    let previewLocale = 'es';
                    let lcLastKey = null;

                    function previewWindow() {
                        const iframe = document.getElementById('lc-preview-iframe');
                        return iframe ? iframe.contentWindow : null;
                    }

                    window.lcHighlight = function (key) {
                        if (key === lcLastKey) return;
                        lcLastKey = key;
                        const win = previewWindow();
                        if (!win) return;
                        win.postMessage({ source: 'lc-admin-preview', type: 'highlight', key: key }, window.location.origin);
                    };

                    window.lcClearHighlight = function () {
                        lcLastKey = null;
                        const win = previewWindow();
                        if (!win) return;
                        win.postMessage({ source: 'lc-admin-preview', type: 'clear' }, window.location.origin);
                    };

                    window.lcSetPreviewLocale = function (locale) {
                        previewLocale = locale;
                        window.lcReloadPreview();
                    };

                    window.lcReloadPreview = function () {
                        const iframe = document.getElementById('lc-preview-iframe');
                        if (!iframe) return;
                        const url = new URL(iframe.src, window.location.origin);
                        url.searchParams.set('preview', '1');
                        url.searchParams.set('preview_locale', previewLocale);
                        iframe.src = url.toString();
                    };
                })();
            </script>
        @endpush
    @endonce
</x-app-layout>
