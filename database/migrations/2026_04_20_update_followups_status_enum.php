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
        Schema::table('followups', function (Blueprint $table) {
            $table->enum('status', ['new', 'under followup', 'visited', 'booked', 'sold', 'closed'])
                ->default('new')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('followups', function (Blueprint $table) {
            $table->enum('status', ['new', 'under followup', 'visited', 'closed'])
                ->default('new')
                ->change();
        });
    }
};
