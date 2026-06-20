@extends('layouts.auth')

@section('content')
    <div class="auth-card">

        <div class="auth-card__header">
            <h2 class="auth-card__title">Welcome back</h2>
            <p class="auth-card__sub">Sign in to continue your learning journey.</p>
        </div>

        @if ($errors->any())
            <div class="auth-alert auth-alert--error">
                <span class="material-symbols-outlined">error</span>
                {{ $errors->first() }}
            </div>
        @endif

        <form class="auth-form" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="auth-field">
                <label class="auth-field__label" for="email">Email address</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">mail</span>
                    <input
                        id="email"
                        class="auth-input @error('email') is-invalid @enderror"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        required
                        autofocus
                        autocomplete="email"
                    />
                </div>
            </div>

            <div class="auth-field">
                <div class="auth-field__row">
                    <label class="auth-field__label" for="password">Password</label>
                </div>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">lock</span>
                    <input
                        id="password"
                        class="auth-input"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    />
                    <button type="button" class="auth-field__toggle" data-pw-toggle="password" aria-label="Show password">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
            </div>

            <label class="auth-checkbox">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                <span class="auth-checkbox__box"></span>
                <span class="auth-checkbox__label">Remember me for 30 days</span>
            </label>

            <button type="submit" class="auth-submit">
                Sign In
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <p class="auth-card__footer">
            Don't have an account?
            <a href="{{ route('register') }}" class="auth-link">Create one free</a>
        </p>

    </div>
@endsection
