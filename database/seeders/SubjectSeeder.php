<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->first();

        if (! $school) {
            return;
        }

        $subjects = [
            ['subject_code' => 'CIV', 'name' => 'Civics'],
            ['subject_code' => 'HIS', 'name' => 'History'],
            ['subject_code' => 'GEO', 'name' => 'Geography'],
            ['subject_code' => 'KIS', 'name' => 'Kiswahili'],
            ['subject_code' => 'ENG', 'name' => 'English Language'],
            ['subject_code' => 'PHY', 'name' => 'Physics'],
            ['subject_code' => 'CHE', 'name' => 'Chemistry'],
            ['subject_code' => 'BIO', 'name' => 'Biology'],
            ['subject_code' => 'B/MAT', 'name' => 'Basic Mathematics'],
            ['subject_code' => 'BOOK', 'name' => 'Book Keeping'],
            ['subject_code' => 'COMM', 'name' => 'Commerce'],
            ['subject_code' => 'ECON', 'name' => 'Economics'],
            ['subject_code' => 'GK', 'name' => 'General Knowledge'],
            ['subject_code' => 'COMP', 'name' => 'Computer Studies'],
            ['subject_code' => 'AGRI', 'name' => 'Agriculture'],
            ['subject_code' => 'FREN', 'name' => 'French'],
            ['subject_code' => 'GER', 'name' => 'German'],
            ['subject_code' => 'ARAB', 'name' => 'Arabic'],
            ['subject_code' => 'FINE', 'name' => 'Fine Art'],
            ['subject_code' => 'MUS', 'name' => 'Music'],
        ];

        foreach ($subjects as $data) {
            Subject::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'subject_code' => $data['subject_code'],
                ],
                [
                    'name' => $data['name'],
                ],
            );
        }
    }
}
