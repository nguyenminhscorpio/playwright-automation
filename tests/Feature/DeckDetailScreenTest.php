<?php

namespace Tests\Feature;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckDetailScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_deck_renders_empty_state_instead_of_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/decks/61');

        $response->assertOk();
        $response->assertSee('No deck found');
        $response->assertSee('You do not have any decks yet');
    }

    public function test_unavailable_deck_shows_empty_state_when_user_has_other_decks(): void
    {
        $user = User::factory()->create();
        Deck::factory()->create(['user_id' => $user->id, 'name' => 'Available Deck']);

        $response = $this->actingAs($user)->get('/decks/61');

        $response->assertOk();
        $response->assertSee('No deck found');
        $response->assertSee('This deck is no longer available');
    }

    public function test_existing_deck_renders_detail_screen(): void
    {
        $user = User::factory()->create();
        $deck = Deck::factory()->create(['user_id' => $user->id, 'name' => 'Existing Deck']);

        $response = $this->actingAs($user)->get("/decks/{$deck->id}");

        $response->assertOk();
        $response->assertSee('Existing Deck');
        $response->assertSee('Cards');
    }
}
