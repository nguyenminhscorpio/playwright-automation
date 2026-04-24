@extends('layouts.app')

@section('content')
    <section class="page-section">
        <div class="hero">
            <div>
                <h1 class="hero__title">Dashboard check for {{ $dashboardUserName }}</h1>
                <p class="hero__subtitle">Imported cards, deck totals, and recent import jobs are shown here so you can verify data right after confirm.</p>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card stat-card--warm">
                <div class="stat-card__header">
                    <div class="stat-card__icon stat-card__icon--gold">
                        <span class="material-symbols-outlined">layers</span>
                    </div>
                    <span class="stat-card__label">Decks</span>
                </div>
                <div class="stat-card__value">{{ $dashboardStats['deck_count'] }} <small>decks</small></div>
                <p class="stat-card__hint">Total decks currently available for this study user.</p>
            </article>

            <article class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <span class="material-symbols-outlined">note_stack</span>
                    </div>
                    <span class="stat-card__label">Notes</span>
                </div>
                <div class="stat-card__value">{{ $dashboardStats['note_count'] }} <small>notes</small></div>
                <p class="stat-card__hint">Each valid imported row creates one note.</p>
            </article>

            <article class="stat-card">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <span class="material-symbols-outlined">style</span>
                    </div>
                    <span class="stat-card__label">Cards</span>
                </div>
                <div class="stat-card__value">{{ $dashboardStats['card_count'] }} <small>cards</small></div>
                <p class="stat-card__hint">Cards available after imports and seeded data.</p>
            </article>
        </div>

        <article class="stat-card stat-card--wide">
            <div class="stat-card__header">
                <div class="stat-card__icon">
                    <span class="material-symbols-outlined">upload_file</span>
                </div>
                <span class="stat-card__label">Import Snapshot</span>
            </div>
            <div class="milestone">
                <div>
                    <div class="stat-card__value">{{ $dashboardStats['latest_imported_cards'] }} <small>rows imported</small></div>
                    <p class="muted-text">{{ $dashboardStats['import_count'] }} import jobs have been recorded so far.</p>
                </div>
                <div class="progress-block">
                    <div class="progress-block__meta">
                        <span>Imported vs total cards</span>
                        <strong>
                            @php($progressPercent = $dashboardStats['card_count'] > 0 ? min(100, (int) round(($dashboardStats['latest_imported_cards'] / $dashboardStats['card_count']) * 100)) : 0)
                            {{ $progressPercent }}%
                        </strong>
                    </div>
                    <div class="progress">
                        <div class="progress__bar" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
            </div>
        </article>

        <section class="section-header">
            <div>
                <h2 class="section-title">Decks With Imported Data</h2>
                <p class="section-subtitle">Use this list to confirm card totals changed after import.</p>
            </div>
            <a href="{{ route('imports.index') }}" class="text-action">Go To Import</a>
        </section>

        <div class="deck-grid">
            @forelse ($dashboardDecks as $deck)
                <article class="deck-card">
                    <div class="deck-card__top">
                        <div class="deck-card__icon">
                            <span class="material-symbols-outlined">style</span>
                        </div>
                        <span class="chip {{ $deck['import_jobs_count'] > 0 ? 'chip--green' : '' }}">
                            {{ $deck['import_jobs_count'] > 0 ? 'Imported' : 'Seeded' }}
                        </span>
                    </div>
                    <h3 class="deck-card__title">{{ $deck['name'] }}</h3>
                    <p class="deck-card__desc">{{ $deck['description'] ?: 'No description provided.' }}</p>
                    <div class="progress-block">
                        <div class="progress-block__meta">
                            <span>Cards / Notes / Imports</span>
                            <strong>{{ $deck['cards_count'] }} / {{ $deck['notes_count'] }} / {{ $deck['import_jobs_count'] }}</strong>
                        </div>
                        @php($deckPercent = $dashboardStats['card_count'] > 0 ? min(100, (int) round(($deck['cards_count'] / $dashboardStats['card_count']) * 100)) : 0)
                        <div class="progress">
                            <div class="progress__bar" style="width: {{ $deckPercent }}%"></div>
                        </div>
                    </div>
                    <div class="deck-card__actions">
                        <span class="secondary-button">New Cards {{ $deck['new_cards_count'] }}</span>
                        <a href="{{ route('study.front', ['deck_id' => $deck['id']]) }}" class="primary-button">Review Deck</a>
                    </div>
                </article>
            @empty
                <article class="deck-card">
                    <h3 class="deck-card__title">No deck found</h3>
                    <p class="deck-card__desc">Create or import data first, then refresh this dashboard.</p>
                    <div class="deck-card__actions">
                        <a href="{{ route('imports.index') }}" class="primary-button">Open Import</a>
                    </div>
                </article>
            @endforelse
        </div>

        <section class="section-header">
            <div>
                <h2 class="section-title">Recent Imports</h2>
                <p class="section-subtitle">Quick audit trail for the latest import results.</p>
            </div>
        </section>

        <div class="import-table-wrap">
            <table class="import-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>File</th>
                        <th>Deck</th>
                        <th>Status</th>
                        <th>Imported</th>
                        <th>Skipped/Invalid</th>
                        <th>Finished</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentImports as $job)
                        <tr>
                            <td>#{{ $job['id'] }}</td>
                            <td>{{ $job['file_name'] }}</td>
                            <td>{{ $job['deck_name'] }}</td>
                            <td>
                                <span class="status-badge {{ $job['status'] === 'imported' ? 'status-badge--green' : 'status-badge--amber' }}">
                                    {{ $job['status'] }}
                                </span>
                            </td>
                            <td>{{ $job['success_rows'] }}</td>
                            <td>{{ $job['failed_rows'] }}</td>
                            <td>{{ $job['finished_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted-text import-table__empty">No imports yet. Run one from the Import screen to see results here.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
