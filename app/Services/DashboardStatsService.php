<?php

namespace App\Services;

use App\Models\Deck;
use App\Models\ReviewLog;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    public function build(User $user): array
    {
        $now = CarbonImmutable::now();

        return [
            'daily_streak' => $this->calculateDailyStreak($user),
            'monthly_learned' => $this->calculateMonthlyLearned($user, $now),
            'monthly_goal' => 600,
            'active_decks' => $this->activeDecks($user, $now),
            'totals' => [
                'deck_count' => $user->decks()->count(),
                'card_count' => $user->cards()->count(),
                'note_count' => $user->notes()->count(),
                'due_count' => $user->cards()
                    ->where(function ($query) use ($now): void {
                        $query
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
                    })
                    ->count(),
            ],
        ];
    }

    private function calculateDailyStreak(User $user): int
    {
        $reviewDates = ReviewLog::query()
            ->where('user_id', $user->id)
            ->selectRaw('DATE(reviewed_at) as review_date')
            ->groupBy('review_date')
            ->orderByDesc('review_date')
            ->pluck('review_date')
            ->map(static fn (string $date): string => CarbonImmutable::parse($date)->toDateString())
            ->values();

        if ($reviewDates->isEmpty()) {
            return 0;
        }

        $today = CarbonImmutable::today()->toDateString();
        $yesterday = CarbonImmutable::yesterday()->toDateString();
        $firstDate = $reviewDates->first();

        if (! in_array($firstDate, [$today, $yesterday], true)) {
            return 0;
        }

        $cursor = CarbonImmutable::parse($firstDate);
        $streak = 0;

        foreach ($reviewDates as $reviewDate) {
            if ($reviewDate !== $cursor->toDateString()) {
                break;
            }

            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }

    private function calculateMonthlyLearned(User $user, CarbonImmutable $now): int
    {
        return ReviewLog::query()
            ->where('user_id', $user->id)
            ->where('next_state', 'review')
            ->whereBetween('reviewed_at', [$now->startOfMonth(), $now->endOfMonth()])
            ->distinct('card_id')
            ->count('card_id');
    }

    private function activeDecks(User $user, CarbonImmutable $now): array
    {
        return Deck::query()
            ->where('decks.user_id', $user->id)
            ->join('cards', 'cards.deck_id', '=', 'decks.id')
            ->select([
                'decks.id',
                'decks.name',
                'decks.description',
                DB::raw('count(cards.id) as total_count'),
                DB::raw("sum(case when cards.state <> 'new' then 1 else 0 end) as learned_count"),
                DB::raw("sum(case when cards.state = 'review' then 1 else 0 end) as review_count"),
                DB::raw("sum(case when (cards.state in ('learning', 'relearning') and (cards.due_at is null or cards.due_at <= ?)) or (cards.state = 'review' and cards.due_at <= ?) then 1 else 0 end) as due_count"),
                DB::raw('max(cards.last_reviewed_at) as last_reviewed_at'),
            ])
            ->setBindings([$now->toDateTimeString(), $now->toDateTimeString()], 'select')
            ->groupBy('decks.id', 'decks.name', 'decks.description')
            ->orderByDesc('due_count')
            ->orderByDesc('last_reviewed_at')
            ->orderByDesc('decks.id')
            ->get()
            ->map(static function ($row): array {
                $totalCount = (int) $row->total_count;
                $reviewCount = (int) $row->review_count;

                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'description' => $row->description,
                    'total_count' => $totalCount,
                    'learned_count' => (int) $row->learned_count,
                    'due_count' => (int) $row->due_count,
                    'mastery_percent' => $totalCount > 0 ? (int) round(($reviewCount / $totalCount) * 100) : 0,
                ];
            })
            ->all();
    }
}
