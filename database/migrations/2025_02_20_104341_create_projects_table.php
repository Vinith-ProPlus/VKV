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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('project_id')->unique();
            $table->string('name')->unique();
            $table->string('location');
            $table->string('type');
            $table->bigInteger('units');
            $table->string('target_customers');
            $table->string('range');
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('area_sqft');
            $table->string('investment_amount')->nullable();
            $table->string('sold_amount')->nullable();
            $table->string('status')->default('In-progress');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
