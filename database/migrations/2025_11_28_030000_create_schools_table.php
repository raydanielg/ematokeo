<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('school_code')->nullable();
            $table->string('level')->nullable(); // e.g. O-Level, A-Level, Primary
            $table->string('ownership')->nullable(); // e.g. Government, Private
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('district')->nullable();
            $table->string('region')->nullable();
            $table->string('head_teacher_name')->nullable();
            $table->string('head_teacher_phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
