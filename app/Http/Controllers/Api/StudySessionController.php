<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\User;
use App\Services\Study\AnswerCheckingService;
use App\Services\Study\StudyRatingService;
use App\Services\Study\StudySessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudySessionController extends Controller
{
    public function __construct(
        private readonly StudySessionService $studySessionService,
        private readonly AnswerCheckingService $answerCheckingService,
        private readonly StudyRatingService $studyRatingService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'deck_id' => ['nullable', 'integer', 'exists:decks,id'],
            'mode' => ['nullable', Rule::in(['flip', 'typing'])],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);

        return response()->json(
            $this->studySessionService->buildSession(
                $user,
                $validated['deck_id'] ?? null,
                $validated['mode'] ?? 'typing',
            )
        );
    }

    public function checkAnswer(Request $request, Card $card): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(['typing'])],
            'user_answer' => ['present', 'nullable', 'string'],
        ]);

        $card->loadMissing('note:id,back_plain_text');

        $correctAnswer = $card->note?->back_plain_text;

        if ($correctAnswer === null || $correctAnswer === '') {
            return response()->json([
                'message' => 'Card does not have a back_plain_text answer for checking.',
                'card_id' => $card->id,
            ], 422);
        }

        return response()->json(
            $this->answerCheckingService->check(
                $validated['user_answer'] ?? '',
                $correctAnswer,
            )
        );
    }

    public function rate(Request $request, Card $card): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['required', Rule::in(['flip', 'typing'])],
            'rating' => ['required', Rule::in(['again', 'hard', 'good', 'easy'])],
            'typed_answer' => ['nullable', 'string'],
            'judged_result' => ['nullable', Rule::in(['correct', 'close_match', 'incorrect'])],
        ]);

        return response()->json(
            $this->studyRatingService->rate(
                $card,
                $validated['mode'],
                $validated['rating'],
                $validated['typed_answer'] ?? null,
                $validated['judged_result'] ?? null,
            )
        );
    }

    public function playTts(Request $request, Card $card): JsonResponse
    {
        return response()->json([
            'message' => 'Play TTS endpoint scaffolded.',
            'card_id' => $card->id,
        ]);
    }
}
