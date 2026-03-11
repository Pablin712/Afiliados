@props([
    'variant' => 'neutral',
    'icon' => 'edit',
    'title' => '',
])

@php
$variants = [
    'neutral' => 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50 hover:text-gray-800 dark:bg-graphite-900 dark:border-graphite-700 dark:text-graphite-300 dark:hover:bg-graphite-800 dark:hover:text-graphite-100',
    'edit' => 'bg-brand-50 border-brand-200 text-brand-700 hover:bg-brand-100 dark:bg-brand-900/20 dark:border-brand-700 dark:text-brand-300 dark:hover:bg-brand-900/40',
    'delete' => 'bg-red-50 border-red-200 text-red-700 hover:bg-red-100 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/40',
    'create' => 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/40',
    'approve' => 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/40',
    'view' => 'bg-sky-50 border-sky-200 text-sky-700 hover:bg-sky-100 dark:bg-sky-900/20 dark:border-sky-700 dark:text-sky-300 dark:hover:bg-sky-900/40',
];

$buttonClasses = 'inline-flex items-center justify-center h-9 w-9 rounded-lg border transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 dark:focus:ring-offset-graphite-900';
$variantClasses = $variants[$variant] ?? $variants['neutral'];
$ariaLabel = $title !== '' ? $title : ucfirst($icon);
@endphp

<button {{ $attributes->merge(['type' => 'button', 'class' => $buttonClasses.' '.$variantClasses, 'title' => $ariaLabel, 'aria-label' => $ariaLabel]) }}>
    @if ($icon === 'edit')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 1 1 3.182 3.182L8.25 18.464 3 21l2.536-5.25 11.326-11.263Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5l4 4"/>
        </svg>
    @elseif ($icon === 'delete')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v3"/>
        </svg>
    @elseif ($icon === 'plus')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
        </svg>
    @elseif ($icon === 'approve')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    @elseif ($icon === 'reject')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    @elseif ($icon === 'eye')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
        </svg>
    @else
        <span class="text-xs font-semibold">{{ strtoupper(substr($icon, 0, 1)) }}</span>
    @endif

    <span class="sr-only">{{ $ariaLabel }}</span>
</button>