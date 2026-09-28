<?php

namespace App\Generator\ItemModels;

use App\Generator\GeneratedQuestion;
use App\Generator\ItemModel;
use App\Generator\MisconceptionLibrary;
use App\Generator\SeededRng;

/**
 * Perkalian 2 angka: "24 × 13 = ?"
 *
 * Misconceptions:
 *   digit-multiply   — only the units digits → 4 × 3
 *   repeated-add-error — a + b − 1 (mangled repeated addition)
 *   times-one        — thinks ×1 adds one → a + 1 (used when b == 1)
 */
final class MultiplicationItemModel implements ItemModel
{
    use FormatsNumbers;

    public function generate(SeededRng $rng, int $difficulty, MisconceptionLibrary $misconceptions, array $misconceptionLabels): GeneratedQuestion
    {
        $max = [12, 49, 99][$difficulty] ?? 49;

        $a = $rng->int(11, $max);
        $b = $rng->int(2, $max);
        $answer = $a * $b;

        $ctx = ['a' => $a, 'b' => $b];

        $stem = "Berapa hasil {$this->num($a)} × {$this->num($b)}?";

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
