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
        Schema::table('visitors', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign('visitors_customer_id_foreign');
            
            // Add new foreign key constraint to leads table
            $table->foreign('customer_id')->references('id')->on('leads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Drop the foreign key constraint to leads
            $table->dropForeign('visitors_customer_id_foreign');
            
            // Restore the original foreign key constraint to users
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
