@extends('layouts.app', ['page' => 'deck-detail'])

@section('content')
<div class="deck-detail-container">
    <div class="card-manager-header">
        <h1 class="card-manager-title">Card Management <span class="card-manager-deck-badge">{{ $deck->name }}</span></h1>
    </div>

    <div class="card-manager-toolbar">
        <div class="toolbar-filters">
            <select class="import-select toolbar-select">
                <option value="all">All Decks</option>
                <option value="{{ $deck->id }}" selected>{{ $deck->name }}</option>
            </select>
            <select class="import-select toolbar-select">
                <option value="all">Any Status</option>
                <option value="learning">Learning</option>
                <option value="review">Review</option>
                <option value="new">New</option>
            </select>
        </div>
        <div class="toolbar-actions">
            <button class="secondary-button" type="button">
                <span class="material-symbols-outlined" style="font-size: 1.25rem;">filter_list</span>
                <span>More Filters</span>
            </button>
        </div>
    </div>

    <div class="table-container">
        <table class="card-table">
            <thead>
                <tr>
                    <th class="col-checkbox"><input type="checkbox" aria-label="Select all cards"></th>
                    <th class="col-front">FRONT</th>
                    <th class="col-back">BACK</th>
                    <th class="col-deck">DECK</th>
                    <th class="col-status">STATUS</th>
                    <th class="col-mastery">MASTERY</th>
                    <th class="col-next">NEXT</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($cards as $card)
                    <tr>
                        <td class="col-checkbox"><input type="checkbox" aria-label="Select card"></td>
                        <td class="col-front"><strong>{{ $card->note->front_plain_text ?? $card->note->front_text }}</strong></td>
                        <td class="col-back">{{ \Illuminate\Support\Str::limit($card->note->back_plain_text ?? '', 50) }}</td>
                        <td class="col-deck"><span class="badge badge--neutral">{{ $deck->name }}</span></td>
                        <td class="col-status">
                            @if($card->state === 'review')
                                <span class="badge badge--success"><span class="badge-dot"></span> Review</span>
                            @elseif($card->state === 'learning' || $card->state === 'relearning')
                                <span class="badge badge--warning"><span class="badge-dot"></span> Learning</span>
                            @else
                                <span class="badge badge--neutral"><span class="badge-dot"></span> New</span>
                            @endif
                        </td>
                        <td class="col-mastery">
                            <div class="mastery-bar">
                                @php
                                    $masteryPercent = $card->state === 'review' ? min(100, $card->stability * 10) : ($card->state === 'new' ? 0 : 20);
                                @endphp
                                <div class="mastery-bar__fill" style="width: {{ $masteryPercent }}%"></div>
                            </div>
                        </td>
                        <td class="col-next">
                            @if(!$card->due_at)
                                -
                            @elseif($card->due_at->isPast() || $card->due_at->isToday())
                                Today
                            @else
                                In {{ $card->due_at->diffInDays() }} days
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state-row">No cards found in this deck.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-manager-footer">
        <div class="pagination-info">
            Showing {{ $cards->firstItem() ?? 0 }} to {{ $cards->lastItem() ?? 0 }} of {{ $cards->total() }} cards
        </div>
        <div class="pagination-controls">
            {{ $cards->links() }}
        </div>
    </div>
</div>
@endsection
