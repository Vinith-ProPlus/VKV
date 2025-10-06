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
        Schema::create('mobile_versions', static function (Blueprint $table) {
            $table->id();
            $table->string('logo')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('current_version')->nullable();
            $table->string('new_version')->nullable();
            $table->string('android_link')->nullable();
            $table->string('ios_link')->nullable();
            $table->string('submit_text')->nullable();
            $table->string('ignore_text')->nullable();
            $table->string('update_type')->nullable();
            $table->string('update_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_versions');
    }
};
