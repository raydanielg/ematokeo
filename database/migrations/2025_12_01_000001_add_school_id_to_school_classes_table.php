<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Backfill existing classes to the first available school (if any)
        if (Schema::hasTable('schools')) {
            $school = School::query()->first();

            if ($school) {
                \DB::table('school_classes')->whereNull('school_id')->update([
                    'school_id' => $school->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });
    }
};
