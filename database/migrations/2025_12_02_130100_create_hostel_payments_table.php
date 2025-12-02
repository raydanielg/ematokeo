<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hostel_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_id')->constrained('hostel_allocations')->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0); // amount paid in TSh
            $table->date('paid_on')->nullable();
            $table->string('method')->nullable(); // e.g. cash, bank, mobile
            $table->string('reference')->nullable(); // receipt no / transaction id
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_payments');
    }
};
