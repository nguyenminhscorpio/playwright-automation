@extends('layouts.app')

@php($studyScreen = 'front')

@section('content')
    @php($mode = request('mode', 'flip'))
    @php($typingMode = $mode === 'typing')
    <section class="study-page">
        <div class="study-shell">
            <div class="study-progress">
                <div>
                    <span class="eyebrow">English Vocabulary</span>
                    <h1 class="study-title">Session Progress</h1>
                </div>
                <span class="progress-pill">12 / 50 Mastered</span>
            </div>

            <div class="progress">
                <div class="progress__bar" style="width: 24%"></div>
            </div>

            <article class="study-card study-card--front">
                <div class="study-chip">
                    <span class="material-symbols-outlined">psychology</span>
                    <span>New Concept</span>
                </div>
                <button class="icon-button study-card__tts" type="button">
                    <span class="material-symbols-outlined">volume_up</span>
                </button>

                <div class="study-card__content">
                    <h2>air conditioning</h2>
                    <p>(thiết bị điều hòa không khí)</p>
                </div>
            </article>

            <div class="study-actions study-actions--center">
                @if ($typingMode)
                    <a href="{{ route('study.typing', ['mode' => 'typing']) }}" class="primary-button primary-button--pill">
                        <span class="material-symbols-outlined">edit_note</span>
                        <span>Bắt đầu nhập chữ</span>
                    </a>
                @else
                    <a href="{{ route('study.answer', ['mode' => 'flip']) }}" class="primary-button primary-button--pill">
                        <span class="material-symbols-outlined">visibility</span>
                        <span>Show Answer</span>
                    </a>
                @endif
            </div>
        </div>
    </section>
@endsection
