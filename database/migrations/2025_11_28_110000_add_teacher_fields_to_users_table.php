<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('is_active');
            $table->string('check_number')->nullable()->after('phone');
            $table->string('teaching_classes')->nullable()->after('check_number'); // e.g. "Form I A, Form II B"
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'check_number', 'teaching_classes']);
        });
    }
};
