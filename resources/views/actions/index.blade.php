<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('Auditoria') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white dark:bg-graphite-900 rounded-lg border border-gray-200 dark:border-graphite-800 p-4 sm:p-6">
                <p class="text-sm text-gray-600 dark:text-graphite-300">
                    Registro centralizado de acciones HTTP, autenticacion y cambios de modelos.
                </p>
            </div>

            <x-enhanced-table
                id="audit-actions-table"
                :headers="[
                    ['label' => 'ID', 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => 'Usuario', 'type' => 'string', 'sort_by' => 'users.name'],
                    ['label' => 'Modulo', 'type' => 'string', 'sort_by' => 'module'],
                    ['label' => 'Accion', 'type' => 'string', 'sort_by' => 'action'],
                    ['label' => 'Metodo', 'type' => 'string', 'sort_by' => 'method'],
                    ['label' => 'Ruta', 'type' => 'string', 'sort_by' => 'route'],
                    ['label' => 'URL', 'type' => 'string', 'sort_by' => 'url'],
                    ['label' => 'IP', 'type' => 'string', 'sort_by' => 'ip_address'],
                    ['label' => 'Fecha', 'type' => 'string', 'sort_by' => 'created_at'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('actions.index')"
                :csv="$canReport"
                :excel="$canReport"
                :json="$canReport"
                :pdf="$canReport"
                :print="true"
                :table_void="$records->isEmpty()"
            >
                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('actions.partials.table-rows', ['records' => $records])
                </tbody>
            </x-enhanced-table>

            @unless($canReport)
                <div class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-md p-3">
                    Solo usuarios con permiso <code>report actions</code> pueden descargar reportes de auditoria (CSV, Excel, JSON y PDF).
                </div>
            @endunless
        </div>
    </div>
</x-app-layout>
