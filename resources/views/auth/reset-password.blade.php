<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}" x-data="{ showPassword: false, showPasswordConfirmation: false }">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('messages.password')" />
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="new-password" />
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
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('messages.confirm_password')" />

            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10"
                                    x-bind:type="showPasswordConfirmation ? 'text' : 'password'"
                                    name="password_confirmation" required autocomplete="new-password" />
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

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('messages.auth.reset_password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
