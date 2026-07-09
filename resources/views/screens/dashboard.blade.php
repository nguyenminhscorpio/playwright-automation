@extends('layouts.app')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $progressPercent = $dashboardStats['monthly_goal'] > 0
            ? min(100, (int) round(($dashboardStats['monthly_learned'] / $dashboardStats['monthly_goal']) * 100))
            : 0;
        $firstDeck = $dashboardDecks[0] ?? null;
    @endphp

    <section class="page-section" data-dashboard-app data-dashboard-user-id="{{ $dashboardUserId ?? '' }}">

        {{-- ── Greeting header ──────────────────────────────── --}}
        <div class="dash-greeting">
            <div>
                <h1 class="dash-greeting__title">{{ $greeting }}, {{ $dashboardUserName }} 👋</h1>
                <p class="dash-greeting__sub">Track your streak, review due cards, and manage your decks.</p>
            </div>
            <div class="dash-greeting__actions">
                <a href="{{ route('study.typing', $firstDeck ? ['deck_id' => $firstDeck['id'], 'mode' => 'typing'] : ['mode' => 'typing']) }}"
                   class="dash-btn dash-btn--primary">
                    <span class="material-symbols-outlined">school</span>
                    Study Now
                </a>
                <a href="{{ route('imports.index') }}" class="dash-btn">
                    <span class="material-symbols-outlined">upload_file</span>
                    Import
                </a>
            </div>
        </div>

        {{-- ── Stat cards ────────────────────────────────────── --}}
        <div class="dash-stats">

            <article class="dash-stat dash-stat--streak">
                <div class="dash-stat__icon">
                    <span class="material-symbols-outlined">local_fire_department</span>
                </div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Daily Streak</span>
                    <div class="dash-stat__val">{{ $dashboardStats['daily_streak'] }}<span class="dash-stat__unit">days</span></div>
                    <p class="dash-stat__hint">Keep the chain alive today</p>
                </div>
            </article>

            <article class="dash-stat {{ $dashboardStats['totals']['due_count'] > 0 ? 'dash-stat--due' : '' }}">
                <div class="dash-stat__icon">
                    <span class="material-symbols-outlined">task_alt</span>
                </div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Due Today</span>
                    <div class="dash-stat__val">{{ $dashboardStats['totals']['due_count'] }}<span class="dash-stat__unit">cards</span></div>
                    <p class="dash-stat__hint">Learning, relearning & review</p>
                </div>
            </article>

            <article class="dash-stat">
                <div class="dash-stat__icon">
                    <span class="material-symbols-outlined">layers</span>
                </div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Library</span>
                    <div class="dash-stat__val">{{ $dashboardStats['totals']['card_count'] }}<span class="dash-stat__unit">cards</span></div>
                    <p class="dash-stat__hint">{{ $dashboardStats['totals']['deck_count'] }} decks · {{ $dashboardStats['totals']['note_count'] }} notes</p>
                </div>
            </article>

            <article class="dash-stat">
                <div class="dash-stat__icon">
                    <span class="material-symbols-outlined">military_tech</span>
                </div>
                <div class="dash-stat__body">
                    <span class="dash-stat__label">Monthly Goal</span>
                    <div class="dash-stat__val">{{ $progressPercent }}<span class="dash-stat__unit">%</span></div>
                    <div class="dash-stat__bar-wrap">
                        <div class="dash-stat__bar-fill" style="width:{{ $progressPercent }}%"></div>
                    </div>
                    <p class="dash-stat__hint">{{ $dashboardStats['monthly_learned'] }} / {{ $dashboardStats['monthly_goal'] }} graduated</p>
                </div>
            </article>

        </div>

        {{-- ── Active Decks ──────────────────────────────────── --}}
        <div class="dash-section-hd">
            <div>
                <h2 class="dash-section-hd__title">Active Decks</h2>
                <p class="dash-section-hd__sub">Jump into a session or manage cards directly.</p>
            </div>
            <button type="button" class="dash-btn" data-create-deck-button>
                <span class="material-symbols-outlined">add</span>New Deck
            </button>
        </div>

        <div class="dash-deck-grid">
            @forelse ($dashboardDecks as $deck)
                <article class="dash-deck" data-deck-card data-deck-id="{{ $deck['id'] }}">

                    <div class="dash-deck__top">
                        <div class="dash-deck__icon">
                            <span class="material-symbols-outlined">style</span>
                        </div>
                        <div class="dash-deck__badges">
                            @if ($deck['due_count'] > 0)
                                <span class="dash-badge dash-badge--due">{{ $deck['due_count'] }} due</span>
                            @else
                                <span class="dash-badge dash-badge--ok">
                                    <span class="material-symbols-outlined">check</span>On track
                                </span>
                            @endif
                        </div>
                        <button class="dash-deck__del" type="button"
                                data-delete-deck-button data-deck-name="{{ $deck['name'] }}"
                                aria-label="Delete deck">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>

                    <h3 class="dash-deck__title">{{ $deck['name'] }}</h3>
                    <p class="dash-deck__desc">{{ $deck['description'] ?: 'No description provided.' }}</p>

                    <div class="dash-deck__progress">
                        <div class="dash-deck__progress-meta">
                            <span>{{ $deck['learned_count'] }} learned</span>
                            <span>{{ $deck['mastery_percent'] }}%</span>
                        </div>
                        <div class="dash-deck__progress-track">
                            <div class="dash-deck__progress-fill" style="width:{{ $deck['mastery_percent'] }}%"></div>
                        </div>
                    </div>

                    <div class="dash-deck__actions">
                        <a href="{{ route('decks.show', ['deck' => $deck['id']]) }}"
                           class="dash-deck__action-secondary">Manage</a>
                        <a href="{{ route('study.typing', ['deck_id' => $deck['id'], 'mode' => 'typing']) }}"
                           class="dash-deck__action-primary">
                            <span class="material-symbols-outlined">play_arrow</span>
                            Review {{ $deck['due_count'] > 0 ? $deck['due_count'].' cards' : 'Deck' }}
                        </a>
                    </div>

                </article>
            @empty
                <div class="dash-empty">
                    <span class="material-symbols-outlined dash-empty__icon">layers</span>
                    <h3 class="dash-empty__title">No decks yet</h3>
                    <p class="dash-empty__desc">Create your first deck or import a TXT file to start studying.</p>
                    <button class="dash-btn dash-btn--primary" type="button" data-create-deck-button>
                        <span class="material-symbols-outlined">add</span>Create First Deck
                    </button>
                </div>
            @endforelse
        </div>

        {{-- ── Delete deck modal ────────────────────────────── --}}
        <dialog id="delete-deck-modal" class="custom-modal">
            <form method="dialog" class="custom-modal__form" data-delete-deck-form>
                <div class="custom-modal__header">
                    <div class="custom-modal__header-icon"
                         style="background:var(--danger-soft);color:var(--danger)">
                        <span class="material-symbols-outlined">delete</span>
                    </div>
                    <div class="custom-modal__header-text">
                        <h2>Delete Deck</h2>
                        <p>This action cannot be undone.</p>
                    </div>
                    <button type="button" class="custom-modal__close" data-close-delete-deck-modal-button>
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="custom-modal__body">
                    <p data-delete-deck-modal-message
                       style="font-size:0.92rem;color:var(--muted);margin:0">
                        Are you sure you want to delete this deck? This also removes its notes and cards.
                    </p>
                    <input type="hidden" data-delete-deck-id-input />
                    <div class="study-feedback is-hidden" data-delete-deck-form-feedback></div>
                </div>
                <div class="custom-modal__footer">
                    <button type="button" class="modal-btn modal-btn--cancel"
                            data-close-delete-deck-modal-button>Cancel</button>
                    <button type="submit" class="modal-btn modal-btn--delete"
                            data-delete-deck-submit-button>
                        <span class="material-symbols-outlined">delete</span>Delete
                    </button>
                </div>
            </form>
        </dialog>

        {{-- ── Recent Imports ────────────────────────────────── --}}
        <div class="dash-section-hd">
            <div>
                <h2 class="dash-section-hd__title">Recent Imports</h2>
                <p class="dash-section-hd__sub">Audit trail for the latest import results.</p>
            </div>
            <a href="{{ route('imports.index') }}" class="dash-btn">
                <span class="material-symbols-outlined">upload_file</span>New Import
            </a>
        </div>

        <div class="dash-imports">
            @forelse ($recentImports as $job)
                <div class="dash-import-row">
                    <span class="dash-import-row__id">#{{ $job['id'] }}</span>
                    <span class="dash-import-row__file">
                        <span class="material-symbols-outlined">description</span>
                        {{ $job['file_name'] }}
                    </span>
                    <span class="dash-import-row__deck">{{ $job['deck_name'] }}</span>
                    <span class="dash-import-badge {{ $job['status'] === 'imported' ? 'dash-import-badge--ok' : 'dash-import-badge--warn' }}">
                        {{ $job['status'] }}
                    </span>
                    <span class="dash-import-row__nums">
                        <span class="dash-import-row__num dash-import-row__num--ok">↑{{ $job['success_rows'] }}</span>
                        <span class="dash-import-row__num">↓{{ $job['failed_rows'] }}</span>
                    </span>
                    <span class="dash-import-row__time">{{ $job['finished_at'] }}</span>
                </div>
            @empty
                <div class="dash-imports__empty">
                    <span class="material-symbols-outlined">upload_file</span>
                    No imports yet —
                    <a href="{{ route('imports.index') }}">run one now</a>
                </div>
            @endforelse
        </div>

    </section>
@endsection
