<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_class_subject_limits', function (Blueprint $table) {
            if (! Schema::hasColumn('timetable_class_subject_limits', 'is_double')) {
                $table->boolean('is_double')->default(false)->after('periods_per_week');
            }
        });
    }

    public function down(): void
    {
        Schema::table('timetable_class_subject_limits', function (Blueprint $table) {
            if (Schema::hasColumn('timetable_class_subject_limits', 'is_double')) {
                $table->dropColumn('is_double');
            }
        });
    }
};
