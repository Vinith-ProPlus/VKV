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
        Schema::create('payrolls', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('labor_id')->constrained('labors')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('date'); // Payment date
            $table->decimal('amount', 10, 2); // Paid salary
            $table->timestamp('paid_at')->useCurrent();
            $table->timestamps();
            $table->softDeletes(); // Allows tracking deleted records
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
