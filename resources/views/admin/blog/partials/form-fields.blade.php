{{--
    Shared body of the create and edit blog forms. Every field is bound with
    x-model against the surrounding blogPostForm() Alpine component, so the
    same markup works for a blank entry and for one being edited - no ids to
    keep unique between both modals.
--}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.title_es') }}</label>
        <input type="text" name="title_es" x-model="post.title_es" required maxlength="200" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.sort_order') }}</label>
        <input type="number" name="sort_order" x-model="post.sort_order" min="0" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.excerpt_es') }}</label>
    <textarea name="excerpt_es" x-model="post.excerpt_es" rows="2" maxlength="500" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
    <p class="mt-1 text-xs text-gray-400">{{ __('messages.admin.blog.forms.excerpt_hint') }}</p>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.body_es') }}</label>
    <textarea name="body_es" x-model="post.body_es" rows="12" required maxlength="40000" class="w-full rounded-md border-gray-300 font-mono text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
    <div class="mt-2 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 dark:border-graphite-700 dark:bg-graphite-950 dark:text-graphite-400">
        <p class="font-semibold text-gray-700 dark:text-graphite-300">{{ __('messages.admin.blog.forms.markdown_title') }}</p>
        <ul class="mt-1 space-y-0.5">
            <li><code>## {{ __('messages.admin.blog.forms.markdown_heading') }}</code></li>
            <li><code>**{{ __('messages.admin.blog.forms.markdown_bold') }}**</code></li>
            <li><code>- {{ __('messages.admin.blog.forms.markdown_list') }}</code></li>
            <li><code>[{{ __('messages.admin.blog.forms.markdown_link_text') }}](https://...)</code></li>
            <li><code>![{{ __('messages.admin.blog.forms.markdown_image_text') }}](https://...)</code></li>
        </ul>
    </div>
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.forms.cover_label') }}</label>
    <input type="file" name="cover" accept="image/*" class="w-full text-sm text-gray-700 dark:text-graphite-200">
    <p class="mt-1 text-xs text-gray-400">{{ __('messages.admin.blog.forms.cover_hint') }}</p>
    <template x-if="currentCoverUrl">
        <img :src="currentCoverUrl" alt="" class="mt-2 h-24 w-40 rounded-md object-cover border border-gray-200 dark:border-graphite-700">
    </template>
</div>

<details class="rounded-md border border-gray-200 dark:border-graphite-700">
    <summary class="cursor-pointer px-3 py-2 text-sm font-medium text-gray-700 dark:text-graphite-300">
        {{ __('messages.admin.blog.forms.english_section') }}
    </summary>

    <div class="space-y-3 px-3 pb-3">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.title_en') }}</label>
            <input type="text" name="title_en" x-model="post.title_en" maxlength="200" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.excerpt_en') }}</label>
            <textarea name="excerpt_en" x-model="post.excerpt_en" rows="2" maxlength="500" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.body_en') }}</label>
            <textarea name="body_en" x-model="post.body_en" rows="8" maxlength="40000" class="w-full rounded-md border-gray-300 font-mono text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
        </div>

        <p class="text-xs text-gray-400">{{ __('messages.admin.blog.forms.english_hint') }}</p>
    </div>
</details>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.published_at') }}</label>
        <input type="date" name="published_at" x-model="post.published_at" class="w-full rounded-md border-gray-300 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.blog.columns.slug') }}</label>
        <input type="text" name="slug" x-model="post.slug" maxlength="200" class="w-full rounded-md border-gray-300 font-mono text-xs dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
        <p class="mt-1 text-xs text-gray-400">{{ __('messages.admin.blog.forms.slug_hint') }}</p>
    </div>

    <div class="flex items-end pb-1">
        <label class="flex items-center gap-2">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" x-model="post.is_published" class="rounded border-gray-300 dark:border-graphite-700">
            <span class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.admin.blog.columns.is_published') }}</span>
        </label>
    </div>
</div>
