<?php

use App\Http\Controllers\ScreenController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [ScreenController::class, 'dashboard'])->name('dashboard');
Route::get('/decks/{deck}', [ScreenController::class, 'deckDetail'])->name('decks.show');
Route::get('/imports', [ScreenController::class, 'imports'])->name('imports.index');
Route::get('/study/front', [ScreenController::class, 'studyFront'])->name('study.front');
Route::get('/study/typing', [ScreenController::class, 'studyTyping'])->name('study.typing');
Route::get('/study/answer', [ScreenController::class, 'studyAnswer'])->name('study.answer');
