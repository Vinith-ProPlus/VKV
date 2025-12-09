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
        Schema::create('site_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('stage_id')->constrained('site_stages')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name');
            $table->timestamp('date');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('Created'); // default Created, options: Created, In-progress, On-hold, Completed, Deleted
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['site_id', 'stage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_tasks');
    }
};
