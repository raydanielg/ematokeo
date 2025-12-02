<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            // Drop old unique constraint on exam_id, student_id, subject_id
            $table->dropUnique('exam_results_exam_id_student_id_subject_id_unique');

            // New unique index also includes school_id so results don't mix between schools
            $table->unique(['exam_id', 'student_id', 'subject_id', 'school_id'], 'exam_results_exam_student_subject_school_unique');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            // Drop new composite unique and restore the original
            $table->dropUnique('exam_results_exam_student_subject_school_unique');
            $table->unique(['exam_id', 'student_id', 'subject_id']);
        });
    }
};
