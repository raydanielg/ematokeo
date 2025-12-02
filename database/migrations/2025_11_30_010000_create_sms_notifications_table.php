<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('general'); // general, exam_results, reminder
            $table->string('audience')->default('all_parents'); // all_parents, class_parents, all_teachers, custom
            $table->string('channel')->default('sms');
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->string('class_level')->nullable();
            $table->text('message');
            $table->string('status')->default('queued'); // queued, sent, failed
            $table->json('recipient_scope')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_notifications');
    }
};
