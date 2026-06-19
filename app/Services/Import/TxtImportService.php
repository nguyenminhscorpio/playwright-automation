<?php

namespace App\Services\Import;

use App\Models\Deck;
use App\Models\ImportJob;
use App\Models\ImportJobRow;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TxtImportService
{
    public function __construct(
        private readonly TxtImportParserService $parser,
    ) {
    }

    public function preview(User $user, Deck $deck, UploadedFile $file): array
    {
        $this->assertDeckOwnership($user, $deck);

        $parsed = $this->parser->parseUploadedFile($file);

        $importJob = DB::transaction(function () use ($user, $deck, $parsed): ImportJob {
            $job = ImportJob::query()->create([
                'user_id' => $user->id,
                'deck_id' => $deck->id,
                'file_name' => $parsed['file_name'],
                'file_hash' => $parsed['file_hash'],
                'status' => 'previewed',
                'total_rows' => $parsed['data_lines'],
                'success_rows' => $parsed['valid_rows'],
                'failed_rows' => $parsed['invalid_rows'],
                'started_at' => now(),
                'finished_at' => now(),
                'error_summary' => $parsed['invalid_rows'] > 0
                    ? sprintf('%d invalid rows detected during preview.', $parsed['invalid_rows'])
                    : null,
            ]);

            // FIX: batch insert all rows instead of N individual creates
            $rowsToInsert = array_map(
                static fn (array $row): array => array_merge($row, ['import_job_id' => $job->id]),
                $parsed['rows']
            );
            foreach (array_chunk($rowsToInsert, 500) as $chunk) {
                DB::table('import_job_rows')->insert($chunk);
            }

            return $job->load('rows');
        });

        return [
            'import_job_id' => $importJob->id,
            'file_name' => $parsed['file_name'],
            'file_hash' => $parsed['file_hash'],
            'detected_format' => $parsed['detected_format'],
            'total_lines' => $parsed['total_lines'],
            'data_lines' => $parsed['data_lines'],
            'valid_rows' => $parsed['valid_rows'],
            'invalid_rows' => $parsed['invalid_rows'],
            'rows' => $this->mapRowsForUi($importJob->rows),
            'errors' => $this->collectErrors($importJob->rows),
            'warnings' => $this->collectWarnings($importJob->rows),
            'summary' => [
                'total' => $parsed['data_lines'],
                'valid' => $parsed['valid_rows'],
                'warning' => $this->countWarnings($importJob->rows),
                'invalid' => $parsed['invalid_rows'],
            ],
            'preview_rows' => $parsed['preview_rows'],
        ];
    }

    public function confirm(User $user, ImportJob $importJob): array
    {
        if ($importJob->user_id !== $user->id) {
            throw new InvalidArgumentException('Import job does not belong to the given user.');
        }

        $importJob->loadMissing(['deck', 'rows']);

        if ($importJob->status === 'imported') {
            return [
                'import_job_id' => $importJob->id,
                'inserted_rows' => $importJob->success_rows,
                'skipped_rows' => 0,
                'errors' => [],
                'summary' => [
                    'total' => $importJob->total_rows,
                    'imported' => $importJob->success_rows,
                    'skipped' => 0,
                    'invalid' => $importJob->failed_rows,
                ],
            ];
        }

        $result = DB::transaction(function () use ($user, $importJob): array {
            // FIX 1: Pre-load all existing notes ONCE into a hash set for O(1) lookup.
            // Old code called Note::query()->get() on every single row — catastrophic N+1.
            $existingNoteKeys = DB::table('notes')
                ->where('user_id', $user->id)
                ->where('deck_id', $importJob->deck_id)
                ->get(['front_plain_text', 'back_plain_text'])
                ->mapWithKeys(fn (object $note): array => [
                    $this->parser->normalizeForDuplicate($note->front_plain_text ?? '') . '||' .
                    $this->parser->normalizeForDuplicate($note->back_plain_text ?? '') => true,
                ])
                ->all();

            $insertedRows   = 0;
            $skippedRows    = 0;
            $errors         = [];
            $cardsToInsert  = [];
            $importedRowIds = [];
            $skippedRowIds  = [];
            $now            = now()->toDateTimeString();

            foreach ($importJob->rows as $row) {
                if ($row->status === 'invalid') {
                    $errors[] = [
                        'row_number'    => $row->row_number,
                        'error_message' => $row->error_message,
                    ];
                    continue;
                }

                $frontText = $row->parsed_front ?? '';
                $backText  = $row->parsed_back  ?? '';

                $key = $this->parser->normalizeForDuplicate($frontText) . '||' .
                       $this->parser->normalizeForDuplicate($backText);

                if (isset($existingNoteKeys[$key])) {
                    $skippedRowIds[] = $row->id;
                    $skippedRows++;
                    continue;
                }

                // Prevent duplicates within the same import batch
                $existingNoteKeys[$key] = true;

                // FIX 2: Use DB::table to avoid Eloquent model overhead per row.
                // insertGetId() still needed because card requires the note ID.
                $noteId = DB::table('notes')->insertGetId([
                    'user_id'          => $user->id,
                    'deck_id'          => $importJob->deck_id,
                    'front_text'       => $frontText,
                    'back_text'        => $backText,
                    'front_plain_text' => $this->parser->toPlainText($frontText),
                    'back_plain_text'  => $this->parser->toPlainText($backText),
                    'source_type'      => 'anki_txt_import',
                    'source_file_name' => $importJob->file_name,
                    'source_raw_line'  => $row->raw_content,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                // Collect card data for batch insert at the end
                $cardsToInsert[] = [
                    'note_id'               => $noteId,
                    'user_id'               => $user->id,
                    'deck_id'               => $importJob->deck_id,
                    'state'                 => 'new',
                    'current_step'          => 0,
                    'learning_steps_json'   => json_encode([1, 10]),
                    'relearning_steps_json' => json_encode([10]),
                    'due_at'                => null,
                    'last_reviewed_at'      => null,
                    'stability'             => 1.0,
                    'difficulty'            => 5.0,
                    'elapsed_days'          => 0,
                    'scheduled_days'        => 0,
                    'reps'                  => 0,
                    'lapses'                => 0,
                    'last_rating'           => null,
                    'is_suspended'          => false,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];

                $importedRowIds[] = $row->id;
                $insertedRows++;

                // FIX 3: Flush cards in chunks of 500 to keep memory bounded
                if (count($cardsToInsert) >= 500) {
                    DB::table('cards')->insert($cardsToInsert);
                    $cardsToInsert = [];
                }
            }

            // Flush remaining cards
            if ($cardsToInsert !== []) {
                DB::table('cards')->insert($cardsToInsert);
            }

            // FIX 4: Batch update row statuses — replaces N individual $row->save() calls
            foreach (array_chunk($importedRowIds, 1000) as $chunk) {
                DB::table('import_job_rows')
                    ->whereIn('id', $chunk)
                    ->update(['status' => 'imported', 'error_message' => null]);
            }
            foreach (array_chunk($skippedRowIds, 1000) as $chunk) {
                DB::table('import_job_rows')
                    ->whereIn('id', $chunk)
                    ->update([
                        'status'        => 'skipped',
                        'error_message' => 'Duplicate row in current user/deck scope.',
                    ]);
            }

            $invalidCount = $importJob->rows->where('status', 'invalid')->count();

            $importJob->forceFill([
                'status'        => 'imported',
                'success_rows'  => $insertedRows,
                'failed_rows'   => $invalidCount + $skippedRows,
                'finished_at'   => now(),
                'error_summary' => ($insertedRows === $importJob->total_rows)
                    ? null
                    : sprintf(
                        'Imported: %d, skipped: %d, invalid: %d',
                        $insertedRows,
                        $skippedRows,
                        $invalidCount
                    ),
            ])->save();

            return [
                'import_job_id' => $importJob->id,
                'inserted_rows' => $insertedRows,
                'skipped_rows'  => $skippedRows,
                'errors'        => $errors,
                'summary'       => [
                    'total'    => $importJob->total_rows,
                    'imported' => $insertedRows,
                    'skipped'  => $skippedRows,
                    'invalid'  => $invalidCount,
                ],
            ];
        });

        return $result;
    }

    public function listJobs(User $user): array
    {
        return ImportJob::query()
            ->with('deck:id,name')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get()
            ->map(static fn (ImportJob $job): array => [
                'id' => $job->id,
                'deck_id' => $job->deck_id,
                'deck_name' => $job->deck?->name,
                'file_name' => $job->file_name,
                'file_hash' => $job->file_hash,
                'status' => $job->status,
                'total_rows' => $job->total_rows,
                'success_rows' => $job->success_rows,
                'failed_rows' => $job->failed_rows,
                'started_at' => $job->started_at?->toIso8601String(),
                'finished_at' => $job->finished_at?->toIso8601String(),
                'error_summary' => $job->error_summary,
            ])
            ->all();
    }

    public function showJob(User $user, ImportJob $importJob): array
    {
        if ($importJob->user_id !== $user->id) {
            throw new InvalidArgumentException('Import job does not belong to the given user.');
        }

        $importJob->loadMissing('deck:id,name');

        return [
            'id' => $importJob->id,
            'deck_id' => $importJob->deck_id,
            'deck_name' => $importJob->deck?->name,
            'file_name' => $importJob->file_name,
            'file_hash' => $importJob->file_hash,
            'status' => $importJob->status,
            'total_rows' => $importJob->total_rows,
            'success_rows' => $importJob->success_rows,
            'failed_rows' => $importJob->failed_rows,
            'started_at' => $importJob->started_at?->toIso8601String(),
            'finished_at' => $importJob->finished_at?->toIso8601String(),
            'error_summary' => $importJob->error_summary,
        ];
    }

    public function listRows(User $user, ImportJob $importJob): array
    {
        if ($importJob->user_id !== $user->id) {
            throw new InvalidArgumentException('Import job does not belong to the given user.');
        }

        return $importJob->rows()
            ->orderBy('row_number')
            ->get()
            ->map(static fn (ImportJobRow $row): array => [
                'id' => $row->id,
                'row_number' => $row->row_number,
                'raw_content' => $row->raw_content,
                'parsed_front' => $row->parsed_front,
                'parsed_back' => $row->parsed_back,
                'status' => $row->status,
                'error_message' => $row->error_message,
                'warnings' => self::rowWarnings($row),
            ])
            ->all();
    }

    private function mapRowsForUi($rows): array
    {
        return $rows
            ->sortBy('row_number')
            ->values()
            ->map(static fn (ImportJobRow $row): array => [
                'index' => $row->row_number,
                'data' => [
                    'front_text' => $row->parsed_front,
                    'back_text' => $row->parsed_back,
                ],
                'status' => $row->status,
                'errors' => $row->error_message === null ? [] : [[
                    'index' => $row->row_number,
                    'field' => null,
                    'message' => $row->error_message,
                ]],
                'warnings' => array_map(
                    static fn (string $message): array => [
                        'index' => $row->row_number,
                        'field' => null,
                        'message' => $message,
                    ],
                    self::rowWarnings($row)
                ),
            ])
            ->all();
    }

    private function collectErrors($rows): array
    {
        return $rows
            ->filter(static fn (ImportJobRow $row): bool => $row->error_message !== null)
            ->map(static fn (ImportJobRow $row): array => [
                'index' => $row->row_number,
                'field' => null,
                'message' => $row->error_message,
            ])
            ->values()
            ->all();
    }

    private function collectWarnings($rows): array
    {
        return $rows
            ->flatMap(static fn (ImportJobRow $row): array => array_map(
                static fn (string $message): array => [
                    'index' => $row->row_number,
                    'field' => null,
                    'message' => $message,
                ],
                self::rowWarnings($row)
            ))
            ->values()
            ->all();
    }

    private function countWarnings($rows): int
    {
        return $rows
            ->reduce(
                static fn (int $carry, ImportJobRow $row): int => $carry + count(self::rowWarnings($row)),
                0
            );
    }

    private static function rowWarnings(ImportJobRow $row): array
    {
        $warnings = [];

        if ($row->parsed_audio_token !== null) {
            $warnings[] = 'Audio token was detected and ignored during import.';
        }

        if ($row->parsed_tags !== null) {
            $warnings[] = 'Tag metadata was detected and ignored during import.';
        }

        return $warnings;
    }

    private function assertDeckOwnership(User $user, Deck $deck): void
    {
        if ($deck->user_id !== $user->id) {
            throw new InvalidArgumentException('Deck does not belong to the given user.');
        }
    }
}
