<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_job_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('import_job_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->longText('raw_content')->nullable();
            $table->text('parsed_front')->nullable();
            $table->text('parsed_back')->nullable();
            $table->text('parsed_audio_token')->nullable();
            $table->text('parsed_tags')->nullable();
            $table->string('status', 32)->default('previewed');
            $table->text('error_message')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_job_rows');
    }
};
