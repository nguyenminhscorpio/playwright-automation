<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportJobRow extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'import_job_id',
        'row_number',
        'raw_content',
        'parsed_front',
        'parsed_back',
        'parsed_audio_token',
        'parsed_tags',
        'status',
        'error_message',
    ];

    public function importJob(): BelongsTo
    {
        return $this->belongsTo(ImportJob::class);
    }
}
