<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_student_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year')->nullable();
            $table->unsignedSmallInteger('total')->nullable();
            $table->decimal('average', 5, 2)->nullable();
            $table->string('grade', 2)->nullable();
            $table->string('division', 10)->nullable();
            $table->unsignedSmallInteger('points')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id', 'school_id'], 'exam_student_results_exam_student_school_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_student_results');
    }
};
