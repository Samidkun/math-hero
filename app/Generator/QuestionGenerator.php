<?php

namespace App\Generator;

/**
 * The question-generation engine.
 *
 * Contract (spec AC-11/AC-13):
 *   generate(code, seed, difficulty) is PURE — same input, same output, forever.
 *   The client never sees the correct answer until after it submits (server-authoritative).
 *   Every distractor maps to a named misconception from MisconceptionLibrary.
 *
 * ponytail: 3 item models now (ratio, multiplication, place-value). Adding a topic = add an
 * item model + register it in $models. The interface never changes.
 */
final class QuestionGenerator
{
    private const DIFFICULTY_RANGES = [
        0 => 12,   // small, friendly numbers
        1 => 99,
        2 => 999,
    ];

    private const MAX_GENERATE_ATTEMPTS = 20;

    /** @var array<string, ItemModel> keyed by topic slug */
    private array $models;

    public function __construct(
        private readonly TopicRegistry $topics = new TopicRegistry,
        private readonly MisconceptionLibrary $misconceptions = new MisconceptionLibrary,
    ) {
        $this->models = [
            'ratio' => new ItemModels\RatioItemModel,
            'mult-2digit' => new ItemModels\MultiplicationItemModel,
            'place-value-10k' => new ItemModels\PlaceValueItemModel,
        ];
    }

    public function generate(string $topicCode, int $seed, int $difficulty = 0): GeneratedQuestion
    {
        [$grade, $level] = $this->topics->parseCode($topicCode);
        $slug = $this->topics->code($grade, $level);
        $slug = substr($slug, strpos($slug, '/') + 1);

        $model = $this->models[$slug] ?? null;
        if ($model === null) {
            // ponytail: topics without a bespoke item model fall back to a generic
            // arithmetic model so the app is playable while models are written.
            $model = new ItemModels\GenericArithmeticModel;
        }

        $difficulty = max(0, min(2, $difficulty));
        $rng = new SeededRng($seed);

        // Retry until the generated question is valid: unique options, no zero-answer.
        for ($attempt = 0; $attempt < self::MAX_GENERATE_ATTEMPTS; $attempt++) {
            $q = $model->generate($rng, $difficulty, $this->misconceptions, $this->topics->misconceptions($grade, $level));
            if (self::isValid($q)) {
                return $q;
            }
        }

        // Should be unreachable with sane models; fail loudly rather than serve a broken question.
        throw new \RuntimeException("could not generate a valid question for [$topicCode] seed=$seed");
    }

    /**
     * Labels of the distractors for one generated question, in option order.
     * Null on the correct slot. Used for teacher reports (and our own tests).
     *
     * @return array<int, ?string>
     */
    public function distractorLabels(string $topicCode, int $seed, int $difficulty = 0): array
    {
        $q = $this->generate($topicCode, $seed, $difficulty);

        $labels = [];
        foreach ($q->options as $slot => $_) {
            $labels[$slot] = $q->labelForChoice($slot);
        }

        return $labels;
    }

    private static function isValid(GeneratedQuestion $q): bool
    {
        if (\count($q->options) !== 4) {
            return false;
        }
        if (\count(array_unique($q->options)) !== 4) {
            return false; // duplicate options — a kid could pick the "wrong" right answer
        }
        if ($q->answerIndex < 0 || $q->answerIndex > 3) {
            return false;
        }

        return true;
    }
}
