<?php

namespace App\Generator;

/**
 * Deterministic RNG seeded per question.
 *
 * Contract: same seed + same topic + same difficulty => identical output forever.
 * This is what lets us store only (topic_code, seed) in the answers table and
 * regenerate the exact question for the review screen (spec AC-11/AC-13).
 *
 * Implementation: 31-bit LCG (glibc constants) with xorshift output mixing —
 * the PCG-style "cheap and good enough" construction. Deliberately 31-bit:
 * PHP ints are 64-bit signed and silently become FLOATS on 64-bit multiply
 * overflow, which would destroy determinism. Every intermediate here is < 2**44.
 *
 * Quality is far beyond what question generation needs (uniformity across
 * ~10 picks per question); it is not cryptographic and never needs to be.
 */
final class SeededRng
{
    private const MULT = 1103515245;

    private const INC = 12345;

    private const MASK = 0x7FFFFFFF; // 2**31 - 1

    private int $state;

    public function __construct(int $seed)
    {
        $this->state = abs($seed % (self::MASK + 1)) & self::MASK;
        if ($this->state === 0) {
            $this->state = 1; // LCG with 0 state is stuck
        }
    }

    /** Raw 31-bit step. */
    public function next(): int
    {
        $this->state = ($this->state * self::MULT + self::INC) & self::MASK;

        return self::mix($this->state);
    }

    /** Inclusive range [min, max]. */
    public function int(int $min, int $max): int
    {
        if ($min > $max) {
            throw new \InvalidArgumentException("min ($min) must be <= max ($max)");
        }

        return $min + self::modPositive($this->next(), $max - $min + 1);
    }

    /** Pick one element from a non-empty list. */
    public function pick(array $items): mixed
    {
        if ($items === []) {
            throw new \InvalidArgumentException('cannot pick from an empty list');
        }

        return $items[self::modPositive($this->next(), \count($items))];
    }

    /** Fisher–Yates shuffle returning a new list (input untouched). */
    public function shuffle(array $items): array
    {
        $out = array_values($items);
        for ($i = \count($out) - 1; $i > 0; $i--) {
            $j = self::modPositive($this->next(), $i + 1);
            [$out[$i], $out[$j]] = [$out[$j], $out[$i]];
        }

        return $out;
    }

    public function chance(int $percent): bool
    {
        return self::modPositive($this->next(), 100) < $percent;
    }

    /** xorshift32-style output mixer. All shifts stay well inside int range. */
    private static function mix(int $x): int
    {
        $x ^= $x << 13;
        $x ^= $x >> 17;
        $x ^= $x << 5;

        return $x & self::MASK;
    }

    private static function modPositive(int $a, int $m): int
    {
        if ($m <= 0) {
            throw new \InvalidArgumentException('modulus must be positive');
        }

        return (($a % $m) + $m) % $m;
    }
}
