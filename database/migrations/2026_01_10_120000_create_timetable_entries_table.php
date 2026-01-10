<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->string('day', 20);
            $table->unsignedTinyInteger('period_index');
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('teacher_initials', 50)->nullable();
            $table->timestamps();

            $table->unique(['timetable_id', 'school_class_id', 'day', 'period_index'], 'tt_entries_unique');
            $table->index(['timetable_id', 'day', 'period_index'], 'tt_entries_slot_idx');
            $table->index(['timetable_id', 'teacher_id', 'day', 'period_index'], 'tt_entries_teacher_slot_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
