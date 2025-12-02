<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->unsignedTinyInteger('raw_marks')->nullable()->after('subject_id');
            $table->unsignedTinyInteger('standardized_marks')->nullable()->after('raw_marks');
        });
    }

    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['raw_marks', 'standardized_marks']);
        });
    }
};
