<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hostel_allocations', function (Blueprint $table) {
            $table->string('guardian_name')->nullable()->after('status');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->string('guardian_relationship')->nullable()->after('guardian_phone');
        });
    }

    public function down(): void
    {
        Schema::table('hostel_allocations', function (Blueprint $table) {
            $table->dropColumn(['guardian_name', 'guardian_phone', 'guardian_relationship']);
        });
    }
};
