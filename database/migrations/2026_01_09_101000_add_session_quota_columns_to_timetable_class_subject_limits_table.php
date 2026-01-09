<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_class_subject_limits', function (Blueprint $table) {
            if (! Schema::hasColumn('timetable_class_subject_limits', 'morning_single')) {
                $table->unsignedSmallInteger('morning_single')->default(0)->after('is_double');
            }
            if (! Schema::hasColumn('timetable_class_subject_limits', 'morning_double')) {
                $table->unsignedSmallInteger('morning_double')->default(0)->after('morning_single');
            }
            if (! Schema::hasColumn('timetable_class_subject_limits', 'afternoon_single')) {
                $table->unsignedSmallInteger('afternoon_single')->default(0)->after('morning_double');
            }
            if (! Schema::hasColumn('timetable_class_subject_limits', 'afternoon_double')) {
                $table->unsignedSmallInteger('afternoon_double')->default(0)->after('afternoon_single');
            }
        });
    }

    public function down(): void
    {
        Schema::table('timetable_class_subject_limits', function (Blueprint $table) {
            if (Schema::hasColumn('timetable_class_subject_limits', 'afternoon_double')) {
                $table->dropColumn('afternoon_double');
            }
            if (Schema::hasColumn('timetable_class_subject_limits', 'afternoon_single')) {
                $table->dropColumn('afternoon_single');
            }
            if (Schema::hasColumn('timetable_class_subject_limits', 'morning_double')) {
                $table->dropColumn('morning_double');
            }
            if (Schema::hasColumn('timetable_class_subject_limits', 'morning_single')) {
                $table->dropColumn('morning_single');
            }
        });
    }
};
