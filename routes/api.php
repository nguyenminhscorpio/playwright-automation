<?php

use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\DashboardStatsController;
use App\Http\Controllers\Api\DeckController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\StudySessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('study')->group(function (): void {
    Route::get('/session', [StudySessionController::class, 'show']);
    Route::post('/cards/{card}/check-answer', [StudySessionController::class, 'checkAnswer']);
    Route::post('/cards/{card}/rate', [StudySessionController::class, 'rate']);
    Route::post('/cards/{card}/play-tts', [StudySessionController::class, 'playTts']);
});

Route::get('/decks', [DeckController::class, 'index']);
Route::post('/decks', [DeckController::class, 'store']);
Route::get('/decks/{deck}', [DeckController::class, 'show']);
Route::put('/decks/{deck}', [DeckController::class, 'update']);
Route::delete('/decks/{deck}', [DeckController::class, 'destroy']);

Route::get('/cards', [CardController::class, 'index']);
Route::post('/cards', [CardController::class, 'store']);
Route::get('/cards/{card}', [CardController::class, 'show']);
Route::put('/cards/{card}', [CardController::class, 'update']);
Route::delete('/cards/{card}', [CardController::class, 'destroy']);

Route::get('/stats/dashboard', [DashboardStatsController::class, 'show']);

Route::prefix('imports')->group(function (): void {
    Route::get('/', [ImportController::class, 'index']);
    Route::get('/{importJob}', [ImportController::class, 'show']);
    Route::get('/{importJob}/rows', [ImportController::class, 'rows']);
    Route::post('/txt/preview', [ImportController::class, 'preview']);
    Route::post('/txt/confirm', [ImportController::class, 'confirm']);
});
