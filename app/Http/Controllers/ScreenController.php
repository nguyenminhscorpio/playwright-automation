<?php

namespace App\Http\Controllers;

use App\Models\Deck;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class ScreenController extends Controller
{
    public function dashboard(): View
    {
        [$user, $decks] = $this->resolveStudyContext(request());

        return view('screens.dashboard', [
            'title' => 'FlashMind - Dashboard',
            'dashboardUserName' => $user?->name ?? 'Learner',
            'dashboardStats' => [
                'deck_count' => $decks->count(),
                'card_count' => $user?->cards()->count() ?? 0,
                'note_count' => $user?->notes()->count() ?? 0,
                'import_count' => $user?->importJobs()->count() ?? 0,
                'latest_imported_cards' => $user?->importJobs()->where('status', 'imported')->sum('success_rows') ?? 0,
            ],
            'dashboardDecks' => $decks
                ->loadCount(['cards', 'notes', 'importJobs'])
                ->map(fn (Deck $deck): array => [
                    'id' => $deck->id,
                    'name' => $deck->name,
                    'description' => $deck->description,
                    'cards_count' => $deck->cards_count,
                    'notes_count' => $deck->notes_count,
                    'import_jobs_count' => $deck->import_jobs_count,
                    'new_cards_count' => $deck->cards()->where('state', 'new')->count(),
                ])
                ->all(),
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

    public function deckDetail(string $deck): View
    {
        [$user] = $this->resolveStudyContext(request());

        $deckModel = Deck::query()->where('user_id', $user?->id)->findOrFail($deck);

        $cards = $deckModel->cards()
            ->with('note:id,front_plain_text,back_plain_text,front_text')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('screens.deck-detail', [
            'title' => 'FlashMind - ' . $deckModel->name,
            'deck' => $deckModel,
            'cards' => $cards,
        ]);
    }

    public function imports(Request $request): View
    {
        [$user, $decks, $selectedDeck] = $this->resolveStudyContext($request);

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
        $user = User::query()
            ->where('email', 'dev.study@example.com')
            ->orWhereHas('cards')
            ->orderBy('id')
            ->first();

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
