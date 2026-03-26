@forelse ($records as $bank)
    @php
        $editOnclick = 'window.openBankEditModal(' . json_encode([
            'id' => $bank->id,
            'name' => $bank->name,
            'owner' => $bank->owner,
            'identification' => $bank->identification,
            'number' => $bank->number,
            'amount' => number_format((float) $bank->amount, 2, '.', ''),
            'detail' => $bank->detail,
        ]) . ')';

        $deleteOnclick = 'window.openBankDeleteModal(' . json_encode([
            'id' => $bank->id,
            'name' => $bank->name,
        ]) . ')';
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $bank->id }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $bank->name }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $bank->owner }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $bank->identification }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">{{ $bank->number }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-200">${{ number_format((float) $bank->amount, 2) }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">{{ optional($bank->created_at)->format('Y-m-d H:i:s') }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
                @can('edit banks')
                    <x-action-icon-button
                        variant="edit"
                        icon="edit"
                        :title="__('messages.admin.banks.buttons.edit')"
                        :onclick="$editOnclick"
                    />
                @endcan

                @can('delete banks')
                    <x-action-icon-button
                        variant="delete"
                        icon="delete"
                        :title="__('messages.admin.banks.buttons.delete')"
                        :onclick="$deleteOnclick"
                    />
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.banks.messages.empty') }}
        </td>
    </tr>
@endforelse
