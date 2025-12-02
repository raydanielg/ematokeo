<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sitting_plans', function (Blueprint $table) {
            $table->unsignedInteger('rooms_count')->default(1)->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('sitting_plans', function (Blueprint $table) {
            $table->dropColumn('rooms_count');
        });
    }
};
