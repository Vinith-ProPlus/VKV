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
        Schema::create('labor_reallocations', static function (Blueprint $table) {
                $table->id();
                $table->foreignId('labor_id')->constrained('labors')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('from_project_labor_date_id')->constrained('project_labor_dates')->cascadeOnUpdate()->restrictOnDelete();
                $table->foreignId('to_project_labor_date_id')->constrained('project_labor_dates')->cascadeOnUpdate()->restrictOnDelete();
                $table->string('remarks')->nullable();
                $table->foreignId('reallocated_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete(); // Tracks who did the reallocation
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_reallocations');
    }
};
