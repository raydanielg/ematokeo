<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_behaviour_ratings', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['exam_id']);
            $table->dropUnique('student_behaviour_ratings_student_id_exam_id_code_unique');
            $table->unique(['student_id', 'rated_date', 'code']);

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_behaviour_ratings', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['exam_id']);
            $table->dropUnique('student_behaviour_ratings_student_id_rated_date_code_unique');
            $table->unique(['student_id', 'exam_id', 'code']);

            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->nullOnDelete();
        });
    }
};
