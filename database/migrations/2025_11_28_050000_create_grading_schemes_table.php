<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('grade'); // e.g. A, B, C, D, F
            $table->unsignedTinyInteger('min_mark');
            $table->unsignedTinyInteger('max_mark');
            $table->string('division')->nullable(); // e.g. Division I, II
            $table->unsignedTinyInteger('points')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_schemes');
    }
};
