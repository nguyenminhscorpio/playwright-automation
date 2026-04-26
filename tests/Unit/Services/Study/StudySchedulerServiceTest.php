<?php

namespace Tests\Unit\Services\Study;

use App\Models\Card;
use App\Services\Study\StudySchedulerService;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class StudySchedulerServiceTest extends TestCase
{
    private StudySchedulerService $service;

    private CarbonImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StudySchedulerService();
        $this->now = CarbonImmutable::parse('2026-05-01 10:00:00');
    }

    private function makeCard(array $overrides = []): Card
    {
        $card = new Card();
        $card->forceFill(array_merge([
            'id' => 1,
            'user_id' => 1,
            'deck_id' => 1,
            'state' => 'new',
            'current_step' => 0,
            'learning_steps_json' => [1, 10],
            'relearning_steps_json' => [10],
            'due_at' => null,
            'scheduled_days' => 0,
            'stability' => 1.0,
            'difficulty' => 5.0,
            'reps' => 0,
            'lapses' => 0,
        ], $overrides));

        return $card;
    }

    // ═══════════════════════════════════════════════════════════
    // A. New Card — 4 cases
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_new_card_when_rate_again_then_enters_learning_step0(): void
    {
        $card = $this->makeCard(['state' => 'new']);

        $result = $this->service->schedule($card, 'again', $this->now);

        $this->assertSame('new', $result['state_before']);
        $this->assertSame('learning', $result['state_after']);
        $this->assertSame(0, $result['step_after']);
        $this->assertSame($this->now->addMinutes(1)->toIso8601String(), $result['next_due_at']);
    }

    #[Test]
    public function given_new_card_when_rate_hard_then_enters_learning_step0(): void
    {
        $card = $this->makeCard(['state' => 'new']);

        $result = $this->service->schedule($card, 'hard', $this->now);

        $this->assertSame('learning', $result['state_after']);
        $this->assertSame(0, $result['step_after']);
    }

    #[Test]
    public function given_new_card_when_rate_good_then_enters_learning_step0(): void
    {
        $card = $this->makeCard(['state' => 'new']);

        $result = $this->service->schedule($card, 'good', $this->now);

        $this->assertSame('learning', $result['state_after']);
        $this->assertSame(0, $result['step_after']);
    }

    #[Test]
    public function given_new_card_when_rate_easy_then_graduates_to_review_4days(): void
    {
        $card = $this->makeCard(['state' => 'new']);

        $result = $this->service->schedule($card, 'easy', $this->now);

        $this->assertSame('review', $result['state_after']);
        $this->assertSame(4, $result['scheduled_days']);
        $this->assertSame($this->now->addDays(4)->toIso8601String(), $result['next_due_at']);
    }

    // ═══════════════════════════════════════════════════════════
    // B. Learning Card — 4 cases
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_learning_step0_when_rate_again_then_resets_to_step0(): void
    {
        $card = $this->makeCard([
            'state' => 'learning',
            'current_step' => 0,
            'reps' => 1,
        ]);

        $result = $this->service->schedule($card, 'again', $this->now);

        $this->assertSame('learning', $result['state_after']);
        $this->assertSame(0, $result['step_after']);
        // Due = now + steps[0] = now + 1min
        $this->assertSame($this->now->addMinutes(1)->toIso8601String(), $result['next_due_at']);
    }

    #[Test]
    public function given_learning_step0_when_rate_good_then_advances_to_step1(): void
    {
        $card = $this->makeCard([
            'state' => 'learning',
            'current_step' => 0,
            'reps' => 1,
        ]);

        $result = $this->service->schedule($card, 'good', $this->now);

        $this->assertSame('learning', $result['state_after']);
        $this->assertSame(1, $result['step_after']);
        // Due = now + steps[1] = now + 10min
        $this->assertSame($this->now->addMinutes(10)->toIso8601String(), $result['next_due_at']);
    }

    #[Test]
    public function given_learning_last_step_when_rate_good_then_graduates_to_review(): void
    {
        $card = $this->makeCard([
            'state' => 'learning',
            'current_step' => 1, // last step of [1, 10]
            'reps' => 2,
        ]);

        $result = $this->service->schedule($card, 'good', $this->now);

        $this->assertSame('review', $result['state_after']);
        $this->assertSame(1, $result['scheduled_days']);
    }

    #[Test]
    public function given_learning_when_rate_easy_then_graduates_to_review_4days(): void
    {
        $card = $this->makeCard([
            'state' => 'learning',
            'current_step' => 0,
            'reps' => 1,
        ]);

        $result = $this->service->schedule($card, 'easy', $this->now);

        $this->assertSame('review', $result['state_after']);
        $this->assertSame(4, $result['scheduled_days']);
    }

    // ═══════════════════════════════════════════════════════════
    // C. Review Card — 4 cases
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_review_when_rate_again_then_enters_relearning_with_lapse(): void
    {
        $card = $this->makeCard([
            'state' => 'review',
            'scheduled_days' => 10,
            'reps' => 5,
            'lapses' => 0,
        ]);

        $result = $this->service->schedule($card, 'again', $this->now);

        $this->assertSame('relearning', $result['state_after']);
        $this->assertSame(1, $result['next_lapses']); // lapses incremented
        $this->assertSame(0, $result['scheduled_days']);
    }

    #[Test]
    public function given_review_10days_when_rate_hard_then_interval_12days(): void
    {
        $card = $this->makeCard([
            'state' => 'review',
            'scheduled_days' => 10,
            'reps' => 5,
        ]);

        $result = $this->service->schedule($card, 'hard', $this->now);

        $this->assertSame('review', $result['state_after']);
        // ceil(10 * 1.2) = 12
        $this->assertSame(12, $result['scheduled_days']);
    }

    #[Test]
    public function given_review_10days_when_rate_good_then_interval_20days(): void
    {
        $card = $this->makeCard([
            'state' => 'review',
            'scheduled_days' => 10,
            'reps' => 5,
        ]);

        $result = $this->service->schedule($card, 'good', $this->now);

        $this->assertSame('review', $result['state_after']);
        // ceil(10 * 2.0) = 20
        $this->assertSame(20, $result['scheduled_days']);
    }

    #[Test]
    public function given_review_10days_when_rate_easy_then_interval_30days(): void
    {
        $card = $this->makeCard([
            'state' => 'review',
            'scheduled_days' => 10,
            'reps' => 5,
        ]);

        $result = $this->service->schedule($card, 'easy', $this->now);

        $this->assertSame('review', $result['state_after']);
        // ceil(10 * 3.0) = 30
        $this->assertSame(30, $result['scheduled_days']);
    }

    // ═══════════════════════════════════════════════════════════
    // D. Edge Cases — 4 cases
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_invalid_rating_when_schedule_then_throws_exception(): void
    {
        $card = $this->makeCard(['state' => 'new']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported rating provided.');

        $this->service->schedule($card, 'invalid_rating', $this->now);
    }

    #[Test]
    public function given_invalid_state_when_schedule_then_throws_exception(): void
    {
        $card = $this->makeCard(['state' => 'unknown_state']);

        $this->expectException(InvalidArgumentException::class);

        $this->service->schedule($card, 'good', $this->now);
    }

    #[Test]
    public function given_review_0days_when_rate_hard_then_minimum_1day(): void
    {
        $card = $this->makeCard([
            'state' => 'review',
            'scheduled_days' => 0,
            'reps' => 3,
        ]);

        $result = $this->service->schedule($card, 'hard', $this->now);

        // baseDays = max(0, 1) = 1, hard = max(1, ceil(1*1.2)) = 2
        $this->assertSame(2, $result['scheduled_days']);
        $this->assertSame($this->now->addDays(2)->toIso8601String(), $result['next_due_at']);
    }

    #[Test]
    public function given_empty_learning_steps_when_schedule_then_uses_defaults(): void
    {
        $card = $this->makeCard([
            'state' => 'learning',
            'current_step' => 0,
            'learning_steps_json' => [], // empty → fallback [1, 10]
            'reps' => 1,
        ]);

        $result = $this->service->schedule($card, 'again', $this->now);

        $this->assertSame('learning', $result['state_after']);
        // Uses fallback steps[0] = 1 minute
        $this->assertSame($this->now->addMinutes(1)->toIso8601String(), $result['next_due_at']);
    }

    // ═══════════════════════════════════════════════════════════
    // E. Stability / Difficulty Adjustments — 2 cases
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_stability_0_when_rate_again_then_clamps_to_0_1(): void
    {
        $card = $this->makeCard([
            'state' => 'new',
            'stability' => 0.0,
        ]);

        $result = $this->service->schedule($card, 'again', $this->now);

        // max(0.1, 0.0 * 0.6) = max(0.1, 0.0) = 0.1
        $this->assertSame(0.1, $result['next_stability']);
    }

    #[Test]
    public function given_difficulty_10_when_rate_again_then_clamps_to_10(): void
    {
        $card = $this->makeCard([
            'state' => 'new',
            'difficulty' => 10.0,
        ]);

        $result = $this->service->schedule($card, 'again', $this->now);

        // min(10.0, 10.0 + 0.5) = 10.0
        $this->assertSame(10.0, $result['next_difficulty']);
    }

    // ═══════════════════════════════════════════════════════════
    // F. Relearning State — 2 cases (previously missing!)
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_relearning_last_step_when_rate_good_then_graduates_to_review(): void
    {
        $card = $this->makeCard([
            'state' => 'relearning',
            'current_step' => 0, // relearning has [10], so step 0 is last
            'relearning_steps_json' => [10],
            'scheduled_days' => 5,
            'reps' => 6,
            'lapses' => 1,
        ]);

        $result = $this->service->schedule($card, 'good', $this->now);

        $this->assertSame('review', $result['state_after']);
        $this->assertSame(5, $result['scheduled_days']); // max(scheduled_days, 1)
        $this->assertSame(0, $result['step_after']);
    }

    #[Test]
    public function given_relearning_when_rate_again_then_resets_and_increments_lapses(): void
    {
        $card = $this->makeCard([
            'state' => 'relearning',
            'current_step' => 0,
            'relearning_steps_json' => [10],
            'scheduled_days' => 5,
            'reps' => 6,
            'lapses' => 1,
        ]);

        $result = $this->service->schedule($card, 'again', $this->now);

        $this->assertSame('relearning', $result['state_after']);
        $this->assertSame(0, $result['step_after']);
        $this->assertSame(2, $result['next_lapses']); // 1 + 1
        $this->assertSame($this->now->addMinutes(10)->toIso8601String(), $result['next_due_at']);
    }

    // ═══════════════════════════════════════════════════════════
    // G. Learning Hard + Reps Counting — 2 cases (previously missing!)
    // ═══════════════════════════════════════════════════════════

    #[Test]
    public function given_learning_step1_when_rate_hard_then_halves_step_minutes(): void
    {
        $card = $this->makeCard([
            'state' => 'learning',
            'current_step' => 1, // steps[1] = 10min
            'learning_steps_json' => [1, 10],
            'reps' => 2,
        ]);

        $result = $this->service->schedule($card, 'hard', $this->now);

        $this->assertSame('learning', $result['state_after']);
        $this->assertSame(1, $result['step_after']); // stays at same step
        // hardLearningMinutes(10) = max(floor(10/2), 1) = 5
        $this->assertSame($this->now->addMinutes(5)->toIso8601String(), $result['next_due_at']);
    }

    #[Test]
    public function given_card_with_3_reps_when_schedule_then_reps_increments_to_4(): void
    {
        $card = $this->makeCard([
            'state' => 'learning',
            'current_step' => 0,
            'reps' => 3,
        ]);

        $result = $this->service->schedule($card, 'again', $this->now);

        $this->assertSame(4, $result['next_reps']);
    }

    #[Test]
    public function given_stability_2_when_rate_good_then_multiplied_by_1_2(): void
    {
        $card = $this->makeCard([
            'state' => 'new',
            'stability' => 2.0,
        ]);

        $result = $this->service->schedule($card, 'good', $this->now);

        // 2.0 * 1.2 = 2.4
        $this->assertSame(2.4, $result['next_stability']);
    }
}
