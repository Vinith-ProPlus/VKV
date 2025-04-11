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
        Schema::create('tbl_settings', function (Blueprint $table) {
            $table->id('SLNO');
            $table->string('KeyName', 150)->nullable();
            $table->mediumText('KeyValue')->nullable();
            $table->enum('SType', ['text', 'number','json','boolean','serialize'])->default('text');
            $table->mediumText('Description')->nullable();
            $table->timestamp('UpdatedOn')->nullable();
            $table->string('UpdatedBy', 50)->nullable();
            $table->mediumText('UKey')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_settings');
    }
};
