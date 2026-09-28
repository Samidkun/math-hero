<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = config('curriculum.levels', []);

        foreach ($levels as $grade => $gradeLevels) {
            foreach ($gradeLevels as $levelNum => $cfg) {
                Level::updateOrCreate(
                    [
                        'grade' => $grade,
                        'level_number' => $levelNum,
                    ],
                    [
                        'topic_code' => "grade-{$grade}/{$cfg['topic']}",
                        'title' => $cfg['title'],
                    ]
                );
            }
        }
    }
}
