<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'FlashMind' }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lexend:wght@500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')

        @php
            $authUser     = auth()->user();
            $authName     = $authUser?->name ?? 'Learner';
            $authInitials = collect(explode(' ', trim($authName)))
                ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                ->take(2)->implode('');
            $isStudyPage = request()->routeIs('study.*');
            $studyMode = request('mode', 'flip');
            $studyRouteVersion = 'study-v2';
            $studyDeckQuery = array_filter([
                'deck_id' => request('deck_id'),
                'sv' => $studyRouteVersion,
            ], fn ($value) => $value !== null && $value !== '');
            $currentStudyScreen = $studyScreen ?? 'front';
            $flipModeUrl = match ($currentStudyScreen) {
                'typing' => route('study.front', [...$studyDeckQuery, 'mode' => 'flip']),
                'answer' => route('study.answer', [...$studyDeckQuery, 'mode' => 'flip']),
                default => route('study.front', [...$studyDeckQuery, 'mode' => 'flip']),
            };
            $typingModeUrl = match ($currentStudyScreen) {
                'typing' => route('study.typing', [...$studyDeckQuery, 'mode' => 'typing']),
                'answer' => route('study.answer', [...$studyDeckQuery, 'mode' => 'typing']),
                default => route('study.typing', [...$studyDeckQuery, 'mode' => 'typing']),
            };
        @endphp
    </head>
    <body
        class="app-body"
        data-auth-user-id="{{ $authUser?->id ?? '' }}"
        data-page="{{ $page ?? 'default' }}"
        data-study-screen="{{ $studyScreen ?? '' }}"
        data-study-mode="{{ $studyMode }}"
        data-study-user-id="{{ $studyUserId ?? '' }}"
        data-study-deck-id="{{ $studyDeckId ?? '' }}"
        data-study-deck-name="{{ $studyDeckName ?? '' }}"
        data-study-front-url="{{ route('study.front', $studyDeckQuery) }}"
        data-study-typing-url="{{ route('study.typing', $studyDeckQuery) }}"
        data-study-answer-url="{{ route('study.answer', $studyDeckQuery) }}"
        data-study-session-api-url="{{ url('/api/study/session') }}"
        data-study-check-answer-url-template="{{ url('/api/study/cards/__CARD__/check-answer') }}"
        data-study-rate-url-template="{{ url('/api/study/cards/__CARD__/rate') }}"
        data-import-preview-url="{{ url('/api/imports/txt/preview') }}"
        data-import-confirm-url="{{ url('/api/imports/txt/confirm') }}"
        data-decks-api-url="{{ url('/api/decks') }}"
        data-deck-url-template="{{ url('/api/decks/__DECK__') }}"
        data-cards-api-url="{{ url('/api/cards') }}"
        data-card-url-template="{{ url('/api/cards/__CARD__') }}"
    >
        <div class="app-shell">
            <aside class="sidebar">
                <div class="sidebar__brand">
                    <div class="sidebar__logo">
                        <span class="material-symbols-outlined">auto_stories</span>
                    </div>
                    <div>
                        <div class="sidebar__name">FlashMind</div>
                        <div class="sidebar__tagline">Master Your Learning</div>
                    </div>
                </div>

                <nav class="nav">
                    <span class="nav__label">Menu</span>
                    <a href="{{ route('dashboard') }}" class="nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                        <div class="nav__icon"><span class="material-symbols-outlined">dashboard</span></div>
                        <span>Dashboard</span>
                    </a>
                    @php($navDeckId = request()->route('deck') ?? ($authUser?->decks()->value('id') ?? 1))
                    <a href="{{ request()->routeIs('decks.*') ? url()->current() : route('decks.show', $navDeckId) }}" class="nav__link {{ request()->routeIs('decks.*') ? 'is-active' : '' }}">
                        <div class="nav__icon"><span class="material-symbols-outlined">layers</span></div>
                        <span>My Decks</span>
                    </a>
                    <a href="{{ route('study.front', ['sv' => $studyRouteVersion]) }}" class="nav__link {{ request()->routeIs('study.*') ? 'is-active' : '' }}">
                        <div class="nav__icon"><span class="material-symbols-outlined">school</span></div>
                        <span>Study Session</span>
                    </a>
                    <a href="{{ route('imports.index') }}" class="nav__link {{ request()->routeIs('imports.*') ? 'is-active' : '' }}">
                        <div class="nav__icon"><span class="material-symbols-outlined">upload_file</span></div>
                        <span>Import</span>
                    </a>
                </nav>

                <div class="sidebar__footer">
                    <a href="{{ route('profile') }}" class="sidebar__user">
                        <div class="sidebar__user-avatar">{{ $authInitials }}</div>
                        <div class="sidebar__user-info">
                            <div class="sidebar__user-name">{{ $authName }}</div>
                            <div class="sidebar__user-status">
                                <span class="sidebar__user-dot"></span>Active
                            </div>
                        </div>
                        <span class="material-symbols-outlined sidebar__user-arrow">chevron_right</span>
                    </a>
                </div>
            </aside>

            <div class="main-shell">
                <header class="topbar">
                    <label class="searchbar">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" placeholder="Search decks..." />
                    </label>

                    <div class="topbar__center">
                        <div class="topbar__page-label">
                            @php($pageLabels = ['dashboard' => 'Dashboard', 'deck-detail' => 'My Decks', 'imports' => 'Import'])
                            <span class="material-symbols-outlined topbar__page-icon">
                                @switch($page ?? 'default')
                                    @case('dashboard') dashboard @break
                                    @case('deck-detail') layers @break
                                    @case('imports') upload_file @break
                                    @default school @break
                                @endswitch
                            </span>
                            <span>{{ $pageLabels[$page ?? ''] ?? 'FlashMind' }}</span>
                        </div>
                    </div>

                    <div class="topbar__actions">
                        @if ($isStudyPage)
                            <div class="study-mode-switch" data-study-mode-switch>
                                <a href="{{ $flipModeUrl }}" class="study-mode-switch__option {{ $studyMode === 'flip' ? 'is-active' : '' }}" data-study-mode-option="flip" aria-pressed="{{ $studyMode === 'flip' ? 'true' : 'false' }}">
                                    <span class="material-symbols-outlined">style</span>
                                    <span>Flip</span>
                                </a>
                                <a href="{{ $typingModeUrl }}" class="study-mode-switch__option {{ $studyMode === 'typing' ? 'is-active' : '' }}" data-study-mode-option="typing" aria-pressed="{{ $studyMode === 'typing' ? 'true' : 'false' }}">
                                    <span class="material-symbols-outlined">keyboard_alt</span>
                                    <span>Typing</span>
                                </a>
                            </div>
                        @endif
                        <a href="{{ route('profile') }}" class="user-chip" title="{{ auth()->user()?->name ?? 'Profile' }}">
                            <span>{{ $authInitials ?? 'ME' }}</span>
                        </a>
                    </div>
                </header>

                <main class="page">
                    @yield('content')
                </main>
            </div>
        </div>

        <dialog id="create-deck-modal" class="custom-modal">
            <form method="dialog" class="custom-modal__form">
                <div class="custom-modal__header">
                    <div class="custom-modal__header-icon">
                        <span class="material-symbols-outlined">layers</span>
                    </div>
                    <div class="custom-modal__header-text">
                        <h2>Create New Deck</h2>
                        <p>Organise your flashcards into a new deck.</p>
                    </div>
                    <button type="button" class="custom-modal__close" onclick="document.getElementById('create-deck-modal').close()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="custom-modal__body">
                    <label class="modal-field">
                        <span class="modal-field__label">Deck Name <span class="modal-field__required">*</span></span>
                        <input type="text" id="new-deck-name" class="modal-input" placeholder="e.g. English Vocabulary" required autocomplete="off" />
                    </label>
                    <label class="modal-field">
                        <span class="modal-field__label">Description <span class="modal-field__optional">(optional)</span></span>
                        <textarea id="new-deck-description" class="modal-input modal-input--textarea" rows="3" placeholder="Short description for this deck"></textarea>
                    </label>
                </div>
                <div class="custom-modal__footer">
                    <button type="button" class="modal-btn modal-btn--cancel" onclick="document.getElementById('create-deck-modal').close()">Cancel</button>
                    <button type="submit" class="modal-btn modal-btn--submit" id="create-deck-submit-btn">
                        <span class="material-symbols-outlined">add</span>
                        Create Deck
                    </button>
                </div>
            </form>
        </dialog>
    </body>
</html>
