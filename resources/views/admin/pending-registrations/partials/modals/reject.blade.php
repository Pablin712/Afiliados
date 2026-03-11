@can('manage payments')
<x-modal name="pending-registration-reject-modal" focusable>
    <form method="POST" id="pending-registration-reject-form" action="" class="p-6 space-y-4">
        @csrf

        <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
            {{ __('messages.admin.confirm_reject_title') }}
        </h3>

        <div class="rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 px-4 py-3 text-sm text-red-800 dark:text-red-300">
            {{ __('messages.admin.confirm_reject_description') }}
        </div>

        <p class="text-sm text-gray-600 dark:text-graphite-300">
            {{ __('messages.admin.user_label') }}:
            <span id="pending-registration-reject-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
        </p>

        <div class="flex justify-end gap-2 pt-2">
            <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'pending-registration-reject-modal' }))">
                {{ __('messages.admin.cancel') }}
            </x-secondary-button>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('messages.admin.reject_registration') }}
            </button>
        </div>
    </form>
</x-modal>
@endcan
