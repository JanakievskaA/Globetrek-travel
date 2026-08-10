<x-layouts.app title="Log in">

    <x-ui.page-title title="Log in" :breadcrumbs="['Log in' => null]" />

    <div class="flat-section">
        <div class="container">
            <div class="gt-card gt-auth-card">
                <form action="{{ route('login.store') }}" method="POST" class="gt-stack">
                    @csrf

                    <div class="gt-field">
                        <label for="lg-email">Email</label>
                        <input type="email" id="lg-email" name="email" required autofocus
                            value="{{ old('email') }}">
                        @error('email') <p class="gt-form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="gt-field">
                        <label for="lg-password">Password</label>
                        <input type="password" id="lg-password" name="password" required>
                        @error('password') <p class="gt-form-error">{{ $message }}</p> @enderror
                    </div>

                    <label class="gt-check">
                        <input type="checkbox" name="remember" value="1">
                        <span>Keep me signed in</span>
                    </label>

                    <button type="submit" class="tf-btn primary hover-1 w-full"><span>Log in</span></button>

                    <p class="text-center gt-hint">
                        No account yet? <a href="{{ route('register') }}" class="text_primary">Create one</a>
                    </p>
                </form>

                <div class="mt-6 p-4" style="background:var(--secondary-color);border-radius:12px">
                    <div class="h5 mb-2">Demo accounts</div>
                    <p class="gt-hint mb-1">Admin — <strong>admin@globetrek.test</strong> / password</p>
                    <p class="gt-hint mb-1">Manager — <strong>manager@globetrek.test</strong> / password</p>
                    <p class="gt-hint">Customer — <strong>customer@globetrek.test</strong> / password</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
