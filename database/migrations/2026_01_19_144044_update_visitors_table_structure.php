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
            // Drop existing foreign key constraints
            $table->dropForeign('visitors_project_id_foreign');
            $table->dropForeign('visitors_user_id_foreign');
            
            // Drop old columns
            $table->dropColumn(['name', 'mobile', 'rating', 'feedback']);
            
            // Rename user_id to customer_id
            $table->renameColumn('user_id', 'customer_id');
            
            // Add new columns
            $table->foreignId('site_id')->nullable()->constrained('sites')->onDelete('cascade')->after('project_id');
            $table->enum('status', ['new', 'under followup', 'visited', 'closed'])->default('new')->after('site_id');
            $table->longText('remarks')->nullable()->after('status');
            
            // Add foreign key constraints with cascade on delete
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign('visitors_project_id_foreign');
            $table->dropForeign('visitors_customer_id_foreign');
            
            // Drop new columns
            $table->dropColumn(['site_id', 'status', 'remarks']);
            
            // Rename customer_id back to user_id
            $table->renameColumn('customer_id', 'user_id');
            
            // Add back old columns
            $table->string('name')->after('id');
            $table->string('mobile')->after('name');
            $table->tinyInteger('rating')->unsigned()->after('mobile');
            $table->text('feedback')->nullable()->after('rating');
            
            // Add back old foreign key constraints
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->restrictOnDelete();
        });
    }
};
