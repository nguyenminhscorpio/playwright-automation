<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'FlashMind' }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lexend:wght@500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php($isStudyPage = request()->routeIs('study.*'))
    @php($studyMode = request('mode', 'flip'))
    @php($currentStudyScreen = $studyScreen ?? 'front')
    @php($flipModeUrl = match ($currentStudyScreen) {
        'typing' => route('study.front', ['mode' => 'flip']),
        'answer' => route('study.answer', ['mode' => 'flip']),
        default => route('study.front', ['mode' => 'flip']),
    })
    @php($typingModeUrl = match ($currentStudyScreen) {
        'typing' => route('study.typing', ['mode' => 'typing']),
        'answer' => route('study.answer', ['mode' => 'typing']),
        default => route('study.typing', ['mode' => 'typing']),
    })
    <body
        class="app-body"
        data-page="{{ $page ?? 'default' }}"
        data-study-screen="{{ $studyScreen ?? '' }}"
        data-study-mode="{{ $studyMode }}"
        data-study-front-url="{{ route('study.front') }}"
        data-study-typing-url="{{ route('study.typing') }}"
        data-study-answer-url="{{ route('study.answer') }}"
    >
        <div class="app-shell">
            <aside class="sidebar">
                <div class="brand">
                    <div class="brand__avatar">F</div>
                    <div>
                        <div class="brand__name">FlashMind</div>
                        <div class="brand__tagline">Master Your Learning</div>
                    </div>
                </div>

                <nav class="nav">
                    <a href="{{ route('dashboard') }}" class="nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                        <span class="material-symbols-outlined">dashboard</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('decks.show', 'english-vocabulary') }}" class="nav__link {{ request()->routeIs('decks.*') ? 'is-active' : '' }}">
                        <span class="material-symbols-outlined">layers</span>
                        <span>My Decks</span>
                    </a>
                    <a href="{{ route('study.front') }}" class="nav__link {{ request()->routeIs('study.*') ? 'is-active' : '' }}">
                        <span class="material-symbols-outlined">school</span>
                        <span>Study Session</span>
                    </a>
                    <a href="#" class="nav__link">
                        <span class="material-symbols-outlined">bar_chart</span>
                        <span>Statistics</span>
                    </a>
                </nav>

                <button class="primary-button primary-button--full" type="button">
                    <span class="material-symbols-outlined">add</span>
                    <span>Create New Deck</span>
                </button>
            </aside>

            <div class="main-shell">
                <header class="topbar">
                    <label class="searchbar">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" placeholder="Search decks..." />
                    </label>

                    <div class="topbar__actions">
                        @if ($isStudyPage)
                            <div class="study-mode-switch" data-study-mode-switch>
                                <a
                                    href="{{ $flipModeUrl }}"
                                    class="study-mode-switch__option {{ $studyMode === 'flip' ? 'is-active' : '' }}"
                                    data-study-mode-option="flip"
                                    aria-pressed="{{ $studyMode === 'flip' ? 'true' : 'false' }}"
                                >
                                    <span class="material-symbols-outlined">style</span>
                                    <span>Lật thẻ</span>
                                </a>
                                <a
                                    href="{{ $typingModeUrl }}"
                                    class="study-mode-switch__option {{ $studyMode === 'typing' ? 'is-active' : '' }}"
                                    data-study-mode-option="typing"
                                    aria-pressed="{{ $studyMode === 'typing' ? 'true' : 'false' }}"
                                >
                                    <span class="material-symbols-outlined">keyboard_alt</span>
                                    <span>Nhập chữ</span>
                                </a>
                            </div>
                        @endif
                        <button class="icon-button" type="button">
                            <span class="material-symbols-outlined">notifications</span>
                        </button>
                        <button class="icon-button" type="button">
                            <span class="material-symbols-outlined">help_outline</span>
                        </button>
                        <div class="user-chip">
                            <span>AL</span>
                        </div>
                    </div>
                </header>

                <main class="page">
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
