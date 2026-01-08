<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_behaviour_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('code', 10);
            $table->string('label', 255);
            $table->string('grade', 2);
            $table->date('rated_date')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'exam_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_behaviour_ratings');
    }
};
