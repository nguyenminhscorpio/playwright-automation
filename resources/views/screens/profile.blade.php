@extends('layouts.app')

@section('content')
    @php
        $initials = collect(explode(' ', trim($user->name)))
            ->map(fn($w) => strtoupper(substr($w, 0, 1)))
            ->take(2)
            ->implode('');
    @endphp

    <section class="page-section">

        <div class="profile-header">
            <div class="profile-avatar">{{ $initials }}</div>
            <div>
                <h1 class="profile-header__name">{{ $user->name }}</h1>
                <p class="profile-header__email">{{ $user->email }}</p>
                <p class="profile-header__since">Member since {{ $user->created_at->format('F Y') }}</p>
            </div>
        </div>

        <div class="profile-grid">

            {{-- ── Account Information ──────────────────────────── --}}
            <section class="profile-card">
                <div class="profile-card__head">
                    <div class="profile-card__head-icon">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <div>
                        <h2 class="profile-card__title">Account Information</h2>
                        <p class="profile-card__desc">Update your name and email address.</p>
                    </div>
                </div>

                @if (session('profile_success'))
                    <div class="profile-alert profile-alert--ok">
                        <span class="material-symbols-outlined">check_circle</span>
                        {{ session('profile_success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="profile-field">
                        <label class="profile-field__label" for="name">Full name</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">badge</span>
                            <input
                                id="name"
                                class="profile-input @error('name') is-invalid @enderror"
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                autocomplete="name"
                            />
                        </div>
                        @error('name')
                            <span class="profile-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label class="profile-field__label" for="email">Email address</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">mail</span>
                            <input
                                id="email"
                                class="profile-input @error('email') is-invalid @enderror"
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                autocomplete="email"
                            />
                        </div>
                        @error('email')
                            <span class="profile-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="profile-form__actions">
                        <button type="submit" class="profile-btn profile-btn--primary">
                            <span class="material-symbols-outlined">save</span>Save Changes
                        </button>
                    </div>
                </form>
            </section>

            {{-- ── Change Password ──────────────────────────────── --}}
            <section class="profile-card">
                <div class="profile-card__head">
                    <div class="profile-card__head-icon profile-card__head-icon--warning">
                        <span class="material-symbols-outlined">lock</span>
                    </div>
                    <div>
                        <h2 class="profile-card__title">Change Password</h2>
                        <p class="profile-card__desc">Use a strong password of at least 8 characters.</p>
                    </div>
                </div>

                @if (session('password_success'))
                    <div class="profile-alert profile-alert--ok">
                        <span class="material-symbols-outlined">check_circle</span>
                        {{ session('password_success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.password') }}" class="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="profile-field">
                        <label class="profile-field__label" for="current_password">Current password</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">lock_open</span>
                            <input
                                id="current_password"
                                class="profile-input @error('current_password') is-invalid @enderror"
                                type="password"
                                name="current_password"
                                placeholder="Your current password"
                                autocomplete="current-password"
                            />
                            <button type="button" class="profile-field__toggle" data-pw-toggle="current_password">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                        @error('current_password')
                            <span class="profile-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label class="profile-field__label" for="new_password">New password</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">lock</span>
                            <input
                                id="new_password"
                                class="profile-input @error('password') is-invalid @enderror"
                                type="password"
                                name="password"
                                placeholder="Min. 8 characters"
                                autocomplete="new-password"
                            />
                            <button type="button" class="profile-field__toggle" data-pw-toggle="new_password">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                        @error('password')
                            <span class="profile-field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="profile-field">
                        <label class="profile-field__label" for="password_confirmation">Confirm new password</label>
                        <div class="profile-field__wrap">
                            <span class="profile-field__icon material-symbols-outlined">lock_clock</span>
                            <input
                                id="password_confirmation"
                                class="profile-input"
                                type="password"
                                name="password_confirmation"
                                placeholder="Repeat new password"
                                autocomplete="new-password"
                            />
                        </div>
                    </div>

                    <div class="profile-form__actions">
                        <button type="submit" class="profile-btn profile-btn--primary">
                            <span class="material-symbols-outlined">key</span>Update Password
                        </button>
                    </div>
                </form>
            </section>

            {{-- ── Danger zone ──────────────────────────────────── --}}
            <section class="profile-card profile-card--danger">
                <div class="profile-card__head">
                    <div class="profile-card__head-icon profile-card__head-icon--danger">
                        <span class="material-symbols-outlined">logout</span>
                    </div>
                    <div>
                        <h2 class="profile-card__title">Sign Out</h2>
                        <p class="profile-card__desc">End your current session on this device.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="profile-btn profile-btn--danger">
                        <span class="material-symbols-outlined">logout</span>Sign Out
                    </button>
                </form>
            </section>

        </div>
    </section>
@endsection
