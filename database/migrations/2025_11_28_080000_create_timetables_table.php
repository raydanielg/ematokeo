<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('table_no')->nullable();
            $table->string('name')->nullable();
            $table->string('class_name')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('term')->nullable();
            $table->string('file_path')->nullable(); // PDF/Excel path (future)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
