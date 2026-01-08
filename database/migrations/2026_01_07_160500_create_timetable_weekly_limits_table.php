<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_weekly_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('periods_per_week');
            $table->timestamps();

            $table->unique(['school_id', 'school_class_id', 'subject_id', 'teacher_id'], 'twl_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_weekly_limits');
    }
};
