<?php

namespace App\Services\Study;

use App\Models\Card;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class StudySessionService
{
    public function buildSession(User $user, ?int $deckId, string $mode): array
    {
        $resolvedMode = in_array($mode, ['flip', 'typing'], true) ? $mode : 'flip';
        $now = CarbonImmutable::now();

        $dueCardsQuery = $this->baseCardsQuery($user, $deckId)
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->where('state', 'relearning')
                    ->where(function (Builder $subQuery) use ($now): void {
                        $subQuery->whereNull('due_at')->orWhere('due_at', '<=', $now);
                    });
            })
            ->orWhere(function (Builder $query) use ($user, $deckId, $now): void {
                $this->applyBaseCardConstraints($query, $user, $deckId);
                $query
                    ->where('state', 'review')
                    ->where(function (Builder $subQuery) use ($now): void {
                        $subQuery->whereNull('due_at')->orWhere('due_at', '<=', $now);
                    });
            })
            ->orWhere(function (Builder $query) use ($user, $deckId, $now): void {
                $this->applyBaseCardConstraints($query, $user, $deckId);
                $query
                    ->where('state', 'learning')
                    ->where(function (Builder $subQuery) use ($now): void {
                        $subQuery->whereNull('due_at')->orWhere('due_at', '<=', $now);
                    });
            });

        $dueCardsCount = (clone $dueCardsQuery)->count();

        $selectedCard = $dueCardsCount > 0
            ? $this->selectDueCard($dueCardsQuery)
            : $this->selectNewCard($user, $deckId);

        $newCount = $this->countNewCards($user, $deckId);
        $learningCount = $this->countDueByStates($user, $deckId, $now, ['learning', 'relearning']);
        $reviewCount = $this->countDueByStates($user, $deckId, $now, ['review']);

        $totalCards = $newCount + $learningCount + $reviewCount;

        return [
            'session_id' => sprintf(
                'study:%d:%s:%s',
                $user->id,
                $deckId ?? 'all',
                $resolvedMode
            ),
            'mode' => $resolvedMode,
            'deck_id' => $deckId,
            'current_card' => $selectedCard ? $this->mapCard($selectedCard) : null,
            'progress' => [
                'new' => $newCount,
                'learning' => $learningCount,
                'review' => $reviewCount,
                'total' => $totalCards,
                'completed' => 0,
                'remaining' => $totalCards,
                'has_cards' => (bool) $selectedCard,
                'ended' => $selectedCard === null,
            ],
        ];
    }

    private function countDueByStates(User $user, ?int $deckId, CarbonImmutable $now, array $states): int
    {
        return $this->baseCardsQuery($user, $deckId)
            ->whereIn('state', $states)
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('due_at')->orWhere('due_at', '<=', $now);
            })
            ->count();
    }

    private function selectDueCard(Builder $query): ?Card
    {
        return $query
            ->orderByRaw("
                case
                    when state = 'relearning' then 1
                    when state = 'review' then 2
                    when state = 'learning' then 3
                    else 9
                end
            ")
            ->orderByRaw('case when due_at is null then 0 else 1 end')
            ->orderBy('due_at')
            ->orderBy('id')
            ->first();
    }

    private function selectNewCard(User $user, ?int $deckId): ?Card
    {
        return $this->baseCardsQuery($user, $deckId)
            ->where('state', 'new')
            ->orderBy('id')
            ->first();
    }

    private function countNewCards(User $user, ?int $deckId): int
    {
        return $this->baseCardsQuery($user, $deckId)
            ->where('state', 'new')
            ->count();
    }

    private function baseCardsQuery(User $user, ?int $deckId): Builder
    {
        $query = Card::query()
            ->with(['note:id,deck_id,front_text,back_text,front_plain_text,back_plain_text', 'deck:id,name'])
            ->where('user_id', $user->id)
            ->where('is_suspended', false);

        if ($deckId !== null) {
            $query->where('deck_id', $deckId);
        }

        return $query;
    }

    private function applyBaseCardConstraints(Builder $query, User $user, ?int $deckId): void
    {
        $query->where('user_id', $user->id)->where('is_suspended', false);

        if ($deckId !== null) {
            $query->where('deck_id', $deckId);
        }
    }

    private function mapCard(Card $card): array
    {
        return [
            'id' => $card->id,
            'deck_id' => $card->deck_id,
            'deck_name' => $card->deck?->name,
            'state' => $card->state,
            'current_step' => $card->current_step,
            'learning_steps' => $card->learning_steps_json,
            'relearning_steps' => $card->relearning_steps_json,
            'due_at' => $card->due_at?->toIso8601String(),
            'front_text' => $card->note?->front_text,
            'back_text' => $card->note?->back_text,
            'front_plain_text' => $card->note?->front_plain_text,
            'back_plain_text' => $card->note?->back_plain_text,
        ];
    }
}
