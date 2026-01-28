<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <x-form-input 
            name="name" 
            type="text" 
            label="Name" 
            :value="old('name')" 
            :required="true"
            :error="$errors->first('name')"
            autofocus 
            autocomplete="name"
        />

        <!-- Email Address -->
        <x-form-input 
            name="email" 
            type="email" 
            label="Email" 
            :value="old('email')" 
            :required="true"
            :error="$errors->first('email')"
            autocomplete="username"
            class="mt-4"
        />

        <!-- Phone -->
        <x-form-input 
            name="phone" 
            type="tel" 
            label="Phone (Optional)" 
            :value="old('phone')" 
            :error="$errors->first('phone')"
            autocomplete="tel"
            class="mt-4"
        />

        <!-- Password -->
        <x-form-input 
            name="password" 
            type="password" 
            label="Password" 
            :required="true"
            :error="$errors->first('password')"
            autocomplete="new-password"
            class="mt-4"
        />

        <!-- Confirm Password -->
        <x-form-input 
            name="password_confirmation" 
            type="password" 
            label="Confirm Password" 
            :required="true"
            :error="$errors->first('password_confirmation')"
            autocomplete="new-password"
            class="mt-4"
        />

        <div class="flex items-center justify-end mt-4">
            <x-button variant="link" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </x-button>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
