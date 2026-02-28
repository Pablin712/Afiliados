@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand-500 text-sm font-medium leading-5 text-brand-600 dark:text-brand-400 focus:outline-none focus:border-brand-600 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-brand-600 hover:border-gray-300 dark:text-graphite-300 dark:hover:text-brand-400 dark:hover:border-graphite-600 focus:outline-none focus:text-brand-600 dark:focus:text-brand-400 focus:border-gray-300 dark:focus:border-graphite-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
