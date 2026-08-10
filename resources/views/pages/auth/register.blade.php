<x-layouts.app title="Create an account">

    <x-ui.page-title title="Create an account" :breadcrumbs="['Register' => null]" />

    <div class="flat-section">
        <div class="container">
            <div class="gt-card gt-auth-card">
                <form action="{{ route('register.store') }}" method="POST" class="gt-stack">
                    @csrf

                    <div class="gt-field">
                        <label for="rg-name">Full name</label>
                        <input type="text" id="rg-name" name="name" required autofocus value="{{ old('name') }}">
                        @error('name') <p class="gt-form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="gt-field">
                        <label for="rg-email">Email</label>
                        <input type="email" id="rg-email" name="email" required value="{{ old('email') }}">
                        @error('email') <p class="gt-form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="gt-field">
                        <label for="rg-password">Password</label>
                        <input type="password" id="rg-password" name="password" required>
                        <p class="gt-hint mt-1">At least 8 characters.</p>
                        @error('password') <p class="gt-form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="gt-field">
                        <label for="rg-password-confirm">Confirm password</label>
                        <input type="password" id="rg-password-confirm" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="tf-btn primary hover-1 w-full"><span>Create account</span></button>

                    <p class="text-center gt-hint">
                        Already registered? <a href="{{ route('login') }}" class="text_primary">Log in</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
