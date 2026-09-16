<x-public-layout :title="$post->localized('title')" :meta-description="$post->summary(160)" :content="$content">
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <a href="{{ route('blog.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-500 dark:text-brand-400">
            <span aria-hidden="true">&larr;</span> {{ __('messages.blog.back_to_index') }}
        </a>

        <header class="mt-5">
            <p class="text-xs uppercase tracking-wide text-gray-400 dark:text-graphite-500">
                {{ optional($post->published_at)->translatedFormat('d M Y') }}
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

        @php($pricesByRegion = $post->pricesByRegion())

        @if ($pricesByRegion->isNotEmpty())
            <section class="mt-12 rounded-3xl border border-gray-200 bg-white p-6 sm:p-8 shadow-sm dark:border-graphite-800 dark:bg-graphite-900">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-graphite-100">
                    {{ __('messages.blog.prices_title') }}
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-graphite-300">
                    {{ __('messages.blog.prices_hint') }}
                </p>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach ($pricesByRegion as $region => $prices)
                        <div class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-graphite-700 dark:bg-graphite-950/50">
                            <p class="inline-flex w-fit items-center rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-brand-700 dark:border-brand-900 dark:bg-brand-950/40 dark:text-brand-300">
                                {{ $region }}
                            </p>

                            <ul class="mt-4 space-y-3">
                                @foreach ($prices as $price)
                                    <li class="flex items-start justify-between gap-4 border-b border-gray-200 pb-3 last:border-0 last:pb-0 dark:border-graphite-800">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 dark:text-graphite-100">
                                                {{ $price->item ?: $post->localized('title') }}
                                            </p>
                                            @if ($price->note)
                                                <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ $price->note }}</p>
                                            @endif
                                        </div>
                                        <p class="shrink-0 text-lg font-bold text-emerald-700 dark:text-emerald-400">
                                            {{ $price->formattedPrice() }}
                                        </p>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if (! empty($content['contact_whatsapp_number_raw']))
                        <a
                            href="https://wa.me/{{ $content['contact_whatsapp_number_raw'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center px-5 py-3 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-500"
                        >
                            {{ __('messages.blog.prices_cta_whatsapp') }}
                        </a>
                    @endif

                    <a href="{{ url('/#contacto') }}" class="inline-flex items-center px-5 py-3 rounded-md border border-gray-300 text-sm font-semibold text-gray-700 hover:text-brand-600 hover:border-brand-400 dark:border-graphite-700 dark:text-graphite-200 dark:hover:text-brand-400">
                        {{ __('messages.blog.prices_cta_contact') }}
                    </a>
                </div>
            </section>
        @endif

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
