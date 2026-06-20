@extends('layouts.auth')

@section('content')
    <div class="auth-card">

        <div class="auth-card__header">
            <h2 class="auth-card__title">Create your account</h2>
            <p class="auth-card__sub">Start learning smarter — it only takes a minute.</p>
        </div>

        @if ($errors->any())
            <div class="auth-alert auth-alert--error">
                <span class="material-symbols-outlined">error</span>
                {{ $errors->first() }}
            </div>
        @endif

        <form class="auth-form" method="POST" action="{{ route('register') }}">
            @csrf

            <div class="auth-field">
                <label class="auth-field__label" for="name">Full name</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">person</span>
                    <input
                        id="name"
                        class="auth-input @error('name') is-invalid @enderror"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Your name"
                        required
                        autofocus
                        autocomplete="name"
                    />
                </div>
                @error('name')
                    <span class="auth-field__error">{{ $message }}</span>
                @enderror
            </div>

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
                        autocomplete="email"
                    />
                </div>
                @error('email')
                    <span class="auth-field__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field">
                <label class="auth-field__label" for="password">Password</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">lock</span>
                    <input
                        id="password"
                        class="auth-input @error('password') is-invalid @enderror"
                        type="password"
                        name="password"
                        placeholder="Min. 8 characters"
                        required
                        autocomplete="new-password"
                    />
                    <button type="button" class="auth-field__toggle" data-pw-toggle="password" aria-label="Show password">
                        <span class="material-symbols-outlined">visibility</span>
                    </button>
                </div>
                @error('password')
                    <span class="auth-field__error">{{ $message }}</span>
                @enderror
            </div>

            <div class="auth-field">
                <label class="auth-field__label" for="password_confirmation">Confirm password</label>
                <div class="auth-field__wrap">
                    <span class="auth-field__icon material-symbols-outlined">lock_clock</span>
                    <input
                        id="password_confirmation"
                        class="auth-input"
                        type="password"
                        name="password_confirmation"
                        placeholder="Repeat your password"
                        required
                        autocomplete="new-password"
                    />
                </div>
            </div>

            <button type="submit" class="auth-submit">
                Create Account
                <span class="material-symbols-outlined">arrow_forward</span>
            </button>
        </form>

        <p class="auth-card__footer">
            Already have an account?
            <a href="{{ route('login') }}" class="auth-link">Sign in</a>
        </p>

    </div>
@endsection
