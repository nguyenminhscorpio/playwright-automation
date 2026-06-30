@extends('layouts.app', ['page' => 'deck-detail'])

@section('content')
<div class="dd" data-deck-detail-app data-deck-id="{{ $deck->id }}" data-user-id="{{ $deckDetailUserId ?? '' }}" data-total-cards="{{ $cards->total() }}">

    {{-- ── Breadcrumb ─────────────────────────────────────── --}}
    <nav class="dd-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="dd-breadcrumb__link">
            <span class="material-symbols-outlined">home</span>
            <span>Dashboard</span>
        </a>
        <span class="material-symbols-outlined dd-breadcrumb__sep">chevron_right</span>
        <span class="dd-breadcrumb__current">{{ $deck->name }}</span>
    </nav>

    {{-- ── Hero Header ────────────────────────────────────── --}}
    <header class="dd-hero">
        <div class="dd-hero__info">
            <div class="dd-hero__icon-wrap">
                <span class="material-symbols-outlined">layers</span>
            </div>
            <div class="dd-hero__text">
                <div class="dd-hero__title-row">
                    <h1 class="dd-hero__title">{{ $deck->name }}</h1>
                    <select class="dd-deck-switcher" data-deck-switcher aria-label="Switch deck">
                        @foreach ($allDecks as $d)
                            <option value="{{ $d->id }}" @selected($d->id === $deck->id)>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="dd-hero__desc">{{ $deck->description ?: 'Manage your flashcards, track progress, and import new content.' }}</p>
            </div>
        </div>
        <div class="dd-hero__actions">
            <a href="{{ route('study.front', ['deck_id' => $deck->id]) }}" class="dd-btn dd-btn--study">
                <span class="material-symbols-outlined">school</span>
                <span>Study Now</span>
            </a>
            <button class="dd-btn dd-btn--primary" type="button" data-open-card-modal-button>
                <span class="material-symbols-outlined">add_circle</span>
                <span>Add Card</span>
            </button>
            <a href="{{ route('imports.index', ['deck_id' => $deck->id]) }}" class="dd-btn dd-btn--ghost">
                <span class="material-symbols-outlined">upload_file</span>
                <span>Import</span>
            </a>
        </div>
    </header>

    {{-- ── Stats Cards ────────────────────────────────────── --}}
    <section class="dd-stats" aria-label="Deck statistics">
        <div class="dd-stat-card">
            <div class="dd-stat-card__icon dd-stat-card__icon--total">
                <span class="material-symbols-outlined">style</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value">{{ $deckStats['total'] }}</span>
                <span class="dd-stat-card__label">Total Cards</span>
            </div>
        </div>
        <div class="dd-stat-card">
            <div class="dd-stat-card__icon dd-stat-card__icon--new">
                <span class="material-symbols-outlined">fiber_new</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value">{{ $deckStats['new'] }}</span>
                <span class="dd-stat-card__label">New</span>
            </div>
        </div>
        <div class="dd-stat-card">
            <div class="dd-stat-card__icon dd-stat-card__icon--learning">
                <span class="material-symbols-outlined">neurology</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value">{{ $deckStats['learning'] }}</span>
                <span class="dd-stat-card__label">Learning</span>
            </div>
        </div>
        <div class="dd-stat-card">
            <div class="dd-stat-card__icon dd-stat-card__icon--review">
                <span class="material-symbols-outlined">verified</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value">{{ $deckStats['review'] }}</span>
                <span class="dd-stat-card__label">Review</span>
            </div>
        </div>
        <div class="dd-stat-card">
            <div class="dd-stat-card__icon dd-stat-card__icon--due">
                <span class="material-symbols-outlined">notifications_active</span>
            </div>
            <div class="dd-stat-card__body">
                <span class="dd-stat-card__value">{{ $deckStats['due'] }}</span>
                <span class="dd-stat-card__label">Due Now</span>
            </div>
        </div>
    </section>

    {{-- ── Toolbar ────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('decks.show', $deck) }}" class="dd-toolbar">
        <div class="dd-toolbar__search">
            <span class="material-symbols-outlined">search</span>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search front or back text..." />
            @if(!empty($filters['q']))
                <a href="{{ route('decks.show', $deck) }}" class="dd-toolbar__clear" aria-label="Clear search">
                    <span class="material-symbols-outlined">close</span>
                </a>
            @endif
        </div>
        <div class="dd-toolbar__filters">
            <select class="dd-toolbar__select" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All Status</option>
                <option value="new" @selected(($filters['status'] ?? '') === 'new')>New</option>
                <option value="learning" @selected(($filters['status'] ?? '') === 'learning')>Learning</option>
                <option value="review" @selected(($filters['status'] ?? '') === 'review')>Review</option>
            </select>
            <button class="dd-btn dd-btn--filter" type="submit">
                <span class="material-symbols-outlined">filter_list</span>
                <span>Filter</span>
            </button>
        </div>
        <button class="dd-btn dd-btn--danger is-hidden" type="button" data-action-bulk-delete>
            <span class="material-symbols-outlined">delete_sweep</span>
            <span>Delete Selected</span>
        </button>
    </form>

    {{-- ── Card Table ─────────────────────────────────────── --}}
    <section class="dd-table-wrap">
        <table class="dd-table">
            <thead>
                <tr>
                    <th class="dd-table__col-check"><input type="checkbox" aria-label="Select all cards" data-select-all-checkbox></th>
                    <th class="dd-table__col-front">Front</th>
                    <th class="dd-table__col-back">Back</th>
                    <th class="dd-table__col-status">Status</th>
                    <th class="dd-table__col-reviewed">Last Reviewed</th>
                    <th class="dd-table__col-mastery">Mastery</th>
                    <th class="dd-table__col-next">Next Due</th>
                    <th class="dd-table__col-actions"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cards as $card)
                    <tr class="dd-table__row" data-card-row data-card-id="{{ $card->id }}">
                        <td class="dd-table__col-check">
                            <input type="checkbox" aria-label="Select card" data-row-checkbox value="{{ $card->id }}">
                        </td>
                        <td class="dd-table__col-front">
                            <span class="dd-table__front-text">{{ \Illuminate\Support\Str::limit($card->note->front_plain_text ?? $card->note->front_text, 60) }}</span>
                        </td>
                        <td class="dd-table__col-back">
                            <span class="dd-table__back-text">{{ \Illuminate\Support\Str::limit($card->note->back_plain_text ?? '', 50) }}</span>
                        </td>
                        <td class="dd-table__col-status">
                            @if($card->state === 'review')
                                <span class="dd-badge dd-badge--review"><span class="dd-badge__dot"></span>Review</span>
                            @elseif($card->state === 'learning' || $card->state === 'relearning')
                                <span class="dd-badge dd-badge--learning"><span class="dd-badge__dot"></span>Learning</span>
                            @else
                                <span class="dd-badge dd-badge--new"><span class="dd-badge__dot"></span>New</span>
                            @endif
                        </td>
                        <td class="dd-table__col-reviewed">
                            <span class="dd-table__muted">{{ $card->last_reviewed_at?->diffForHumans() ?? 'Never' }}</span>
                        </td>
                        <td class="dd-table__col-mastery">
                            @php($masteryPercent = $card->state === 'review' ? min(100, (int) round($card->stability * 10)) : ($card->state === 'new' ? 0 : 20))
                            <div class="dd-mastery" title="{{ $masteryPercent }}% mastery">
                                <div class="dd-mastery__track">
                                    <div class="dd-mastery__fill dd-mastery__fill--{{ $masteryPercent >= 70 ? 'high' : ($masteryPercent >= 30 ? 'mid' : 'low') }}" style="width: {{ $masteryPercent }}%"></div>
                                </div>
                                <span class="dd-mastery__label">{{ $masteryPercent }}%</span>
                            </div>
                        </td>
                        <td class="dd-table__col-next">
                            @if(!$card->due_at)
                                <span class="dd-table__muted">-</span>
                            @else
                                @php($now = now())
                                @php($diffDays = (int) $now->startOfDay()->diffInDays($card->due_at->startOfDay(), false))
                                @if($diffDays < 0 || ($diffDays == 0 && $card->due_at->isPast()))
                                    <span class="dd-due dd-due--overdue">Overdue</span>
                                @elseif($diffDays == 0)
                                    @php($diffMins = $now->diffInMinutes($card->due_at))
                                    @if($diffMins < 60)
                                        <span class="dd-due dd-due--soon">{{ $diffMins }}m</span>
                                    @else
                                        <span class="dd-due dd-due--soon">{{ (int)($diffMins/60) }}h</span>
                                    @endif
                                @else
                                    <span class="dd-due">{{ $diffDays }}d</span>
                                @endif
                            @endif
                        </td>
                        <td class="dd-table__col-actions">
                            <div class="dd-table__actions">
                                <button class="dd-icon-btn dd-icon-btn--edit" type="button" data-edit-card-button data-card-front="{{ e($card->note->front_text ?? $card->note->front_plain_text ?? '') }}" data-card-back="{{ e($card->note->back_text ?? $card->note->back_plain_text ?? '') }}" aria-label="Edit card" title="Edit">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <button class="dd-icon-btn dd-icon-btn--delete" type="button" data-delete-card-button aria-label="Delete card" title="Delete">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="dd-empty">
                                <div class="dd-empty__icon">
                                    <span class="material-symbols-outlined">note_stack</span>
                                </div>
                                <h3 class="dd-empty__title">No cards yet</h3>
                                <p class="dd-empty__desc">
                                    @if(!empty($filters['q']) || ($filters['status'] ?? 'all') !== 'all')
                                        No cards match your current filters. Try adjusting your search or status filter.
                                    @else
                                        Get started by creating your first card or importing from a TXT file.
                                    @endif
                                </p>
                                @if(empty($filters['q']) && ($filters['status'] ?? 'all') === 'all')
                                    <div class="dd-empty__actions">
                                        <button class="dd-btn dd-btn--primary" type="button" data-open-card-modal-button>
                                            <span class="material-symbols-outlined">add_circle</span>
                                            <span>Create Card</span>
                                        </button>
                                        <a href="{{ route('imports.index', ['deck_id' => $deck->id]) }}" class="dd-btn dd-btn--ghost">
                                            <span class="material-symbols-outlined">upload_file</span>
                                            <span>Import TXT</span>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    {{-- ── Pagination ─────────────────────────────────────── --}}
    @if($cards->hasPages())
        <footer class="dd-pagination">
            <span class="dd-pagination__info">
                Showing <strong>{{ $cards->firstItem() }}</strong> - <strong>{{ $cards->lastItem() }}</strong> of <strong>{{ $cards->total() }}</strong>
            </span>
            <div class="dd-pagination__controls">{{ $cards->links() }}</div>
        </footer>
    @endif

    {{-- ── Create/Edit Modal ──────────────────────────────── --}}
    <dialog id="card-modal" class="dd-modal">
        <form method="dialog" class="dd-modal__form" data-card-form>
            <div class="dd-modal__header">
                <div class="dd-modal__header-icon">
                    <span class="material-symbols-outlined">edit_note</span>
                </div>
                <div>
                    <h2 class="dd-modal__title" data-card-modal-title>Create Card</h2>
                    <p class="dd-modal__subtitle">Add front and back content for your flashcard.</p>
                </div>
                <button type="button" class="dd-icon-btn" data-close-card-modal-button aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="dd-modal__body">
                <input type="hidden" data-card-id-input />
                <label class="dd-field">
                    <span class="dd-field__label">Front (Question)</span>
                    <textarea class="dd-field__textarea" rows="4" data-card-front-input required placeholder="Enter the question or prompt..."></textarea>
                </label>
                <label class="dd-field">
                    <span class="dd-field__label">Back (Answer)</span>
                    <textarea class="dd-field__textarea" rows="4" data-card-back-input required placeholder="Enter the answer or explanation..."></textarea>
                </label>
                <div class="dd-modal__feedback is-hidden" data-card-form-feedback></div>
            </div>
            <div class="dd-modal__footer">
                <button type="button" class="dd-btn dd-btn--ghost" data-close-card-modal-button>Cancel</button>
                <button type="submit" class="dd-btn dd-btn--primary" data-card-submit-button>
                    <span class="material-symbols-outlined">save</span>
                    <span>Save Card</span>
                </button>
            </div>
        </form>
    </dialog>

    {{-- ── Delete Confirm Modal ───────────────────────────── --}}
    <dialog id="delete-card-modal" class="dd-modal dd-modal--compact">
        <form method="dialog" class="dd-modal__form" data-delete-card-form>
            <div class="dd-modal__header">
                <div class="dd-modal__header-icon dd-modal__header-icon--danger">
                    <span class="material-symbols-outlined">warning</span>
                </div>
                <div>
                    <h2 class="dd-modal__title">Delete Card</h2>
                    <p class="dd-modal__subtitle">This action cannot be undone.</p>
                </div>
                <button type="button" class="dd-icon-btn" data-close-delete-modal-button aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="dd-modal__body">
                <p class="dd-modal__message" data-delete-modal-message>Are you sure you want to delete this card?</p>
                <input type="hidden" data-delete-card-id-input />
                <div class="dd-modal__feedback is-hidden" data-delete-card-form-feedback></div>
            </div>
            <div class="dd-modal__footer">
                <button type="button" class="dd-btn dd-btn--ghost" data-close-delete-modal-button>Cancel</button>
                <button type="submit" class="dd-btn dd-btn--danger" data-delete-card-submit-button>
                    <span class="material-symbols-outlined">delete</span>
                    <span>Delete</span>
                </button>
            </div>
        </form>
    </dialog>
</div>
@endsection
