<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
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

    public function startedAtForDisplay(): ?CarbonInterface
    {
        return $this->utcTimestampForDisplay('started_at');
    }

    public function finishedAtForDisplay(): ?CarbonInterface
    {
        return $this->utcTimestampForDisplay('finished_at');
    }

    private function utcTimestampForDisplay(string $attribute): ?CarbonInterface
    {
        $rawValue = $this->getRawOriginal($attribute);

        if (blank($rawValue)) {
            return null;
        }

        return Carbon::parse($rawValue, 'UTC')
            ->setTimezone(config('app.display_timezone'));
    }
}
