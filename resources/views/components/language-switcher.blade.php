<div class="language-switcher">
    <div class="flex gap-1">
        @foreach ($availableLocales as $locale)
            <a
                href="{{ request()->fullUrlWithQuery(['locale' => $locale]) }}"
                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md transition-colors duration-200
                    @if ($currentLocale === $locale)
                        bg-brand-500 text-white shadow-sm
                    @else
                        bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-graphite-700 dark:text-graphite-200 dark:hover:bg-graphite-600
                    @endif
                "
                title="{{ __('messages.switch_to') }} {{ $localeNames[$locale] ?? $locale }}"
            >
                {{ strtoupper($locale) }}
            </a>
        @endforeach
    </div>
</div>
