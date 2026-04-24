<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 32);
            $table->string('rating', 16);
            $table->text('typed_answer')->nullable();
            $table->string('judged_result', 32)->nullable();
            $table->string('previous_state', 32)->nullable();
            $table->string('next_state', 32);
            $table->unsignedInteger('previous_step')->nullable();
            $table->unsignedInteger('next_step')->nullable();
            $table->timestamp('previous_due_at')->nullable();
            $table->timestamp('next_due_at')->nullable();
            $table->decimal('previous_stability', 8, 4)->nullable();
            $table->decimal('next_stability', 8, 4)->nullable();
            $table->decimal('previous_difficulty', 8, 4)->nullable();
            $table->decimal('next_difficulty', 8, 4)->nullable();
            $table->timestamp('reviewed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_logs');
    }
};
