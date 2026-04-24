<?php

use App\Http\Controllers\Api\ImportController;
use App\Http\Controllers\Api\StudySessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('study')->group(function (): void {
    Route::get('/session', [StudySessionController::class, 'show']);
    Route::post('/cards/{card}/check-answer', [StudySessionController::class, 'checkAnswer']);
    Route::post('/cards/{card}/rate', [StudySessionController::class, 'rate']);
    Route::post('/cards/{card}/play-tts', [StudySessionController::class, 'playTts']);
});

Route::prefix('imports')->group(function (): void {
    Route::get('/', [ImportController::class, 'index']);
    Route::get('/{importJob}', [ImportController::class, 'show']);
    Route::get('/{importJob}/rows', [ImportController::class, 'rows']);
    Route::post('/txt/preview', [ImportController::class, 'preview']);
    Route::post('/txt/confirm', [ImportController::class, 'confirm']);
});
