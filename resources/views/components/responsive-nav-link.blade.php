@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-500 text-start text-base font-medium text-brand-700 bg-brand-50 dark:text-brand-300 dark:bg-graphite-800 focus:outline-none focus:text-brand-700 dark:focus:text-brand-200 focus:bg-brand-100 dark:focus:bg-graphite-800 focus:border-brand-500 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-brand-700 hover:bg-gray-50 hover:border-gray-300 dark:text-graphite-300 dark:hover:text-brand-300 dark:hover:bg-graphite-800 dark:hover:border-graphite-700 focus:outline-none focus:text-brand-700 dark:focus:text-brand-300 focus:bg-gray-50 dark:focus:bg-graphite-800 focus:border-gray-300 dark:focus:border-graphite-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
