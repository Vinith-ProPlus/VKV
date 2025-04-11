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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_title');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('address', 255);
            $table->foreignId('state_id')->constrained('states')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('pincode_id')->constrained('pincodes')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('gst_number')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile_number');
            $table->string('whatsapp_number');
            $table->foreignId('lead_source_id')->constrained('lead_sources')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('lead_status_id')->constrained('lead_statuses')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('lead_owner_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('lead_follow_by_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('image')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
