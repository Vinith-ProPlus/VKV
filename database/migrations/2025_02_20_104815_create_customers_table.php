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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 255);
            $table->string('email')->unique();
            $table->string('phone_number', 15);
            $table->date('date_of_birth')->nullable();
            $table->string('street_address', 255);
            $table->foreignId('city_id')->constrained('cities')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('state_id')->constrained('states')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('pincode_id')->constrained('pincodes')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('property_type', ['Residential', 'Commercial', 'Land']);
            $table->string('budget_range', 50)->nullable();
            $table->string('preferred_location', 255)->nullable();
            $table->boolean('is_active')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
