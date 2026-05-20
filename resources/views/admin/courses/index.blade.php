<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                    {{ __('messages.admin.courses.title') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">
                    {{ __('messages.admin.courses.subtitle') }}
                </p>
            </div>

            <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-brand-400 hover:text-brand-600 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                {{ __('messages.admin.courses.view_student_catalog') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
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

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs uppercase tracking-[0.22em] text-gray-500 dark:text-graphite-400">{{ __('messages.admin.courses.stats.modules') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-graphite-100">{{ $modules->count() }}</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs uppercase tracking-[0.22em] text-gray-500 dark:text-graphite-400">{{ __('messages.admin.courses.stats.videos') }}</p>
                    <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-graphite-100">{{ $videosCount }}</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <p class="text-xs uppercase tracking-[0.22em] text-gray-500 dark:text-graphite-400">{{ __('messages.admin.courses.stats.storage') }}</p>
                    <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.courses.stats.storage_value') }}</p>
                </div>
            </div>

            <div class="rounded-3xl border border-brand-200 bg-brand-50/60 p-6 shadow-sm dark:border-brand-900/40 dark:bg-brand-950/20">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-brand-900 dark:text-brand-200">{{ __('messages.admin.courses.import_title') }}</h3>
                        <p class="mt-1 text-sm text-brand-800 dark:text-brand-300">{{ __('messages.admin.courses.import_description') }}</p>
                    </div>

                    <form method="POST" action="{{ route('admin.courses.import-existing') }}">
                        @csrf
                        <x-primary-button>{{ __('messages.admin.courses.import_button') }}</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.courses.module_form_title') }}</h3>

                    <form method="POST" action="{{ route('admin.courses.modules.store') }}" class="mt-5 grid gap-4">
                        @csrf

                        <div>
                            <x-input-label for="course_module_name" :value="__('messages.admin.courses.field_name')" />
                            <x-text-input id="course_module_name" name="name" class="mt-1 block w-full" required />
                        </div>

                        <div>
                            <x-input-label for="course_module_description" :value="__('messages.admin.courses.field_description')" />
                            <textarea id="course_module_description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
                        </div>

                        <div>
                            <x-input-label for="course_module_sort_order" :value="__('messages.admin.courses.field_sort_order')" />
                            <x-text-input id="course_module_sort_order" type="number" min="0" max="999" name="sort_order" class="mt-1 block w-full" value="0" />
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-graphite-300">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500" checked>
                            {{ __('messages.admin.courses.field_is_active') }}
                        </label>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-graphite-300">
                            <input type="checkbox" name="for_free" value="1" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                            {{ __('messages.admin.courses.field_for_free') }}
                        </label>

                        <div class="pt-2">
                            <x-primary-button>{{ __('messages.admin.courses.create_module') }}</x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.courses.video_form_title') }}</h3>

                    <form id="video-upload-form" method="POST" action="{{ route('admin.courses.videos.store') }}" enctype="multipart/form-data" class="mt-5 grid gap-4">
                        @csrf

                        <div>
                            <x-input-label for="course_video_module" :value="__('messages.admin.courses.field_module')" />
                            <select id="course_video_module" name="course_module_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100" required>
                                <option value="">--</option>
                                @foreach ($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="course_video_title" :value="__('messages.admin.courses.field_name')" />
                            <x-text-input id="course_video_title" name="title" class="mt-1 block w-full" required />
                        </div>

                        <div>
                            <x-input-label for="course_video_description" :value="__('messages.admin.courses.field_description')" />
                            <textarea id="course_video_description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
                        </div>

                        <div>
                            <x-input-label for="course_video_file" :value="__('messages.admin.courses.field_video_file')" />
                            <input id="course_video_file" type="file" name="video" accept="video/mp4,video/webm,video/quicktime" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200" required>
                            <p class="mt-2 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.courses.field_video_help') }}</p>
                        </div>

                        <div>
                            <x-input-label for="course_video_sort_order" :value="__('messages.admin.courses.field_sort_order')" />
                            <x-text-input id="course_video_sort_order" type="number" min="0" max="999" name="sort_order" class="mt-1 block w-full" value="0" />
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-graphite-300">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500" checked>
                            {{ __('messages.admin.courses.field_is_active') }}
                        </label>

                        <div class="pt-2">
                            <x-primary-button id="video-upload-btn">{{ __('messages.admin.courses.upload_video') }}</x-primary-button>
                        </div>

                        {{-- Barra de progreso --}}
                        <div id="upload-progress-container" class="hidden space-y-2 rounded-2xl border border-brand-200 bg-brand-50 p-4 dark:border-brand-900/40 dark:bg-brand-950/20">
                            <div class="flex items-center justify-between text-sm font-medium text-brand-800 dark:text-brand-300">
                                <span id="upload-progress-status">Subiendo video...</span>
                                <span id="upload-progress-text">0%</span>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-brand-200 dark:bg-brand-900/60">
                                <div id="upload-progress-bar" class="h-full w-0 rounded-full bg-brand-500 transition-all duration-200"></div>
                            </div>
                            <p class="text-xs text-amber-700 dark:text-amber-400">No cierre esta pestaña mientras se sube el video.</p>
                        </div>

                        <div id="upload-error-container" class="hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300"></div>
                    </form>

                    <script>
                    (function () {
                        var form       = document.getElementById('video-upload-form');
                        var fileInput  = document.getElementById('course_video_file');
                        var submitBtn  = document.getElementById('video-upload-btn');
                        var container  = document.getElementById('upload-progress-container');
                        var bar        = document.getElementById('upload-progress-bar');
                        var pct        = document.getElementById('upload-progress-text');
                        var status     = document.getElementById('upload-progress-status');
                        var errBox     = document.getElementById('upload-error-container');
                        var MAX_BYTES  = 1024 * 1024 * 1024; // 1 GB

                        fileInput.addEventListener('change', function () {
                            var file = this.files[0];
                            if (file && file.size > MAX_BYTES) {
                                errBox.textContent = 'El archivo supera el límite de 1 GB.';
                                errBox.classList.remove('hidden');
                                this.value = '';
                            } else {
                                errBox.classList.add('hidden');
                            }
                        });

                        form.addEventListener('submit', function (e) {
                            e.preventDefault();

                            var file = fileInput.files[0];
                            if (!file) return;

                            if (file.size > MAX_BYTES) {
                                errBox.textContent = 'El archivo supera el límite de 1 GB.';
                                errBox.classList.remove('hidden');
                                return;
                            }

                            errBox.classList.add('hidden');
                            container.classList.remove('hidden');
                            submitBtn.disabled = true;

                            bar.className = 'h-full w-0 rounded-full bg-brand-500 transition-all duration-200';
                            bar.style.width = '0%';
                            pct.textContent = '0%';
                            status.textContent = 'Subiendo video, por favor espere...';

                            var xhr = new XMLHttpRequest();

                            xhr.upload.addEventListener('progress', function (e) {
                                if (e.lengthComputable) {
                                    var p = Math.round((e.loaded / e.total) * 100);
                                    bar.style.width = p + '%';
                                    pct.textContent = p + '%';
                                    status.textContent = p < 100
                                        ? 'Subiendo video (' + p + '%)... Por favor espere.'
                                        : 'Procesando en el servidor...';
                                }
                            });

                            xhr.addEventListener('load', function () {
                                if (xhr.status >= 200 && xhr.status < 300) {
                                    bar.style.width = '100%';
                                    pct.textContent = '100%';
                                    bar.className = 'h-full w-full rounded-full bg-green-500';
                                    status.textContent = '¡Video subido exitosamente! Recargando...';
                                    setTimeout(function () { window.location.reload(); }, 1200);
                                } else {
                                    var msg = 'Error al subir el video.';
                                    try {
                                        var res = JSON.parse(xhr.responseText);
                                        if (res.errors) {
                                            msg = Object.values(res.errors).flat().join(' ');
                                        } else if (res.message) {
                                            msg = res.message;
                                        }
                                    } catch (err) {}
                                    bar.className = 'h-full rounded-full bg-red-500';
                                    status.textContent = 'Error al subir.';
                                    errBox.textContent = msg;
                                    errBox.classList.remove('hidden');
                                    submitBtn.disabled = false;
                                }
                            });

                            xhr.addEventListener('error', function () {
                                bar.className = 'h-full rounded-full bg-red-500';
                                status.textContent = 'Error de conexión.';
                                errBox.textContent = 'No se pudo conectar con el servidor. Verifique su conexión e intente nuevamente.';
                                errBox.classList.remove('hidden');
                                submitBtn.disabled = false;
                            });

                            xhr.open('POST', form.action);
                            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                            xhr.setRequestHeader('Accept', 'application/json');
                            xhr.send(new FormData(form));
                        });
                    }());
                    </script>
                </div>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.courses.catalog_title') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">{{ __('messages.admin.courses.catalog_description') }}</p>

                <div class="mt-6 space-y-5">
                    @forelse ($modules as $module)
                        <article class="rounded-3xl border border-gray-200 bg-gray-50 p-5 dark:border-graphite-800 dark:bg-graphite-950/60">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ $module->name }}</span>
                                        <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-600 dark:bg-graphite-900 dark:text-graphite-300">{{ __('messages.courses.module_videos', ['count' => $module->videos->count()]) }}</span>
                                        @if ($module->for_free)
                                            <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">{{ __('messages.admin.courses.for_free_badge') }}</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">{{ __('messages.admin.courses.members_only_badge') }}</span>
                                        @endif
                                    </div>
                                    @if ($module->description)
                                        <p class="mt-3 max-w-3xl text-sm text-gray-600 dark:text-graphite-300">{{ $module->description }}</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('courses.index', ['module' => $module->slug]) }}" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-brand-400 hover:text-brand-600 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                        {{ __('messages.admin.courses.video_study_view') }}
                                    </a>
                                    <form method="POST" action="{{ route('admin.courses.modules.toggle-free', $module) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition
                                                {{ $module->for_free
                                                    ? 'border-emerald-300 bg-emerald-50 text-emerald-700 hover:border-emerald-400 hover:bg-emerald-100 dark:border-emerald-700/50 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:border-emerald-500'
                                                    : 'border-amber-300 bg-amber-50 text-amber-700 hover:border-amber-400 hover:bg-amber-100 dark:border-amber-700/50 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:border-amber-500' }}">
                                            {{ $module->for_free
                                                ? __('messages.admin.courses.toggle_to_members_only')
                                                : __('messages.admin.courses.toggle_to_free') }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.courses.modules.destroy', $module) }}">
                                        @csrf
                                        @method('DELETE')
                                        <x-secondary-button type="submit">{{ __('messages.admin.courses.module_delete') }}</x-secondary-button>
                                    </form>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3">
                                @forelse ($module->videos as $video)
                                    <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-graphite-800 dark:bg-graphite-900">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $video->title }}</p>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ number_format(((int) $video->file_size) / 1048576, 1) }} MB</p>
                                                @if ($video->description)
                                                    <p class="mt-3 text-sm text-gray-600 dark:text-graphite-300">{{ $video->description }}</p>
                                                @endif
                                            </div>

                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('courses.videos.stream', $video) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-sky-400 hover:text-sky-600 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-sky-500 dark:hover:text-sky-300">
                                                    {{ __('messages.admin.courses.video_open') }}
                                                </a>
                                                <a href="{{ route('courses.index', ['module' => $module->slug, 'video' => $video->slug]) }}" class="inline-flex items-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-brand-400 hover:text-brand-600 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                                                    {{ __('messages.admin.courses.video_study_view') }}
                                                </a>
                                                <form method="POST" action="{{ route('admin.courses.videos.destroy', $video) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-secondary-button type="submit">{{ __('messages.admin.courses.video_delete') }}</x-secondary-button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-gray-300 p-5 text-sm text-gray-500 dark:border-graphite-700 dark:text-graphite-400">
                                        {{ __('messages.admin.courses.module_empty') }}
                                    </div>
                                @endforelse
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 p-6 text-sm text-gray-500 dark:border-graphite-700 dark:text-graphite-400">
                            {{ __('messages.admin.courses.catalog_description') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
