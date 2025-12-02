<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Backfill existing subjects to the first available school (if any)
        if (Schema::hasTable('schools')) {
            $school = School::query()->first();

            if ($school) {
                \DB::table('subjects')->whereNull('school_id')->update([
                    'school_id' => $school->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_id');
        });
    }
};
