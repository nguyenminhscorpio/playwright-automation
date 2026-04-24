<?php

namespace App\Services\Study;

use App\Models\Card;
use App\Models\ReviewLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class StudyRatingService
{
    public function __construct(
        private readonly StudySchedulerService $studySchedulerService,
        private readonly StudySessionService $studySessionService,
    ) {
    }

    public function rate(
        Card $card,
        string $mode,
        string $rating,
        ?string $typedAnswer = null,
        ?string $judgedResult = null,
    ): array {
        return DB::transaction(function () use ($card, $mode, $rating, $typedAnswer, $judgedResult): array {
            $card->loadMissing('user');

            $beforeDueAt = $card->due_at;
            $scheduled = $this->studySchedulerService->schedule($card, $rating, CarbonImmutable::now());
            $nextDueAt = CarbonImmutable::parse($scheduled['next_due_at']);

            $card->forceFill([
                'state' => $scheduled['state_after'],
                'current_step' => $scheduled['step_after'],
                'due_at' => $nextDueAt,
                'last_reviewed_at' => now(),
                'scheduled_days' => $scheduled['scheduled_days'],
                'stability' => $scheduled['next_stability'],
                'difficulty' => $scheduled['next_difficulty'],
                'reps' => $scheduled['next_reps'],
                'lapses' => $scheduled['next_lapses'],
                'last_rating' => strtolower($rating),
            ])->save();

            ReviewLog::query()->create([
                'user_id' => $card->user_id,
                'card_id' => $card->id,
                'mode' => $mode,
                'rating' => strtolower($rating),
                'typed_answer' => $typedAnswer,
                'judged_result' => $judgedResult,
                'previous_state' => $scheduled['state_before'],
                'next_state' => $scheduled['state_after'],
                'previous_step' => $scheduled['step_before'],
                'next_step' => $scheduled['step_after'],
                'previous_due_at' => $beforeDueAt,
                'next_due_at' => $nextDueAt,
                'previous_stability' => $scheduled['stability_before'],
                'next_stability' => $scheduled['next_stability'],
                'previous_difficulty' => $scheduled['difficulty_before'],
                'next_difficulty' => $scheduled['next_difficulty'],
                'reviewed_at' => now(),
            ]);

            $nextSession = $this->studySessionService->buildSession(
                $card->user,
                $card->deck_id,
                $mode,
            );

            return [
                'card_id' => $card->id,
                'state_before' => $scheduled['state_before'],
                'state_after' => $scheduled['state_after'],
                'step_before' => $scheduled['step_before'],
                'step_after' => $scheduled['step_after'],
                'next_due_at' => $scheduled['next_due_at'],
                'scheduled_days' => $scheduled['scheduled_days'],
                'next_stability' => $scheduled['next_stability'],
                'next_difficulty' => $scheduled['next_difficulty'],
                'updated_progress' => $nextSession['progress'],
                'next_card_id' => $nextSession['current_card']['id'] ?? null,
            ];
        });
    }
}
