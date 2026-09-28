<?php

namespace App\Generator\ItemModels;

use App\Generator\GeneratedQuestion;
use App\Generator\ItemModel;
use App\Generator\MisconceptionLibrary;
use App\Generator\SeededRng;

/**
 * Rasio kelas 6: "Perbandingan air dan sirup adalah 3 : 1. Ada 12 gelas air.
 * Berapa gelas sirup diperlukan?"
 *
 * answer = units * b / a   (a parts water, b parts syrup, units glasses of water)
 *
 * Misconceptions (docs/curriculum.md):
 *   ratio-reversal   — flips the ratio → units * a / b
 *   ratio-additive   — subtracts instead of dividing → units - a + b
 *   ratio-multiplies — multiplies straight through → units * a
 *
 * Non-negotiable: all four options must be distinct positive integers.
 */
final class RatioItemModel implements ItemModel
{
    use FormatsNumbers;

    public function generate(SeededRng $rng, int $difficulty, MisconceptionLibrary $misconceptions, array $misconceptionLabels): GeneratedQuestion
    {
        $cap = [12, 40, 200][$difficulty] ?? 40;

        for ($attempt = 0; $attempt < 40; $attempt++) {
            $a = $rng->int(2, 5);
            $b = $rng->int(1, 4);
            // units must be a multiple of a so the answer is a whole glass.
            $units = $a * $rng->int(2, max(2, intdiv($cap, $a)));
            $answer = intdiv($units * $b, $a);

            if ($answer < 1) {
                continue;
            }

            $ctx = ['a' => $a, 'b' => $b, 'units' => $units];

            $options = [];
            $labels = [];
            foreach ($misconceptionLabels as $label) {
                $wrong = $misconceptions->apply($label, $answer, $ctx);
                if ($wrong === $answer || $wrong < 1 || \in_array($wrong, $options, strict: true)) {
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

            $stem = "Perbandingan air dan sirup adalah {$a} : {$b}. ";
            $stem .= "Ada {$this->num($units)} gelas air. Berapa gelas sirup diperlukan?";

            $all = array_merge([$answer], $options);
            if (\count(array_unique($all)) !== 4) {
                continue; // still collided; try another draw
            }
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

        throw new \RuntimeException('RatioItemModel: could not draw a clean question');
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
}
