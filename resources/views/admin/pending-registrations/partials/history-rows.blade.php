@forelse ($records as $payment)
    @php
        $receiptOnclick = 'window.openReceiptModal(' . json_encode([
            'photo' => $payment->photo ? asset('storage/' . $payment->photo) : null,
            'name'  => $payment->user->name,
        ]) . ')';

        $stateBadge = match ($payment->state) {
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
            default    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        };

        $stateLabel = match ($payment->state) {
            'approved' => __('messages.admin.state_approved'),
            'rejected' => __('messages.admin.state_rejected'),
            default    => __('messages.admin.state_pending'),
        };
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
        <td class="px-4 sm:px-6 py-3 text-sm">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stateBadge }}">
                {{ $stateLabel }}
            </span>
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">
            {{ $payment->created_at?->format('Y-m-d H:i') }}
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-graphite-400 whitespace-nowrap">
            {{ $payment->reviewed_at?->format('Y-m-d H:i') ?? '—' }}
        </td>
        <td class="px-4 sm:px-6 py-3 text-sm text-gray-700 dark:text-graphite-300">
            {{ $payment->reviewer?->name ?? '—' }}
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.no_payment_history') }}
        </td>
    </tr>
@endforelse
