<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\School;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            if (!Schema::hasColumn('timetables', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
            }
        });

        if (Schema::hasColumn('timetables', 'school_id')) {
            $school = School::query()->first();
            if ($school) {
                \DB::table('timetables')->whereNull('school_id')->update(['school_id' => $school->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            if (Schema::hasColumn('timetables', 'school_id')) {
                $table->dropConstrainedForeignId('school_id');
            }
        });
    }
};
