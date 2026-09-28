<?php

namespace App\Generator;

/**
 * One generated multiple-choice question.
 *
 * @param  string[]  $options  exactly 4 strings; correct one at $answerIndex
 * @param  array<int, ?string>  $labelBySlot  misconception label per option slot; null on the correct slot
 */
final class GeneratedQuestion
{
    public function __construct(
        public readonly string $stem,
        public readonly array $options,
        public readonly int $answerIndex,
        public readonly array $labelBySlot = [],
    ) {}

    /** Label of the distractor the player picked (null if they were correct). */
    public function labelForChoice(int $slot): ?string
    {
        if ($slot === $this->answerIndex || ! isset($this->labelBySlot[$slot])) {
            return null;
        }

        return $this->labelBySlot[$slot];
    }
}
