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
        Schema::table('site_lead_mappings', function (Blueprint $table) {
            // Drop the foreign key constraint first if it exists
            $table->dropForeign(['site_id']);
            // Drop the unique constraint on site_id
            $table->dropUnique(['site_id']);
        });

        Schema::table('site_lead_mappings', function (Blueprint $table) {
            // Re-add the foreign key without unique constraint
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_lead_mappings', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['site_id']);
            // Re-add the unique constraint
            $table->unique('site_id');
        });

        Schema::table('site_lead_mappings', function (Blueprint $table) {
            // Re-add the foreign key with the unique constraint
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }
};
