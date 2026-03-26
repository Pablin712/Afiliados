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

            @if ($isAdmin)
                <div class="rounded-3xl border border-indigo-200 bg-indigo-50/60 p-6 shadow-sm dark:border-indigo-800/40 dark:bg-indigo-900/10">
                    <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-200">
                        {{ __('messages.plans.admin_programs_title') }}
                    </h3>
                    <p class="mt-1 text-sm text-indigo-800 dark:text-indigo-300">
                        {{ __('messages.plans.admin_programs_description') }}
                    </p>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                    <h4 class="text-base font-semibold text-gray-900 dark:text-graphite-100">
                        {{ __('messages.plans.new_program') }}
                    </h4>

                    <form method="POST" action="{{ route('plans.programs.store') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                        @csrf

                        <div class="md:col-span-2">
                            <x-input-label for="program_new_name" :value="__('messages.plans.field_name')" />
                            <x-text-input id="program_new_name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2">
                            <x-input-label for="program_new_description" :value="__('messages.plans.field_description')" />
                            <textarea id="program_new_description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="program_new_first_payment_cost" :value="__('messages.plans.field_first_payment_cost')" />
                            <x-text-input id="program_new_first_payment_cost" type="number" step="0.01" min="0" name="first_payment_cost" class="mt-1 block w-full" :value="old('first_payment_cost')" required />
                            <x-input-error :messages="$errors->get('first_payment_cost')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="program_new_renewal_cost" :value="__('messages.plans.field_renewal_cost')" />
                            <x-text-input id="program_new_renewal_cost" type="number" step="0.01" min="0" name="renewal_cost" class="mt-1 block w-full" :value="old('renewal_cost')" required />
                            <x-input-error :messages="$errors->get('renewal_cost')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="program_new_duration_months" :value="__('messages.plans.field_duration_months')" />
                            <x-text-input id="program_new_duration_months" type="number" min="1" max="24" name="duration_months" class="mt-1 block w-full" :value="old('duration_months', 2)" required />
                            <x-input-error :messages="$errors->get('duration_months')" class="mt-2" />
                        </div>

                        <div class="flex items-end">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-graphite-300">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500" {{ old('is_active', 1) ? 'checked' : '' }}>
                                {{ __('messages.plans.field_is_active') }}
                            </label>
                        </div>

                        <div class="md:col-span-2 pt-2">
                            <x-primary-button>{{ __('messages.plans.create_program') }}</x-primary-button>
                        </div>
                    </form>
                </div>

                @foreach ($programs as $program)
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-graphite-100">
                            {{ __('messages.plans.edit_program') }}: {{ $program->name }}
                        </h4>

                        <form method="POST" action="{{ route('plans.programs.update', $program) }}" class="mt-5 grid gap-4 md:grid-cols-2">
                            @csrf
                            @method('PUT')

                            <div class="md:col-span-2">
                                <x-input-label for="program_{{ $program->id }}_name" :value="__('messages.plans.field_name')" />
                                <x-text-input id="program_{{ $program->id }}_name" name="name" class="mt-1 block w-full" :value="old('name', $program->name)" required />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="program_{{ $program->id }}_description" :value="__('messages.plans.field_description')" />
                                <textarea id="program_{{ $program->id }}_description" name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-300 focus:ring focus:ring-brand-200 focus:ring-opacity-50 dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100">{{ old('description', $program->description) }}</textarea>
                            </div>

                            <div>
                                <x-input-label for="program_{{ $program->id }}_first_payment_cost" :value="__('messages.plans.field_first_payment_cost')" />
                                <x-text-input id="program_{{ $program->id }}_first_payment_cost" type="number" step="0.01" min="0" name="first_payment_cost" class="mt-1 block w-full" :value="old('first_payment_cost', (float) $program->first_payment_cost)" required />
                            </div>

                            <div>
                                <x-input-label for="program_{{ $program->id }}_renewal_cost" :value="__('messages.plans.field_renewal_cost')" />
                                <x-text-input id="program_{{ $program->id }}_renewal_cost" type="number" step="0.01" min="0" name="renewal_cost" class="mt-1 block w-full" :value="old('renewal_cost', (float) $program->renewal_cost)" required />
                            </div>

                            <div>
                                <x-input-label for="program_{{ $program->id }}_duration_months" :value="__('messages.plans.field_duration_months')" />
                                <x-text-input id="program_{{ $program->id }}_duration_months" type="number" min="1" max="24" name="duration_months" class="mt-1 block w-full" :value="old('duration_months', $program->duration_months)" required />
                            </div>

                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-graphite-300">
                                    <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500" {{ old('is_active', $program->is_active) ? 'checked' : '' }}>
                                    {{ __('messages.plans.field_is_active') }}
                                </label>
                            </div>

                            <div class="md:col-span-2 pt-2">
                                <x-primary-button>{{ __('messages.plans.save_changes') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                @endforeach
            @else
            {{-- Current membership status --}}
            @php
                $membershipTypeName = strtolower((string) ($membershipTypeName ?? $membership?->membershipType?->name ?? 'free'));
                $membershipStatus   = (string) ($membershipStatus ?? $membership?->status ?? 'free');
            @endphp
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600 dark:text-brand-400">
                    {{ __('messages.plans.current_plan_title') }}
                </p>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold
                        @if($membershipStatus === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                        @elseif($membershipStatus === 'expired') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                        @else bg-gray-100 text-gray-700 dark:bg-graphite-800 dark:text-graphite-300
                        @endif">
                        {{ ucfirst((string) $membershipTypeName) }}
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

            {{-- Programs list --}}
            @if (! $canSubmitPaidRenewal)
                <div class="rounded-3xl border border-indigo-200 bg-indigo-50/70 p-6 shadow-sm dark:border-indigo-800/50 dark:bg-indigo-900/15">
                    <h4 class="text-base font-semibold text-indigo-900 dark:text-indigo-200">
                        {{ __('messages.plans.tier_benefit_title') }}
                    </h4>
                    <p class="mt-1 text-sm text-indigo-800 dark:text-indigo-300">
                        {{ __('messages.plans.tier_benefit_description', ['count' => (int) ($activeDirectAffiliates ?? 0)]) }}
                    </p>

                    @if ($canFreeRenewToday)
                        <form method="POST" action="{{ route('plans.renew-free') }}" class="mt-4">
                            @csrf
                            <x-primary-button type="submit">
                                {{ __('messages.plans.renew_free_button') }}
                            </x-primary-button>
                        </form>
                    @else
                        <p class="mt-3 text-sm text-indigo-800 dark:text-indigo-300">
                            {{ __('messages.plans.renew_free_waiting') }}
                        </p>
                    @endif
                </div>
            @elseif ($programs->isEmpty())
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                    <p class="text-sm text-gray-600 dark:text-graphite-400">{{ __('messages.plans.no_programs') }}</p>
                </div>
            @else
                @foreach ($programs as $program)
                    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40 overflow-hidden">

                        {{-- Program header --}}
                        <div class="p-6 border-b border-gray-100 dark:border-graphite-800">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    @php($programContextDescription = $hasApprovedPayment
                                        ? __('messages.plans.program_context_customer_renewal')
                                        : __('messages.plans.program_context_first_payment'))
                                    <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600 dark:text-brand-400">
                                        {{ __('messages.plans.program_badge') }}
                                    </p>
                                    <h3 class="mt-1 text-xl font-bold text-gray-900 dark:text-graphite-100">
                                        {{ $program->name }}
                                    </h3>
                                    @if ($program->description)
                                        <p class="mt-2 text-sm text-gray-600 dark:text-graphite-400 max-w-xl">
                                            {{ $program->description }}
                                        </p>
                                    @endif
                                    <p class="mt-2 text-sm font-medium text-brand-700 dark:text-brand-300 max-w-xl">
                                        {{ $programContextDescription }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    @php($displayDurationMonths = $hasApprovedPayment ? 1 : (int) $program->duration_months)
                                    <p class="text-xs text-gray-500 dark:text-graphite-400">
                                        {{ $hasApprovedPayment ? __('messages.plans.price_renewal') : __('messages.plans.price_first') }}
                                    </p>
                                    <p class="text-3xl font-bold text-brand-600 dark:text-brand-400">
                                        ${{ $hasApprovedPayment ? number_format((float) $program->renewal_cost, 2) : number_format((float) $program->first_payment_cost, 2) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-graphite-400">
                                        {{ __('messages.plans.duration_months', ['count' => $displayDurationMonths]) }}
                                    </p>
                                    @if (!$hasApprovedPayment)
                                        <p class="mt-1 text-xs text-gray-400 dark:text-graphite-500">
                                            {{ __('messages.plans.renewal_note', ['price' => number_format((float) $program->renewal_cost, 2)]) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Pending payment notice OR payment form --}}
                        @if ($pendingPayment && $pendingPayment->program_id === $program->id)
                            <div class="p-6 bg-yellow-50 dark:bg-yellow-900/10">
                                <div class="flex items-start gap-4">
                                    <div class="mt-0.5 shrink-0 text-yellow-500 dark:text-yellow-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-yellow-900 dark:text-yellow-200">
                                            {{ __('messages.plans.payment_pending_title') }}
                                        </h4>
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
                        @elseif (!$pendingPayment)
                            @if ($banks->isEmpty())
                                <div class="p-6">
                                    <p class="text-sm text-gray-600 dark:text-graphite-400">{{ __('messages.plans.no_banks') }}</p>
                                </div>
                            @else
                                <div
                                    class="p-6"
                                    x-data="{
                                        selectedBankId: @js(old('bank_id', '')),
                                        banks: @js($banks),
                                        get selectedBank() {
                                            if (!this.selectedBankId) return null;
                                            return this.banks.find(b => String(b.id) === String(this.selectedBankId)) ?? null;
                                        }
                                    }"
                                >
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-graphite-100">
                                        {{ __('messages.plans.upgrade_title') }}
                                    </h4>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
                                        {{ __('messages.plans.upgrade_description') }}
                                    </p>

                                    <form
                                        method="POST"
                                        action="{{ route('plans.payment.store') }}"
                                        enctype="multipart/form-data"
                                        class="mt-5"
                                    >
                                        @csrf

                                        <input type="hidden" name="program_id" value="{{ $program->id }}">

                                        <div class="grid gap-6 lg:grid-cols-2">

                                            {{-- Left: Bank selection + details --}}
                                            <div class="space-y-4">
                                                <div>
                                                    <x-input-label for="bank_id_{{ $program->id }}" :value="__('messages.plans.select_bank_label')" />
                                                    <select
                                                        id="bank_id_{{ $program->id }}"
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

                                                <div
                                                    x-show="selectedBank"
                                                    x-cloak
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 translate-y-1"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    class="rounded-xl border border-brand-100 bg-brand-50/50 p-4 text-sm dark:border-brand-900/50 dark:bg-brand-900/10"
                                                >
                                                    <h5 class="mb-3 font-medium text-brand-700 dark:text-brand-400">
                                                        {{ __('messages.plans.bank_details_title') }}
                                                    </h5>
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
                                                    <x-input-label :value="__('messages.plans.amount_label')" />
                                                    @php($fixedAmount = $hasApprovedPayment ? (float) $program->renewal_cost : (float) $program->first_payment_cost)
                                                    <div class="mt-1 flex items-center justify-between rounded-lg border border-brand-200 bg-brand-50 px-4 py-3 dark:border-brand-900/60 dark:bg-brand-900/20">
                                                        <span class="text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.plans.fixed_amount_note') }}</span>
                                                        <span class="text-lg font-bold text-brand-700 dark:text-brand-300">${{ number_format($fixedAmount, 2) }}</span>
                                                    </div>
                                                    <input type="hidden" name="amount" value="{{ number_format($fixedAmount, 2, '.', '') }}">
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
                @endforeach
            @endif

            {{-- Pending payment for unknown/null program (edge case) --}}
            @if ($pendingPayment && $pendingPayment->program_id === null)
                <div class="rounded-3xl border border-yellow-300 bg-yellow-50 p-6 dark:border-yellow-700/50 dark:bg-yellow-900/10">
                    <h4 class="font-semibold text-yellow-900 dark:text-yellow-200">
                        {{ __('messages.plans.payment_pending_title') }}
                    </h4>
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
            @endif
            @endif

        </div>
    </div>
</x-app-layout>
