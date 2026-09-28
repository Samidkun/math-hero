<?php

namespace App\Generator\ItemModels;

use App\Generator\GeneratedQuestion;
use App\Generator\ItemModel;
use App\Generator\MisconceptionLibrary;
use App\Generator\SeededRng;

/**
 * Luas kelas 4: "Sebuah persegi panjang berukuran 8 cm × 5 cm. Berapa luasnya?"
 *
 * answer = l * w   (a square is just the l == w case)
 *
 * Misconceptions (docs/curriculum.md):
 *   perimeter-confusion — finds the perimeter when asked for area → l * w * 2
 *   add-all-sides       — adds the sides instead of multiplying → l + w
 *   unit-forget         — forgets the square; its library form IS the answer, so it
 *                         always degrades to a near-miss fallback (same trick the
 *                         multiplication model uses for times-one)
 *
 * Non-negotiable: all four options must be distinct positive integers.
 */
final class AreaItemModel implements ItemModel
{
    use FormatsNumbers;

    public function generate(SeededRng $rng, int $difficulty, MisconceptionLibrary $misconceptions, array $misconceptionLabels): GeneratedQuestion
    {
        $max = [9, 15, 25][$difficulty] ?? 15;

        // ponytail: sides start at 3 — below that, l*w and l+w collapse onto each other
        // ((2,2) is the only integer collision) and the distractors stop being distinct.
        $l = $rng->int(3, $max);
        $w = $rng->chance(50) ? $l : $rng->int(3, $max);

        // (l-2)(w-2) == 4 → (3,6), (4,4), (6,3): area and perimeter coincide, so the
        // perimeter-confusion distractor would equal the answer and get swapped for a
        // fallback that is not the perimeter. Nudge a side to keep every label honest.
        if ($l * $w === 2 * ($l + $w)) {
            $l++;
        }

        $answer = $l * $w;

        $ctx = ['l' => $l, 'w' => $w];

        if ($l === $w) {
            $stem = "Sebuah persegi memiliki sisi {$this->num($l)} cm. Berapa luasnya?";
        } else {
            $stem = "Sebuah persegi panjang berukuran {$this->num($l)} cm × {$this->num($w)} cm. Berapa luasnya?";
        }

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
