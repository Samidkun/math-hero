<?php

return [
    'levels' => [
        // Kelas 4 (Fase B) — 10 level, 1 topik per level. Sumber: docs/curriculum.md
        4 => [
        1 => ['topic' => 'place-value-10k', 'title' => 'Bilangan Cacah sampai 10.000', 'misconceptions' => ['place-value', 'digit-swap', 'magnitude-flip']],
        2 => ['topic' => 'add-sub-3digit', 'title' => 'Penjumlahan & Pengurangan', 'misconceptions' => ['no-regroup', 'sign-flip', 'zero-subtraction']],
        3 => ['topic' => 'mult-2digit', 'title' => 'Perkalian', 'misconceptions' => ['repeated-add-error', 'times-one', 'digit-multiply']],
        4 => ['topic' => 'division-remainder', 'title' => 'Pembagian Bersisa', 'misconceptions' => ['no-remainder', 'divisor-swap', 'quotient-plus-one']],
        5 => ['topic' => 'fraction-equivalent', 'title' => 'Pecahan Senilai', 'misconceptions' => ['numerator-comp', 'whole-number-thinking', 'unit-stacking']],
        6 => ['topic' => 'fraction-add-like', 'title' => 'Penjumlahan Pecahan', 'misconceptions' => ['add-denominators', 'unit-stacking', 'improper-fear']],
        7 => ['topic' => 'area-rect', 'title' => 'Luas Persegi & Persegi Panjang', 'misconceptions' => ['perimeter-confusion', 'add-all-sides', 'unit-forget']],
        8 => ['topic' => 'perimeter', 'title' => 'Keliling Bangun Datar', 'misconceptions' => ['area-confusion', 'one-side-times-2', 'missing-side']],
        9 => ['topic' => 'units-length-mass', 'title' => 'Satuan Panjang & Massa', 'misconceptions' => ['unit-direction', 'deci-centi', 'add-unlike-units']],
        10 => ['topic' => 'time-interval', 'title' => 'Waktu & Interval', 'misconceptions' => ['minute-hour', 'cross-midday', 'duration-vs-point']],
    ],
    5 => [
        1 => ['topic' => 'place-value-1m', 'title' => 'Bilangan Cacah sampai 1.000.000', 'misconceptions' => ['place-value', 'zero-placeholder', 'magnitude-flip']],
        2 => ['topic' => 'bodmas', 'title' => 'Operasi Campuran', 'misconceptions' => ['order-ignored', 'mult-before-paren', 'distribute-misuse']],
        3 => ['topic' => 'rounding', 'title' => 'Pembulatan & Estimasi', 'misconceptions' => ['round-down-at-5', 'place-round', 'round-then-op']],
        4 => ['topic' => 'kpk-fpb', 'title' => 'KPK & FPB', 'misconceptions' => ['kpk-fpb-swap', 'prime-miss', 'common-only']],
        5 => ['topic' => 'mixed-fractions', 'title' => 'Pecahan Campuran', 'misconceptions' => ['whole-fraction-add', 'improper-fear', 'borrow-error']],
        6 => ['topic' => 'decimals-1', 'title' => 'Desimal 1 Angka', 'misconceptions' => ['whole-number-thinking', 'align-decimal', 'trailing-zero']],
        7 => ['topic' => 'percent-basic', 'title' => 'Persen Dasar', 'misconceptions' => ['percent-greater', '100-ignore', 'of-swap']],
        8 => ['topic' => 'area-tri-par', 'title' => 'Luas Segitiga & Jajargenjang', 'misconceptions' => ['no-half', 'half-parallelogram', 'base-height-mismatch']],
        9 => ['topic' => 'volume-cube', 'title' => 'Volume Kubus & Balok', 'misconceptions' => ['volume-area-confusion', 'unit-forget', 'face-count']],
        10 => ['topic' => 'angles', 'title' => 'Sudut & Jenisnya', 'misconceptions' => ['degree-minute', 'right-angle-assume', 'protractor-inner']],
    ],
    6 => [
        1 => ['topic' => 'fraction-ops-all', 'title' => 'Operasi Pecahan Campuran', 'misconceptions' => ['kpk-denominator', 'sign-flip', 'whole-fraction-add']],
        2 => ['topic' => 'decimals-2-percent', 'title' => 'Desimal 2 Angka & Persen', 'misconceptions' => ['align-decimal', 'percent-decimal-shift', 'of-swap']],
        3 => ['topic' => 'scale-map', 'title' => 'Skala & Peta', 'misconceptions' => ['scale-direction', 'unit-mismatch', 'distance-sum']],
        4 => ['topic' => 'ratio', 'title' => 'Perbandingan (Rasio)', 'misconceptions' => ['ratio-reversal', 'ratio-additive', 'ratio-multiplies']],
        5 => ['topic' => 'percent-discount', 'title' => 'Persen & Diskon', 'misconceptions' => ['percent-of-original', 'double-discount-add', 'percent-greater']],
        6 => ['topic' => 'algebra-basic', 'title' => 'Aljabar Dasar', 'misconceptions' => ['inverse-op', 'variable-ignore', 'solution-arithmetic']],
        7 => ['topic' => 'area-composite', 'title' => 'Luas Gabungan', 'misconceptions' => ['overlap-double', 'wrong-decompose', 'unit-forget']],
        8 => ['topic' => 'volume-composite', 'title' => 'Volume Gabungan', 'misconceptions' => ['overlap-double', 'wrong-decompose', 'unit-forget']],
        9 => ['topic' => 'data-mean', 'title' => 'Data & Rata-rata', 'misconceptions' => ['mean-median-swap', 'outlier-ignore', 'sum-count-error']],
        10 => ['topic' => 'probability-basic', 'title' => 'Peluang Dasar', 'misconceptions' => ['certain-impossible-swap', 'sample-space-miss', 'fraction-prob']],
        ],
    ],
];
