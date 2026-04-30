@extends('layouts.app')

@php($studyScreen = 'front')

@section('content')
    <section class="study-page">
        <div class="study-shell" data-study-app>
            <div class="study-progress">
                <div>
                    <span class="eyebrow" data-study-deck-name>{{ $studyDeckName ?? 'Study Session' }}</span>
                    <h1 class="study-title" data-study-progress-title>Session Progress</h1>
                </div>
                <div class="progress-group">
                    <span class="progress-pill progress-pill--new" data-study-new-count>0</span>
                    <span class="progress-pill progress-pill--learning" data-study-learning-count>0</span>
                    <span class="progress-pill progress-pill--review" data-study-review-count>0</span>
                </div>
            </div>
            <div class="study-feedback is-hidden" data-study-feedback></div>
            <div class="progress" aria-hidden="true"><div class="progress__bar" data-study-progress-bar style="width: 0%"></div></div>
            <article class="study-card study-card--front" data-study-card>
                <div class="study-chip" data-study-state-chip><span class="material-symbols-outlined">psychology</span><span data-study-state-label>Loading card</span></div>
                <button class="icon-button study-card__tts" type="button" data-study-tts-button disabled><span class="material-symbols-outlined">volume_up</span></button>
                <div class="study-card__content"><h2 data-study-front-text>Loading...</h2></div>
            </article>
            <section class="study-empty-state is-hidden" data-study-empty-state><span class="material-symbols-outlined">check_circle</span><h2 data-study-empty-title>Session complete</h2><p data-study-empty-message>No cards are ready right now. Try again later or switch deck.</p></section>
            <div class="study-actions study-actions--center"><button class="primary-button primary-button--pill" type="button" data-study-reveal-button disabled><span class="material-symbols-outlined">visibility</span><span>Show Answer</span></button></div>
        </div>
    </section>
@endsection
