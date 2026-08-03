@props([
    'src' => asset('storage/aet-logo-light.png'),
    'srcDark' => asset('storage/aet-logo-dark.png'),
    'alt' => config('app.name', 'Laravel') . ' logo',
])

<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => 'object-contain' . ($srcDark ? ' dark:hidden' : '')]) }}>
@if ($srcDark)
    <img src="{{ $srcDark }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => 'hidden object-contain dark:block']) }}>
@endif
