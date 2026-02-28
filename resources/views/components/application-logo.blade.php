@props([
    'src' => asset('storage/siglas.png'),
    'alt' => config('app.name', 'Laravel') . ' logo',
])

<img src="{{ $src }}" alt="{{ $alt }}" {{ $attributes }}>
