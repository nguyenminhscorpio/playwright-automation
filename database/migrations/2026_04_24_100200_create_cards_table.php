<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();
            $table->string('state', 32)->default('new');
            $table->unsignedInteger('current_step')->default(0);
            $table->json('learning_steps_json');
            $table->json('relearning_steps_json');
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->decimal('stability', 8, 4)->default(1.0000);
            $table->decimal('difficulty', 8, 4)->default(5.0000);
            $table->unsignedInteger('elapsed_days')->default(0);
            $table->unsignedInteger('scheduled_days')->default(0);
            $table->unsignedInteger('reps')->default(0);
            $table->unsignedInteger('lapses')->default(0);
            $table->string('last_rating', 16)->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
