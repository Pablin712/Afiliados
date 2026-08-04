@php
    /** @var string $inputName */
    $inputName ??= 'style';
    $selected ??= 'primary';
@endphp
<div>
    <label class="block text-sm font-medium text-gray-700 dark:text-graphite-300 mb-1">{{ __('messages.admin.hero_buttons.columns.style') }}</label>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" data-style-swatches>
        @foreach (\App\Models\HeroButton::styles() as $key => $classes)
            <button
                type="button"
                data-style="{{ $key }}"
                onclick="window.pickHeroButtonStyle(this)"
                class="rounded-lg border-2 p-1 transition focus:outline-none {{ $selected === $key ? 'border-brand-500' : 'border-transparent' }}"
            >
                <span class="{{ $classes }} pointer-events-none block w-full truncate text-center !px-2 !py-1.5 !text-xs">
                    {{ \App\Models\HeroButton::styleLabels()[$key] }}
                </span>
            </button>
        @endforeach
    </div>
    <input type="hidden" name="{{ $inputName }}" value="{{ $selected }}" data-style-input>
</div>
