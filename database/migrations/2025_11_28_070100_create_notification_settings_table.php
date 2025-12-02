<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            // Email
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            $table->string('smtp_host')->nullable();
            $table->unsignedInteger('smtp_port')->nullable();
            $table->string('smtp_encryption')->nullable();
            $table->string('smtp_username')->nullable();
            $table->string('smtp_password')->nullable();
            // SMS
            $table->string('sms_provider_name')->nullable();
            $table->string('sms_api_url')->nullable();
            $table->string('sms_sender_id')->nullable();
            $table->string('sms_api_key')->nullable();
            $table->string('sms_callback_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
