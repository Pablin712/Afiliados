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

<div class="rounded-md border border-gray-200 dark:border-graphite-700">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-gray-50 px-3 py-2 dark:border-graphite-700 dark:bg-graphite-950">
        <div>
            <p class="text-sm font-semibold text-gray-700 dark:text-graphite-300">{{ __('messages.admin.blog.forms.prices_title') }}</p>
            <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.admin.blog.forms.prices_hint') }}</p>
        </div>
        <button type="button" x-on:click="addPrice()" class="inline-flex items-center rounded-md border border-brand-300 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:border-brand-800 dark:bg-brand-950/30 dark:text-brand-300">
            + {{ __('messages.admin.blog.forms.add_price') }}
        </button>
    </div>

    <div class="space-y-2 p-3">
        <template x-for="(row, index) in prices" :key="index">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-12 items-start">
                <div class="sm:col-span-3">
                    <input type="text" :name="'prices['+index+'][region]'" x-model="row.region" maxlength="120" placeholder="{{ __('messages.admin.blog.forms.region_placeholder') }}" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>
                <div class="sm:col-span-3">
                    <input type="text" :name="'prices['+index+'][item]'" x-model="row.item" maxlength="150" placeholder="{{ __('messages.admin.blog.forms.item_placeholder') }}" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>
                <div class="sm:col-span-2">
                    <input type="number" step="0.01" min="0" :name="'prices['+index+'][price]'" x-model="row.price" placeholder="0.00" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>
                <div class="sm:col-span-1">
                    <select :name="'prices['+index+'][currency]'" x-model="row.currency" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <input type="text" :name="'prices['+index+'][note]'" x-model="row.note" maxlength="255" placeholder="{{ __('messages.admin.blog.forms.note_placeholder') }}" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                </div>
                <div class="sm:col-span-1 flex sm:justify-end">
                    <button type="button" x-on:click="removePrice(index)" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/30" title="{{ __('messages.admin.blog.forms.remove_price') }}">
                        &times;
                    </button>
                </div>
            </div>
        </template>

        <template x-if="prices.length === 0">
            <p class="text-xs text-gray-400">{{ __('messages.admin.blog.forms.no_prices') }}</p>
        </template>
    </div>
</div>

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
