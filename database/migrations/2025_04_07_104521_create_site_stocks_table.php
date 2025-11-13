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
        Schema::create('site_stocks', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('category_id')->constrained('product_categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('last_transaction_type')->nullable()->comment('PO Created, Stock Adjustment, etc.');
            $table->timestamps();

            // Add unique constraint to prevent duplicate product entries for a site
            $table->unique(['site_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_stocks');
    }
};
