<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Deck;
use App\Models\Note;
use App\Models\User;
use App\Repositories\CardRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CardController extends Controller
{
    public function __construct(
        private readonly CardRepository $cardRepository,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $validated = $request->validate([
            'deck_id' => ['nullable', 'integer', 'exists:decks,id'],
            'q' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $cards = $this->cardRepository->paginateForUser(
            $user,
            $validated,
            (int) ($validated['per_page'] ?? 20),
        );

        return response()->json($cards->through(fn (Card $card): array => $this->mapCard($card)));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $validated = $request->validate([
            'deck_id' => ['required', 'integer', 'exists:decks,id'],
            'front_text' => ['required', 'string'],
            'back_text' => ['required', 'string'],
        ]);

        $deck = Deck::query()->where('user_id', $user->id)->findOrFail($validated['deck_id']);
        $card = $this->createCard($user, $deck, $validated['front_text'], $validated['back_text']);

        return response()->json($this->mapCard($card->load(['note', 'deck'])), 201);
    }

    public function show(Request $request, Card $card): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($card->user_id === $user->id, 404);

        return response()->json($this->mapCard($card->loadMissing(['note', 'deck'])));
    }

    public function update(Request $request, Card $card): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($card->user_id === $user->id, 404);

        $validated = $request->validate([
            'front_text' => ['required', 'string'],
            'back_text' => ['required', 'string'],
        ]);

        $card->loadMissing('note');
        $card->note->update([
            'front_text' => $validated['front_text'],
            'back_text' => $validated['back_text'],
            'front_plain_text' => $this->plainText($validated['front_text']),
            'back_plain_text' => $this->plainText($validated['back_text']),
        ]);

        return response()->json($this->mapCard($card->fresh()->load(['note', 'deck'])));
    }

    public function destroy(Request $request, Card $card): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($card->user_id === $user->id, 404);

        $card->loadMissing('note');
        $note = $card->note;
        $card->delete();

        if ($note !== null && $note->cards()->count() === 0) {
            $note->delete();
        }

        return response()->json(['deleted' => true, 'id' => $card->id]);
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $validated = $request->validate([
            'deck_id' => ['nullable', 'integer', 'exists:decks,id'],
            'all' => ['nullable', 'boolean'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:cards,id'],
            'exclude_ids' => ['nullable', 'array'],
            'exclude_ids.*' => ['integer', 'exists:cards,id'],
        ]);

        $query = Card::query()->where('user_id', $user->id);

        if (!empty($validated['all']) && !empty($validated['deck_id'])) {
            $query->where('deck_id', $validated['deck_id']);
            if (!empty($validated['exclude_ids'])) {
                $query->whereNotIn('id', $validated['exclude_ids']);
            }
        } elseif (!empty($validated['ids'])) {
            $query->whereIn('id', $validated['ids']);
        } else {
            return response()->json(['deleted' => 0]);
        }

        $cards = $query->with('note')->get();
        $count = 0;

        foreach ($cards as $card) {
            $note = $card->note;
            $card->delete();
            if ($note !== null && $note->cards()->count() === 0) {
                $note->delete();
            }
            $count++;
        }

        return response()->json(['deleted' => $count]);
    }

    private function createCard(User $user, Deck $deck, string $frontText, string $backText): Card
    {
        $note = Note::query()->create([
            'user_id' => $user->id,
            'deck_id' => $deck->id,
            'front_text' => $frontText,
            'back_text' => $backText,
            'front_plain_text' => $this->plainText($frontText),
            'back_plain_text' => $this->plainText($backText),
            'source_type' => 'manual',
        ]);

        return Card::query()->create([
            'note_id' => $note->id,
            'user_id' => $user->id,
            'deck_id' => $deck->id,
            'state' => 'new',
            'current_step' => 0,
            'learning_steps_json' => [1, 10],
            'relearning_steps_json' => [10],
            'due_at' => null,
            'last_reviewed_at' => null,
            'stability' => 1.0,
            'difficulty' => 5.0,
            'elapsed_days' => 0,
            'scheduled_days' => 0,
            'reps' => 0,
            'lapses' => 0,
            'last_rating' => null,
            'is_suspended' => false,
        ]);
    }

    private function plainText(string $value): string
    {
        return trim(Str::of(strip_tags($value))->replaceMatches('/\s+/', ' ')->toString());
    }

    private function mapCard(Card $card): array
    {
        return [
            'id' => $card->id,
            'deck_id' => $card->deck_id,
            'deck_name' => $card->deck?->name,
            'front_text' => $card->note?->front_text,
            'back_text' => $card->note?->back_text,
            'front_plain_text' => $card->note?->front_plain_text,
            'back_plain_text' => $card->note?->back_plain_text,
            'state' => $card->state,
            'last_reviewed_at' => $card->last_reviewed_at?->toIso8601String(),
            'due_at' => $card->due_at?->toIso8601String(),
            'stability' => (float) $card->stability,
        ];
    }

    private function resolveUser(Request $request): User
    {
        $userId = (int) ($request->input('user_id') ?: 0);

        if ($userId > 0) {
            return User::query()->findOrFail($userId);
        }

        return User::query()->firstOrCreate(
            ['email' => 'dev.study@example.com'],
            ['name' => 'Dev Study User', 'password' => Hash::make('password')]
        );
    }
}
