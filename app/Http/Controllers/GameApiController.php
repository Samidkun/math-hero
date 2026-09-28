<?php

namespace App\Http\Controllers;

use App\Generator\QuestionGenerator;
use App\Generator\TopicRegistry;
use App\Models\Answer;
use App\Models\Classroom;
use App\Models\GameSession;
use App\Models\Level;
use App\Models\Player;
use App\Models\PlayerLevelProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameApiController extends Controller
{
    public function __construct(
        private readonly QuestionGenerator $generator = new QuestionGenerator(),
        private readonly TopicRegistry $topics = new TopicRegistry(),
    ) {}

    /**
     * Create or retrieve a player (Solo or Class).
     */
    public function storePlayer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nickname' => 'required|string|max:32',
            'grade' => 'required|integer|in:4,5,6',
            'class_code' => 'nullable|string|size:6',
            'pin' => 'nullable|string|digits:4',
        ]);

        $classId = null;
        if (! empty($validated['class_code'])) {
            $class = Classroom::where('code', strtoupper($validated['class_code']))->first();
            if (! $class) {
                return response()->json(['message' => 'Kode kelas tidak ditemukan.'], 404);
            }
            $classId = $class->id;
        }

        // Check unique nickname in class/solo
        $query = Player::where('nickname', $validated['nickname'])->where('class_id', $classId);
        $player = $query->first();

        if ($player) {
            // Player already exists: verify PIN if protected
            if ($player->pin_hash && ! empty($validated['pin'])) {
                if (! password_verify($validated['pin'], $player->pin_hash)) {
                    return response()->json(['message' => 'PIN salah.'], 403);
                }
            }
        } else {
            $player = Player::create([
                'nickname' => $validated['nickname'],
                'class_id' => $classId,
                'pin_hash' => ! empty($validated['pin']) ? password_hash($validated['pin'], PASSWORD_BCRYPT) : null,
            ]);

            // Bootstrap player level progress for chosen grade
            $levels = Level::where('grade', $validated['grade'])->orderBy('level_number')->get();
            foreach ($levels as $lvl) {
                PlayerLevelProgress::create([
                    'player_id' => $player->id,
                    'level_id' => $lvl->id,
                    'is_unlocked' => ($lvl->level_number === 1),
                ]);
            }
        }

        session(['player_id' => $player->id]);

        return response()->json([
            'player' => [
                'id' => $player->id,
                'nickname' => $player->nickname,
                'class_id' => $player->class_id,
                'total_stars' => $player->total_stars,
            ],
        ], 201);
    }

    /**
     * Start a 10-question game session.
     */
    public function startSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
            'level_id' => 'required|exists:levels,id',
        ]);

        $progress = PlayerLevelProgress::where('player_id', $validated['player_id'])
            ->where('level_id', $validated['level_id'])
            ->first();

        if (! $progress || ! $progress->is_unlocked) {
            return response()->json(['message' => 'Level masih terkunci.'], 403);
        }

        $level = Level::findOrFail($validated['level_id']);

        // Generate 10 random seeds for deterministic question generation
        $seeds = [];
        for ($i = 0; $i < 10; $i++) {
            $seeds[] = random_int(1000, 999999);
        }

        $session = GameSession::create([
            'player_id' => $validated['player_id'],
            'level_id' => $validated['level_id'],
            'status' => 'active',
            'question_seeds' => $seeds,
        ]);

        // Generate questions (WITHOUT answerIndex for server authority)
        $questions = [];
        foreach ($seeds as $idx => $seed) {
            $q = $this->generator->generate($level->topic_code, $seed, 0);
            $questions[] = [
                'index' => $idx,
                'stem' => $q->stem,
                'options' => $q->options,
                // answerIndex is deliberately omitted!
            ];
        }

        return response()->json([
            'session_id' => $session->id,
            'level' => [
                'number' => $level->level_number,
                'title' => $level->title,
                'grade' => $level->grade,
            ],
            'questions' => $questions,
        ], 201);
    }

    /**
     * Submit an answer to a single question.
     */
    public function answerQuestion(Request $request, GameSession $session): JsonResponse
    {
        $validated = $request->validate([
            'question_index' => 'required|integer|between:0,9',
            'chosen' => 'nullable|integer|between:0,3',
            'time_taken_ms' => 'nullable|integer',
        ]);

        if ($session->status !== 'active') {
            return response()->json(['message' => 'Sesi permainan sudah selesai.'], 400);
        }

        $idx = $validated['question_index'];
        $seeds = $session->question_seeds;
        $seed = $seeds[$idx] ?? null;

        if ($seed === null) {
            return response()->json(['message' => 'Soal tidak valid.'], 400);
        }

        $level = $session->level;
        $q = $this->generator->generate($level->topic_code, $seed, 0);

        $isCorrect = ($validated['chosen'] !== null && $validated['chosen'] === $q->answerIndex);
        $misconceptionLabel = ($validated['chosen'] !== null) ? $q->labelForChoice($validated['chosen']) : 'timeout';

        $answer = Answer::updateOrCreate(
            [
                'game_session_id' => $session->id,
                'question_index' => $idx,
            ],
            [
                'question_seed' => $seed,
                'topic_code' => $level->topic_code,
                'chosen' => $validated['chosen'],
                'is_correct' => $isCorrect,
                'misconception_label' => $isCorrect ? null : $misconceptionLabel,
                'time_taken_ms' => $validated['time_taken_ms'],
            ]
        );

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_index' => $q->answerIndex,
            'misconception_label' => $answer->misconception_label,
        ]);
    }

    /**
     * Finish game session and calculate score.
     */
    public function finishSession(GameSession $session): JsonResponse
    {
        if ($session->status === 'completed') {
            return response()->json(['message' => 'Sesi sudah ditutup.'], 400);
        }

        $answers = $session->answers()->get();
        $correctCount = $answers->where('is_correct', true)->count();
        $scorePct = (int) round(($correctCount / 10) * 100);
        $totalTime = $answers->sum('time_taken_ms');

        $session->update([
            'status' => 'completed',
            'finished_at' => now(),
            'score_pct' => $scorePct,
            'total_time_ms' => $totalTime,
        ]);

        // Update progress
        $progress = PlayerLevelProgress::where('player_id', $session->player_id)
            ->where('level_id', $session->level_id)
            ->first();

        if ($progress) {
            $progress->attempts += 1;
            if ($scorePct > $progress->best_score_pct) {
                $progress->best_score_pct = $scorePct;
            }
            if ($scorePct === 100) {
                if ($progress->best_time_ms === null || $totalTime < $progress->best_time_ms) {
                    $progress->best_time_ms = $totalTime;
                }

                // Unlock next level in this grade
                $currentLvl = $session->level;
                $nextLvl = Level::where('grade', $currentLvl->grade)
                    ->where('level_number', $currentLvl->level_number + 1)
                    ->first();

                if ($nextLvl) {
                    PlayerLevelProgress::where('player_id', $session->player_id)
                        ->where('level_id', $nextLvl->id)
                        ->update(['is_unlocked' => true]);
                }
            }
            $progress->save();
        }

        // Build reviews for wrong answers
        $reviews = [];
        foreach ($answers as $ans) {
            if (! $ans->is_correct) {
                $q = $this->generator->generate($ans->topic_code, $ans->question_seed, 0);
                $reviews[] = [
                    'question_index' => $ans->question_index,
                    'stem' => $q->stem,
                    'chosen_text' => $ans->chosen !== null ? ($q->options[$ans->chosen] ?? null) : 'Tidak menjawab',
                    'correct_text' => $q->options[$q->answerIndex],
                    'misconception' => $ans->misconception_label,
                ];
            }
        }

        return response()->json([
            'score_pct' => $scorePct,
            'correct_count' => $correctCount,
            'total_time_ms' => $totalTime,
            'perfect' => ($scorePct === 100),
            'reviews' => $reviews,
        ]);
    }

    /**
     * Abandon game session.
     */
    public function abandonSession(GameSession $session): JsonResponse
    {
        $session->update([
            'status' => 'abandoned',
            'finished_at' => now(),
            'abandon_reason' => 'user_left',
        ]);

        return response()->json(['message' => 'Sesi dibatalkan. Progres tidak disimpan.']);
    }
}
