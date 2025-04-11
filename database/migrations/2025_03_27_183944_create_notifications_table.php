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
            Schema::create('notifications', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('title');
                $table->text('message');
                $table->text('module_name')->nullable();
                $table->text('module_id')->nullable();
                $table->json('device_ids')->nullable();
                $table->json('fcm_tokens')->nullable();
                $table->enum('status', ['sent', 'failed', 'partially failed'])->default('sent');
                $table->boolean('is_read')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
