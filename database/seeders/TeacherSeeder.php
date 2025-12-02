<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['name' => 'Teacher Asha M.', 'email' => 'asha.teacher@example.com'],
            ['name' => 'Teacher John K.', 'email' => 'john.teacher@example.com'],
            ['name' => 'Teacher Neema S.', 'email' => 'neema.teacher@example.com'],
            ['name' => 'Teacher Paul D.', 'email' => 'paul.teacher@example.com'],
        ];

        foreach ($teachers as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => 'teacher',
                    'is_active' => true,
                    'password' => bcrypt('12345678'),
                ]
            );
        }
    }
}
