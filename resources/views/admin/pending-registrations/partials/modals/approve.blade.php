@can('manage payments')
<x-modal name="pending-registration-approve-modal" focusable>
    <form method="POST" id="pending-registration-approve-form" action="" class="p-6 space-y-4">
        @csrf

        <h3 class="text-lg font-semibold text-gray-800 dark:text-graphite-100">
            {{ __('messages.admin.confirm_approve_title') }}
        </h3>

        <div class="rounded-md bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-300">
            {{ __('messages.admin.confirm_approve_description') }}
        </div>

        <p class="text-sm text-gray-600 dark:text-graphite-300">
            {{ __('messages.admin.user_label') }}:
            <span id="pending-registration-approve-name" class="font-semibold text-gray-900 dark:text-graphite-100"></span>
        </p>

        <div class="flex justify-end gap-2 pt-2">
            <x-secondary-button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'pending-registration-approve-modal' }))">
                {{ __('messages.admin.cancel') }}
            </x-secondary-button>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('messages.admin.approve_registration') }}
            </button>
        </div>
    </form>
</x-modal>
@endcan
