<?php

return [
    /*
    | Math Hero knobs. Ponytail: move to an admin panel when the project
    | upgrades to T1 (see brainstorm decision C — interface stays stable).
    */

    'question_timeout' => (int) env('MATHHERO_QUESTION_TIMEOUT', 30),
    'questions_per_level' => (int) env('MATHHERO_QUESTIONS_PER_LEVEL', 10),
    'pass_percent' => (int) env('MATHHERO_PASS_PERCENT', 100),
    'adaptive_inject_pct' => (int) env('MATHHERO_ADAPTIVE_INJECT_PCT', 30),
    'pin_min' => (int) env('MATHHERO_PIN_MIN', 1000),
    'pin_max' => (int) env('MATHHERO_PIN_MAX', 9999),
    'class_code_length' => (int) env('MATHHERO_CLASS_CODE_LEN', 6),

    // alphabet without ambiguous chars (no O/0/I/1)
    'class_code_alphabet' => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789',

    'grades' => [4, 5, 6],
];
