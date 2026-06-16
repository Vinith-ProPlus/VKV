<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_logs', static function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('stock_logs', static function (Blueprint $table) {
            $table->foreignId('site_id')->after('id')->constrained('sites')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_logs', static function (Blueprint $table) {
            $table->dropForeign(['site_id']);
            $table->dropColumn('site_id');
        });

        Schema::table('stock_logs', static function (Blueprint $table) {
            $table->foreignId('project_id')->after('id')->constrained('projects')->cascadeOnUpdate()->restrictOnDelete();
        });
    }
};
