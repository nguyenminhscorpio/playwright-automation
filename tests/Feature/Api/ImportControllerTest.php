<?php

namespace Tests\Feature\Api;

use App\Models\Card;
use App\Models\ImportJob;
use App\Models\Note;
use Database\Seeders\DevStudySessionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DevStudySessionSeeder::class);
    }

    public function test_preview_txt_import_creates_import_job_and_rows(): void
    {
        $samplePath = base_path('app/Spec/Phase/Study-Session-Phase-1/Sample-File-Import/1000 Cụm từ Tiếng Anh.txt');
        $file = UploadedFile::fake()->createWithContent('1000-cu-tieng-anh.txt', file_get_contents($samplePath));

        $response = $this->postJson('/api/imports/txt/preview', [
            'user_id' => 1,
            'deck_id' => 1,
            'file' => $file,
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'import_job_id',
                'file_name',
                'file_hash',
                'detected_format',
                'total_lines',
                'data_lines',
                'valid_rows',
                'invalid_rows',
                'preview_rows',
            ]);

        $importJob = ImportJob::query()->findOrFail($response->json('import_job_id'));

        $this->assertSame('previewed', $importJob->status);
        $this->assertGreaterThan(0, $importJob->rows()->count());
        $this->assertGreaterThan(0, $response->json('valid_rows'));
    }

    public function test_confirm_txt_import_creates_notes_and_cards_and_marks_duplicates_as_skipped(): void
    {
        $samplePath = base_path('app/Spec/Phase/Study-Session-Phase-1/Sample-File-Import/1000 Cụm từ Tiếng Anh.txt');
        $content = file_get_contents($samplePath);
        $file = UploadedFile::fake()->createWithContent('1000-cu-tieng-anh.txt', $content);

        $previewResponse = $this->postJson('/api/imports/txt/preview', [
            'user_id' => 1,
            'deck_id' => 1,
            'file' => $file,
        ]);

        $previewResponse->assertOk();

        $importJobId = $previewResponse->json('import_job_id');

        $confirmResponse = $this->postJson('/api/imports/txt/confirm', [
            'user_id' => 1,
            'import_job_id' => $importJobId,
        ]);

        $confirmResponse
            ->assertOk()
            ->assertJsonStructure([
                'import_job_id',
                'inserted_rows',
                'skipped_rows',
                'errors',
            ]);

        $insertedRows = $confirmResponse->json('inserted_rows');

        $this->assertGreaterThan(0, $insertedRows);
        $this->assertGreaterThan(4, Note::query()->where('user_id', 1)->count());
        $this->assertGreaterThan(4, Card::query()->where('user_id', 1)->count());

        $repeatFile = UploadedFile::fake()->createWithContent('1000-cu-tieng-anh-repeat.txt', $content);
        $repeatPreviewResponse = $this->postJson('/api/imports/txt/preview', [
            'user_id' => 1,
            'deck_id' => 1,
            'file' => $repeatFile,
        ]);

        $repeatPreviewResponse->assertOk();

        $repeatConfirmResponse = $this->postJson('/api/imports/txt/confirm', [
            'user_id' => 1,
            'import_job_id' => $repeatPreviewResponse->json('import_job_id'),
        ]);

        $repeatConfirmResponse->assertOk();
        $this->assertGreaterThan(0, $repeatConfirmResponse->json('skipped_rows'));
    }

    public function test_confirm_txt_import_can_swap_front_and_back(): void
    {
        $file = UploadedFile::fake()->createWithContent('swap-test.txt', "Front A\tBack A\n");

        $previewResponse = $this->postJson('/api/imports/txt/preview', [
            'user_id' => 1,
            'deck_id' => 1,
            'file' => $file,
        ]);

        $previewResponse->assertOk();

        $confirmResponse = $this->postJson('/api/imports/txt/confirm', [
            'user_id' => 1,
            'import_job_id' => $previewResponse->json('import_job_id'),
            'swap_front_back' => true,
        ]);

        $confirmResponse->assertOk();

        $note = Note::query()
            ->where('user_id', 1)
            ->where('front_text', 'Back A')
            ->where('back_text', 'Front A')
            ->first();

        $this->assertNotNull($note);
    }
}
