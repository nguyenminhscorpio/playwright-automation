<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScreenController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// ── Guest-only auth routes ───────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── Authenticated app routes ─────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ScreenController::class, 'dashboard'])->name('dashboard');
    Route::get('/decks/{deck}', [ScreenController::class, 'deckDetail'])->name('decks.show');
    Route::get('/imports', [ScreenController::class, 'imports'])->name('imports.index');
    Route::get('/study/front',  [ScreenController::class, 'studyFront'])->name('study.front');
    Route::get('/study/typing', [ScreenController::class, 'studyTyping'])->name('study.typing');
    Route::get('/study/answer', [ScreenController::class, 'studyAnswer'])->name('study.answer');

    Route::get('/profile',              [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile',              [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password',     [ProfileController::class, 'updatePassword'])->name('profile.password');
});
