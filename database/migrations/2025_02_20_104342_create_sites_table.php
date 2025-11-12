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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_no')->nullable();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('type')->nullable();
            $table->bigInteger('units')->nullable();
            $table->string('range')->nullable();
            $table->foreignId('engineer_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('area_sqft');
            $table->string('investment_amount')->nullable();
            $table->string('sold_amount')->nullable();
            $table->string('status')->default('In-progress');
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
