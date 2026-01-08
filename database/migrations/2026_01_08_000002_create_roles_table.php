<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'key' => 'teacher',
                'name' => 'Teacher',
                'is_default' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'headmaster',
                'name' => 'Headmaster',
                'is_default' => false,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'accountant',
                'name' => 'Accountant (Mhasibu)',
                'is_default' => false,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'academic_teacher',
                'name' => 'Academic Teacher',
                'is_default' => false,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'senior_academic',
                'name' => 'Senior Academic',
                'is_default' => false,
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Ensure existing users have a role (default: teacher)
        if (Schema::hasColumn('users', 'role')) {
            DB::table('users')->whereNull('role')->update(['role' => 'teacher']);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
