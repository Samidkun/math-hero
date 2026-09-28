<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->char('code', 6)->unique();
            $table->string('name', 64);
            $table->smallInteger('grade'); // 4, 5, 6
            $table->string('teacher_name', 64);
            $table->timestamps();

            $table->index('code');
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->string('nickname', 32);
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->string('pin_hash', 255)->nullable();
            $table->integer('total_stars')->default(0);
            $table->timestamps();

            // Unique within class, or unique among solo players (when class_id is null)
            $table->unique(['class_id', 'nickname']);
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('grade');
            $table->integer('level_number');
            $table->string('topic_code', 64);
            $table->string('title', 64);
            $table->timestamps();

            $table->unique(['grade', 'level_number']);
            $table->index('topic_code');
        });

        Schema::create('player_level_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->integer('best_score_pct')->default(0);
            $table->integer('best_time_ms')->nullable();
            $table->integer('attempts')->default(0);
            $table->boolean('is_unlocked')->default(false);
            $table->timestamps();

            $table->unique(['player_id', 'level_id']);
            $table->index(['player_id', 'is_unlocked']);
        });

        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->string('status', 16)->default('active'); // active, completed, abandoned
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->integer('score_pct')->nullable();
            $table->integer('total_time_ms')->nullable();
            $table->string('abandon_reason', 64)->nullable();
            $table->json('question_seeds')->nullable();
            $table->timestamps();

            $table->index(['player_id', 'level_id', 'status']);
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->integer('question_index'); // 0..9
            $table->bigInteger('question_seed');
            $table->string('topic_code', 64);
            $table->smallInteger('chosen')->nullable(); // 0..3 or null
            $table->boolean('is_correct');
            $table->string('misconception_label', 64)->nullable();
            $table->integer('time_taken_ms')->nullable();
            $table->timestamp('answered_at')->useCurrent();

            $table->unique(['game_session_id', 'question_index']);
            $table->index(['topic_code', 'misconception_label']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('game_sessions');
        Schema::dropIfExists('player_level_progress');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('players');
        Schema::dropIfExists('classes');
    }
};
