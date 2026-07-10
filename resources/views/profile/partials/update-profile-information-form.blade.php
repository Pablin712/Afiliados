<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-graphite-100">
            {{ __('messages.profile.information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
            {{ __('messages.profile.update_info') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        @if (session('phone_required'))
            <div class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                {{ session('phone_required') }}
            </div>
        @endif

        @if (session('address_required'))
            <div class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-200">
                {{ session('address_required') }}
            </div>
        @endif

        <div>
            <x-input-label for="name" :value="__('messages.name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" placeholder="tunombre@gmail.com" />
            <p class="mt-1 text-xs text-gray-500 dark:text-graphite-400">{{ __('messages.auth.email_gmail_only') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-graphite-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 dark:text-graphite-400 dark:hover:text-brand-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 focus:ring-offset-white dark:focus:ring-offset-graphite-900">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="phone-display" :value="__('messages.profile.phone_label')" />
            <input type="hidden" id="phone-hidden" name="phone" value="{{ old('phone', $user->phone) }}">
            <input
                type="tel"
                id="phone-display"
                autocomplete="tel"
                required
                class="mt-1 block w-full border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:ring-brand-500 rounded-md shadow-sm dark:border-graphite-700 dark:bg-graphite-900 dark:text-graphite-100 dark:placeholder-graphite-500 dark:focus:border-brand-500 dark:focus:ring-brand-500"
            >
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="address" :value="__('messages.profile.address_label')" />
            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->address)" autocomplete="street-address" />
            <x-input-error class="mt-2" :messages="$errors->get('address')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-graphite-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

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
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24/build/js/intlTelInputWithUtils.js" defer></script>
<script>
(function () {
    function initPhoneInput() {
        const phoneDisplay = document.getElementById('phone-display');
        const phoneHidden  = document.getElementById('phone-hidden');
        if (!phoneDisplay || !phoneHidden || typeof intlTelInput === 'undefined') return;

        const invalidMsg = @js(__('messages.auth.phone_invalid'));

        const iti = intlTelInput(phoneDisplay, {
            initialCountry: 'ec',
            separateDialCode: true,
            autoPlaceholder: 'aggressive',
            preferredCountries: ['ec', 'co', 'pe', 'mx', 'ar', 'cl', 've', 'us'],
        });

        const currentPhone = phoneHidden.value;
        if (currentPhone) {
            iti.setNumber('+' + currentPhone);
        }

        function sync() {
            const val = phoneDisplay.value.trim();
            if (!val) {
                phoneHidden.value = '';
                phoneDisplay.setCustomValidity('');
                return;
            }
            if (iti.isValidNumber()) {
                phoneHidden.value = iti.getNumber().replace(/\D/g, '');
                phoneDisplay.setCustomValidity('');
            } else {
                phoneHidden.value = '';
                phoneDisplay.setCustomValidity(invalidMsg);
            }
        }

        phoneDisplay.addEventListener('input', sync);
        phoneDisplay.addEventListener('countrychange', sync);
        phoneDisplay.closest('form')?.addEventListener('submit', sync);
    }

    const t = setInterval(function () {
        if (typeof intlTelInput !== 'undefined') { clearInterval(t); initPhoneInput(); }
    }, 50);
})();
</script>
@endpush
