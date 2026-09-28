<?php

namespace App\Generator;

/**
 * One item model: knows how to pose a question for one topic family and how to
 * derive each named misconception's wrong answer from the generated context.
 *
 * ponytail: this is the seam for adding topics. Nothing else in the codebase changes.
 *
 * @phpstan-type Context array<string, int>
 */
interface ItemModel
{
    /**
     * @param  Context  $misconceptionLabels  labels valid for this topic (from config)
     */
    public function generate(
        SeededRng $rng,
        int $difficulty,
        MisconceptionLibrary $misconceptions,
        array $misconceptionLabels,
    ): GeneratedQuestion;
}
