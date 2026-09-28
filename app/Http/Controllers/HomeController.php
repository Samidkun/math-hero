<?php

namespace App\Http\Controllers;

use App\Generator\QuestionGenerator;
use App\Models\Classroom;
use App\Models\GameSession;
use App\Models\Level;
use App\Models\Player;
use App\Models\PlayerLevelProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class HomeController extends Controller
{
    public function __construct(
        private readonly QuestionGenerator $generator = new QuestionGenerator(),
    ) {}

    public function index(Request $request): Response
    {
        $playerId = $request->session()->get('player_id');
        $player = null;
        $progressSummary = null;

        if ($playerId) {
            $player = Player::with('progress.level')->find($playerId);
            if ($player) {
                $unlockedCount = $player->progress->where('is_unlocked', true)->count();
                $perfectCount = $player->progress->where('best_score_pct', 100)->count();
                $progressSummary = [
                    'unlocked_levels' => $unlockedCount,
                    'perfect_levels' => $perfectCount,
                    'total_stars' => $perfectCount * 3,
                    'next_level' => min(10, $unlockedCount),
                ];
            }
        }

        return Inertia::render('Home', [
            'progressSummary' => $progressSummary,
        ]);
    }

    public function map(Request $request)
    {
        $playerId = $request->session()->get('player_id');
        if (! $playerId) {
            return redirect()->route('home');
        }

        $player = Player::with('progress.level')->findOrFail($playerId);
        $firstProg = $player->progress->first();
        $grade = $firstProg ? $firstProg->level->grade : 4;

        $levels = Level::where('grade', $grade)
            ->orderBy('level_number')
            ->get()
            ->map(function ($lvl) use ($player) {
                $prog = $player->progress->firstWhere('level_id', $lvl->id);

                return [
                    'id' => $lvl->id,
                    'number' => $lvl->level_number,
                    'title' => $lvl->title,
                    'grade' => $lvl->grade,
                    'is_unlocked' => $prog ? $prog->is_unlocked : false,
                    'best_score_pct' => $prog ? $prog->best_score_pct : 0,
                    'best_time_ms' => $prog ? $prog->best_time_ms : null,
                    'is_perfect' => $prog ? ($prog->best_score_pct === 100) : false,
                ];
            });

        return Inertia::render('Map', [
            'grade' => $grade,
            'levels' => $levels,
            'stats' => [
                'total_stars' => $player->progress->where('best_score_pct', 100)->count() * 3,
                'open_levels' => $player->progress->where('is_unlocked', true)->count(),
            ],
        ]);
    }

    public function play(GameSession $session)
    {
        if ($session->status !== 'active') {
            return redirect()->route('results', $session->id);
        }

        $level = $session->level;
        $questions = [];

        foreach ($session->question_seeds as $idx => $seed) {
            $q = $this->generator->generate($level->topic_code, $seed, 0);
            $questions[] = [
                'index' => $idx,
                'stem' => $q->stem,
                'options' => $q->options,
            ];
        }

        return Inertia::render('Play', [
            'session_id' => $session->id,
            'level' => [
                'id' => $level->id,
                'number' => $level->level_number,
                'title' => $level->title,
                'grade' => $level->grade,
            ],
            'questions' => $questions,
        ]);
    }

    public function results(GameSession $session)
    {
        $level = $session->level;
        $answers = $session->answers()->get();
        $correctCount = $answers->where('is_correct', true)->count();
        $scorePct = $session->score_pct ?? (int) round(($correctCount / 10) * 100);

        $reviews = [];
        foreach ($answers as $ans) {
            if (! $ans->is_correct) {
                $q = $this->generator->generate($ans->topic_code, $ans->question_seed, 0);
                $reviews[] = [
                    'index' => $ans->question_index,
                    'stem' => $q->stem,
                    'chosen_text' => $ans->chosen !== null ? ($q->options[$ans->chosen] ?? null) : 'Waktu habis',
                    'correct_text' => $q->options[$q->answerIndex],
                    'misconception' => $ans->misconception_label,
                ];
            }
        }

        return Inertia::render('Results', [
            'session_id' => $session->id,
            'level' => [
                'id' => $level->id,
                'number' => $level->level_number,
                'title' => $level->title,
                'grade' => $level->grade,
            ],
            'score_pct' => $scorePct,
            'correct_count' => $correctCount,
            'total_time_ms' => $session->total_time_ms ?? $answers->sum('time_taken_ms'),
            'perfect' => ($scorePct === 100),
            'reviews' => $reviews,
        ]);
    }

    public function join()
    {
        return Inertia::render('Join');
    }

    public function classroom(string $code)
    {
        $class = Classroom::with(['players.progress'])->where('code', strtoupper($code))->firstOrFail();

        $students = $class->players->map(function ($p) {
            $highestLevel = $p->progress->where('is_unlocked', true)->max('level_id') ?? 1;
            $perfectCount = $p->progress->where('best_score_pct', 100)->count();

            return [
                'id' => $p->id,
                'nickname' => $p->nickname,
                'current_level' => $highestLevel,
                'perfect_levels' => $perfectCount,
                'total_stars' => $perfectCount * 3,
            ];
        });

        return Inertia::render('Classroom', [
            'classroom' => [
                'code' => $class->code,
                'name' => $class->name,
                'grade' => $class->grade,
                'teacher_name' => $class->teacher_name,
            ],
            'students' => $students,
        ]);
    }

    public function report(string $code, Player $player)
    {
        $classroom = Classroom::where('code', strtoupper($code))->firstOrFail();
        $answers = $player->sessions()->with('answers')->get()->pluck('answers')->flatten();

        $misconceptions = [];
        foreach ($answers as $ans) {
            if ($ans->misconception_label && ! $ans->is_correct) {
                $misconceptions[$ans->topic_code][$ans->misconception_label] =
                    ($misconceptions[$ans->topic_code][$ans->misconception_label] ?? 0) + 1;
            }
        }

        return Inertia::render('Report', [
            'classroom' => [
                'code' => $classroom->code,
                'name' => $classroom->name,
            ],
            'player' => [
                'id' => $player->id,
                'nickname' => $player->nickname,
            ],
            'misconceptions' => $misconceptions,
        ]);
    }
}
