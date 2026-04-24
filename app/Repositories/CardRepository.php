<?php

namespace App\Repositories;

use App\Models\Card;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CardRepository
{
    public function paginateForUser(User $user, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Card::query()
            ->with(['note:id,front_text,back_text,front_plain_text,back_plain_text', 'deck:id,name'])
            ->where('user_id', $user->id);

        if (($deckId = (int) ($filters['deck_id'] ?? 0)) > 0) {
            $query->where('deck_id', $deckId);
        }

        $this->applySearch($query, (string) ($filters['q'] ?? ''));
        $this->applyStatus($query, $filters['status'] ?? null);

        return $query
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function applySearch(Builder $query, string $search): void
    {
        $search = trim($search);

        if ($search === '') {
            return;
        }

        $query->whereHas('note', function (Builder $noteQuery) use ($search): void {
            $noteQuery->where(function (Builder $nestedQuery) use ($search): void {
                $nestedQuery
                    ->where('front_text', 'like', '%' . $search . '%')
                    ->orWhere('back_text', 'like', '%' . $search . '%')
                    ->orWhere('front_plain_text', 'like', '%' . $search . '%')
                    ->orWhere('back_plain_text', 'like', '%' . $search . '%')
                    ->orWhere('note_text', 'like', '%' . $search . '%');
            });
        });
    }

    private function applyStatus(Builder $query, mixed $status): void
    {
        if (! is_string($status) || $status === '' || $status === 'all') {
            return;
        }

        if ($status === 'learning') {
            $query->whereIn('state', ['learning', 'relearning']);
            return;
        }

        if (in_array($status, ['new', 'review', 'relearning'], true)) {
            $query->where('state', $status);
        }
    }
}
