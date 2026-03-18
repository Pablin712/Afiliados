<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.plans.title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash messages --}}
            @if (session('status'))
                <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Current plan card --}}
            @php
                $membershipTypeName = $membership?->membershipType?->name ?? 'free';
                $membershipStatus   = $membership?->status ?? 'free';
            @endphp
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600 dark:text-brand-400">
                    {{ __('messages.plans.badge') }}
                </p>
                <h2 class="mt-1 text-xl font-semibold text-gray-900 dark:text-graphite-100">
                    {{ __('messages.plans.current_plan_title') }}
                </h2>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold
                        @if($membershipStatus === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                        @elseif($membershipStatus === 'expired') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                        @else bg-gray-100 text-gray-700 dark:bg-graphite-800 dark:text-graphite-300
                        @endif">
                        {{ ucfirst($membershipTypeName) }}
                    </span>
                    <span class="text-sm text-gray-600 dark:text-graphite-400">
                        @if($membershipStatus === 'active' && $membership?->expires_at)
                            {{ __('messages.plans.status_active', ['date' => $membership->expires_at->format('d/m/Y')]) }}
                        @elseif($membershipStatus === 'expired' && $membership?->expires_at)
                            {{ __('messages.plans.status_expired', ['date' => $membership->expires_at->format('d/m/Y')]) }}
                        @else
                            {{ __('messages.plans.status_free') }}
                        @endif
                    </span>
                </div>
            </div>

            {{-- Available paid plans --}}
            @if ($paidTypes->isNotEmpty())
                <div>
                    <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-graphite-100">
                        {{ __('messages.plans.available_plans') }}
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($paidTypes as $type)
                            <div class="rounded-2xl border border-brand-200 bg-brand-50/60 p-5 dark:border-brand-900/50 dark:bg-brand-900/10">
                                <p class="text-xs font-semibold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                                    {{ __('messages.plans.badge') }}
                                </p>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-graphite-100">
                                    {{ ucfirst($type->name) }}
                                </p>
                                <p class="mt-3 text-3xl font-bold text-brand-600 dark:text-brand-400">
                                    ${{ number_format((float) $type->cost, 2) }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-graphite-400">
                                    {{ __('messages.plans.duration_months', ['count' => 2]) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pending payment notice OR upload form --}}
            @if ($pendingPayment)
                <div class="rounded-3xl border border-yellow-300 bg-yellow-50 p-6 dark:border-yellow-700/50 dark:bg-yellow-900/10">
                    <div class="flex items-start gap-4">
                        <div class="mt-0.5 shrink-0 text-yellow-500 dark:text-yellow-400">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-yellow-900 dark:text-yellow-200">
                                {{ __('messages.plans.payment_pending_title') }}
                            </h3>
                            <p class="mt-1 text-sm text-yellow-800 dark:text-yellow-300">
                                {{ __('messages.plans.payment_pending_description') }}
                            </p>
                            <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1.5 text-sm">
                                <dt class="font-medium text-yellow-800 dark:text-yellow-300">{{ __('messages.auth.payment_reference') }}</dt>
                                <dd class="font-mono text-yellow-900 dark:text-yellow-200">{{ $pendingPayment->number }}</dd>
                                <dt class="font-medium text-yellow-800 dark:text-yellow-300">{{ __('messages.auth.payment_amount') }}</dt>
                                <dd class="text-yellow-900 dark:text-yellow-200">${{ number_format((float) $pendingPayment->amount, 2) }}</dd>
                                <dt class="font-medium text-yellow-800 dark:text-yellow-300">{{ __('messages.admin.registered_at') }}</dt>
                                <dd class="text-yellow-900 dark:text-yellow-200">{{ $pendingPayment->created_at?->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @else
                @if ($banks->isEmpty())
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                        <p class="text-sm text-gray-600 dark:text-graphite-400">
                            {{ __('messages.plans.no_banks') }}
                        </p>
                    </div>
                @else
                    <div
                        class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40"
                        x-data="{
                            selectedBankId: @js(old('bank_id', '')),
                            banks: @js($banks),
                            get selectedBank() {
                                if (!this.selectedBankId) return null;
                                return this.banks.find(b => String(b.id) === String(this.selectedBankId)) ?? null;
                            }
                        }"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">
                            {{ __('messages.plans.upgrade_title') }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
                            {{ __('messages.plans.upgrade_description') }}
                        </p>

                        <form
                            method="POST"
                            action="{{ route('plans.payment.store') }}"
                            enctype="multipart/form-data"
                            class="mt-6"
                        >
                            @csrf

                            <div class="grid gap-6 lg:grid-cols-2">
                                {{-- Left: Bank selection + dynamic bank details --}}
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="bank_id" :value="__('messages.plans.select_bank_label')" />
                                        <select
                                            id="bank_id"
                                            name="bank_id"
                                            x-model="selectedBankId"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100"
                                        >
                                            <option value="">{{ __('messages.plans.select_bank_placeholder') }}</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                                    {{ $bank->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('bank_id')" class="mt-2" />
                                    </div>

                                    {{-- Dynamic bank details panel --}}
                                    <div
                                        x-show="selectedBank"
                                        x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="rounded-xl border border-brand-100 bg-brand-50/50 p-4 text-sm dark:border-brand-900/50 dark:bg-brand-900/10"
                                    >
                                        <h4 class="mb-3 font-medium text-brand-700 dark:text-brand-400">
                                            {{ __('messages.plans.bank_details_title') }}
                                        </h4>
                                        <dl class="space-y-1.5">
                                            <div class="flex gap-2">
                                                <dt class="min-w-28 text-gray-500 dark:text-graphite-400">{{ __('messages.plans.bank_owner') }}:</dt>
                                                <dd class="font-medium text-gray-900 dark:text-graphite-100" x-text="selectedBank?.owner ?? ''"></dd>
                                            </div>
                                            <div class="flex gap-2">
                                                <dt class="min-w-28 text-gray-500 dark:text-graphite-400">{{ __('messages.plans.bank_identification') }}:</dt>
                                                <dd class="font-medium text-gray-900 dark:text-graphite-100" x-text="selectedBank?.identification ?? ''"></dd>
                                            </div>
                                            <div class="flex gap-2">
                                                <dt class="min-w-28 text-gray-500 dark:text-graphite-400">{{ __('messages.plans.bank_number') }}:</dt>
                                                <dd class="font-mono font-medium text-gray-900 dark:text-graphite-100" x-text="selectedBank?.number ?? ''"></dd>
                                            </div>
                                            <div x-show="selectedBank?.detail" class="flex gap-2">
                                                <dt class="min-w-28 text-gray-500 dark:text-graphite-400">{{ __('messages.plans.bank_detail') }}:</dt>
                                                <dd class="text-gray-700 dark:text-graphite-300" x-text="selectedBank?.detail ?? ''"></dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                {{-- Right: Payment fields --}}
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="number" :value="__('messages.plans.reference_label')" />
                                        <x-text-input
                                            id="number"
                                            class="mt-1 block w-full"
                                            type="text"
                                            name="number"
                                            :value="old('number')"
                                            required
                                            autocomplete="off"
                                        />
                                        <x-input-error :messages="$errors->get('number')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="amount" :value="__('messages.plans.amount_label')" />
                                        <x-text-input
                                            id="amount"
                                            class="mt-1 block w-full"
                                            type="number"
                                            name="amount"
                                            step="0.01"
                                            min="0.01"
                                            :value="old('amount')"
                                            required
                                        />
                                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="photo" :value="__('messages.plans.receipt_label')" />
                                        <input
                                            id="photo"
                                            name="photo"
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            required
                                            class="mt-1 block w-full text-sm text-gray-700 dark:text-graphite-300
                                                file:mr-3 file:cursor-pointer file:rounded-md file:border-0
                                                file:bg-brand-600 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white
                                                hover:file:bg-brand-700 dark:file:bg-brand-700 dark:hover:file:bg-brand-600"
                                        />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">
                                            {{ __('messages.plans.receipt_help') }}
                                        </p>
                                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                                    </div>

                                    <div class="pt-2">
                                        <x-primary-button type="submit">
                                            {{ __('messages.plans.submit_button') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
