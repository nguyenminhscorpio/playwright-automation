@extends('layouts.app')

@php($studyScreen = 'typing')

@section('content')
    <section class="study-page">
        <div class="study-shell">
            <div class="study-progress study-progress--compact">
                <div class="progress-block__meta">
                    <span>Current Deck Progress</span>
                    <strong>15 / 50</strong>
                </div>
                <div class="progress">
                    <div class="progress__bar" style="width: 30%"></div>
                </div>
            </div>

            <article class="study-card study-card--typing">
                <span class="study-side-label">Front side</span>
                <div class="study-card__content">
                    <h2>Define: Spaced Repetition</h2>
                </div>
            </article>

            <section class="answer-panel">
                <label for="answer-input" class="answer-panel__label">Your Answer</label>
                <textarea id="answer-input" class="answer-panel__input" rows="5" placeholder="Type your explanation here..."></textarea>
                <div class="answer-panel__actions">
                    <button class="text-button" type="button">
                        <span class="material-symbols-outlined">lightbulb</span>
                        <span>Show Hint</span>
                    </button>
                    <a href="{{ route('study.answer', ['mode' => 'typing']) }}" class="primary-button">
                        <span>Check Answer</span>
                        <span class="material-symbols-outlined">keyboard_return</span>
                    </a>
                </div>
            </section>
        </div>
    </section>
@endsection
