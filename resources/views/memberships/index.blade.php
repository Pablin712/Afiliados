<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('memberships.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white dark:bg-graphite-900 rounded-lg border border-gray-200 dark:border-graphite-800 p-4 sm:p-6">
                <p class="text-sm text-gray-600 dark:text-graphite-300">
                    {{ __('memberships.messages.description') }}
                </p>
            </div>

            <x-enhanced-table
                id="memberships-table"
                :headers="[
                    ['label' => __('memberships.columns.id'), 'type' => 'number', 'sort_by' => 'id'],
                    ['label' => __('memberships.columns.user'), 'type' => 'string', 'sort_by' => 'users.name'],
                    ['label' => __('memberships.columns.membership_type'), 'type' => 'string', 'sort_by' => 'membership_types.name'],
                    ['label' => __('memberships.columns.status'), 'type' => 'string', 'sort_by' => 'status'],
                    ['label' => __('memberships.columns.started_at'), 'type' => 'string', 'sort_by' => 'started_at'],
                    ['label' => __('memberships.columns.expires_at'), 'type' => 'string', 'sort_by' => 'expires_at'],
                    ['label' => __('memberships.columns.created_at'), 'type' => 'string', 'sort_by' => 'created_at'],
                ]"
                :serverSide="true"
                :totalRecords="$totalRecords"
                :searchUrl="route('memberships.index')"
                :extraParams="[
                    'status' => $filters['status'] ?? '',
                    'membership_type_id' => $filters['membership_type_id'] ?? '',
                ]"
                :csv="$canReport"
                :excel="$canReport"
                :json="$canReport"
                :pdf="$canReport"
                :print="true"
                :table_void="$records->isEmpty()"
            >
                <x-slot name="buttons">
                    @can('view membership_types')
                        <a href="{{ route('membership-types.index') }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-indigo-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('memberships.buttons.manage_membership_types') }}
                        </a>
                    @endcan

                    <form method="GET" action="{{ route('memberships.index') }}" class="flex flex-wrap items-center gap-2">
                        <select name="status" class="border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 text-gray-900 dark:text-graphite-100 rounded-md px-3 py-2 text-sm">
                            <option value="">{{ __('memberships.filters.all_statuses') }}</option>
                            @foreach($statusOptions as $statusValue)
                                <option value="{{ $statusValue }}" @selected(($filters['status'] ?? '') === $statusValue)>
                                    {{ __('memberships.statuses.'.$statusValue) }}
                                </option>
                            @endforeach
                        </select>

                        <select name="membership_type_id" class="border border-gray-300 dark:border-graphite-700 bg-white dark:bg-graphite-900 text-gray-900 dark:text-graphite-100 rounded-md px-3 py-2 text-sm">
                            <option value="">{{ __('memberships.filters.all_types') }}</option>
                            @foreach($membershipTypes as $type)
                                <option value="{{ $type->id }}" @selected((string) ($filters['membership_type_id'] ?? '') === (string) $type->id)>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>

                        <x-secondary-button type="submit">{{ __('memberships.buttons.apply_filters') }}</x-secondary-button>
                        <a href="{{ route('memberships.index') }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 dark:bg-graphite-900 dark:border-graphite-700 dark:text-graphite-200 dark:hover:bg-graphite-800 transition ease-in-out duration-150">
                            {{ __('memberships.buttons.clear_filters') }}
                        </a>
                    </form>
                </x-slot>

                <tbody class="divide-y divide-gray-200 dark:divide-graphite-800">
                    @include('memberships.partials.table-rows', ['records' => $records])
                </tbody>
            </x-enhanced-table>

            @unless($canReport)
                <div class="text-xs text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-md p-3">
                    {{ __('memberships.messages.report_permission', ['permission' => __('memberships.messages.permission_key')]) }}
                </div>
            @endunless
        </div>
    </div>
</x-app-layout>
