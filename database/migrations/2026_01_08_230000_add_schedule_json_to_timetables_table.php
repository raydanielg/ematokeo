<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            if (! Schema::hasColumn('timetables', 'schedule_json')) {
                $table->json('schedule_json')->nullable()->after('file_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            if (Schema::hasColumn('timetables', 'schedule_json')) {
                $table->dropColumn('schedule_json');
            }
        });
    }
};
