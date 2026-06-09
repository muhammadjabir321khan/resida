<x-auth-layout>
    <div class="auth-card">
        <h2 class="auth-card__title">{{ __('Welcome back') }}</h2>
        <p class="auth-card__subtitle">{{ __('Sign in to your account to continue') }}</p>

        @if (session('status'))
            <div class="auth-alert auth-alert--success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="auth-alert auth-alert--error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="auth-field">
                <label for="email" class="auth-field__label">{{ __('Email address') }}</label>
                <div class="auth-field__wrap">
                    <svg class="auth-field__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="you@example.com"
                        class="auth-input"
                    />
                </div>
                @error('email')
                    <p class="auth-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password" class="auth-field__label">{{ __('Password') }}</label>
                <div class="auth-field__wrap">
                    <svg class="auth-field__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="auth-input"
                    />
                </div>
                @error('password')
                    <p class="auth-field__error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-row">
                <label for="remember_me" class="auth-remember">
                    <input id="remember_me" type="checkbox" name="remember" value="1" @checked(old('remember')) />
                    {{ __('Remember me') }}
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-link">{{ __('Forgot password?') }}</a>
                @endif
            </div>

            <button type="submit" class="auth-btn">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                </svg>
                {{ __('Sign in') }}
            </button>
        </form>

        @if (Route::has('register'))
            <p class="auth-footer">
                {{ __("Don't have an account?") }}
                <a href="{{ route('register') }}" class="auth-link">{{ __('Create account') }}</a>
            </p>
        @endif
    </div>
</x-auth-layout>
