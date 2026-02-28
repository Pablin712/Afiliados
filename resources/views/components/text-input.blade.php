@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 dark:placeholder-graphite-500 dark:focus:border-brand-500 dark:focus:ring-brand-500']) }}>
