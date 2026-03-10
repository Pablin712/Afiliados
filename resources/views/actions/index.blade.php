<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.audit.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white dark:bg-graphite-900 rounded-lg border border-gray-200 dark:border-graphite-800 p-4 sm:p-6">
                <p class="text-sm text-gray-600 dark:text-graphite-300">
                    {{ __('messages.audit.description') }}
                </p>
            </div>

            <x-enhanced-table
                id="audit-actions-table"
                :headers="[
                    ['label' => __('messages.audit.column_id'), 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('messages.audit.column_user'), 'type' => 'string', 'sort_by' => 'users.name'],
                    ['label' => __('messages.audit.column_module'), 'type' => 'string', 'sort_by' => 'module'],
                    ['label' => __('messages.audit.column_action'), 'type' => 'string', 'sort_by' => 'action'],
                    ['label' => __('messages.audit.column_method'), 'type' => 'string', 'sort_by' => 'method'],
                    ['label' => __('messages.audit.column_route'), 'type' => 'string', 'sort_by' => 'route'],
                    ['label' => __('messages.audit.column_url'), 'type' => 'string', 'sort_by' => 'url'],
                    ['label' => __('messages.audit.column_ip'), 'type' => 'string', 'sort_by' => 'ip_address'],
                    ['label' => __('messages.audit.column_date'), 'type' => 'string', 'sort_by' => 'created_at'],
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
                    {{ __('messages.audit.report_permission', ['permission' => __('messages.audit.permission_key')]) }}
                </div>
            @endunless
        </div>
    </div>
</x-app-layout>
