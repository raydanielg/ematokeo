<?php

namespace Database\Seeders;

use App\Models\GradingScheme;
use Illuminate\Database\Seeder;

class GradingSchemeSeeder extends Seeder
{
    public function run(): void
    {
        // Simple NECTA-style grading example
        $schemes = [
            [
                'min_mark' => 75,
                'max_mark' => 100,
                'grade' => 'A',
                'points' => 1,
            ],
            [
                'min_mark' => 65,
                'max_mark' => 74,
                'grade' => 'B',
                'points' => 2,
            ],
            [
                'min_mark' => 45,
                'max_mark' => 64,
                'grade' => 'C',
                'points' => 3,
            ],
            [
                'min_mark' => 30,
                'max_mark' => 44,
                'grade' => 'D',
                'points' => 4,
            ],
            [
                'min_mark' => 0,
                'max_mark' => 29,
                'grade' => 'F',
                'points' => 5,
            ],
        ];

        foreach ($schemes as $row) {
            GradingScheme::updateOrCreate(
                [
                    'min_mark' => $row['min_mark'],
                    'max_mark' => $row['max_mark'],
                ],
                [
                    'grade' => $row['grade'],
                    'points' => $row['points'],
                ],
            );
        }
    }
}
