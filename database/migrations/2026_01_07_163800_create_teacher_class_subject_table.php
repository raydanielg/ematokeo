<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_class_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['school_id', 'teacher_id', 'school_class_id', 'subject_id'], 'tcs_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_subject');
    }
};
