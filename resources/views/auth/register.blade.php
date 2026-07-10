<x-guest-layout>
    <form
        method="POST"
        action="{{ route('register') }}"
        x-data="{
            step: 1,
            name: @js(old('name', '')),
            email: @js(old('email', '')),
            phone: @js(old('phone', '')),
            identification: @js(old('identification', '')),
            passwordValue: '',
            passwordConfirmationValue: '',
            showPassword: false,
            showPasswordConfirmation: false,
            passwordPolicyMessage: @js(__('messages.auth.password_policy_help')),
            gmailOnlyMessage: @js(__('messages.auth.email_gmail_only')),
            emailAvailabilityError: '',
            gmailOnlyError: '',
            identificationAvailabilityError: '',
            availabilityUrl: @js(route('register.availability')),
            async checkAvailability() {
                this.emailAvailabilityError = '';
                this.identificationAvailabilityError = '';

                if (!this.email && !this.identification) {
                    return true;
                }

                try {
                    const params = new URLSearchParams({
                        email: this.email,
                        identification: this.identification,
                    });

                    const response = await fetch(`${this.availabilityUrl}?${params.toString()}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return true;
                    }

                    const data = await response.json();

                    if (data.email_exists) {
                        this.emailAvailabilityError = @js(__('messages.auth.email_already_used'));
                    }

                    if (data.identification_exists) {
                        this.identificationAvailabilityError = @js(__('messages.auth.identification_already_used'));
                    }

                    const emailInput = this.$refs.emailInput;
                    if (emailInput) {
                        emailInput.setCustomValidity(this.emailAvailabilityError);
                    }

                    const identificationInput = this.$refs.identificationInput;
                    if (identificationInput) {
                        identificationInput.setCustomValidity(this.identificationAvailabilityError);
                    }

                    return !data.email_exists && !data.identification_exists;
                } catch (error) {
                    return true;
                }
            },
            validateStepBasic() {
                const refs = [
                    this.$refs.nameInput,
                    this.$refs.identificationInput,
                    this.$refs.emailInput,
                    this.$refs.passwordInput,
                    this.$refs.passwordConfirmationInput,
                ].filter(Boolean);

                refs.forEach((field) => field.setCustomValidity(''));

                const gmailPattern = /^[^\s@]+@gmail\.com$/i;
                if (this.email && !gmailPattern.test(this.email)) {
                    this.$refs.emailInput?.setCustomValidity(this.gmailOnlyMessage);
                    this.gmailOnlyError = this.gmailOnlyMessage;
                }

                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;
                if (!passwordPattern.test(this.passwordValue)) {
                    this.$refs.passwordInput?.setCustomValidity(this.passwordPolicyMessage);
                }

                if (this.passwordValue !== this.passwordConfirmationValue) {
                    this.$refs.passwordConfirmationInput?.setCustomValidity(@js(__('validation.confirmed')));
                }

                const firstInvalid = refs.find((field) => !field.checkValidity());

                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                    return false;
                }

                return true;
            },
            async goToConfirm() {
                const panel = this.$refs.stepBasic;
                const fields = panel ? Array.from(panel.querySelectorAll('input, select, textarea')) : [];
                const firstInvalid = fields.find((field) => !field.checkValidity());

                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                    return;
                }

                if (!this.validateStepBasic()) {
                    return;
                }

                const available = await this.checkAvailability();
                if (!available) {
                    const targetInput = this.emailAvailabilityError ? this.$refs.emailInput : this.$refs.identificationInput;
                    if (targetInput) {
                        targetInput.reportValidity();
                        targetInput.focus();
                    }
                    return;
                }

                this.step = 2;
            },
            backToBasic() {
                this.step = 1;
            }
        }"
        class="space-y-8"
    >
        @csrf

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <input type="hidden" name="sponsor_id" value="{{ old('sponsor_id', $sponsor->id) }}">

        <div class="space-y-2">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600 dark:text-brand-400">{{ __('messages.auth.register_badge') }}</p>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.auth.register_title') }}</h1>
            <p class="text-sm text-gray-600 dark:text-graphite-400">{{ __('messages.auth.register_description') }}</p>
        </div>

        <section
            x-show="step === 1"
            x-ref="stepBasic"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]"
        >
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.auth.step_basic_title') }}</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">{{ __('messages.auth.step_basic_description') }}</p>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <x-input-label for="sponsor_name" :value="__('messages.auth.sponsor_name')" />
                        <x-text-input id="sponsor_name" class="mt-1 block w-full" type="text" :value="$sponsor->name" readonly />
                        <p class="mt-1 text-xs text-gray-600 dark:text-graphite-400">{{ __('messages.auth.sponsor_code') }}: {{ $sponsor->currentAffiliateCode() }}</p>
                        <x-input-error :messages="$errors->get('sponsor_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="name" :value="__('messages.name')" />
                        <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" x-model="name" x-ref="nameInput" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="phone-display" :value="__('messages.auth.phone_label')" />
                        {{-- Hidden input: form submission + Alpine binding --}}
                        <input type="hidden" id="phone-hidden" name="phone" x-model="phone">
                        <input
                            type="tel"
                            id="phone-display"
                            autocomplete="tel"
                            required
                            class="mt-1 block w-full border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 dark:placeholder-graphite-500 dark:focus:border-brand-500 dark:focus:ring-brand-500"
                        >
                        <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.auth.phone_hint') }}</p>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="identification" :value="__('messages.auth.identification_label')" />
                        <x-text-input id="identification" class="mt-1 block w-full" type="text" name="identification" x-model="identification" x-ref="identificationInput" x-on:input="identificationAvailabilityError = ''; $refs.identificationInput.setCustomValidity('');" :value="old('identification')" required autocomplete="off" />
                        <p x-show="identificationAvailabilityError" x-text="identificationAvailabilityError" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
                        <x-input-error :messages="$errors->get('identification')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="address" :value="__('messages.profile.address_label')" />
                        <x-text-input id="address" class="mt-1 block w-full" type="text" name="address" :value="old('address')" required autocomplete="street-address" />
                        <x-input-error :messages="$errors->get('address')" class="mt-2" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="email" :value="__('messages.email')" />
                        <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" x-model="email" x-ref="emailInput" x-on:input="emailAvailabilityError = ''; gmailOnlyError = ''; $refs.emailInput.setCustomValidity('');" :value="old('email')" required autocomplete="username" placeholder="tunombre@gmail.com" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.auth.email_gmail_only') }}</p>
                        <p x-show="emailAvailabilityError" x-text="emailAvailabilityError" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
                        <p x-show="gmailOnlyError" x-text="gmailOnlyError" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('messages.password')" />
                        <div class="relative">
                            <x-text-input id="password" class="mt-1 block w-full pr-10" x-bind:type="showPassword ? 'text' : 'password'" name="password" x-ref="passwordInput" x-model="passwordValue" x-on:input="$refs.passwordInput.setCustomValidity(''); $refs.passwordConfirmationInput?.setCustomValidity('');" required autocomplete="new-password" minlength="8" />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 mt-1 px-3 text-gray-500 hover:text-gray-700 dark:text-graphite-400 dark:hover:text-graphite-200"
                                x-on:click="showPassword = !showPassword"
                                x-bind:aria-label="showPassword ? @js(__('messages.auth.hide_password')) : @js(__('messages.auth.show_password'))"
                                x-bind:title="showPassword ? @js(__('messages.auth.hide_password')) : @js(__('messages.auth.show_password'))"
                            >
                                <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.477 10.488A3 3 0 0 0 12 15a3 3 0 0 0 2.524-4.512M9.88 5.09A9.94 9.94 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a10.014 10.014 0 0 1-4.028 5.056M6.228 6.228a10.012 10.012 0 0 0-4.192 5.455 1.012 1.012 0 0 0 0 .639C3.423 16.493 7.36 19.5 12 19.5a9.97 9.97 0 0 0 5.529-1.676" />
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.auth.password_policy_help') }}</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />
                        <div class="relative">
                            <x-text-input id="password_confirmation" class="mt-1 block w-full pr-10" x-bind:type="showPasswordConfirmation ? 'text' : 'password'" name="password_confirmation" x-ref="passwordConfirmationInput" x-model="passwordConfirmationValue" x-on:input="$refs.passwordConfirmationInput.setCustomValidity('');" required autocomplete="new-password" />
                            <button
                                type="button"
                                class="absolute inset-y-0 right-0 mt-1 px-3 text-gray-500 hover:text-gray-700 dark:text-graphite-400 dark:hover:text-graphite-200"
                                x-on:click="showPasswordConfirmation = !showPasswordConfirmation"
                                x-bind:aria-label="showPasswordConfirmation ? @js(__('messages.auth.hide_password')) : @js(__('messages.auth.show_password'))"
                                x-bind:title="showPasswordConfirmation ? @js(__('messages.auth.hide_password')) : @js(__('messages.auth.show_password'))"
                            >
                                <svg x-show="!showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg x-show="showPasswordConfirmation" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18M10.477 10.488A3 3 0 0 0 12 15a3 3 0 0 0 2.524-4.512M9.88 5.09A9.94 9.94 0 0 1 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a10.014 10.014 0 0 1-4.028 5.056M6.228 6.228a10.012 10.012 0 0 0-4.192 5.455 1.012 1.012 0 0 0 0 .639C3.423 16.493 7.36 19.5 12 19.5a9.97 9.97 0 0 0 5.529-1.676" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>
            </div>

            <aside class="rounded-3xl border border-gray-200 bg-gradient-to-br from-brand-50 to-white p-6 dark:border-graphite-800 dark:from-brand-900/10 dark:to-graphite-950/60">
                <h3 class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-700 dark:text-brand-300">{{ __('messages.auth.step_summary_title') }}</h3>
                <p class="mt-3 text-sm text-gray-700 dark:text-graphite-300">{{ __('messages.auth.basic_side_note') }}</p>
                <ul class="mt-4 space-y-3 text-sm text-gray-600 dark:text-graphite-400">
                    <li>{{ __('messages.auth.basic_side_item_1') }}</li>
                    <li>{{ __('messages.auth.basic_side_item_2') }}</li>
                    <li>{{ __('messages.auth.basic_side_item_3') }}</li>
                </ul>
            </aside>
        </section>

        <section
            x-show="step === 2"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            class="grid gap-6 lg:grid-cols-[1fr_0.9fr]"
        >
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-graphite-100">{{ __('messages.auth.step_confirm_title') }}</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">{{ __('messages.auth.step_confirm_description') }}</p>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 p-4 dark:border-graphite-800">
                        <p class="text-xs uppercase tracking-[0.25em] text-gray-500 dark:text-graphite-500">{{ __('messages.name') }}</p>
                        <p class="mt-2 font-medium text-gray-900 dark:text-graphite-100" x-text="name"></p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-4 dark:border-graphite-800">
                        <p class="text-xs uppercase tracking-[0.25em] text-gray-500 dark:text-graphite-500">{{ __('messages.auth.phone_label') }}</p>
                        <p class="mt-2 font-medium text-gray-900 dark:text-graphite-100" x-text="phone ? '+' + phone : ''"></p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-4 dark:border-graphite-800">
                        <p class="text-xs uppercase tracking-[0.25em] text-gray-500 dark:text-graphite-500">{{ __('messages.auth.identification_label') }}</p>
                        <p class="mt-2 font-medium text-gray-900 dark:text-graphite-100" x-text="identification"></p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 p-4 dark:border-graphite-800 md:col-span-2">
                        <p class="text-xs uppercase tracking-[0.25em] text-gray-500 dark:text-graphite-500">{{ __('messages.email') }}</p>
                        <p class="mt-2 font-medium text-gray-900 dark:text-graphite-100" x-text="email"></p>
                    </div>
                </div>
            </div>

            <aside class="rounded-3xl border border-brand-200 bg-brand-50/80 p-6 dark:border-brand-800 dark:bg-brand-900/10">
                <h3 class="text-sm font-semibold uppercase tracking-[0.25em] text-brand-700 dark:text-brand-300">{{ __('messages.auth.confirm_notice_title') }}</h3>
                <p class="mt-3 text-sm text-brand-900 dark:text-brand-200">{{ __('messages.auth.confirm_notice_body') }}</p>
                <ul class="mt-4 space-y-3 text-sm text-brand-900 dark:text-brand-200">
                    <li>{{ __('messages.auth.confirm_notice_item_2') }}</li>
                    <li>{{ __('messages.auth.basic_side_item_1') }}</li>
                </ul>
            </aside>
        </section>

        <div class="flex flex-col gap-4 border-t border-gray-200 pt-6 dark:border-graphite-800 sm:flex-row sm:items-center sm:justify-between">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-graphite-400 dark:hover:text-brand-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 focus:ring-offset-white dark:focus:ring-offset-graphite-900" href="{{ route('login') }}">
                {{ __('messages.already_registered') }}
            </a>

            <div class="flex items-center gap-3">
                <x-secondary-button type="button" x-show="step === 2" x-on:click="backToBasic()">
                    {{ __('messages.auth.previous_step') }}
                </x-secondary-button>

                <x-primary-button type="button" x-show="step === 1" x-on:click="goToConfirm()">
                    {{ __('messages.auth.next_step') }}
                </x-primary-button>

                <x-primary-button x-show="step === 2">
                    {{ __('messages.register') }}
                </x-primary-button>
            </div>
        </div>
    </form>
    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24/build/css/intlTelInput.min.css">
    <style>
        .iti { display: block; }
        .dark .iti__dropdown-content { background-color: #1e2025; border-color: #3a404b; }
        .dark .iti__search-input { background-color: #2d3139; border-color: #3a404b; color: #e7e8eb; }
        .dark .iti__country:hover, .dark .iti__country.iti__highlight { background-color: #2d3139; }
        .dark .iti__country-name, .dark .iti__dial-code { color: #e7e8eb; }
        .dark .iti__selected-country-primary { background-color: transparent; }
        .dark .iti__arrow { border-top-color: #a8adb9; }
        .dark .iti__arrow--up { border-bottom-color: #a8adb9; }
    </style>
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24/build/js/intlTelInputWithUtils.js"></script>
    <script>
    (function () {
        const phoneDisplay = document.getElementById('phone-display');
        const phoneHidden  = document.getElementById('phone-hidden');
        if (!phoneDisplay || !phoneHidden) return;

        const invalidMsg = @js(__('messages.auth.phone_invalid'));

        const iti = intlTelInput(phoneDisplay, {
            initialCountry: 'ec',
            separateDialCode: true,
            autoPlaceholder: 'aggressive',
        });

        window.phoneIti = iti;

        const oldPhone = phoneHidden.value;
        if (oldPhone) {
            iti.setNumber('+' + oldPhone);
        }

        function sync() {
            const val = phoneDisplay.value.trim();
            if (!val) {
                phoneHidden.value = '';
                phoneHidden.dispatchEvent(new Event('input', { bubbles: true }));
                phoneDisplay.setCustomValidity('');
                return;
            }
            if (iti.isValidNumber()) {
                const digits = iti.getNumber().replace(/\D/g, '');
                phoneHidden.value = digits;
                phoneHidden.dispatchEvent(new Event('input', { bubbles: true }));
                phoneDisplay.setCustomValidity('');
            } else {
                phoneHidden.value = '';
                phoneHidden.dispatchEvent(new Event('input', { bubbles: true }));
                phoneDisplay.setCustomValidity(invalidMsg);
            }
        }

        phoneDisplay.addEventListener('input', sync);
        phoneDisplay.addEventListener('countrychange', sync);
        phoneDisplay.closest('form')?.addEventListener('submit', sync);
    })();
    </script>
    @endpush
</x-guest-layout>
