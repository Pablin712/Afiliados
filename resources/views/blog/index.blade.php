<x-public-layout :title="__('messages.blog.title')" :meta-description="__('messages.blog.subtitle')" :content="$content">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <section class="rounded-3xl border border-gray-200 bg-white p-8 sm:p-10 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
            <p class="inline-flex items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold tracking-wide text-brand-700 dark:border-brand-900 dark:bg-brand-950/50 dark:text-brand-300">
                {{ __('messages.blog.badge') }}
            </p>

            <h1 class="mt-5 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-graphite-100">
                {{ __('messages.blog.title') }}
            </h1>

            <p class="mt-4 max-w-3xl text-base sm:text-lg text-gray-600 dark:text-graphite-300 leading-relaxed">
                {{ __('messages.blog.subtitle') }}
            </p>
        </section>

        @if ($posts->isEmpty())
            <div class="mt-10 rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-graphite-700 dark:bg-graphite-900">
                <p class="text-sm text-gray-500 dark:text-graphite-400">{{ __('messages.blog.empty') }}</p>
            </div>
        @else
            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($posts as $post)
                    <article class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-brand-300 hover:shadow-md dark:border-graphite-800 dark:bg-graphite-900 dark:hover:border-brand-700">
                        <a href="{{ route('blog.show', $post->slug) }}" class="block aspect-[16/9] bg-gray-100 dark:bg-graphite-800 overflow-hidden">
                            @if ($post->cover_image_path)
                                <img
                                    src="{{ asset('storage/'.$post->cover_image_path) }}"
                                    alt="{{ $post->localized('title') }}"
                                    loading="lazy"
                                    class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                                >
                            @else
                                <div class="flex h-full w-full items-center justify-center">
                                    <x-application-logo class="h-12 w-auto opacity-40" />
                                </div>
                            @endif
                        </a>

                        <div class="flex flex-1 flex-col p-5">
                            <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-graphite-500">
                                {{ optional($post->published_at)->translatedFormat('d M Y') }}
                            </p>

                            <h2 class="mt-2 text-lg font-semibold text-gray-900 dark:text-graphite-100">
                                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-brand-600 dark:hover:text-brand-400">
                                    {{ $post->localized('title') }}
                                </a>
                            </h2>

                            <p class="mt-2 flex-1 text-sm leading-6 text-gray-600 dark:text-graphite-300">
                                {{ $post->summary() }}
                            </p>

                            <a href="{{ route('blog.show', $post->slug) }}" class="mt-5 inline-flex items-center text-sm font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">
                                {{ __('messages.blog.read_more') }}
                                <span aria-hidden="true" class="ml-1">&rarr;</span>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</x-public-layout>
