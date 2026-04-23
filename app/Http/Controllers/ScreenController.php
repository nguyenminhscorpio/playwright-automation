<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ScreenController extends Controller
{
    public function dashboard(): View
    {
        return view('screens.dashboard', ['title' => 'FlashMind - Dashboard']);
    }

    public function deckDetail(string $deck): View
    {
        return view('screens.deck-detail', ['title' => 'FlashMind - Deck Detail']);
    }

    public function studyFront(): View
    {
        return view('screens.study-front', ['title' => 'FlashMind - Study Front']);
    }

    public function studyTyping(): View
    {
        return view('screens.study-typing', ['title' => 'FlashMind - Typing Mode']);
    }

    public function studyAnswer(): View
    {
        return view('screens.study-answer', ['title' => 'FlashMind - Answer Revealed']);
    }
}
