<?php

namespace Tests\Feature\Services;

use App\Models\Card;
use App\Models\Deck;
use App\Models\ImportJob;
use App\Models\Note;
use App\Models\ReviewLog;
use App\Models\User;
use App\Services\DashboardStatsService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private DashboardStatsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->service = app(DashboardStatsService::class);
    }

    private function createReviewLog(Card $card, string $date, string $nextState = 'learning'): void
    {
        ReviewLog::create([
            'user_id' => $this->user->id,
            'card_id' => $card->id,
            'mode' => 'flip',
            'rating' => 'good',
            'previous_state' => 'new',
            'next_state' => $nextState,
            'previous_step' => 0,
            'next_step' => 0,
            'reviewed_at' => CarbonImmutable::parse($date),
        ]);
    }

    private function createCard(): Card
    {
        $deck = Deck::factory()->create(['user_id' => $this->user->id]);

        $note = Note::create([
            'user_id' => $this->user->id,
            'deck_id' => $deck->id,
            'front_text' => 'test',
            'back_text' => 'test',
            'front_plain_text' => 'test',
            'back_plain_text' => 'test',
            'source_type' => 'manual',
        ]);

        return Card::create([
            'note_id' => $note->id,
            'user_id' => $this->user->id,
            'deck_id' => $deck->id,
            'state' => 'new',
            'current_step' => 0,
            'learning_steps_json' => [1, 10],
            'relearning_steps_json' => [10],
            'stability' => 1.0,
            'difficulty' => 5.0,
            'elapsed_days' => 0,
            'scheduled_days' => 0,
            'reps' => 0,
            'lapses' => 0,
            'is_suspended' => false,
        ]);
    }

    // ─── Daily Streak ───────────────────────────────────────────

    #[Test]
    public function given_consecutive_reviews_when_calculate_streak_then_counts_days(): void
    {
        // Arrange — 3 consecutive days ending today
        $card = $this->createCard();
        $today = CarbonImmutable::today();

        $this->createReviewLog($card, $today->toDateTimeString());
        $this->createReviewLog($card, $today->subDay()->toDateTimeString());
        $this->createReviewLog($card, $today->subDays(2)->toDateTimeString());

        // Act
        $result = $this->service->build($this->user);

        // Assert
        $this->assertSame(3, $result['daily_streak']);
    }

    #[Test]
    public function given_gap_in_reviews_when_calculate_streak_then_stops_at_gap(): void
    {
        // Arrange — today + yesterday, then gap, then 3 days ago
        $card = $this->createCard();
        $today = CarbonImmutable::today();

        $this->createReviewLog($card, $today->toDateTimeString());
        $this->createReviewLog($card, $today->subDay()->toDateTimeString());
        // skip subDays(2)
        $this->createReviewLog($card, $today->subDays(3)->toDateTimeString());

        // Act
        $result = $this->service->build($this->user);

        // Assert — streak stops at 2 (today + yesterday)
        $this->assertSame(2, $result['daily_streak']);
    }

    #[Test]
    public function given_no_reviews_when_calculate_streak_then_returns_0(): void
    {
        // Act
        $result = $this->service->build($this->user);

        // Assert
        $this->assertSame(0, $result['daily_streak']);
    }

    // ─── Monthly Learned ────────────────────────────────────────

    #[Test]
    public function given_reviews_this_month_when_monthly_learned_then_counts_unique_cards(): void
    {
        // Arrange — 2 unique cards graduated to 'review' this month
        $card1 = $this->createCard();
        $card2 = $this->createCard();
        $now = CarbonImmutable::now();

        $this->createReviewLog($card1, $now->toDateTimeString(), 'review');
        $this->createReviewLog($card2, $now->toDateTimeString(), 'review');
        // Duplicate card1 review — should not double-count
        $this->createReviewLog($card1, $now->toDateTimeString(), 'review');

        // Act
        $result = $this->service->build($this->user);

        // Assert — 2 unique cards, not 3
        $this->assertSame(2, $result['monthly_learned']);
    }

    // ─── BUG: due_count inconsistency ───────────────────────────

    #[Test]
    public function given_learning_card_with_future_due_when_build_then_NOT_counted_as_due(): void
    {
        // Arrange — card in learning state, due_at = 10 minutes from now (NOT yet due)
        $card = $this->createCard();
        $card->update([
            'state' => 'learning',
            'due_at' => CarbonImmutable::now()->addMinutes(10),
        ]);

        // Act
        $result = $this->service->build($this->user);

        // Assert — card chưa đến lượt, KHÔNG nên đếm vào due_count
        // BUG: hiện tại code đếm TẤT CẢ learning/relearning, không check due_at
        $this->assertSame(0, $result['totals']['due_count']);
    }

    #[Test]
    public function given_learning_card_with_past_due_when_build_then_counted_as_due(): void
    {
        // Arrange — card in learning state, due_at = 5 minutes ago (overdue)
        $card = $this->createCard();
        $card->update([
            'state' => 'learning',
            'due_at' => CarbonImmutable::now()->subMinutes(5),
        ]);

        // Act
        $result = $this->service->build($this->user);

        // Assert — card đã đến lượt, CẦN đếm
        $this->assertSame(1, $result['totals']['due_count']);
    }

    #[Test]
    public function given_previewed_import_without_cards_when_build_then_deck_is_not_active(): void
    {
        $deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Preview Only Deck',
        ]);

        ImportJob::create([
            'user_id' => $this->user->id,
            'deck_id' => $deck->id,
            'file_name' => 'preview-only.txt',
            'file_hash' => 'preview-only-hash',
            'status' => 'previewed',
            'total_rows' => 10,
            'success_rows' => 10,
            'failed_rows' => 0,
            'started_at' => now(),
            'finished_at' => now(),
        ]);

        $result = $this->service->build($this->user);

        $this->assertSame([], $result['active_decks']);
    }
}
