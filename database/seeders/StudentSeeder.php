<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->first();
        $prefix = $school && $school->school_code ? $school->school_code : 'EMT01';

        $currentYear = AcademicYear::where('is_current', true)->first();
        $academicYear = $currentYear?->year ?? date('Y');

        $samples = [
            ['full_name' => 'Asha Juma', 'class_level' => 'Form I', 'stream' => 'A', 'gender' => 'F'],
            ['full_name' => 'John Peter', 'class_level' => 'Form I', 'stream' => 'B', 'gender' => 'M'],
            ['full_name' => 'Neema Joseph', 'class_level' => 'Form II', 'stream' => 'A', 'gender' => 'F'],
            ['full_name' => 'Paul Daniel', 'class_level' => 'Form II', 'stream' => 'B', 'gender' => 'M'],
            ['full_name' => 'Rehema Said', 'class_level' => 'Form III', 'stream' => 'A', 'gender' => 'F'],
            ['full_name' => 'Michael James', 'class_level' => 'Form IV', 'stream' => 'A', 'gender' => 'M'],
            ['full_name' => 'Fatma Ali', 'class_level' => 'Form I', 'stream' => 'A', 'gender' => 'F'],
            ['full_name' => 'Joseph Mark', 'class_level' => 'Form I', 'stream' => 'B', 'gender' => 'M'],
            ['full_name' => 'Sara Victor', 'class_level' => 'Form II', 'stream' => 'A', 'gender' => 'F'],
            ['full_name' => 'Peter Lucas', 'class_level' => 'Form II', 'stream' => 'B', 'gender' => 'M'],
        ];

        $counter = 1;

        foreach ($samples as $row) {
            $number = str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
            $examNumber = $prefix . '-' . $number;

            Student::updateOrCreate(
                ['exam_number' => $examNumber],
                [
                    'full_name' => $row['full_name'],
                    'class_level' => $row['class_level'],
                    'stream' => $row['stream'],
                    'gender' => $row['gender'],
                    'date_of_birth' => now()->subYears(15)->subDays($counter),
                    'school_id' => $school?->id,
                    'academic_year' => $academicYear,
                ]
            );

            $counter++;
        }
    }
}
