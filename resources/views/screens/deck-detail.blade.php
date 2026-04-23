@extends('layouts.app')

@section('content')
    <section class="page-section">
        <div class="breadcrumb">
            <a href="{{ route('dashboard') }}">My Decks</a>
            <span>/</span>
            <span>English Vocabulary</span>
        </div>

        <section class="section-header section-header--stack-mobile">
            <div>
                <h1 class="hero__title">English Vocabulary</h1>
                <p class="hero__subtitle">Giao tiếp hằng ngày - 142 cards đang được quản lý trong deck này.</p>
            </div>
            <div class="action-row">
                <button class="secondary-button" type="button">
                    <span class="material-symbols-outlined">upload_file</span>
                    <span>Import TXT/CSV</span>
                </button>
                <button class="primary-button" type="button">
                    <span class="material-symbols-outlined">add</span>
                    <span>Add New Card</span>
                </button>
            </div>
        </section>

        <div class="card-grid">
            <article class="vocab-card">
                <div class="vocab-card__accent vocab-card__accent--green"></div>
                <div class="vocab-card__head">
                    <span class="status-badge status-badge--green">Mastered</span>
                    <span class="rating-stars">★★☆</span>
                </div>
                <div class="vocab-card__body">
                    <h3>accommodation</h3>
                    <p>nơi ở, chỗ lưu trú</p>
                </div>
                <div class="vocab-card__foot">
                    <span><span class="material-symbols-outlined">event</span> Hôm nay</span>
                    <div class="inline-actions">
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">edit</span></button>
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
            </article>

            <article class="vocab-card">
                <div class="vocab-card__accent vocab-card__accent--amber"></div>
                <div class="vocab-card__head">
                    <span class="status-badge status-badge--amber">Learning</span>
                    <span class="rating-stars">★★★</span>
                </div>
                <div class="vocab-card__body">
                    <h3>departure gate</h3>
                    <p>cổng khởi hành</p>
                </div>
                <div class="vocab-card__foot">
                    <span><span class="material-symbols-outlined">event</span> 2 ngày nữa</span>
                    <div class="inline-actions">
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">edit</span></button>
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
            </article>

            <article class="vocab-card">
                <div class="vocab-card__accent vocab-card__accent--blue"></div>
                <div class="vocab-card__head">
                    <span class="status-badge">Review</span>
                    <span class="rating-stars">★☆☆</span>
                </div>
                <div class="vocab-card__body">
                    <h3>make yourself at home</h3>
                    <p>cứ tự nhiên như ở nhà</p>
                </div>
                <div class="vocab-card__foot">
                    <span><span class="material-symbols-outlined">event</span> Quá hạn 1 ngày</span>
                    <div class="inline-actions">
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">edit</span></button>
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
            </article>

            <article class="vocab-card">
                <div class="vocab-card__accent vocab-card__accent--slate"></div>
                <div class="vocab-card__head">
                    <span class="status-badge status-badge--slate">New</span>
                    <span class="rating-stars">☆☆☆</span>
                </div>
                <div class="vocab-card__body">
                    <h3>reservation confirmation</h3>
                    <p>xác nhận đặt chỗ</p>
                </div>
                <div class="vocab-card__foot">
                    <span><span class="material-symbols-outlined">new_releases</span> Mới</span>
                    <div class="inline-actions">
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">edit</span></button>
                        <button class="icon-button icon-button--small" type="button"><span class="material-symbols-outlined">delete</span></button>
                    </div>
                </div>
            </article>
        </div>
    </section>
@endsection
