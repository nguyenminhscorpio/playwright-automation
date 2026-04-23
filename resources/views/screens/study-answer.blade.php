@extends('layouts.app')

@php($studyScreen = 'answer')

@section('content')
    @php($mode = request('mode', 'flip'))
    <section class="study-page">
        <div class="study-shell">
            <div class="study-progress study-progress--split">
                <div>
                    <span class="eyebrow">Cognitive Science 101</span>
                    <h1 class="study-title">Card 14 of 45</h1>
                </div>
                <div class="progress-inline">
                    <div class="progress progress--small">
                        <div class="progress__bar" style="width: 30%"></div>
                    </div>
                    <span>30%</span>
                </div>
            </div>

            <article class="study-card study-card--answer">
                <div class="study-section">
                    <span class="study-section__label">Prompt</span>
                    <h2>Define: Spaced Repetition</h2>
                </div>

                @if ($mode === 'typing')
                    <div class="study-section study-section--muted">
                        <span class="study-section__label">
                            <span class="material-symbols-outlined">edit_note</span>
                            <span>Your Answer</span>
                        </span>
                        <p>"Reviewing material at gradually increasing intervals to remember it better."</p>
                    </div>
                @endif

                <div class="study-section">
                    <span class="study-section__label study-section__label--success">
                        <span class="material-symbols-outlined">check_circle</span>
                        <span>{{ $mode === 'flip' ? 'Back Side' : 'Correct Definition' }}</span>
                    </span>
                    <p>
                        An evidence-based learning technique that increases the time intervals between
                        review sessions to strengthen long-term retention.
                    </p>
                </div>

                <div class="tag-row">
                    <span class="chip chip--subtle">Psychology</span>
                    <span class="chip chip--subtle">High Yield</span>
                </div>
            </article>

            <section class="rating-panel">
                <p class="rating-panel__title">How difficult was this to recall?</p>
                <div class="rating-grid">
                    <button class="rating-card rating-card--again" type="button">
                        <strong>1</strong>
                        <span>Again</span>
                        <small>&lt; 1 min</small>
                    </button>
                    <button class="rating-card rating-card--hard" type="button">
                        <strong>2</strong>
                        <span>Hard</span>
                        <small>6 min</small>
                    </button>
                    <button class="rating-card rating-card--good" type="button">
                        <strong>3</strong>
                        <span>Good</span>
                        <small>10 min</small>
                    </button>
                    <button class="rating-card rating-card--easy" type="button">
                        <strong>4</strong>
                        <span>Easy</span>
                        <small>4 days</small>
                    </button>
                </div>
            </section>
        </div>
    </section>
@endsection
