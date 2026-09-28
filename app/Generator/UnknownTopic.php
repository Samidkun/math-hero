<?php

namespace App\Generator;

/**
 * Thrown when a topic code or misconception label is not in the curriculum config.
 * This is a programming error (config typo), never a user-facing input failure.
 */
final class UnknownTopic extends \DomainException
{
    public static function topic(string $code): self
    {
        return new self("Unknown topic code: [$code]. Add it to config/curriculum.php.");
    }

    public static function misconception(string $label): self
    {
        return new self("Unknown misconception label: [$label]. Add it to MisconceptionLibrary.");
    }
}
