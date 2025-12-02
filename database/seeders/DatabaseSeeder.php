<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'testuser@example.com'],
            [
                'name' => 'testuser',
                'username' => 'testuser',
                'password' => bcrypt('12345678'),
            ],
        );

        $this->call([
            GradingSchemeSeeder::class,
            SubjectSeeder::class,
            TeacherSeeder::class,
            SchoolClassSeeder::class,
            StudentSeeder::class,
        ]);
    }
}
