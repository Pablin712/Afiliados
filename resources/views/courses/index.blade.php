<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
                    {{ __('messages.courses.title') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">
                    {{ __('messages.courses.subtitle') }}
                </p>
            </div>

            @role('admin')
                <a href="{{ route('admin.courses.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-brand-400 hover:text-brand-600 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                    {{ __('messages.courses.open_admin') }}
                </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            @if ($modules->isEmpty())
                <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm dark:border-graphite-700 dark:bg-graphite-900">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-brand-600 dark:text-brand-400">{{ __('messages.courses.badge') }}</p>
                    <h3 class="mt-4 text-2xl font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.courses.empty_title') }}</h3>
                    <p class="mt-3 text-sm text-gray-600 dark:text-graphite-300">{{ __('messages.courses.empty_description') }}</p>
                    @role('admin')
                        <a href="{{ route('admin.courses.index') }}" class="mt-5 inline-flex items-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">
                            {{ __('messages.courses.empty_admin_cta') }}
                        </a>
                    @endrole
                </div>
            @else
                <section class="overflow-hidden rounded-[2rem] border border-gray-200 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-950 p-6 shadow-[0_32px_80px_-40px_rgba(15,23,42,0.95)] dark:border-brand-900/40 sm:p-8 lg:p-10">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,0.8fr)] lg:items-end">
                        <div>
                            <p class="inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-[0.26em] text-brand-200">
                                {{ __('messages.courses.badge') }}
                            </p>
                            <h3 class="mt-5 max-w-3xl text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                                {{ __('messages.courses.hero_title') }}
                            </h3>
                            <p class="mt-4 max-w-3xl text-sm leading-7 text-slate-300 sm:text-base">
                                {{ __('messages.courses.hero_description') }}
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">{{ __('messages.courses.module_label') }}</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $modules->count() }}</p>
                                <p class="mt-1 text-xs text-slate-300">{{ __('messages.courses.modules_count', ['count' => $modules->count()]) }}</p>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Videos</p>
                                <p class="mt-2 text-3xl font-semibold text-white">{{ $videosCount }}</p>
                                <p class="mt-1 text-xs text-slate-300">{{ __('messages.courses.videos_count', ['count' => $videosCount]) }}</p>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-5 backdrop-blur">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Experiencia</p>
                                <p class="mt-2 text-2xl font-semibold text-white">{{ __('messages.courses.student_mode') }}</p>
                                <p class="mt-1 text-xs text-slate-300">Campus visual, modulos y reproduccion integrada.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="grid gap-6 xl:grid-cols-[330px_minmax(0,1fr)]">
                    <aside class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-graphite-800 dark:bg-graphite-900 xl:sticky xl:top-24 xl:self-start">
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-gray-500 dark:text-graphite-400">{{ __('messages.courses.module_label') }}</p>
                        <div class="mt-4 space-y-3">
                            @foreach ($modules as $module)
                                <div class="rounded-2xl border {{ $selectedModule?->id === $module->id ? 'border-brand-300 bg-brand-50 dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-gray-200 bg-gray-50 dark:border-graphite-800 dark:bg-graphite-950/60' }} p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $module->name }}</p>
                                                @if ($module->for_free)
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">{{ __('messages.admin.courses.for_free_badge') }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.courses.module_videos', ['count' => $module->videos->count()]) }}</p>
                                        </div>
                                        <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-semibold {{ $selectedModule?->id === $module->id ? 'bg-white text-brand-700 dark:bg-graphite-900 dark:text-brand-300' : 'bg-gray-200 text-gray-700 dark:bg-graphite-800 dark:text-graphite-300' }}">
                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>

                                    <div class="mt-4 space-y-2">
                                        @foreach ($module->videos as $video)
                                            <a href="{{ route('courses.index', ['module' => $module->slug, 'video' => $video->slug]) }}" class="block rounded-xl border px-3 py-3 text-sm transition {{ $selectedVideo?->id === $video->id ? 'border-brand-300 bg-white text-brand-700 shadow-sm dark:border-brand-500/40 dark:bg-brand-500/10 dark:text-brand-200' : 'border-transparent bg-white/70 text-gray-700 hover:border-gray-200 hover:bg-white dark:bg-graphite-900 dark:text-graphite-300 dark:hover:border-graphite-700' }}">
                                                <span class="block font-medium">{{ $video->title }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </aside>

                    <section class="space-y-6">
                        @if ($selectedVideo)
                            <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                                <div class="aspect-video bg-black" oncontextmenu="return false;">
                                    <video
                                        class="h-full w-full"
                                        controls
                                        controlsList="nodownload noplaybackrate"
                                        disablePictureInPicture
                                        disableRemotePlayback
                                        playsinline
                                        preload="metadata"
                                        oncontextmenu="return false;"
                                        src="{{ $selectedVideo->streamUrl() }}"
                                    ></video>
                                </div>

                                <div class="p-6 sm:p-8">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-brand-700 dark:bg-brand-500/10 dark:text-brand-300">{{ __('messages.courses.video_player_label') }}</span>
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.courses.video_meta_module', ['module' => $selectedModule?->name ?? '-']) }}</span>
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.courses.video_meta_size', ['size' => number_format(((int) $selectedVideo->file_size) / 1048576, 1)]) }}</span>
                                    </div>

                                    <h3 class="mt-5 text-2xl font-semibold tracking-tight text-gray-900 dark:text-graphite-100">{{ $selectedVideo->title }}</h3>
                                    <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-graphite-300">
                                        {{ $selectedVideo->description ?: __('messages.courses.video_description_fallback') }}
                                    </p>
                                    <p class="mt-4 text-sm font-medium text-brand-700 dark:text-brand-300">{{ __('messages.courses.continue_learning') }}</p>
                                </div>
                            </div>

                            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.courses.playlist_title') }}</h4>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-graphite-400">{{ $selectedModule?->description }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-graphite-800 dark:text-graphite-300">{{ __('messages.courses.module_videos', ['count' => $selectedModule?->videos->count() ?? 0]) }}</span>
                                </div>

                                <div class="mt-5 grid gap-3 md:grid-cols-2">
                                    @forelse ($selectedModule?->videos ?? [] as $video)
                                        <a href="{{ route('courses.index', ['module' => $selectedModule->slug, 'video' => $video->slug]) }}" class="rounded-2xl border p-4 transition {{ $selectedVideo->id === $video->id ? 'border-brand-300 bg-brand-50 dark:border-brand-500/40 dark:bg-brand-500/10' : 'border-gray-200 bg-gray-50 hover:border-gray-300 hover:bg-white dark:border-graphite-800 dark:bg-graphite-950/60 dark:hover:border-graphite-700' }}">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-graphite-100">{{ $video->title }}</p>
                                            <p class="mt-2 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.courses.video_meta_size', ['size' => number_format(((int) $video->file_size) / 1048576, 1)]) }}</p>
                                        </a>
                                    @empty
                                        <div class="rounded-2xl border border-dashed border-gray-300 p-5 text-sm text-gray-500 dark:border-graphite-700 dark:text-graphite-400">
                                            {{ __('messages.courses.playlist_empty') }}
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        @endif
                    </section>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
