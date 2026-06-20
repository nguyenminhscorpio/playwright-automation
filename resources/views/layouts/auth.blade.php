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

        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? '';
            $jsFile  = $manifest['resources/js/app.js']['file'] ?? '';
        @endphp
        @if($cssFile)
            <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}" />
        @endif
        @if($jsFile)
            <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
        @endif
    </head>
    <body class="auth-body">
        <div class="auth-shell">

            {{-- Left decorative panel --}}
            <aside class="auth-panel">
                <div class="auth-panel__inner">
                    <div class="auth-panel__logo">
                        <span class="material-symbols-outlined">auto_stories</span>
                    </div>
                    <h1 class="auth-panel__brand">FlashMind</h1>
                    <p class="auth-panel__tagline">The smarter way to learn anything — powered by spaced repetition.</p>

                    <ul class="auth-panel__features">
                        <li>
                            <span class="auth-panel__feat-icon"><span class="material-symbols-outlined">bolt</span></span>
                            <span>Intelligent spaced repetition scheduling</span>
                        </li>
                        <li>
                            <span class="auth-panel__feat-icon"><span class="material-symbols-outlined">trending_up</span></span>
                            <span>Track streaks, goals and mastery progress</span>
                        </li>
                        <li>
                            <span class="auth-panel__feat-icon"><span class="material-symbols-outlined">upload_file</span></span>
                            <span>Import Anki decks in seconds</span>
                        </li>
                    </ul>
                </div>

                {{-- Decorative blobs --}}
                <div class="auth-panel__blob auth-panel__blob--1"></div>
                <div class="auth-panel__blob auth-panel__blob--2"></div>
            </aside>

            {{-- Right form panel --}}
            <main class="auth-main">
                @yield('content')
            </main>

        </div>
    </body>
</html>
