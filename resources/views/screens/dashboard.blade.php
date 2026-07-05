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
        <div class="hero">
            <div>
                <h1 class="hero__title">Welcome back, {{ $dashboardUserName }}</h1>
                <p class="hero__subtitle">Track your streak, monthly milestone, and the decks that need attention most today.</p>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card stat-card--warm">
                <div class="stat-card__header">
                    <div class="stat-card__icon stat-card__icon--gold"><span class="material-symbols-outlined">local_fire_department</span></div>
                    <span class="stat-card__label">Daily Streak</span>
                </div>
                <div class="stat-card__value">{{ $dashboardStats['daily_streak'] }} <small>days</small></div>
                <p class="stat-card__hint">Keep the chain alive with one more review session today.</p>
            </article>

            <article class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon"><span class="material-symbols-outlined">trophy</span></div>
                    <span class="stat-card__label">Due Today</span>
                </div>
                <div class="stat-card__value">{{ $dashboardStats['totals']['due_count'] }} <small>cards</small></div>
                <p class="stat-card__hint">Cards in learning, relearning, or already due for review.</p>
            </article>

            <article class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon"><span class="material-symbols-outlined">layers</span></div>
                    <span class="stat-card__label">Decks</span>
                </div>
                <div class="stat-card__value">{{ $dashboardStats['totals']['deck_count'] }} <small>decks</small></div>
                <p class="stat-card__hint">{{ $dashboardStats['totals']['card_count'] }} cards and {{ $dashboardStats['totals']['note_count'] }} notes in total.</p>
            </article>
        </div>

        <article class="stat-card stat-card--wide">
            <div class="stat-card__header">
                <div class="stat-card__icon"><span class="material-symbols-outlined">military_tech</span></div>
                <span class="stat-card__label">Learning Milestone</span>
            </div>
            <div class="milestone">
                <div>
                    <div class="stat-card__value">{{ $dashboardStats['monthly_learned'] }} <small>/ {{ $dashboardStats['monthly_goal'] }}</small></div>
                    <p class="muted-text">Cards graduated into review this month versus the current monthly goal.</p>
                </div>
                <div class="progress-block">
                    <div class="progress-block__meta">
                        <span>Monthly progress</span>
                        <strong>
                            @php($progressPercent = $dashboardStats['monthly_goal'] > 0 ? min(100, (int) round(($dashboardStats['monthly_learned'] / $dashboardStats['monthly_goal']) * 100)) : 0)
                            {{ $progressPercent }}%
                        </strong>
                    </div>
                    <div class="progress"><div class="progress__bar" style="width: {{ $progressPercent }}%"></div></div>
                </div>
            </div>
        </article>

        <section class="section-header">
            <div>
                <h2 class="section-title">Active Decks</h2>
                <p class="section-subtitle">Jump into review or open deck detail to manage cards directly.</p>
            </div>
            <a href="{{ route('imports.index') }}" class="text-action">Go To Import</a>
        </section>

        <div class="deck-grid">
            @forelse ($dashboardDecks as $deck)
                <article class="deck-card" data-deck-card data-deck-id="{{ $deck['id'] }}">
                    <div class="deck-card__top">
                        <div class="deck-card__icon"><span class="material-symbols-outlined">style</span></div>
                        <div class="deck-card__top-actions">
                            <span class="chip {{ $deck['due_count'] > 0 ? 'chip--amber' : 'chip--green' }}">{{ $deck['due_count'] > 0 ? $deck['due_count'] . ' due' : 'On track' }}</span>
                            <button class="icon-button icon-button--small" type="button" data-delete-deck-button data-deck-name="{{ $deck['name'] }}" aria-label="Delete deck"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                        <button class="dash-deck__del" type="button"
                                data-delete-deck-button data-deck-name="{{ $deck['name'] }}"
                                aria-label="Delete deck">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                    <h3 class="deck-card__title">{{ $deck['name'] }}</h3>
                    <p class="deck-card__desc">{{ $deck['description'] ?: 'No description provided.' }}</p>
                    <div class="progress-block">
                        <div class="progress-block__meta">
                            <span>Learned / Total</span>
                            <strong>{{ $deck['learned_count'] }} / {{ $deck['total_count'] }}</strong>
                        </div>
                        <div class="progress"><div class="progress__bar" style="width: {{ $deck['mastery_percent'] }}%"></div></div>
                    </div>
                    <div class="deck-card__actions">
                        <a href="{{ route('decks.show', ['deck' => $deck['id']]) }}" class="secondary-button">Open Deck</a>
                        <a href="{{ route('study.front', ['deck_id' => $deck['id']]) }}" class="primary-button">Review {{ $deck['due_count'] }} Cards</a>
                    </div>

                </article>
            @empty
                <article class="deck-card">
                    <h3 class="deck-card__title">No deck found</h3>
                    <p class="deck-card__desc">Create a new deck or import a TXT file to start building your study system.</p>
                    <div class="deck-card__actions"><button class="primary-button" type="button" data-create-deck-button>Create First Deck</button></div>
                </article>
            @endforelse
        </div>

        <dialog id="delete-deck-modal" class="custom-modal">
            <form method="dialog" class="custom-modal__form" data-delete-deck-form>
                <div class="custom-modal__header">
                    <h2>Delete Deck</h2>
                    <button type="button" class="icon-button" data-close-delete-deck-modal-button><span class="material-symbols-outlined">close</span></button>
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
                    <button type="button" class="secondary-button" data-close-delete-deck-modal-button>Cancel</button>
                    <button
                        type="submit"
                        class="primary-button primary-button--success"
                        style="background: var(--danger); box-shadow: 0 8px 18px rgba(186, 26, 26, 0.18);"
                        data-delete-deck-submit-button
                    >
                        Delete
                    </button>
                </div>
            </form>
        </dialog>

        <section class="section-header">
            <div>
                <h2 class="section-title">Recent Imports</h2>
                <p class="section-subtitle">Quick audit trail for the latest import results.</p>
            </div>
        </section>

        <div class="import-table-wrap">
            <table class="import-table">
                <thead>
                    <tr><th>Job</th><th>File</th><th>Deck</th><th>Status</th><th>Imported</th><th>Skipped/Invalid</th><th>Finished</th></tr>
                </thead>
                <tbody>
                    @forelse ($recentImports as $job)
                        <tr>
                            <td>#{{ $job['id'] }}</td>
                            <td>{{ $job['file_name'] }}</td>
                            <td>{{ $job['deck_name'] }}</td>
                            <td><span class="status-badge {{ $job['status'] === 'imported' ? 'status-badge--green' : 'status-badge--amber' }}">{{ $job['status'] }}</span></td>
                            <td>{{ $job['success_rows'] }}</td>
                            <td>{{ $job['failed_rows'] }}</td>
                            <td>{{ $job['finished_at'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted-text import-table__empty">No imports yet. Run one from the Import screen to see results here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>
@endsection
