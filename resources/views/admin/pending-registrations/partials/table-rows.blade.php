@forelse ($records as $payment)
    @php
        $approveOnclick = 'window.openApproveModal(' . json_encode([
            'id'   => $payment->id,
            'name' => $payment->user->name,
        ]) . ')';
        $rejectOnclick = 'window.openRejectModal(' . json_encode([
            'id'   => $payment->id,
            'name' => $payment->user->name,
        ]) . ')';
        $receiptOnclick = 'window.openReceiptModal(' . json_encode([
            'photo' => $payment->photo ? asset('storage/' . $payment->photo) : null,
            'name'  => $payment->user->name,
        ]) . ')';
    @endphp
    <tr class="hover:bg-gray-50 dark:hover:bg-graphite-800/60 transition-colors duration-150">
        <td class="px-4 sm:px-6 py-3 text-sm font-medium text-gray-900 dark:text-graphite-100">{{ $payment->user->name }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-300">{{ $payment->user->email }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm font-mono text-gray-700 dark:text-graphite-300">{{ $payment->number }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-300">{{ $payment->transaction?->bank?->name ?? '—' }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm font-medium text-gray-900 dark:text-graphite-100">${{ number_format((float) $payment->amount, 2) }}</td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            @if ($payment->photo)
                <x-action-icon-button
                    variant="view"
                    icon="eye"
                    :title="__('messages.admin.view_receipt')"
                    :onclick="$receiptOnclick"
                />
            @else
                <span class="text-gray-400 dark:text-graphite-600">—</span>
            @endif
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">
            {{ $payment->created_at?->format('Y-m-d H:i') }}
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
                @can('manage payments')
                    <x-action-icon-button
                        variant="approve"
                        icon="approve"
                        :title="__('messages.admin.approve_registration')"
                        :onclick="$approveOnclick"
                    />
                    <x-action-icon-button
                        variant="delete"
                        icon="reject"
                        :title="__('messages.admin.reject_registration')"
                        :onclick="$rejectOnclick"
                    />
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.no_pending_registrations') }}
        </td>
    </tr>
@endforelse
