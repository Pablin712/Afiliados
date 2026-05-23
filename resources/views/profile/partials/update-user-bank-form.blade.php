<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-graphite-100">
            {{ __('messages.profile.binance_title') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-graphite-400">
            {{ __('messages.profile.binance_description') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
        <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
        <input type="hidden" name="phone" value="{{ old('phone', $user->phone) }}">

        <div>
            <x-input-label for="binance_account_id" :value="__('messages.profile.binance_account_id_label')" />
            <x-text-input
                id="binance_account_id"
                name="binance_account_id"
                type="text"
                class="mt-1 block w-full"
                :value="old('binance_account_id', $userBank?->identification)"
                autocomplete="off"
            />
            <x-input-error class="mt-2" :messages="$errors->get('binance_account_id')" />
        </div>

        <div>
            <x-input-label for="binance_username" :value="__('messages.profile.binance_username_label')" />
            <x-text-input
                id="binance_username"
                name="binance_username"
                type="text"
                class="mt-1 block w-full"
                :value="old('binance_username', $userBank?->owner)"
                autocomplete="off"
            />
            <x-input-error class="mt-2" :messages="$errors->get('binance_username')" />
        </div>

        <p class="text-xs text-gray-500 dark:text-graphite-400">
            {{ __('messages.profile.binance_hint') }}
        </p>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('messages.profile.binance_save_button') }}</x-primary-button>

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
