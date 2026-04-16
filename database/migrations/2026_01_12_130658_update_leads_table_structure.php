<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {

            $table->dropForeign(['lead_status_id']);
            $table->dropForeign(['lead_owner_id']);
            $table->dropForeign(['lead_follow_by_id']);

            // Drop columns
            $table->dropColumn([
                'lead_title',
                'first_name',
                'last_name',
                'lead_status_id',
                'lead_owner_id',
                'lead_follow_by_id',
                'whatsapp_number',
                'gst_number',
            ]);

            $table->string('name')->after('id');
            $table->string('mobile_number')->nullable(false)->change();

            // Make others nullable if needed (optional safety)
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {

            // Re-add removed columns
            $table->string('lead_title')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('whatsapp_number');
            $table->string('gst_number')->nullable();

            // Remove name column
            $table->dropColumn('name');
        });
    }
};
