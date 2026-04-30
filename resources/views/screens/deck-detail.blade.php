@extends('layouts.app', ['page' => 'deck-detail'])

@section('content')
<div class="deck-detail-container" data-deck-detail-app data-deck-id="{{ $deck->id }}" data-user-id="{{ $deckDetailUserId ?? '' }}" data-total-cards="{{ $cards->total() }}">
    <div class="breadcrumb">
        <a href="{{ route('dashboard') }}">My Decks</a>
        <span>/</span>
        <select class="deck-switcher" data-deck-switcher>
            @foreach ($allDecks as $d)
                <option value="{{ $d->id }}" @selected($d->id === $deck->id)>{{ $d->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="card-manager-header">
        <div>
            <h1 class="card-manager-title">Card Management <span class="card-manager-deck-badge">{{ $deck->name }}</span></h1>
            <p class="hero__subtitle">{{ $deck->description ?: 'Manage cards, search quickly, and jump into import for this deck.' }}</p>
        </div>
        <div class="toolbar-actions">
            <button class="secondary-button text-danger is-hidden" type="button" data-action-bulk-delete>
                <span class="material-symbols-outlined">delete_sweep</span>
                <span>Delete Selected</span>
            </button>
            <button class="primary-button" type="button" data-open-card-modal-button>
                <span class="material-symbols-outlined">add</span>
                <span>Create Card</span>
            </button>
            <a href="{{ route('imports.index', ['deck_id' => $deck->id]) }}" class="secondary-button">
                <span class="material-symbols-outlined">upload_file</span>
                <span>Import</span>
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('decks.show', $deck) }}" class="card-manager-toolbar">
        <div class="toolbar-filters toolbar-filters--grow">
            <label class="toolbar-search">
                <span class="material-symbols-outlined">search</span>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search cards by front, back, or description..." />
            </label>
            <select class="import-select toolbar-select" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>Any Status</option>
                <option value="learning" @selected(($filters['status'] ?? '') === 'learning')>Learning</option>
                <option value="review" @selected(($filters['status'] ?? '') === 'review')>Review</option>
                <option value="new" @selected(($filters['status'] ?? '') === 'new')>New</option>
            </select>
        </div>
        <div class="toolbar-actions">
            <button class="secondary-button" type="submit">
                <span class="material-symbols-outlined">filter_list</span>
                <span>Apply</span>
            </button>
        </div>
    </form>

    <div class="table-container">
        <table class="card-table">
            <thead>
                <tr>
                    <th class="col-checkbox"><input type="checkbox" aria-label="Select all cards" data-select-all-checkbox></th>
                    <th class="col-front">FRONT</th>
                    <th class="col-back">BACK</th>

                    <th class="col-status">STATUS</th>
                    <th class="col-last-reviewed">LAST REVIEWED</th>
                    <th class="col-mastery">MASTERY</th>
                    <th class="col-next">NEXT</th>
                    <th class="col-actions">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cards as $card)
                    <tr data-card-row data-card-id="{{ $card->id }}">
                        <td class="col-checkbox"><input type="checkbox" aria-label="Select card" data-row-checkbox value="{{ $card->id }}"></td>
                        <td class="col-front"><strong>{{ $card->note->front_plain_text ?? $card->note->front_text }}</strong></td>
                        <td class="col-back">{{ \Illuminate\Support\Str::limit($card->note->back_plain_text ?? '', 50) }}</td>

                        <td class="col-status">
                            @if($card->state === 'review')
                                <span class="badge badge--success"><span class="badge-dot"></span> Review</span>
                            @elseif($card->state === 'learning' || $card->state === 'relearning')
                                <span class="badge badge--warning"><span class="badge-dot"></span> Learning</span>
                            @else
                                <span class="badge badge--neutral"><span class="badge-dot"></span> New</span>
                            @endif
                        </td>
                        <td class="col-last-reviewed">{{ $card->last_reviewed_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="col-mastery">
                            <div class="mastery-bar">
                                @php($masteryPercent = $card->state === 'review' ? min(100, (int) round($card->stability * 10)) : ($card->state === 'new' ? 0 : 20))
                                <div class="mastery-bar__fill" style="width: {{ $masteryPercent }}%"></div>
                            </div>
                        </td>
                        <td class="col-next">
                            @if(!$card->due_at)
                                -
                            @else
                                @php($now = now())
                                @php($diffDays = (int) $now->startOfDay()->diffInDays($card->due_at->startOfDay(), false))
                                
                                @if($diffDays < 0 || ($diffDays == 0 && $card->due_at->isPast()))
                                    <span style="color: var(--danger); font-weight: 700;">Today</span>
                                @elseif($diffDays == 0)
                                    @php($diffMins = $now->diffInMinutes($card->due_at))
                                    @if($diffMins < 60)
                                        <span style="color: var(--warning); font-weight: 600;">In {{ $diffMins }}m</span>
                                    @else
                                        <span style="color: var(--warning); font-weight: 600;">In {{ (int)($diffMins/60) }}h</span>
                                    @endif
                                @else
                                    In {{ $diffDays }} {{ \Illuminate\Support\Str::plural('day', $diffDays) }}
                                @endif
                            @endif
                        </td>
                        <td class="col-actions">
                            <div class="table-actions">
                                <button class="icon-button icon-button--small action-edit" type="button" data-edit-card-button data-card-front="{{ e($card->note->front_text ?? $card->note->front_plain_text ?? '') }}" data-card-back="{{ e($card->note->back_text ?? $card->note->back_plain_text ?? '') }}" aria-label="Edit card"><span class="material-symbols-outlined">edit</span></button>
                                <button class="icon-button icon-button--small action-delete" type="button" data-delete-card-button aria-label="Delete card"><span class="material-symbols-outlined">delete</span></button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-state-row">No cards found for the current search or status filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-manager-footer">
        <div class="pagination-info">Showing {{ $cards->firstItem() ?? 0 }} to {{ $cards->lastItem() ?? 0 }} of {{ $cards->total() }} cards</div>
        <div class="pagination-controls">{{ $cards->links() }}</div>
    </div>

    <dialog id="card-modal" class="custom-modal">
        <form method="dialog" class="custom-modal__form" data-card-form>
            <div class="custom-modal__header">
                <h2 data-card-modal-title>Create Card</h2>
                <button type="button" class="icon-button" data-close-card-modal-button><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="custom-modal__body">
                <input type="hidden" data-card-id-input />
                <label class="import-field"><span class="import-field__label">Front</span><textarea class="import-file-input" rows="4" data-card-front-input required></textarea></label>
                <label class="import-field"><span class="import-field__label">Back</span><textarea class="import-file-input" rows="4" data-card-back-input required></textarea></label>
                <div class="study-feedback is-hidden" data-card-form-feedback></div>
            </div>
            <div class="custom-modal__footer">
                <button type="button" class="secondary-button" data-close-card-modal-button>Cancel</button>
                <button type="submit" class="primary-button" data-card-submit-button>Save Card</button>
            </div>
        </form>
    </dialog>

    <dialog id="delete-card-modal" class="custom-modal">
        <form method="dialog" class="custom-modal__form" data-delete-card-form>
            <div class="custom-modal__header">
                <h2>Delete Card</h2>
                <button type="button" class="icon-button" data-close-delete-modal-button><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="custom-modal__body">
                <p data-delete-modal-message>Are you sure you want to delete this card? This action cannot be undone.</p>
                <input type="hidden" data-delete-card-id-input />
                <div class="study-feedback is-hidden" data-delete-card-form-feedback></div>
            </div>
            <div class="custom-modal__footer">
                <button type="button" class="secondary-button" data-close-delete-modal-button>Cancel</button>
                <button type="submit" class="primary-button primary-button--success" style="background: var(--danger); box-shadow: 0 8px 18px rgba(186, 26, 26, 0.18);" data-delete-card-submit-button>Delete</button>
            </div>
        </form>
    </dialog>
</div>
@endsection
