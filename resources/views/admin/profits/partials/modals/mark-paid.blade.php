<x-modal name="profit-mark-paid-modal" :show="false" maxWidth="md">
    <form id="profit-mark-paid-form" method="POST" class="p-6 space-y-4">
        @csrf
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.admin.profits.modal_title') }}</h3>
            <p id="profit-mark-paid-text" class="mt-1 text-sm text-gray-600 dark:text-graphite-300"></p>
        </div>

        <div>
            <x-input-label for="profit_bank_id" :value="__('messages.admin.profits.select_bank')" />
            <select id="profit_bank_id" name="bank_id" required class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                <option value="">{{ __('messages.admin.profits.select_bank_placeholder') }}</option>
                @foreach ($banks as $bank)
                    <option value="{{ $bank->id }}">{{ $bank->name }} ({{ $bank->number }}) - ${{ number_format((float) $bank->amount, 2) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-input-label for="profit_detail" :value="__('messages.admin.profits.detail_optional')" />
            <textarea id="profit_detail" name="detail" rows="3" class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"></textarea>
        </div>

        <div class="flex justify-end gap-2">
            <x-secondary-button x-on:click.prevent="$dispatch('close')">{{ __('messages.cancel') }}</x-secondary-button>
            <x-primary-button type="submit">{{ __('messages.confirm') }}</x-primary-button>
        </div>
    </form>
</x-modal>
