<?php

namespace App\Generator\ItemModels;

/** Indonesian number formatting so stems read naturally to a 10-year-old. */
trait FormatsNumbers
{
    private function num(int $v): string
    {
        return number_format($v, thousands_separator: '.');
    }
}
