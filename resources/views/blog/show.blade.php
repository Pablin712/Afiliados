<x-public-layout :title="$post->localized('title')" :meta-description="$post->summary(160)" :content="$content">
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <a href="{{ route('blog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500 dark:text-brand-400">
            <span aria-hidden="true">&larr;</span> {{ __('messages.blog.back_to_index') }}
        </a>

        <header class="mt-5">
            <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-graphite-500">
                {{ optional($post->published_at)->translatedFormat('d M Y') }}
                @if ($post->author)
                    · {{ __('messages.blog.by_author', ['name' => $post->author->name]) }}
                @endif
            </p>

            <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-gray-900 dark:text-graphite-100">
                {{ $post->localized('title') }}
            </h1>

            @if (filled($post->localized('excerpt')))
                <p class="mt-4 text-base sm:text-lg leading-relaxed text-gray-600 dark:text-graphite-300">
                    {{ $post->localized('excerpt') }}
                </p>
            @endif
        </header>

        @if ($post->cover_image_path)
            <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-gray-100 dark:border-graphite-800 dark:bg-graphite-800">
                <img src="{{ asset('storage/'.$post->cover_image_path) }}" alt="{{ $post->localized('title') }}" class="w-full h-auto object-cover">
            </div>
        @endif

        {{-- Rendered from the admin's markdown, with any raw HTML escaped. --}}
        <div class="blog-body mt-8">
            {!! $post->bodyHtml() !!}
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-12">
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                    {{ __('messages.blog.related_title') }}
                </h2>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-5">
                    @foreach ($related as $item)
                        <a href="{{ route('blog.show', $item->slug) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-brand-300 dark:border-graphite-800 dark:bg-graphite-900 dark:hover:border-brand-700">
                            <div class="aspect-[16/9] bg-gray-100 dark:bg-graphite-800 overflow-hidden">
                                @if ($item->cover_image_path)
                                    <img src="{{ asset('storage/'.$item->cover_image_path) }}" alt="{{ $item->localized('title') }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                @else
                                    <div class="flex h-full w-full items-center justify-center">
                                        <x-application-logo class="h-10 w-auto opacity-40" />
                                    </div>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 dark:text-graphite-100 dark:group-hover:text-brand-400">
                                    {{ $item->localized('title') }}
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</x-public-layout>
