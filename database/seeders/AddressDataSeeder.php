<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AddressDataSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('sql_files');

        // Order matters: areas before pincodes (FK area_id on pincodes)
        $tablesToRun = ['states', 'districts', 'areas', 'pincodes'];

        foreach ($tablesToRun as $table) {
            $table = strtolower($table);
            $file = "$path/{$table}.sql";

            if (!file_exists($file)) {
                $this->command?->warn("Skipped: $table.sql (file not found)");
                continue;
            }

            if (!Schema::hasTable($table)) {
                $this->command?->warn("Skipped: $table (table does not exist)");
                continue;
            }

            if (DB::table($table)->count() > 0) {
                $this->command?->warn("Skipped: $table (already has data)");
                continue;
            }

            $sql = File::get($file);
            $statements = array_filter(
                array_map('trim', preg_split('/;\s*\n/', $sql)),
                static fn (string $statement) => $statement !== '' && stripos($statement, 'INSERT') === 0
            );

            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($statements as $statement) {
                DB::unprepared($statement . ';');
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->command?->info("Executed: $table.sql (" . count($statements) . ' statement(s))');
        }
    }
}
