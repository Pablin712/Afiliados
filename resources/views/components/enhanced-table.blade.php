@props([
    'id' => 'enhanced-table',
    'csv' => true,
    'excel' => true,
    'print' => true,
    'json' => true,
    'pdf' => true,
    'headers' => [],
    'table_void' => false,
    'serverSide' => false,      // Nuevo: Forzar server-side
    'searchUrl' => '',          // Nuevo: URL para búsquedas AJAX
    'totalRecords' => 0,        // Nuevo: Total de registros (para server-side)
])

@php
    // Auto-detectar server-side si no se especifica
    $autoServerSide = $serverSide || $totalRecords > 500;
@endphp

<!-- Controles de tabla mejorados -->
<div class="space-y-4">
    <!-- Header de controles -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <!-- Fila de botones principales -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Slot para botones personalizados -->
                @isset($buttons)
                    {{ $buttons }}
                @endisset

                <!-- Botones de exportación -->
                @if ($csv)
                    <x-primary-button id="{{ $id }}-export-csv" class="text-sm px-3 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('Export CSV') }}
                    </x-primary-button>
                @endif

                @if ($excel)
                    <x-secondary-button id="{{ $id }}-export-excel" class="text-sm px-3 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('Export Excel') }}
                    </x-secondary-button>
                @endif

                @if ($json)
                    <x-primary-button id="{{ $id }}-export-json" class="text-sm px-3 py-2 bg-orange-600 hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        {{ __('Export JSON') }}
                    </x-primary-button>
                @endif

                @if ($print)
                    <x-primary-button id="{{ $id }}-print" class="text-sm px-3 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        {{ __('Print') }}
                    </x-primary-button>
                @endif

                @if ($pdf)
                    <button id="{{ $id }}-export-pdf" class="inline-flex items-center px-3 py-2 bg-red-600 border border-red-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Export PDF') }}
                    </button>
                @endif
            </div>

            <!-- Controles de búsqueda y paginación -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <div class="relative">
                    <input id="{{ $id }}-search" type="text"
                           placeholder="{{ __('Search...') }}"
                           class="pl-10 pr-4 py-2 border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 rounded-md focus:ring-2 focus:ring-brand-500 focus:border-brand-500 text-sm w-full sm:w-64 text-gray-900 dark:text-graphite-100 placeholder-gray-500 dark:placeholder-graphite-400">
                    <svg class="w-4 h-4 absolute left-3 top-2.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select id="{{ $id }}-rows-per-page" class="border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 text-gray-900 dark:text-graphite-100 rounded-md px-6 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="5">5 {{ __('per page') }}</option>
                    <option value="10" selected>10 {{ __('per page') }}</option>
                    <option value="15">15 {{ __('per page') }}</option>
                    <option value="20">20 {{ __('per page') }}</option>
                </select>
            </div>
        </div>

        <!-- Controles de columnas (colapsible en móvil) -->
        @if (count($headers) > 0)
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <button id="{{ $id }}-toggle-columns" class="sm:hidden flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 mb-2 focus:outline-none">
                <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{ __('Toggle Columns') }}
            </button>

            <div id="{{ $id }}-columns-container" class="hidden sm:flex flex-wrap gap-3">
                @foreach ($headers as $index => $header)
                <label class="inline-flex items-center space-x-2 text-sm cursor-pointer hover:text-brand-600 dark:hover:text-brand-400 transition-colors duration-200">
                    <input type="checkbox" data-toggle-col-{{ $id }} checked
                           class="rounded border-gray-300 dark:border-graphite-700 text-brand-600 dark:text-brand-400 shadow-sm focus:ring-brand-500 dark:focus:ring-brand-400 bg-white dark:bg-graphite-900">
                    <span class="text-gray-700 dark:text-gray-300 select-none">{{ __($header['label']) }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Info y paginación -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div id="{{ $id }}-row-info" class="text-sm text-gray-700 dark:text-gray-300 text-center sm:text-left font-medium"></div>
            <div id="{{ $id }}-pagination" class="flex flex-wrap gap-1 justify-center sm:justify-end"></div>
        </div>
    </div>

    <!-- Contenedor de tabla con scroll horizontal mejorado -->
    <div class="overflow-x-auto bg-white dark:bg-graphite-900 rounded-lg border border-gray-200 dark:border-graphite-800 shadow-lg relative"
         id="{{ $id }}-table-container">
        <table id="{{ $id }}"
               data-table="{{ $id }}"
               data-server-side="{{ $autoServerSide ? 'true' : 'false' }}"
               data-search-url="{{ $searchUrl ?: request()->url() }}"
               data-total-records="{{ $totalRecords }}"
               class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-700 sticky top-0 z-10">
                <tr>
                    @foreach ($headers as $index => $header)
                        <th class="sortable px-4 sm:px-6 py-3 sm:py-4 text-left text-xs font-bold uppercase tracking-wider cursor-pointer text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200 whitespace-nowrap"
                            data-type="{{ $header['type'] ?? 'string' }}" data-col="{{ $index }}" data-sort-by="{{ $header['sort_by'] ?? $index }}">
                            <div class="flex items-center gap-2">
                                {{ __($header['label']) }}
                                @if (($header['type'] ?? 'string') !== 'actions')
                                <span class="sort-arrow transition-transform duration-200 opacity-60 hover:opacity-100">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                    </svg>
                                </span>
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            {{ $slot }}
            @if ($table_void)
                <tbody>
                    <tr>
                        <td colspan="{{ count($headers) }}" class="text-center p-8 text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg font-medium">{{ __('No data available') }}</p>
                                <p class="text-sm text-gray-400">{{ __('There are no records to display at this time.') }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @endif
        </table>
    </div>
</div>

