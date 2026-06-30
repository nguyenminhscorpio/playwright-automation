<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportJob extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'deck_id',
        'file_name',
        'file_path',
        'file_hash',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'started_at',
        'finished_at',
        'error_summary',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'deck_id' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(ImportJobRow::class);
    }
}
