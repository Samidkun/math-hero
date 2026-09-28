<?php

namespace App\Generator\ItemModels;

use App\Generator\GeneratedQuestion;
use App\Generator\ItemModel;
use App\Generator\MisconceptionLibrary;
use App\Generator\SeededRng;

/**
 * Fallback for topics that don't have a bespoke item model yet.
 * Uses the topic's own misconception labels to perturb the answer.
 * ponytail: replace by registering a real model in QuestionGenerator::$models.
 */
final class GenericArithmeticModel implements ItemModel
{
    use FormatsNumbers;

    public function generate(SeededRng $rng, int $difficulty, MisconceptionLibrary $misconceptions, array $misconceptionLabels): GeneratedQuestion
    {
        $max = [99, 999, 9999][$difficulty] ?? 999;

        $a = $rng->int(10, $max);
        $b = $rng->int(2, min($max, 20));
        $answer = $a + $b;

        $ctx = ['a' => $a, 'b' => $b, 'whole' => $a, 'p' => $b, 'units' => $a, 'n' => $b];

        $stem = "Berapa hasil {$this->num($a)} + {$this->num($b)}?";

        $options = [];
        $labels = [];
        foreach ($misconceptionLabels as $label) {
            $wrong = $misconceptions->apply($label, $answer, $ctx);
            if ($wrong === $answer || \in_array($wrong, $options, strict: true)) {
                $wrong = $this->fallback($answer, $options, $rng);
            }
            $options[] = $wrong;
            $labels[$wrong] = $label;
        }
        while (\count($options) < 3) {
            $wrong = $this->fallback($answer, $options, $rng);
            $options[] = $wrong;
            $labels[$wrong] = $misconceptionLabels[0] ?? 'unknown';
        }

        return $this->assemble($rng, $stem, $answer, $options, $labels);
    }

    /** A fresh wrong value that is not the answer and not already used. */
    private function fallback(int $answer, array $existing, SeededRng $rng): int
    {
        $v = max(1, $answer + $rng->int(1, 5));
        $guard = 0;
        while (($v === $answer || \in_array($v, $existing, strict: true)) && $guard++ < 20) {
            $v = max(1, $v + 1);
        }

        return $v;
    }

    private function assemble(SeededRng $rng, string $stem, int $answer, array $options, array $labels): GeneratedQuestion
    {
        $all = array_merge([$answer], $options);
        $shuffled = $rng->shuffle($all);
        $answerIndex = array_search($answer, $shuffled, strict: true);

        $labelBySlot = [];
        foreach ($shuffled as $slot => $v) {
            $labelBySlot[$slot] = $v === $answer ? null : ($labels[$v] ?? null);
        }

        return new GeneratedQuestion(
            stem: $stem,
            options: array_map(fn ($v) => $this->num($v), $shuffled),
            answerIndex: $answerIndex,
            labelBySlot: $labelBySlot,
        );
    }
}
