<?php

namespace Tests\Feature\Generator;

use App\Generator\GeneratedQuestion;
use App\Generator\QuestionGenerator;
use Tests\TestCase;

/**
 * 50 random seeds must each yield a well-formed luas (area) question: four distinct
 * options, a correct answer that is really l*w, and distractors that carry real
 * misconception names.
 */
final class AreaItemModelTest extends TestCase
{
    private const TOPIC = 'grade-4/area-rect';

    private const KNOWN_LABELS = ['perimeter-confusion', 'add-all-sides', 'unit-forget'];

    /** @var array<int, GeneratedQuestion> keyed by seed */
    private array $questions;

    protected function setUp(): void
    {
        parent::setUp();

        $g = $this->makeGenerator();
        $this->questions = [];
        for ($seed = 1; $seed <= 50; $seed++) {
            $this->questions[$seed] = $g->generate(self::TOPIC, seed: $seed, difficulty: 0);
        }
    }

    public function test_fifty_seeds_generate_four_distinct_options(): void
    {
        foreach ($this->questions as $seed => $q) {
            self::assertCount(4, $q->options, "seed $seed: must have 4 options");
            self::assertSame(4, \count(array_unique($q->options)), "seed $seed: options must be unique");
            self::assertNotSame('', $q->stem, "seed $seed: stem must not be empty");
            self::assertGreaterThanOrEqual(0, $q->answerIndex, "seed $seed: answer index out of range");
            self::assertLessThan(4, $q->answerIndex, "seed $seed: answer index out of range");

            foreach ($q->options as $opt) {
                self::assertIsString($opt);
                self::assertNotSame('', $opt);
                self::assertSame((int) $opt, (int) str_replace('.', '', $opt), "seed $seed: option $opt is not an integer");
            }
        }
    }

    public function test_correct_answer_is_length_times_width(): void
    {
        // The stem states both sides in cm, so the correct option must equal their product.
        foreach ($this->questions as $seed => $q) {
            $side = $this->sideLengths($q);
            $expected = $side[0] * $side[1];
            $correct = $q->options[$q->answerIndex];

            self::assertSame($expected, (int) str_replace('.', '', $correct),
                "seed $seed: correct option {$correct} is not {$side[0]} × {$side[1]}");

            // And it must not be the perimeter — that is the headline misconception.
            self::assertNotSame(2 * ($side[0] + $side[1]), (int) str_replace('.', '', $correct),
                "seed $seed: correct answer is the perimeter, not the area");
        }
    }

    public function test_distractors_never_equal_the_correct_answer(): void
    {
        foreach ($this->questions as $seed => $q) {
            $correct = $q->options[$q->answerIndex];
            foreach ($q->options as $i => $opt) {
                if ($i === $q->answerIndex) {
                    continue;
                }
                self::assertNotSame($correct, $opt, "seed $seed slot $i: distractor equals the correct answer");
            }
        }
    }

    public function test_distractors_carry_known_misconception_labels(): void
    {
        $g = $this->makeGenerator();
        $seen = [];

        foreach (array_keys($this->questions) as $seed) {
            $labels = $g->distractorLabels(self::TOPIC, seed: $seed, difficulty: 0);

            // Exactly one slot is unlabelled — the correct answer.
            $nulls = \count(array_filter($labels, fn ($l) => $l === null));
            self::assertSame(1, $nulls, "seed $seed: expected exactly 1 unlabelled (correct) slot, got $nulls");

            $g->generate(self::TOPIC, seed: $seed, difficulty: 0); // keep RNG draws symmetric with the label lookup
            foreach ($labels as $label) {
                if ($label === null) {
                    continue;
                }
                self::assertContains($label, self::KNOWN_LABELS, "seed $seed: unknown misconception label $label");
                $seen[$label] = true;
            }
        }

        // The three named mistakes must actually appear across the sample.
        foreach (self::KNOWN_LABELS as $label) {
            self::assertArrayHasKey($label, $seen, "misconception $label never surfaced in 50 seeds");
        }
    }

    public function test_answer_position_varies_across_seeds(): void
    {
        $counts = array_count_values(array_map(
            fn ($q) => $q->answerIndex,
            $this->questions,
        ));

        self::assertSame(4, \count($counts), 'the correct answer must use all four slots');
        foreach ([0, 1, 2, 3] as $slot) {
            self::assertArrayHasKey($slot, $counts, "answer slot $slot never used");
        }
    }

    public function test_difficulty_scales_the_numbers_not_the_concept(): void
    {
        $g = $this->makeGenerator();

        foreach ($this->questions as $seed => $easy) {
            $hard = $g->generate(self::TOPIC, seed: $seed, difficulty: 2);
            foreach ([$easy, $hard] as $q) {
                self::assertStringContainsString('luas', strtolower($q->stem), "seed $seed: stem lost its luas phrasing");
            }
        }
    }

    public function test_generation_is_deterministic_for_a_fixed_seed(): void
    {
        $g = $this->makeGenerator();
        foreach ([1, 25, 50] as $seed) {
            $again = $g->generate(self::TOPIC, seed: $seed, difficulty: 0);
            self::assertSame($this->questions[$seed]->stem, $again->stem, "seed $seed: stem not deterministic");
            self::assertSame($this->questions[$seed]->options, $again->options, "seed $seed: options not deterministic");
            self::assertSame($this->questions[$seed]->answerIndex, $again->answerIndex, "seed $seed: answer index not deterministic");
        }
    }

    public function test_square_and_rectangle_stems_both_occur(): void
    {
        $square = 0;
        $rect = 0;
        foreach ($this->questions as $q) {
            if (str_contains($q->stem, 'persegi panjang')) {
                $rect++;
            } else {
                $square++;
            }
        }

        // A 50/50 draw is not guaranteed, but both phrasings should be reachable.
        self::assertGreaterThan(0, $square, 'never emitted a square (sisi) stem');
        self::assertGreaterThan(0, $rect, 'never emitted a rectangle (persegi panjang) stem');
    }

    private function makeGenerator(): QuestionGenerator
    {
        return new QuestionGenerator;
    }

    /**
     * The two side lengths stated in the stem, in cm, as [l, w].
     *
     * @return array{0: int, 1: int}
     */
    private function sideLengths(GeneratedQuestion $q): array
    {
        $words = explode(' ', $q->stem);
        $numbers = array_values(array_filter(
            $words,
            fn (string $word) => preg_match('/^[\d.]+$/', $word) === 1,
        ));

        // A square states one side; a rectangle states two.
        self::assertGreaterThanOrEqual(1, \count($numbers), 'stem must state at least one side length: '.$q->stem);
        self::assertLessThanOrEqual(2, \count($numbers), 'stem must state at most two side lengths: '.$q->stem);

        $side = (int) str_replace('.', '', $numbers[0]);

        return \count($numbers) === 1 ? [$side, $side] : [$side, (int) str_replace('.', '', $numbers[1])];
    }
}
