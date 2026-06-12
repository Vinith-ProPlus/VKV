<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pincodes', function (Blueprint $table) {
            if ($this->indexExists('pincodes', 'pincodes_pincode_unique')) {
                $table->dropUnique(['pincode']);
            }

            if (!$this->indexExists('pincodes', 'pincodes_area_id_unique')) {
                $table->unique('area_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pincodes', function (Blueprint $table) {
            if ($this->indexExists('pincodes', 'pincodes_area_id_unique')) {
                $table->dropUnique(['area_id']);
            }

            if (!$this->indexExists('pincodes', 'pincodes_pincode_unique')) {
                $table->unique('pincode');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $definition) {
            if (($definition['name'] ?? '') === $index) {
                return true;
            }
        }

        return false;
    }
};
