<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hostels', function (Blueprint $table) {
            if (!Schema::hasColumn('hostels', 'academic_year')) {
                $table->string('academic_year')->nullable()->after('type');
            }
        });

        Schema::table('hostel_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('hostel_rooms', 'school_id')) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('hostel_rooms', 'academic_year')) {
                $table->string('academic_year')->nullable()->after('school_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hostels', function (Blueprint $table) {
            if (Schema::hasColumn('hostels', 'academic_year')) {
                $table->dropColumn('academic_year');
            }
        });

        Schema::table('hostel_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('hostel_rooms', 'academic_year')) {
                $table->dropColumn('academic_year');
            }
            if (Schema::hasColumn('hostel_rooms', 'school_id')) {
                $table->dropConstrainedForeignId('school_id');
            }
        });
    }
};
