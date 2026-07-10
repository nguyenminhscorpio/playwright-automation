<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Repositories\CardRepository;
use App\Services\DashboardStatsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScreenController extends Controller
{
    public function dashboard(DashboardStatsService $dashboardStatsService): View
    {
        [$user] = $this->resolveStudyContext(request());
        $stats = $user ? $dashboardStatsService->build($user) : [
            'daily_streak' => 0,
            'monthly_learned' => 0,
            'monthly_goal' => 600,
            'active_decks' => [],
            'totals' => [
                'deck_count' => 0,
                'card_count' => 0,
                'note_count' => 0,
                'due_count' => 0,
            ],
        ];

        return view('screens.dashboard', [
            'title' => 'FlashMind - Dashboard',
            'page' => 'dashboard',
            'dashboardUserId' => $user?->id,
            'dashboardUserName' => $user?->name ?? 'Learner',
            'dashboardStats' => $stats,
            'dashboardDecks' => $stats['active_decks'],
            'recentImports' => $user?->importJobs()
                ->with('deck:id,name')
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(static fn ($job): array => [
                    'id' => $job->id,
                    'file_name' => $job->file_name,
                    'deck_name' => $job->deck?->name ?? 'Unknown deck',
                    'status' => $job->status,
                    'success_rows' => $job->success_rows,
                    'failed_rows' => $job->failed_rows,
                    'finished_at' => $job->finished_at?->format('Y-m-d H:i') ?? '-',
                ])
                ->all() ?? [],
        ]);
    }

    public function deckDetail(Request $request, string $deck, CardRepository $cardRepository): Response
    {
        [$user] = $this->resolveStudyContext($request);

        $allDecks = Deck::query()
            ->where('user_id', $user?->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $deckModel = $allDecks->firstWhere('id', (int) $deck);

        if ($deckModel === null) {
            return view('screens.deck-detail', [
                'title' => 'FlashMind - No Deck Found',
                'page' => 'deck-detail',
                'deckDetailUserId' => $user?->id,
                'deck' => null,
                'allDecks' => $allDecks,
            ]);
        }

        $filters = [
            'deck_id' => $deckModel->id,
            'q' => $request->string('q')->toString(),
            'status' => $request->string('status')->toString(),
        ];
        $cards = $cardRepository->paginateForUser($user, $filters, 20);

        // Deck stats for the header
        $now = now();
        $deckStats = [
            'total' => $deckModel->cards()->count(),
            'new' => $deckModel->cards()->where('state', 'new')->count(),
            'learning' => $deckModel->cards()->whereIn('state', ['learning', 'relearning'])->count(),
            'review' => $deckModel->cards()->where('state', 'review')->count(),
            'due' => $deckModel->cards()->where(function ($q) use ($now) {
                $q->where(function ($lrQ) use ($now) {
                    $lrQ->whereIn('state', ['learning', 'relearning'])
                        ->where(function ($dueQ) use ($now) {
                            $dueQ->whereNull('due_at')->orWhere('due_at', '<=', $now);
                        });
                })->orWhere(function ($revQ) use ($now) {
                    $revQ->where('state', 'review')
                        ->whereNotNull('due_at')
                        ->where('due_at', '<=', $now);
                });
            })->count(),
        ];

        return response()
            ->view('screens.deck-detail', [
                'title' => 'FlashMind - ' . $deckModel->name,
                'page' => 'deck-detail',
                'deckDetailUserId' => $user?->id,
                'deck' => $deckModel,
                'allDecks' => $allDecks,
                'cards' => $cards,
                'filters' => $filters,
                'deckStats' => $deckStats,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    public function imports(Request $request): View
    {
        $user = auth()->user();
        $deckId = $request->integer('deck_id');
        $decks = collect();
        $selectedDeck = null;

        if ($user !== null) {
            $decks = Deck::query()
                ->where('user_id', $user->id)
                ->orderBy('id')
                ->get(['id', 'name', 'description']);

            if ($deckId > 0) {
                $selectedDeck = $decks->firstWhere('id', $deckId);
            }
        }

        return view('screens.imports', [
            'title' => 'FlashMind - Import TXT',
            'page' => 'imports',
            'importUserId' => $user?->id,
            'importDecks' => $decks,
            'importSelectedDeckId' => $selectedDeck?->id,
        ]);
    }

    public function studyFront(Request $request): Response
    {
        return $this->studyScreenResponse($request, 'front', 'FlashMind - Study Front');
    }

    public function studyTyping(Request $request): Response
    {
        return $this->studyScreenResponse($request, 'typing', 'FlashMind - Typing Mode');
    }

    public function studyAnswer(Request $request): Response
    {
        return $this->studyScreenResponse($request, 'answer', 'FlashMind - Answer Revealed');
    }

    private function studyScreenData(Request $request, string $studyScreen, string $title): array
    {
        [$user, , $deck] = $this->resolveStudyContext($request);

        return [
            'title' => $title,
            'studyScreen' => $studyScreen,
            'studyUserId' => $user?->id,
            'studyDeckId' => $deck?->id,
            'studyDeckName' => $deck?->name,
        ];
    }

    private function studyScreenResponse(Request $request, string $studyScreen, string $title): Response
    {
        return response()
            ->view(
                match ($studyScreen) {
                    'typing' => 'screens.study-typing',
                    'answer' => 'screens.study-answer',
                    default => 'screens.study-front',
                },
                $this->studyScreenData($request, $studyScreen, $title)
            )
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }

    private function resolveStudyContext(Request $request): array
    {
        $user = auth()->user();

        $deckId = $request->integer('deck_id');
        $decks = collect();
        $selectedDeck = null;

        if ($user !== null) {
            $decks = Deck::query()
                ->where('user_id', $user->id)
                ->orderBy('id')
                ->get(['id', 'name', 'description']);

            $selectedDeck = $decks
                ->when($deckId > 0, fn ($collection) => $collection->where('id', $deckId))
                ->first() ?? $decks->first();
        }

        return [$user, $decks, $selectedDeck];
    }
}
