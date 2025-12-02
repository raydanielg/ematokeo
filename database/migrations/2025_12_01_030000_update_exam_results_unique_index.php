<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            // Add a new composite unique index that also includes school_id so
            // results don't mix between schools. We intentionally do NOT drop
            // the old unique index here, because on some MySQL setups that
            // index is referenced by a foreign key constraint, which causes
            // "Cannot drop index ... needed in a foreign key constraint".
            $table->unique(['exam_id', 'student_id', 'subject_id', 'school_id'], 'exam_results_exam_student_subject_school_unique');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            // Drop only the new composite unique index we added in up().
            // We don't touch the original index to avoid foreign key issues
            // on MySQL installations where it may be referenced.
            $table->dropUnique('exam_results_exam_student_subject_school_unique');
        });
    }
};
