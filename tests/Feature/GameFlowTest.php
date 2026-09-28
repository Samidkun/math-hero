<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Classroom;
use App\Models\GameSession;
use App\Models\Level;
use App\Models\Player;
use App\Models\PlayerLevelProgress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\LevelSeeder::class);
    }

    public function test_player_can_start_solo_and_level_1_is_unlocked(): void
    {
        // 1. Join/Create Solo Player
        $resp = $this->postJson('/api/players', [
            'nickname' => 'Budi',
            'grade' => 4,
        ]);

        $resp->assertCreated();
        $playerId = $resp->json('player.id');
        $this->assertNotNull($playerId);

        // Player progress Level 1 should be unlocked
        $progress = PlayerLevelProgress::where('player_id', $playerId)->get();
        $this->assertCount(10, $progress);
        $lvl1 = $progress->firstWhere('level_id', Level::where('grade', 4)->where('level_number', 1)->first()->id);
        $this->assertTrue($lvl1->is_unlocked);

        // 2. Start Game Session on Level 1
        $sessionResp = $this->postJson('/api/sessions', [
            'player_id' => $playerId,
            'level_id' => $lvl1->level_id,
        ]);

        $sessionResp->assertCreated();
        $sessionId = $sessionResp->json('session_id');
        $questions = $sessionResp->json('questions');
        $this->assertCount(10, $questions);
        $this->assertArrayNotHasKey('answerIndex', $questions[0], 'Client must not receive answerIndex (server-authoritative)');

        // 3. Submit Answers
        $firstQ = $questions[0];
        $ansResp = $this->postJson("/api/sessions/{$sessionId}/answer", [
            'question_index' => 0,
            'chosen' => 0,
            'time_taken_ms' => 3500,
        ]);

        $ansResp->assertOk();
        $this->assertArrayHasKey('is_correct', $ansResp->json());

        // 4. Finish Session
        $finishResp = $this->postJson("/api/sessions/{$sessionId}/finish");
        $finishResp->assertOk();
        $this->assertArrayHasKey('score_pct', $finishResp->json());
        $this->assertArrayHasKey('reviews', $finishResp->json());
    }

    public function test_100_percent_score_unlocks_next_level(): void
    {
        $player = Player::create(['nickname' => 'Siti']);
        $lvl1 = Level::where('grade', 4)->where('level_number', 1)->first();
        $lvl2 = Level::where('grade', 4)->where('level_number', 2)->first();

        PlayerLevelProgress::create([
            'player_id' => $player->id,
            'level_id' => $lvl1->id,
            'is_unlocked' => true,
        ]);
        PlayerLevelProgress::create([
            'player_id' => $player->id,
            'level_id' => $lvl2->id,
            'is_unlocked' => false,
        ]);

        // Start session
        $session = GameSession::create([
            'player_id' => $player->id,
            'level_id' => $lvl1->id,
            'status' => 'active',
            'question_seeds' => range(1, 10),
        ]);

        // Mock 10 correct answers
        for ($i = 0; $i < 10; $i++) {
            Answer::create([
                'game_session_id' => $session->id,
                'question_index' => $i,
                'question_seed' => $i + 1,
                'topic_code' => $lvl1->topic_code,
                'chosen' => 0,
                'is_correct' => true,
                'time_taken_ms' => 2000,
            ]);
        }

        $resp = $this->postJson("/api/sessions/{$session->id}/finish");
        $resp->assertOk();
        $this->assertSame(100, $resp->json('score_pct'));

        // Level 2 should now be unlocked!
        $lvl2Prog = PlayerLevelProgress::where('player_id', $player->id)->where('level_id', $lvl2->id)->first();
        $this->assertTrue($lvl2Prog->fresh()->is_unlocked);
    }

    public function test_adaptive_injection_inserts_thirty_percent_weak_topics(): void
    {
        $player = Player::create(['nickname' => 'Doni']);
        $lvl1 = Level::where('grade', 4)->where('level_number', 1)->first();
        $lvl2 = Level::where('grade', 4)->where('level_number', 2)->first();

        PlayerLevelProgress::create([
            'player_id' => $player->id,
            'level_id' => $lvl1->id,
            'is_unlocked' => true,
        ]);
        PlayerLevelProgress::create([
            'player_id' => $player->id,
            'level_id' => $lvl2->id,
            'is_unlocked' => true,
        ]);

        // Prior session on level 1 where player made errors on 'grade-4/place-value-10k'
        $oldSession = GameSession::create([
            'player_id' => $player->id,
            'level_id' => $lvl1->id,
            'status' => 'completed',
            'question_seeds' => range(1, 10),
            'question_topics' => array_fill(0, 10, 'grade-4/place-value-10k'),
        ]);

        // 3 wrong answers with misconception labels on 'grade-4/place-value-10k'
        for ($i = 0; $i < 3; $i++) {
            Answer::create([
                'game_session_id' => $oldSession->id,
                'question_index' => $i,
                'question_seed' => 100 + $i,
                'topic_code' => 'grade-4/place-value-10k',
                'chosen' => 1,
                'is_correct' => false,
                'misconception_label' => 'place-skip',
                'time_taken_ms' => 4000,
            ]);
        }

        // Now start a new session on level 2
        $resp = $this->postJson('/api/sessions', [
            'player_id' => $player->id,
            'level_id' => $lvl2->id,
        ]);

        $resp->assertCreated();
        $sessionId = $resp->json('session_id');
        $session = GameSession::findOrFail($sessionId);

        $topics = $session->question_topics;
        $this->assertCount(10, $topics);

        // Indices 0..6 must be core topic of level 2
        for ($i = 0; $i < 7; $i++) {
            $this->assertSame($lvl2->topic_code, $topics[$i]);
        }

        // Indices 7..9 must be adaptively injected from 'grade-4/place-value-10k'
        for ($i = 7; $i < 10; $i++) {
            $this->assertSame('grade-4/place-value-10k', $topics[$i]);
        }
    }
}
