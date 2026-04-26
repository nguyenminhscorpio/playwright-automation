<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deck;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeckController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $now = CarbonImmutable::now();

        $items = Deck::query()
            ->where('user_id', $user->id)
            ->withCount([
                'cards as total_count',
                'cards as learned_count' => fn ($query) => $query->where('state', '<>', 'new'),
                'cards as due_count' => fn ($query) => $query->where(function ($nestedQuery) use ($now): void {
                    $nestedQuery
                        ->where(function ($lrQuery) use ($now): void {
                            $lrQuery
                                ->whereIn('state', ['learning', 'relearning'])
                                ->where(function ($dueQuery) use ($now): void {
                                    $dueQuery->whereNull('due_at')->orWhere('due_at', '<=', $now);
                                });
                        })
                        ->orWhere(function ($reviewQuery) use ($now): void {
                            $reviewQuery
                                ->where('state', 'review')
                                ->whereNotNull('due_at')
                                ->where('due_at', '<=', $now);
                        });
                }),
            ])
            ->orderBy('id')
            ->get()
            ->map(static fn (Deck $deck): array => [
                'id' => $deck->id,
                'name' => $deck->name,
                'description' => $deck->description,
                'learned_count' => $deck->learned_count,
                'total_count' => $deck->total_count,
                'due_count' => $deck->due_count,
            ])
            ->all();

        return response()->json(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $deck = Deck::query()->create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return response()->json($this->mapDeck($deck), 201);
    }

    public function show(Request $request, Deck $deck): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($deck->user_id === $user->id, 404);

        return response()->json($this->mapDeck($deck));
    }

    public function update(Request $request, Deck $deck): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($deck->user_id === $user->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $deck->update($validated);

        return response()->json($this->mapDeck($deck->fresh()));
    }

    public function destroy(Request $request, Deck $deck): JsonResponse
    {
        $user = $this->resolveUser($request);
        abort_unless($deck->user_id === $user->id, 404);

        $deck->delete();

        return response()->json(['deleted' => true, 'id' => $deck->id]);
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

    private function mapDeck(Deck $deck): array
    {
        return [
            'id' => $deck->id,
            'user_id' => $deck->user_id,
            'name' => $deck->name,
            'description' => $deck->description,
            'created_at' => $deck->created_at?->toIso8601String(),
            'updated_at' => $deck->updated_at?->toIso8601String(),
        ];
    }
}
