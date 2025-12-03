<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@ematokeo.local'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'email' => 'admin@ematokeo.local',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'is_active' => true,
                'school_id' => null, // Admin doesn't belong to a specific school
            ]
        );
    }
}
