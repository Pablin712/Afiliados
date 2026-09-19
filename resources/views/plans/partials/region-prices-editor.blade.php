@props(['program' => null])

{{--
    Region price table for the admin plan forms. Each row overrides the plan's
    four costs for buyers of that region; with no rows the plan charges its own
    costs everywhere, exactly as before this existed.
--}}
@php
    $initialRows = $program
        ? $program->regionPrices->map(fn ($price) => [
            'region' => $price->region,
            'first_payment_cost' => (string) $price->first_payment_cost,
            'card_first_payment_cost' => $price->card_first_payment_cost !== null ? (string) $price->card_first_payment_cost : '',
            'renewal_cost' => (string) $price->renewal_cost,
            'card_renewal_cost' => $price->card_renewal_cost !== null ? (string) $price->card_renewal_cost : '',
        ])->values()->all()
        : [];
@endphp

<div
    class="md:col-span-2 rounded-xl border border-gray-200 dark:border-graphite-700"
    x-data="regionPriceEditor(@js($initialRows))"
>
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-graphite-700 dark:bg-graphite-900">
        <div>
            <p class="text-sm font-semibold text-gray-700 dark:text-graphite-200">{{ __('messages.plans.region_prices.title') }}</p>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.plans.region_prices.hint') }}</p>
        </div>
        <button
            type="button"
            x-on:click="addRow()"
            class="inline-flex items-center rounded-md border border-brand-300 bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-100 dark:border-brand-800 dark:bg-brand-950/30 dark:text-brand-300"
        >
            + {{ __('messages.plans.region_prices.add') }}
        </button>
    </div>

    <div class="space-y-3 p-4">
        <template x-if="rows.length === 0">
            <p class="text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.plans.region_prices.empty') }}</p>
        </template>

        <template x-for="(row, index) in rows" :key="index">
            <div class="rounded-lg border border-gray-200 p-3 dark:border-graphite-700">
                <div class="grid gap-3 sm:grid-cols-5">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-medium text-gray-600 dark:text-graphite-400 mb-1">{{ __('messages.plans.region_prices.region') }}</label>
                        <input
                            type="text"
                            :name="'region_prices['+index+'][region]'"
                            x-model="row.region"
                            maxlength="120"
                            placeholder="{{ __('messages.plans.region_prices.region_placeholder') }}"
                            class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-graphite-400 mb-1">{{ __('messages.plans.region_prices.first_payment_cost') }}</label>
                        <input type="number" step="0.01" min="0" :name="'region_prices['+index+'][first_payment_cost]'" x-model="row.first_payment_cost" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-graphite-400 mb-1">{{ __('messages.plans.region_prices.card_first_payment_cost') }}</label>
                        <input type="number" step="0.01" min="0" :name="'region_prices['+index+'][card_first_payment_cost]'" x-model="row.card_first_payment_cost" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-graphite-400 mb-1">{{ __('messages.plans.region_prices.renewal_cost') }}</label>
                        <input type="number" step="0.01" min="0" :name="'region_prices['+index+'][renewal_cost]'" x-model="row.renewal_cost" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 dark:text-graphite-400 mb-1">{{ __('messages.plans.region_prices.card_renewal_cost') }}</label>
                            <input type="number" step="0.01" min="0" :name="'region_prices['+index+'][card_renewal_cost]'" x-model="row.card_renewal_cost" class="w-full rounded-md border-gray-300 text-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">
                        </div>
                        <button
                            type="button"
                            x-on:click="removeRow(index)"
                            class="mb-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/30"
                            title="{{ __('messages.plans.region_prices.remove') }}"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
