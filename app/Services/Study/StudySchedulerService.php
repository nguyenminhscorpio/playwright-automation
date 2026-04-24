<?php

namespace App\Services\Study;

use App\Models\Card;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

class StudySchedulerService
{
    public function schedule(Card $card, string $rating, ?CarbonImmutable $now = null): array
    {
        $resolvedNow = $now ?? CarbonImmutable::now();
        $resolvedRating = strtolower($rating);

        if (! in_array($resolvedRating, ['again', 'hard', 'good', 'easy'], true)) {
            throw new InvalidArgumentException('Unsupported rating provided.');
        }

        $before = [
            'state' => $card->state,
            'step' => $card->current_step,
            'due_at' => $card->due_at,
            'scheduled_days' => $card->scheduled_days,
            'stability' => (float) $card->stability,
            'difficulty' => (float) $card->difficulty,
            'reps' => $card->reps,
            'lapses' => $card->lapses,
        ];

        $payload = match ($card->state) {
            'new' => $this->scheduleFromNew($card, $resolvedRating, $resolvedNow),
            'learning' => $this->scheduleLearning($card, $resolvedRating, $resolvedNow),
            'review' => $this->scheduleReview($card, $resolvedRating, $resolvedNow),
            'relearning' => $this->scheduleRelearning($card, $resolvedRating, $resolvedNow),
            default => throw new InvalidArgumentException('Unsupported card state provided.'),
        };

        return [
            'state_before' => $before['state'],
            'state_after' => $payload['state'],
            'step_before' => $before['step'],
            'step_after' => $payload['current_step'],
            'due_before' => $before['due_at']?->toIso8601String(),
            'next_due_at' => $payload['due_at']?->toIso8601String(),
            'scheduled_days_before' => $before['scheduled_days'],
            'scheduled_days' => $payload['scheduled_days'],
            'stability_before' => $before['stability'],
            'next_stability' => $payload['stability'],
            'difficulty_before' => $before['difficulty'],
            'next_difficulty' => $payload['difficulty'],
            'reps_before' => $before['reps'],
            'next_reps' => $payload['reps'],
            'lapses_before' => $before['lapses'],
            'next_lapses' => $payload['lapses'],
        ];
    }

    private function scheduleFromNew(Card $card, string $rating, CarbonImmutable $now): array
    {
        if ($rating === 'easy') {
            return $this->graduateToReview($card, $now, 4, 'easy');
        }

        return $this->buildPayload(
            card: $card,
            state: 'learning',
            currentStep: 0,
            dueAt: $now->addMinutes(1),
            scheduledDays: 0,
            rating: $rating,
            stability: $this->adjustStability((float) $card->stability, $rating),
            difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
            repsDelta: 1,
            lapsesDelta: 0,
        );
    }

    private function scheduleLearning(Card $card, string $rating, CarbonImmutable $now): array
    {
        $steps = $this->learningSteps($card);
        $currentStep = min($card->current_step, max(count($steps) - 1, 0));

        return match ($rating) {
            'again' => $this->buildPayload(
                card: $card,
                state: 'learning',
                currentStep: 0,
                dueAt: $now->addMinutes($steps[0]),
                scheduledDays: 0,
                rating: $rating,
                stability: $this->adjustStability((float) $card->stability, $rating),
                difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
                repsDelta: 1,
                lapsesDelta: 0,
            ),
            'hard' => $this->buildPayload(
                card: $card,
                state: 'learning',
                currentStep: $currentStep,
                dueAt: $now->addMinutes($this->hardLearningMinutes($steps[$currentStep])),
                scheduledDays: 0,
                rating: $rating,
                stability: $this->adjustStability((float) $card->stability, $rating),
                difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
                repsDelta: 1,
                lapsesDelta: 0,
            ),
            'good' => $this->scheduleLearningGood($card, $currentStep, $steps, $now),
            'easy' => $this->graduateToReview($card, $now, 4, 'easy'),
        };
    }

    private function scheduleLearningGood(Card $card, int $currentStep, array $steps, CarbonImmutable $now): array
    {
        $nextStep = $currentStep + 1;

        if ($nextStep >= count($steps)) {
            return $this->graduateToReview($card, $now, 1, 'good');
        }

        return $this->buildPayload(
            card: $card,
            state: 'learning',
            currentStep: $nextStep,
            dueAt: $now->addMinutes($steps[$nextStep]),
            scheduledDays: 0,
            rating: 'good',
            stability: $this->adjustStability((float) $card->stability, 'good'),
            difficulty: $this->adjustDifficulty((float) $card->difficulty, 'good'),
            repsDelta: 1,
            lapsesDelta: 0,
        );
    }

