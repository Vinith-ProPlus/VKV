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
        Schema::create('tbl_countries', function (Blueprint $table) {
            $table->string('CountryID', 50)->primary();
            $table->string('sortname', 3)->nullable();
            $table->string('CountryName', 150);
            $table->integer('PhoneCode')->nullable();
            $table->string('PhoneLength', 20)->default('0');
            $table->string('CurrencyID', 50)->nullable();
            $table->enum('ActiveStatus', ['Active', 'Inactive'])->default('Active');
            $table->tinyInteger('DFlag')->default(0);
            $table->timestamp('CreatedOn')->useCurrent();
            $table->timestamp('UpdatedOn')->nullable();
            $table->timestamp('DeletedOn')->nullable();
            $table->string('CreatedBy', 50)->nullable();
            $table->string('UpdatedBy', 50)->nullable();
            $table->string('DeletedBy', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_countries');
    }
};
