<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hostel_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('hostel_id')->nullable()->constrained('hostels')->nullOnDelete();
            $table->string('class_level')->nullable(); // e.g. FORM I, FORM II
            $table->string('academic_year')->nullable();
            $table->string('title')->nullable();
            $table->text('items')->nullable(); // items to buy / requirements list (markdown or plain text)
            $table->text('rules')->nullable(); // hostel rules summary (markdown or plain text)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_requirements');
    }
};
