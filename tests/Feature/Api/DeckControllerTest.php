<?php

namespace Tests\Feature\Api;

use App\Models\Card;
use App\Models\Deck;
use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeckControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ─── Store ──────────────────────────────────────────────────

    #[Test]
    public function given_valid_data_when_post_decks_then_creates_and_returns_201(): void
    {
        // Act
        $response = $this->postJson('/api/decks', [
            'user_id' => $this->user->id,
            'name' => 'Japanese N5',
            'description' => 'Basic JLPT vocabulary',
        ]);

        // Assert
        $response->assertCreated()
            ->assertJsonFragment([
                'name' => 'Japanese N5',
                'description' => 'Basic JLPT vocabulary',
                'user_id' => $this->user->id,
            ]);

        $this->assertDatabaseHas('decks', [
            'user_id' => $this->user->id,
            'name' => 'Japanese N5',
        ]);
    }

    #[Test]
    public function given_missing_name_when_post_decks_then_returns_422(): void
    {
        // Act
        $response = $this->postJson('/api/decks', [
            'user_id' => $this->user->id,
            'description' => 'No name provided',
        ]);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    // ─── Index ──────────────────────────────────────────────────

    #[Test]
    public function given_decks_exist_when_get_decks_then_returns_list(): void
    {
        // Arrange
        Deck::factory()->count(3)->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/decks?user_id=' . $this->user->id);

        // Assert
        $response->assertOk()
            ->assertJsonCount(3, 'items');
    }

    // ─── Update ─────────────────────────────────────────────────

    #[Test]
    public function given_existing_deck_when_put_then_updates_name(): void
    {
        // Arrange
        $deck = Deck::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
        ]);

        // Act
        $response = $this->putJson("/api/decks/{$deck->id}", [
            'user_id' => $this->user->id,
            'name' => 'New Name',
            'description' => 'Updated desc',
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment(['name' => 'New Name']);

        $this->assertDatabaseHas('decks', [
            'id' => $deck->id,
            'name' => 'New Name',
        ]);
    }

    // ─── Destroy ────────────────────────────────────────────────

    #[Test]
    public function given_existing_deck_when_delete_then_removes(): void
    {
        // Arrange
        $deck = Deck::factory()->create(['user_id' => $this->user->id]);

        // Act
        $response = $this->deleteJson("/api/decks/{$deck->id}", [
            'user_id' => $this->user->id,
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonFragment(['deleted' => true]);

        $this->assertDatabaseMissing('decks', ['id' => $deck->id]);
    }

    // ─── Ownership Isolation ────────────────────────────────────

    #[Test]
    public function given_other_users_deck_when_delete_then_returns_404(): void
    {
        // Arrange — deck belongs to another user
        $otherUser = User::factory()->create();
        $otherDeck = Deck::factory()->create(['user_id' => $otherUser->id]);

        // Act — try to delete with our user_id
        $response = $this->deleteJson("/api/decks/{$otherDeck->id}", [
            'user_id' => $this->user->id,
        ]);

        // Assert — should be 404, deck still exists
        $response->assertNotFound();
        $this->assertDatabaseHas('decks', ['id' => $otherDeck->id]);
    }
}
