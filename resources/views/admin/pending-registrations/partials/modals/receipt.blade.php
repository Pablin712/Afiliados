<x-modal name="pending-registration-receipt-modal" focusable maxWidth="xl">
    <div class="p-6 space-y-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
            {{ __('messages.admin.view_receipt_title') }}
        </h3>

        <p class="text-sm text-gray-500 dark:text-graphite-400">
            {{ __('messages.admin.user_label') }}:
            <span id="pending-registration-receipt-user-name" class="font-medium text-gray-700 dark:text-graphite-200"></span>
        </p>

        <div id="pending-registration-receipt-container" class="flex justify-center min-h-24">
        </div>

        <div class="flex justify-end pt-2">
            <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'pending-registration-receipt-modal' }))">
                {{ __('messages.admin.cancel') }}
            </x-secondary-button>
        </div>
    </div>
</x-modal>
