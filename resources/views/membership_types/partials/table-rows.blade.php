@forelse ($records as $type)
    @php
        $editOnclick = 'window.openMembershipTypeEditModal(' . json_encode([
            'id'                  => $type->id,
            'name'                => $type->name,
            'affiliates_required' => $type->affiliates_required,
            'cost'                => number_format((float) $type->cost, 2, '.', ''),
            'profit'              => number_format((float) $type->profit, 2, '.', ''),
        ]) . ')';
        $deleteOnclick = 'window.openMembershipTypeDeleteModal(' . json_encode([
            'id'   => $type->id,
            'name' => $type->name,
        ]) . ')';
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $type->id }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ __('membership_types.types.' . $type->name) }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $type->affiliates_required }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ number_format((float) $type->cost, 2) }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ number_format((float) $type->profit, 2) }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">{{ optional($type->created_at)->format('Y-m-d H:i:s') }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
                @can('edit membership_types')
                    <x-action-icon-button
                        variant="edit"
                        icon="edit"
                        :title="__('membership_types.buttons.edit')"
                        :onclick="$editOnclick"
                    />
                @endcan

                @can('delete membership_types')
                    <x-action-icon-button
                        variant="delete"
                        icon="delete"
                        :title="__('membership_types.buttons.delete')"
                        :onclick="$deleteOnclick"
                    />
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('membership_types.messages.empty') }}
        </td>
    </tr>
@endforelse
