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
        Schema::create('contract_labors', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_labor_date_id')->constrained('site_labor_dates')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('site_contract_id')->constrained('site_contracts')->cascadeOnUpdate()->restrictOnDelete();
            $table->integer('count');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_labors');
    }
};
