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

Route::post('/decks', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate(['name' => 'required|string|max:255']);
    $user = \App\Models\User::firstOrCreate(
        ['email' => 'dev.study@example.com'],
        ['name' => 'Dev Learner', 'password' => bcrypt('password')]
    );
    
    $deck = \App\Models\Deck::create([
        'user_id' => $user->id,
        'name' => $validated['name'],
        'description' => 'Created via UI prompt'
    ]);
    
    return response()->json($deck);
});

Route::prefix('imports')->group(function (): void {
    Route::get('/', [ImportController::class, 'index']);
    Route::get('/{importJob}', [ImportController::class, 'show']);
    Route::get('/{importJob}/rows', [ImportController::class, 'rows']);
    Route::post('/txt/preview', [ImportController::class, 'preview']);
    Route::post('/txt/confirm', [ImportController::class, 'confirm']);
});
