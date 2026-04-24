<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'card_id',
        'mode',
        'rating',
        'typed_answer',
        'judged_result',
        'previous_state',
        'next_state',
        'previous_step',
        'next_step',
        'previous_due_at',
        'next_due_at',
        'previous_stability',
        'next_stability',
        'previous_difficulty',
        'next_difficulty',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'previous_due_at' => 'datetime',
            'next_due_at' => 'datetime',
            'previous_stability' => 'decimal:4',
            'next_stability' => 'decimal:4',
            'previous_difficulty' => 'decimal:4',
            'next_difficulty' => 'decimal:4',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}
