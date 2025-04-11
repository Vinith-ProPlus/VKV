<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mobile_user_attendances', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('type', ['check_in', 'check_out']);
            $table->string('ip_address')->nullable();
            $table->foreignId('user_device_id')->constrained('user_devices')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_device_location_id')->constrained('user_device_locations')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_user_attendances');
    }
};
