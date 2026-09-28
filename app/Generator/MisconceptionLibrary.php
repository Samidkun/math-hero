<?php

namespace App\Generator;

/**
 * Maps a topic to its named misconceptions and the arithmetic that produces each wrong answer.
 *
 * Each entry: label => callable that takes the correct answer + context and returns the
 * wrong value the way a child actually would. This is the whole differentiator of Math Hero:
 * distractors are real mistakes with names, so a teacher report says "60% of the class adds
 * ratios instead of multiplying" instead of just "wrong".
 *
 * ponytail: this is hand-curated per topic. v2 could mine real answer logs; the contract
 * (label => value) stays the same.
 */
final class MisconceptionLibrary
{
    /**
     * @var array<string, array<string, callable(int|array<string,int>): int>>
     */
    private array $map;

    public function __construct()
    {
        $this->map = [
            // --- grade 4 -------------------------------------------------------
            'place-value' => fn (int $a) => (int) ($a / 10),
            'digit-swap' => function (int $a): int {
                $s = (string) $a;

                return (int) ($s[1].$s[0].substr($s, 2));
            },
            'magnitude-flip' => fn (int $a, array $ctx) => (int) ($ctx['other'] ?? $a),
            'no-regroup' => fn (int $a) => (int) ($a % 100),
            'sign-flip' => fn (int $a, array $ctx) => $ctx['other'] ?? $a,
            'zero-subtraction' => fn (int $a) => 0,
            'repeated-add-error' => fn (int $a, array $ctx) => $a + ($ctx['b'] ?? 1) - 1,
            'times-one' => fn (int $a, array $ctx) => $a + 1,
            'digit-multiply' => fn (int $a, array $ctx) => ($ctx['a'] % 10) * ($ctx['b'] % 10),
            'no-remainder' => fn (int $a) => $a,
            'divisor-swap' => fn (int $a, array $ctx) => intdiv($ctx['b'] ?? 1, $ctx['a'] ?? 1) ?: 1,
            'quotient-plus-one' => fn (int $a) => $a + 1,
            'numerator-comp' => fn (int $a) => $a + 1,
            'whole-number-thinking' => fn (int $a, array $ctx) => (int) ceil($a / 2),
            'unit-stacking' => fn (int $a, array $ctx) => $a + 1,
            'add-denominators' => fn (int $a, array $ctx) => $ctx['d'] ?? 1,
            'improper-fear' => fn (int $a, array $ctx) => $a - ($ctx['d'] ?? 1),
            'perimeter-confusion' => fn (int $a, array $ctx) => ($ctx['l'] ?? 1) * ($ctx['w'] ?? 1) * 2,
            'add-all-sides' => fn (int $a, array $ctx) => ($ctx['l'] ?? 1) + ($ctx['w'] ?? 1),
            'unit-forget' => fn (int $a) => $a,
            'area-confusion' => fn (int $a, array $ctx) => $ctx['l'] * $ctx['w'] ?? $a,
            'one-side-times-2' => fn (int $a, array $ctx) => ($ctx['side'] ?? 1) * 2,
            'missing-side' => fn (int $a) => $a - 1,
            'unit-direction' => fn (int $a, array $ctx) => $a * ($ctx['factor'] ?? 1000),
            'deci-centi' => fn (int $a) => $a * 10,
            'add-unlike-units' => fn (int $a, array $ctx) => ($ctx['m'] ?? 1) + ($ctx['cm'] ?? 0),
            'minute-hour' => fn (int $a) => $a * 60,
            'cross-midday' => fn (int $a) => $a + 12,
            'duration-vs-point' => fn (int $a) => $a % 12,

            // --- grade 5 -------------------------------------------------------
            'zero-placeholder' => fn (int $a) => (int) str_replace('0', '', (string) $a),
            'order-ignored' => fn (int $a) => $a,
            'mult-before-paren' => fn (int $a, array $ctx) => ($ctx['a'] * $ctx['b']) + $ctx['c'],
            'distribute-misuse' => fn (int $a, array $ctx) => ($ctx['a'] * $ctx['b']) + $ctx['c'],
            'round-down-at-5' => fn (int $a, array $ctx) => (int) (floor($a / 10) * 10),
            'place-round' => fn (int $a) => (int) round($a / 100) * 100,
            'round-then-op' => fn (int $a) => $a,
            'kpk-fpb-swap' => fn (int $a, array $ctx) => self::gcd($ctx['a'] ?? 1, $ctx['b'] ?? 1),
            'prime-miss' => fn (int $a) => $a * 2,
            'common-only' => fn (int $a) => $a,
            'whole-fraction-add' => fn (int $a, array $ctx) => ($ctx['whole'] ?? 0) + ($ctx['p'] ?? 0) + 1,
            'borrow-error' => fn (int $a, array $ctx) => $a - 10,
            'align-decimal' => fn (int $a) => $a * 10,
            'percent-decimal-shift' => fn (int $a) => (int) ($a / 10) ?: 1,
            'trailing-zero' => fn (int $a) => (int) ((string) $a),
            'percent-greater' => fn (int $a, array $ctx) => ($ctx['whole'] ?? 100) + 1,
            '100-ignore' => fn (int $a, array $ctx) => ($ctx['p'] ?? 50),
            'of-swap' => fn (int $a, array $ctx) => (int) round($ctx['whole'] * $ctx['p'] / 100) ?: $a,
            'no-half' => fn (int $a) => $a * 2,
            'half-parallelogram' => fn (int $a) => (int) ($a / 2),
            'base-height-mismatch' => fn (int $a, array $ctx) => ($ctx['b'] ?? 1) * ($ctx['h2'] ?? 1),
            'volume-area-confusion' => fn (int $a, array $ctx) => ($ctx['s'] ?? 1) * ($ctx['s2'] ?? 1),
            'face-count' => fn (int $a) => 6,
            'degree-minute' => fn (int $a) => $a * 60,
            'right-angle-assume' => fn (int $a) => 90,
            'protractor-inner' => fn (int $a) => 180 - $a,

            // --- grade 6 -------------------------------------------------------
            'kpk-denominator' => fn (int $a, array $ctx) => ($ctx['d1'] ?? 2) * ($ctx['d2'] ?? 3),
            'scale-direction' => fn (int $a, array $ctx) => intdiv($a, $ctx['scale'] ?? 1) ?: 1,
            'unit-mismatch' => fn (int $a) => $a * 100,
            'distance-sum' => fn (int $a, array $ctx) => $a + ($ctx['extra'] ?? 0),
            'ratio-reversal' => fn (int $a, array $ctx) => ($ctx['a'] ?? 1) * ($ctx['units'] ?? 1),
            'ratio-additive' => fn (int $a, array $ctx) => ($ctx['units'] ?? 1) - ($ctx['a'] ?? 1) + 1,
            'ratio-multiplies' => fn (int $a, array $ctx) => ($ctx['a'] ?? 1) * ($ctx['units'] ?? 1),
            'percent-of-original' => fn (int $a, array $ctx) => ($ctx['price'] ?? 100) - ($ctx['disc'] ?? 0),
            'double-discount-add' => fn (int $a, array $ctx) => $a * 2,
            'inverse-op' => fn (int $a, array $ctx) => ($ctx['result'] ?? 1) * ($ctx['x'] ?? 1),
            'variable-ignore' => fn (int $a) => $a + 1,
            'solution-arithmetic' => fn (int $a) => $a + 2,
            'overlap-double' => fn (int $a, array $ctx) => $a + ($ctx['overlap'] ?? 0),
            'wrong-decompose' => fn (int $a, array $ctx) => $a - ($ctx['part'] ?? 0),
            'mean-median-swap' => fn (int $a) => $a + 1,
            'outlier-ignore' => fn (int $a, array $ctx) => (int) round($a - ($ctx['out'] ?? 0) / 3),
            'sum-count-error' => fn (int $a, array $ctx) => intdiv($a, ($ctx['n'] ?? 1) + 1) ?: 1,
            'certain-impossible-swap' => fn (int $a) => 0,
            'sample-space-miss' => fn (int $a) => $a - 1,
            'fraction-prob' => fn (int $a, array $ctx) => ($ctx['n'] ?? 1) + 1,
        ];
    }

    /**
     * Apply a misconception to produce the wrong answer a child would write.
     *
     * @param  array<string,int>  $ctx
     */
    public function apply(string $label, int $correct, array $ctx = []): int
    {
        if (! isset($this->map[$label])) {
            throw UnknownTopic::misconception($label);
        }

        return ($this->map[$label])($correct, $ctx);
    }

    public function has(string $label): bool
    {
        return isset($this->map[$label]);
    }

    private static function gcd(int $a, int $b): int
    {
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return $a;
    }
}
