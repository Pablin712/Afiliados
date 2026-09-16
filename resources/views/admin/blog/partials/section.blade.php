@php
    // The edit modal is fed from this payload instead of a per-row onclick
    // argument: a post body can run to tens of thousands of characters, which
    // has no business living inside an HTML attribute.
    $blogPostsPayload = $blogPosts->mapWithKeys(fn ($post) => [$post->id => [
        'id' => $post->id,
        'slug' => $post->slug,
        'title_es' => $post->title_es,
        'title_en' => $post->title_en,
        'excerpt_es' => $post->excerpt_es,
        'excerpt_en' => $post->excerpt_en,
        'body_es' => $post->body_es,
        'body_en' => $post->body_en,
        'is_published' => (bool) $post->is_published,
        'published_at' => optional($post->published_at)->format('Y-m-d'),
        'sort_order' => $post->sort_order,
        'coverUrl' => $post->cover_image_path ? asset('storage/'.$post->cover_image_path) : null,
        'publicUrl' => route('blog.show', $post->slug),
        'title' => $post->title_es,
        'prices' => $post->prices->map(fn ($price) => [
            'region' => $price->region,
            'item' => $price->item,
            'price' => (string) $price->price,
            'currency' => $price->currency,
            'note' => $price->note,
        ])->values(),
    ]]);
@endphp

<div class="bg-white dark:bg-graphite-900 rounded-lg shadow border border-gray-200 dark:border-graphite-700 overflow-hidden">
    <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3 bg-gray-50 dark:bg-graphite-950 border-b border-gray-200 dark:border-graphite-800">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-graphite-300">
                {{ __('messages.admin.blog.heading') }}
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">
                {{ __('messages.admin.blog.hint') }}
                <a href="{{ route('blog.index') }}" target="_blank" rel="noopener" class="font-mono text-brand-600 hover:underline dark:text-brand-400">{{ route('blog.index') }}</a>
            </p>
        </div>

        @can('edit landing_content')
            <x-primary-button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'blog-post-create-modal' }))">
                {{ __('messages.admin.blog.buttons.create') }}
            </x-primary-button>
        @endcan
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-graphite-800">
            <thead class="bg-gray-50 dark:bg-graphite-950">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.blog.columns.cover') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.blog.columns.title_es') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.blog.columns.prices') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.blog.columns.published_at') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.blog.columns.sort_order') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.blog.columns.is_published') }}</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-graphite-400 uppercase">{{ __('messages.admin.testimonials.columns.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                @forelse ($blogPosts as $post)
                    <tr class="transition-colors hover:bg-amber-50 dark:hover:bg-amber-900/10">
                        <td class="px-4 py-2">
                            @if ($post->cover_image_path)
                                <img src="{{ asset('storage/'.$post->cover_image_path) }}" alt="{{ $post->title_es }}" class="h-12 w-20 rounded-md object-cover border border-gray-200 dark:border-graphite-700">
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <p class="text-sm text-gray-700 dark:text-graphite-200">{{ $post->title_es }}</p>
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="text-xs font-mono text-gray-400 hover:text-brand-600 dark:text-graphite-500 dark:hover:text-brand-400">/blog/{{ $post->slug }}</a>
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-graphite-200">
                            @if ($post->prices->isEmpty())
                                <span class="text-xs text-gray-400">—</span>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($post->prices->unique('region') as $price)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">{{ $price->region }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-graphite-200">{{ optional($post->published_at)->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-graphite-200">{{ $post->sort_order }}</td>
                        <td class="px-4 py-2 text-sm">
                            @if ($post->is_published)
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
                                        :title="__('messages.admin.blog.buttons.edit')"
                                        :onclick="'window.openBlogPostEditModal('.$post->id.')'"
                                    />
                                    <x-action-icon-button
                                        variant="delete"
                                        icon="delete"
                                        :title="__('messages.admin.blog.buttons.delete')"
                                        :onclick="'window.openBlogPostDeleteModal('.$post->id.')'"
                                    />
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
                            {{ __('messages.admin.blog.messages.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script type="application/json" id="blog-posts-data">@json($blogPostsPayload)</script>
