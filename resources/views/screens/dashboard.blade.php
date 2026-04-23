@extends('layouts.app')

@section('content')
    <section class="page-section">
        <div class="hero">
            <div>
                <h1 class="hero__title">Welcome back, Alex!</h1>
                <p class="hero__subtitle">Bạn đang duy trì nhịp học rất tốt. Sẵn sàng cho phiên học hôm nay chưa?</p>
            </div>
        </div>

        <div class="stats-grid">
            <article class="stat-card stat-card--warm">
                <div class="stat-card__header">
                    <div class="stat-card__icon stat-card__icon--gold">
                        <span class="material-symbols-outlined">local_fire_department</span>
                    </div>
                    <span class="stat-card__label">Daily Streak</span>
                </div>
                <div class="stat-card__value">25 <small>ngày</small></div>
                <p class="stat-card__hint">Giữ nhịp thật tốt, bạn đang rất ổn định.</p>
            </article>

            <article class="stat-card stat-card--wide">
                <div class="stat-card__header">
                    <div class="stat-card__icon">
                        <span class="material-symbols-outlined">emoji_events</span>
                    </div>
                    <span class="stat-card__label">Learning Milestone</span>
                </div>
                <div class="milestone">
                    <div>
                        <div class="stat-card__value">500 <small>concepts learned</small></div>
                        <p class="muted-text">Mục tiêu tháng này: 600 khái niệm</p>
                    </div>
                    <div class="progress-block">
                        <div class="progress-block__meta">
                            <span>Goal 600</span>
                            <strong>83%</strong>
                        </div>
                        <div class="progress">
                            <div class="progress__bar" style="width: 83%"></div>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <section class="section-header">
            <div>
                <h2 class="section-title">Active Decks</h2>
                <p class="section-subtitle">Chọn bộ thẻ để tiếp tục review theo đúng lịch.</p>
            </div>
            <a href="{{ route('decks.show', 'english-vocabulary') }}" class="text-action">View All</a>
        </section>

        <div class="deck-grid">
            <article class="deck-card">
                <div class="deck-card__top">
                    <div class="deck-card__icon">
                        <span class="material-symbols-outlined">language</span>
                    </div>
                    <span class="chip">Language</span>
                </div>
                <h3 class="deck-card__title">English Vocabulary</h3>
                <p class="deck-card__desc">Advanced conversational terms and idioms for daily speaking.</p>
                <div class="progress-block">
                    <div class="progress-block__meta">
                        <span>Mastery</span>
                        <strong>75%</strong>
                    </div>
                    <div class="progress">
                        <div class="progress__bar" style="width: 75%"></div>
                    </div>
                </div>
                <div class="deck-card__actions">
                    <a href="{{ route('decks.show', 'english-vocabulary') }}" class="secondary-button">Chi tiết deck</a>
                    <a href="{{ route('study.front') }}" class="primary-button">Review 24 Cards</a>
                </div>
            </article>

            <article class="deck-card">
                <div class="deck-card__top">
                    <div class="deck-card__icon">
                        <span class="material-symbols-outlined">record_voice_over</span>
                    </div>
                    <span class="chip chip--green">Speaking</span>
                </div>
                <h3 class="deck-card__title">Business English</h3>
                <p class="deck-card__desc">Vocabulary for meetings, email writing and presentation flow.</p>
                <div class="progress-block">
                    <div class="progress-block__meta">
                        <span>Mastery</span>
                        <strong>48%</strong>
                    </div>
                    <div class="progress">
                        <div class="progress__bar" style="width: 48%"></div>
                    </div>
                </div>
                <div class="deck-card__actions">
                    <a href="{{ route('decks.show', 'business-english') }}" class="secondary-button">Chi tiết deck</a>
                    <a href="{{ route('study.front') }}" class="primary-button">Review 12 Cards</a>
                </div>
            </article>

            <article class="deck-card">
                <div class="deck-card__top">
                    <div class="deck-card__icon">
                        <span class="material-symbols-outlined">travel_explore</span>
                    </div>
                    <span class="chip chip--amber">Travel</span>
                </div>
                <h3 class="deck-card__title">Travel Expressions</h3>
                <p class="deck-card__desc">Useful phrases for airport, hotel, transport and daily travel needs.</p>
                <div class="progress-block">
                    <div class="progress-block__meta">
                        <span>Mastery</span>
                        <strong>64%</strong>
                    </div>
                    <div class="progress">
                        <div class="progress__bar" style="width: 64%"></div>
                    </div>
                </div>
                <div class="deck-card__actions">
                    <a href="{{ route('decks.show', 'travel-expressions') }}" class="secondary-button">Chi tiết deck</a>
                    <a href="{{ route('study.front') }}" class="primary-button">Review 18 Cards</a>
                </div>
            </article>
        </div>
    </section>
@endsection
