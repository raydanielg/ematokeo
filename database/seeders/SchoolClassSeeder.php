<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['name' => 'Form I A', 'level' => 'Form I', 'stream' => 'A'],
            ['name' => 'Form I B', 'level' => 'Form I', 'stream' => 'B'],
            ['name' => 'Form II A', 'level' => 'Form II', 'stream' => 'A'],
            ['name' => 'Form II B', 'level' => 'Form II', 'stream' => 'B'],
            ['name' => 'Form III A', 'level' => 'Form III', 'stream' => 'A'],
            ['name' => 'Form IV A', 'level' => 'Form IV', 'stream' => 'A'],
        ];

        $coreSubjectCodes = ['CIV', 'HIS', 'GEO', 'KIS', 'ENG', 'PHY', 'CHE', 'BIO', 'B/MAT'];
        $coreSubjectIds = Subject::whereIn('subject_code', $coreSubjectCodes)->pluck('id')->all();

        $teacherIds = User::where('role', 'teacher')->pluck('id')->all();

        $index = 0;

        foreach ($classes as $data) {
            $class = SchoolClass::updateOrCreate(
                ['name' => $data['name']],
                [
                    'level' => $data['level'],
                    'stream' => $data['stream'],
                    'description' => $data['level'] . ' ' . $data['stream'] . ' Stream',
                ]
            );

            if (!empty($coreSubjectIds)) {
                $class->subjects()->sync($coreSubjectIds);
            }

            if (!empty($teacherIds)) {
                $class->teacher_id = $teacherIds[$index % count($teacherIds)];
                $class->save();
                $index++;
            }
        }
    }
}
