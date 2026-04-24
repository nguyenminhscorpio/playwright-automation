<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deck_id',
        'front_text',
        'back_text',
        'front_plain_text',
        'back_plain_text',
        'note_text',
        'source_type',
        'source_file_name',
        'source_raw_line',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
