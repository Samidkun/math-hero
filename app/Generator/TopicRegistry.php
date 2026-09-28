<?php

namespace App\Generator;

/**
 * Resolves topic codes from config/curriculum.php — the single source of truth for
 * "which topics exist and what misconceptions they carry".
 *
 * ponytail: config file now; the admin panel of brainstorm decision C writes to this
 * same shape later, so this interface is the stable seam.
 */
final class TopicRegistry
{
    /** @var array<int, array<int, array{topic: string, title: string, misconceptions: string[]}>> */
    private array $curriculum;

    public function __construct(?array $curriculum = null)
    {
        $this->curriculum = $curriculum ?? config('curriculum.levels') ?? [];
    }

    /**
     * Full code used everywhere: "grade-6/ratio".
     */
    public function code(int $grade, int $level): string
    {
        return "grade-$grade/".$this->topicSlug($grade, $level);
    }

    public function title(int $grade, int $level): string
    {
        return $this->levelConfig($grade, $level)['title'];
    }

    /**
     * @return string[] misconception labels for the topic
     */
    public function misconceptions(int $grade, int $level): array
    {
        return $this->levelConfig($grade, $level)['misconceptions'];
    }

    /**
     * Parse "grade-6/ratio" back into [grade, level].
     *
     * @return array{0: int, 1: int}
     */
    public function parseCode(string $code): array
    {
        if (! preg_match('#^grade-([4-6])/([a-z0-9-]+)$#', $code, $m)) {
            throw UnknownTopic::topic($code);
        }
        $grade = (int) $m[1];
        $level = $this->levelOfSlug($grade, $m[2]);
        if ($level === null) {
            throw UnknownTopic::topic($code);
        }

        return [$grade, $level];
    }

    public function exists(string $code): bool
    {
        try {
            $this->parseCode($code);

            return true;
        } catch (UnknownTopic) {
            return false;
        }
    }

    // --- internals ----------------------------------------------------------

    private function topicSlug(int $grade, int $level): string
    {
        return $this->levelConfig($grade, $level)['topic'];
    }

    /** @return array{topic: string, title: string, misconceptions: string[]} */
    private function levelConfig(int $grade, int $level): array
    {
        if (! isset($this->curriculum[$grade][$level])) {
            throw UnknownTopic::topic("grade-$grade level-$level");
        }

        return $this->curriculum[$grade][$level];
    }

    private function levelOfSlug(int $grade, string $slug): ?int
    {
        foreach ($this->curriculum[$grade] ?? [] as $level => $cfg) {
            if ($cfg['topic'] === $slug) {
                return $level;
            }
        }

        return null;
    }
}
