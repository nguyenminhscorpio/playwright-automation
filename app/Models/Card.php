<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'user_id',
        'deck_id',
        'state',
        'current_step',
        'learning_steps_json',
        'relearning_steps_json',
        'due_at',
        'last_reviewed_at',
        'stability',
        'difficulty',
        'elapsed_days',
        'scheduled_days',
        'reps',
        'lapses',
        'last_rating',
        'is_suspended',
    ];

    protected function casts(): array
    {
        return [
            'learning_steps_json' => 'array',
            'relearning_steps_json' => 'array',
            'due_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
            'stability' => 'decimal:4',
            'difficulty' => 'decimal:4',
            'is_suspended' => 'boolean',
        ];
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function reviewLogs(): HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }
}
