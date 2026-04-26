<?php

namespace Tests\Feature\Api;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CardControllerTest extends TestCase
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

    private function createCardWithNote(array $noteOverrides = [], array $cardOverrides = []): Card
    {
        $note = Note::create(array_merge([
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'front_text' => 'Front text',
            'back_text' => 'Back text',
            'front_plain_text' => 'Front text',
            'back_plain_text' => 'Back text',
            'source_type' => 'manual',
        ], $noteOverrides));

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

    // ─── Store ──────────────────────────────────────────────────

    #[Test]
    public function given_valid_data_when_post_cards_then_creates_card_and_note(): void
    {
        // Act
        $response = $this->postJson('/api/cards', [
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'front_text' => '食べる',
            'back_text' => 'To eat',
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonFragment([
                'front_text' => '食べる',
                'back_text' => 'To eat',
                'state' => 'new',
            ]);

        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'front_text' => '食べる',
        ]);

        $this->assertDatabaseHas('cards', [
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'state' => 'new',
        ]);
    }

    // ─── Update ─────────────────────────────────────────────────

    #[Test]
    public function given_existing_card_when_put_then_updates_note(): void
    {
        // Arrange
        $card = $this->createCardWithNote([
            'front_text' => 'Old front',
            'back_text' => 'Old back',
        ]);

        // Act
        $response = $this->putJson("/api/cards/{$card->id}", [
            'user_id' => $this->user->id,
            'front_text' => 'New front',
            'back_text' => 'New back',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment([
                'front_text' => 'New front',
                'back_text' => 'New back',
            ]);

        $this->assertDatabaseHas('notes', [
            'id' => $card->note_id,
            'front_text' => 'New front',
            'back_text' => 'New back',
        ]);
    }

    // ─── Bulk Destroy ───────────────────────────────────────────

    #[Test]
    public function given_card_ids_when_bulk_delete_then_removes_all(): void
    {
        // Arrange
        $card1 = $this->createCardWithNote(['front_text' => 'Card 1']);
        $card2 = $this->createCardWithNote(['front_text' => 'Card 2']);

        // Act
        $response = $this->deleteJson('/api/cards/bulk', [
            'user_id' => $this->user->id,
            'ids' => [$card1->id, $card2->id],
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment(['deleted' => 2]);

        $this->assertDatabaseMissing('cards', ['id' => $card1->id]);
        $this->assertDatabaseMissing('cards', ['id' => $card2->id]);
    }

    #[Test]
    public function given_all_flag_with_excludes_when_bulk_delete_then_keeps_excluded(): void
    {
        // Arrange
        $card1 = $this->createCardWithNote(['front_text' => 'Keep me']);
        $card2 = $this->createCardWithNote(['front_text' => 'Delete me']);
        $card3 = $this->createCardWithNote(['front_text' => 'Delete me too']);

        // Act
        $response = $this->deleteJson('/api/cards/bulk', [
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'all' => true,
            'exclude_ids' => [$card1->id],
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment(['deleted' => 2]);

        $this->assertDatabaseHas('cards', ['id' => $card1->id]);
        $this->assertDatabaseMissing('cards', ['id' => $card2->id]);
        $this->assertDatabaseMissing('cards', ['id' => $card3->id]);
    }

    // ─── Validation ─────────────────────────────────────────────

    #[Test]
    public function given_missing_front_text_when_post_cards_then_returns_422(): void
    {
        // Act — missing front_text
        $response = $this->postJson('/api/cards', [
            'user_id' => $this->user->id,
            'deck_id' => $this->deck->id,
            'back_text' => 'To eat',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['front_text']);
    }

    #[Test]
    public function given_no_ids_and_no_all_when_bulk_delete_then_returns_0(): void
    {
        // Arrange
        $this->createCardWithNote(['front_text' => 'Should survive']);

        // Act — neither ids nor all flag
        $response = $this->deleteJson('/api/cards/bulk', [
            'user_id' => $this->user->id,
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment(['deleted' => 0]);

        $this->assertDatabaseCount('cards', 1); // card still exists
    }
}
