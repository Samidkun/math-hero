<?php

namespace App\Generator\ItemModels;

use App\Generator\GeneratedQuestion;
use App\Generator\ItemModel;
use App\Generator\MisconceptionLibrary;
use App\Generator\SeededRng;

/**
 * Nilai tempat kelas 4: "Berapa nilai tempat angka 5 pada 8.543?"
 *
 * Misconceptions:
 *   place-value   — one place too small (50 instead of 500)
 *   digit-swap    — swaps the first two digits (58...)
 *   magnitude-flip — reads the neighbouring digit's value instead
 */
final class PlaceValueItemModel implements ItemModel
{
    use FormatsNumbers;

    public function generate(SeededRng $rng, int $difficulty, MisconceptionLibrary $misconceptions, array $misconceptionLabels): GeneratedQuestion
    {
        $digits = 4 + $difficulty; // 4..6 digit numbers
        $max = (int) (10 ** $digits) - 1;

        // Build a number where the target digit appears exactly once (a fair question).
        $target = $rng->int(1, 9);
        $other = $rng->int(1, 9);
        $value = $target * (10 ** $rng->int(0, $digits - 1));
        $value += $other * (10 ** $rng->int(0, $digits - 1));
        $number = $value + $rng->int(1, 9);

        $answer = $target * (int) (10 ** $rng->int(1, $digits - 1));
        $place = (int) log10($answer);

        $stem = "Berapa nilai tempat angka {$target} pada {$this->num($number)}?";
        $ctx = ['other' => (int) ($number / (10 ** $place)) % 10 * (10 ** max(0, $place - 1)), 'number' => $number, 'target' => $target];

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
