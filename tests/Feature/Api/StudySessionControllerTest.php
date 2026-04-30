<?php

namespace Tests\Feature\Api;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudySessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Deck $deck;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->deck = Deck::factory()->create(['user_id' => $this->user->id]);
    }

    private function createCardWithNote(array $cardOverrides = []): Card
    {
        $note = Note::create([
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'front_text' => '食べる',
            'back_text' => 'To eat',
            'front_plain_text' => '食べる',
            'back_plain_text' => 'To eat',
            'source_type' => 'manual',
        ]);

        return Card::create(array_merge([
            'note_id' => $note->id,
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
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
        ], $cardOverrides));
    }

    // ─── Show (Build Session) ───────────────────────────────────

    #[Test]
    public function given_new_cards_exist_when_show_session_then_returns_card(): void
    {
        // Arrange
        $this->createCardWithNote();

        // Act
        $response = $this->getJson('/api/study/session?' . http_build_query([
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'mode' => 'flip',
        ]));

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'session_id',
                'mode',
                'deck_id',
                'current_card' => ['id', 'front_text', 'back_text', 'state'],
                'progress' => ['new', 'learning', 'review', 'total'],
            ]);

        $this->assertSame('flip', $response->json('mode'));
        $this->assertTrue($response->json('progress.has_cards'));
    }

    #[Test]
    public function given_no_cards_when_show_session_then_current_card_null(): void
    {
        // Act
        $response = $this->getJson('/api/study/session?' . http_build_query([
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'mode' => 'flip',
        ]));

        // Assert
        $response->assertOk();
        $this->assertNull($response->json('current_card'));
        $this->assertTrue($response->json('progress.ended'));
    }

    #[Test]
    public function given_suspended_cards_when_show_session_then_excluded(): void
    {
        // Arrange — only suspended cards
        $this->createCardWithNote(['is_suspended' => true]);

        // Act
        $response = $this->getJson('/api/study/session?' . http_build_query([
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'mode' => 'flip',
        ]));

        // Assert
        $response->assertOk();
        $this->assertNull($response->json('current_card'));
    }

    #[Test]
    public function given_typing_mode_when_show_session_then_mode_is_typing(): void
    {
        // Arrange
        $this->createCardWithNote();

        // Act
        $response = $this->getJson('/api/study/session?' . http_build_query([
            'user_id' => $this->user->id,
            'mode' => 'typing',
        ]));

        // Assert
        $response->assertOk();
        $this->assertSame('typing', $response->json('mode'));
    }

    // ─── Check Answer ───────────────────────────────────────────

    #[Test]
    public function given_correct_answer_when_check_then_returns_correct(): void
    {
        // Arrange
        $card = $this->createCardWithNote();

        // Act
        $response = $this->postJson("/api/study/cards/{$card->id}/check-answer", [
            'mode' => 'typing',
            'user_answer' => 'To eat',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment(['result' => 'correct']);
    }

    #[Test]
    public function given_wrong_answer_when_check_then_returns_incorrect(): void
    {
        // Arrange
        $card = $this->createCardWithNote(); // back_plain_text = "To eat"

        // Act
        $response = $this->postJson("/api/study/cards/{$card->id}/check-answer", [
            'mode' => 'typing',
            'user_answer' => 'completely wrong answer here',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment(['result' => 'incorrect']);
    }

    // ─── Rate ───────────────────────────────────────────────────

    #[Test]
    public function given_new_card_when_rate_good_then_state_transitions(): void
    {
        // Arrange
        $card = $this->createCardWithNote();

        // Act
        $response = $this->postJson("/api/study/cards/{$card->id}/rate", [
            'mode' => 'flip',
            'rating' => 'good',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment([
                'state_before' => 'new',
                'state_after' => 'learning',
            ]);

        // Verify card state updated in DB
        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'state' => 'learning',
        ]);

        // Verify review log created
        $this->assertDatabaseHas('review_logs', [
            'card_id' => $card->id,
            'rating' => 'good',
        ]);
    }

    #[Test]
    public function given_review_card_when_rate_again_then_lapses_incremented(): void
    {
        // Arrange
        $card = $this->createCardWithNote([
            'state' => 'review',
            'scheduled_days' => 10,
            'reps' => 5,
            'lapses' => 0,
        ]);

        // Act
        $response = $this->postJson("/api/study/cards/{$card->id}/rate", [
            'mode' => 'flip',
            'rating' => 'again',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment([
                'state_before' => 'review',
                'state_after' => 'relearning',
            ]);

        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'state' => 'relearning',
            'lapses' => 1,
        ]);
    }

    #[Test]
    public function given_typing_mode_when_rate_then_stores_typed_answer_in_log(): void
    {
        // Arrange
        $card = $this->createCardWithNote();

        // Act
        $response = $this->postJson("/api/study/cards/{$card->id}/rate", [
            'mode' => 'typing',
            'rating' => 'easy',
            'typed_answer' => 'To eat',
            'judged_result' => 'correct',
        ]);

        // Assert
        $response->assertOk();

        $this->assertDatabaseHas('review_logs', [
            'card_id' => $card->id,
            'mode' => 'typing',
            'typed_answer' => 'To eat',
            'judged_result' => 'correct',
        ]);
    }
}