    private function scheduleReview(Card $card, string $rating, CarbonImmutable $now): array
    {
        if ($rating === 'again') {
            $steps = $this->relearningSteps($card);

            return $this->buildPayload(
                card: $card,
                state: 'relearning',
                currentStep: 0,
                dueAt: $now->addMinutes($steps[0]),
                scheduledDays: 0,
                rating: $rating,
                stability: $this->adjustStability((float) $card->stability, $rating),
                difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
                repsDelta: 1,
                lapsesDelta: 1,
            );
        }

        $baseDays = max($card->scheduled_days, 1);
        $scheduledDays = match ($rating) {
            'hard' => max(1, (int) ceil($baseDays * 1.2)),
            'good' => max(2, (int) ceil($baseDays * 2.0)),
            'easy' => max(4, (int) ceil($baseDays * 3.0)),
        };

        return $this->buildPayload(
            card: $card,
            state: 'review',
            currentStep: 0,
            dueAt: $now->addDays($scheduledDays),
            scheduledDays: $scheduledDays,
            rating: $rating,
            stability: $this->adjustStability((float) $card->stability, $rating),
            difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
            repsDelta: 1,
            lapsesDelta: 0,
        );
    }

    private function scheduleRelearning(Card $card, string $rating, CarbonImmutable $now): array
    {
        $steps = $this->relearningSteps($card);
        $currentStep = min($card->current_step, max(count($steps) - 1, 0));

        return match ($rating) {
            'again' => $this->buildPayload(
                card: $card,
                state: 'relearning',
                currentStep: 0,
                dueAt: $now->addMinutes($steps[0]),
                scheduledDays: 0,
                rating: $rating,
                stability: $this->adjustStability((float) $card->stability, $rating),
                difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
                repsDelta: 1,
                lapsesDelta: 1,
            ),
            'hard' => $this->buildPayload(
                card: $card,
                state: 'relearning',
                currentStep: $currentStep,
                dueAt: $now->addMinutes($this->hardLearningMinutes($steps[$currentStep])),
                scheduledDays: 0,
                rating: $rating,
                stability: $this->adjustStability((float) $card->stability, $rating),
                difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
                repsDelta: 1,
                lapsesDelta: 0,
            ),
            'good' => $this->scheduleRelearningGood($card, $currentStep, $steps, $now),
            'easy' => $this->graduateToReview($card, $now, max($card->scheduled_days, 1), 'easy'),
        };
    }

    private function scheduleRelearningGood(Card $card, int $currentStep, array $steps, CarbonImmutable $now): array
    {
        $nextStep = $currentStep + 1;

        if ($nextStep >= count($steps)) {
            return $this->graduateToReview($card, $now, max($card->scheduled_days, 1), 'good');
        }

        return $this->buildPayload(
            card: $card,
            state: 'relearning',
            currentStep: $nextStep,
            dueAt: $now->addMinutes($steps[$nextStep]),
            scheduledDays: 0,
            rating: 'good',
            stability: $this->adjustStability((float) $card->stability, 'good'),
            difficulty: $this->adjustDifficulty((float) $card->difficulty, 'good'),
            repsDelta: 1,
            lapsesDelta: 0,
        );
    }

    private function graduateToReview(Card $card, CarbonImmutable $now, int $days, string $rating): array
    {
        return $this->buildPayload(
            card: $card,
            state: 'review',
            currentStep: 0,
            dueAt: $now->addDays($days),
            scheduledDays: $days,
            rating: $rating,
            stability: $this->adjustStability((float) $card->stability, $rating),
            difficulty: $this->adjustDifficulty((float) $card->difficulty, $rating),
            repsDelta: 1,
            lapsesDelta: 0,
        );
    }

    private function buildPayload(
        Card $card,
        string $state,
        int $currentStep,
        CarbonImmutable $dueAt,
        int $scheduledDays,
        string $rating,
        float $stability,
        float $difficulty,
        int $repsDelta,
        int $lapsesDelta,
    ): array {
        return [
            'state' => $state,
            'current_step' => $currentStep,
            'due_at' => $dueAt,
            'scheduled_days' => $scheduledDays,
            'stability' => $stability,
            'difficulty' => $difficulty,
            'reps' => $card->reps + $repsDelta,
            'lapses' => $card->lapses + $lapsesDelta,
            'last_rating' => $rating,
        ];
    }

    private function learningSteps(Card $card): array
    {
        return $this->resolveSteps($card->learning_steps_json, [1, 10]);
    }

    private function relearningSteps(Card $card): array
    {
        return $this->resolveSteps($card->relearning_steps_json, [10]);
    }

    private function resolveSteps(mixed $steps, array $fallback): array
    {
        if (! is_array($steps) || $steps === []) {
            return $fallback;
        }

        $resolved = array_values(array_filter(array_map(
            static fn (mixed $step): int => max((int) $step, 1),
            $steps
        )));

        return $resolved === [] ? $fallback : $resolved;
    }

    private function hardLearningMinutes(int $currentStepMinutes): int
    {
        if ($currentStepMinutes <= 1) {
            return 1;
        }

        return max((int) floor($currentStepMinutes / 2), 1);
    }

    private function adjustStability(float $current, string $rating): float
    {
        return round(match ($rating) {
            'again' => max(0.1, $current * 0.6),
            'hard' => max(0.1, $current * 0.9),
            'good' => $current * 1.2,
            'easy' => $current * 1.5,
        }, 4);
    }

    private function adjustDifficulty(float $current, string $rating): float
    {
        return round(match ($rating) {
            'again' => min(10.0, $current + 0.5),
            'hard' => min(10.0, $current + 0.2),
            'good' => max(1.0, $current - 0.1),
            'easy' => max(1.0, $current - 0.3),
        }, 4);
    }
}
