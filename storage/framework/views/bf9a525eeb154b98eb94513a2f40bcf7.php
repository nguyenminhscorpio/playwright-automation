<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <title><?php echo e($title ?? 'FlashMind'); ?></title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Lexend:wght@500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400" rel="stylesheet">

        <?php
            $manifestPath = public_path('build/manifest.json');
            $manifest = file_exists($manifestPath)
                ? json_decode(file_get_contents($manifestPath), true)
                : [];
            $cssFile = $manifest['resources/css/app.css']['file'] ?? '';
            $jsFile  = $manifest['resources/js/app.js']['file'] ?? '';
        ?>
        <?php if($cssFile): ?>
            <link rel="stylesheet" href="<?php echo e(asset('build/' . $cssFile)); ?>" />
        <?php endif; ?>
        <?php if($jsFile): ?>
            <script type="module" src="<?php echo e(asset('build/' . $jsFile)); ?>"></script>
        <?php endif; ?>

        <?php
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
        ?>
    </head>
    <body
        class="app-body"
        data-auth-user-id="<?php echo e($authUser?->id ?? ''); ?>"
        data-page="<?php echo e($page ?? 'default'); ?>"
        data-study-screen="<?php echo e($studyScreen ?? ''); ?>"
        data-study-mode="<?php echo e($studyMode); ?>"
        data-study-user-id="<?php echo e($studyUserId ?? ''); ?>"
        data-study-deck-id="<?php echo e($studyDeckId ?? ''); ?>"
        data-study-deck-name="<?php echo e($studyDeckName ?? ''); ?>"
        data-study-front-url="<?php echo e(route('study.front', $studyDeckQuery)); ?>"
        data-study-typing-url="<?php echo e(route('study.typing', $studyDeckQuery)); ?>"
        data-study-answer-url="<?php echo e(route('study.answer', $studyDeckQuery)); ?>"
        data-study-session-api-url="<?php echo e(url('/api/study/session')); ?>"
        data-study-check-answer-url-template="<?php echo e(url('/api/study/cards/__CARD__/check-answer')); ?>"
        data-study-rate-url-template="<?php echo e(url('/api/study/cards/__CARD__/rate')); ?>"
        data-import-preview-url="<?php echo e(url('/api/imports/txt/preview')); ?>"
        data-import-confirm-url="<?php echo e(url('/api/imports/txt/confirm')); ?>"
        data-decks-api-url="<?php echo e(url('/api/decks')); ?>"
        data-deck-url-template="<?php echo e(url('/api/decks/__DECK__')); ?>"
        data-cards-api-url="<?php echo e(url('/api/cards')); ?>"
        data-card-url-template="<?php echo e(url('/api/cards/__CARD__')); ?>"
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
                    <a href="<?php echo e(route('dashboard')); ?>" class="nav__link <?php echo e(request()->routeIs('dashboard') ? 'is-active' : ''); ?>">
                        <div class="nav__icon"><span class="material-symbols-outlined">dashboard</span></div>
                        <span>Dashboard</span>
                    </a>
                    <?php ($navDeckId = request()->route('deck') ?? ($authUser?->decks()->value('id') ?? 1)); ?>
                    <a href="<?php echo e(request()->routeIs('decks.*') ? url()->current() : route('decks.show', $navDeckId)); ?>" class="nav__link <?php echo e(request()->routeIs('decks.*') ? 'is-active' : ''); ?>">
                        <div class="nav__icon"><span class="material-symbols-outlined">layers</span></div>
                        <span>My Decks</span>
                    </a>
                    <a href="<?php echo e(route('study.front', ['sv' => $studyRouteVersion])); ?>" class="nav__link <?php echo e(request()->routeIs('study.*') ? 'is-active' : ''); ?>">
                        <div class="nav__icon"><span class="material-symbols-outlined">school</span></div>
                        <span>Study Session</span>
                    </a>
                    <a href="<?php echo e(route('imports.index')); ?>" class="nav__link <?php echo e(request()->routeIs('imports.*') ? 'is-active' : ''); ?>">
                        <div class="nav__icon"><span class="material-symbols-outlined">upload_file</span></div>
                        <span>Import</span>
                    </a>
                </nav>

                <div class="sidebar__footer">
                    <a href="<?php echo e(route('profile')); ?>" class="sidebar__user">
                        <div class="sidebar__user-avatar"><?php echo e($authInitials); ?></div>
                        <div class="sidebar__user-info">
                            <div class="sidebar__user-name"><?php echo e($authName); ?></div>
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
                            <?php ($pageLabels = ['dashboard' => 'Dashboard', 'deck-detail' => 'My Decks', 'imports' => 'Import']); ?>
                            <span class="material-symbols-outlined topbar__page-icon">
                                <?php switch($page ?? 'default'):
                                    case ('dashboard'): ?> dashboard <?php break; ?>
                                    <?php case ('deck-detail'): ?> layers <?php break; ?>
                                    <?php case ('imports'): ?> upload_file <?php break; ?>
                                    <?php default: ?> school <?php break; ?>
                                <?php endswitch; ?>
                            </span>
                            <span><?php echo e($pageLabels[$page ?? ''] ?? 'FlashMind'); ?></span>
                        </div>
                    </div>

                    <div class="topbar__actions">
                        <?php if($isStudyPage): ?>
                            <div class="study-mode-switch" data-study-mode-switch>
                                <a href="<?php echo e($flipModeUrl); ?>" class="study-mode-switch__option <?php echo e($studyMode === 'flip' ? 'is-active' : ''); ?>" data-study-mode-option="flip" aria-pressed="<?php echo e($studyMode === 'flip' ? 'true' : 'false'); ?>">
                                    <span class="material-symbols-outlined">style</span>
                                    <span>Flip</span>
                                </a>
                                <a href="<?php echo e($typingModeUrl); ?>" class="study-mode-switch__option <?php echo e($studyMode === 'typing' ? 'is-active' : ''); ?>" data-study-mode-option="typing" aria-pressed="<?php echo e($studyMode === 'typing' ? 'true' : 'false'); ?>">
                                    <span class="material-symbols-outlined">keyboard_alt</span>
                                    <span>Typing</span>
                                </a>
                            </div>
                        <?php endif; ?>
                        <a href="<?php echo e(route('profile')); ?>" class="user-chip" title="<?php echo e(auth()->user()?->name ?? 'Profile'); ?>">
                            <span><?php echo e($authInitials ?? 'ME'); ?></span>
                        </a>
                    </div>
                </header>

                <main class="page">
                    <?php echo $__env->yieldContent('content'); ?>
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
<?php /**PATH C:\laragon\www\playwright-automation\resources\views/layouts/app.blade.php ENDPATH**/ ?>