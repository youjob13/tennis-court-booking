<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <x-form-input 
            name="email" 
            type="email" 
            label="Email" 
            :value="old('email')" 
            :required="true"
            :error="$errors->first('email')"
            autofocus 
            autocomplete="username"
        />

        <!-- Password -->
        <x-form-input 
            name="password" 
            type="password" 
            label="Password" 
            :required="true"
            :error="$errors->first('password')"
            autocomplete="current-password"
            class="mt-4"
        />

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <x-button variant="link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </x-button>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
