<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Exception;

class SqlImportController extends Controller
{
    public function importSqlFiles()
    {
        $sqlFiles = File::files(database_path('sql'));
        $processedFiles = [];
        $failedFiles = [];
        
        DB::beginTransaction();
        
        try {
            foreach ($sqlFiles as $file) {
                $fileName = $file->getFilename();        
                try {
                    $sql = File::get($file);
                    DB::unprepared($sql);
        
                    $processedFiles[] = $fileName;
                    logger("Successfully processed: " . $fileName);
                } catch (\Exception $e) {
                    $failedFiles[] = $fileName;
                    logger("Error in file: " . $fileName . " - " . $e->getMessage());
                    continue;
                }
            }
        
            if (count($failedFiles) > 0) {
                DB::rollBack();
                return response()->json([
                    "message" => "Some SQL files encountered errors",
                    "failed_files" => $failedFiles,
                    "processed_files" => []
                ], 500);
            }
        
            DB::commit();
            return response()->json([
                "message" => "All SQL files executed successfully",
                "executed_files" => $processedFiles
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => "Unexpected error while executing SQL files",
                "error" => $e->getMessage(),
                "processed_files" => []
            ], 500);
        }
    }
    public function importTableRows($TableName)
    {
        $sqlFilePath = database_path("sql/{$TableName}.sql");

        if (!File::exists($sqlFilePath)) {
            return response()->json([
                "message" => "SQL file for the table '{$TableName}' not found.",
                "file_path" => $sqlFilePath
            ], 404);
        }

        DB::beginTransaction();

        try {
            $sql = File::get($sqlFilePath);
            DB::unprepared($sql);

            DB::commit();

            return response()->json([
                "message" => "SQL file '{$TableName}.sql' executed successfully"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "message" => "Error executing '{$TableName}.sql'",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function exportMenus()
    {
        try {
            $filePath = database_path('sql/tbl_menus.sql');
    
            $structure = DB::select("SHOW CREATE TABLE tbl_menus");
            $createTableSQL = $structure[0]->{'Create Table'} . ";\n\n";
    
            $rows = DB::table('tbl_menus')->get();
            $insertSQL = "";
            
            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    return is_null($value) ? "NULL" : "'" . addslashes($value) . "'";
                }, (array) $row);
                
                $insertSQL .= "INSERT INTO tbl_menus (`" . implode("`, `", array_keys((array) $row)) . "`) VALUES (" . implode(", ", $values) . ");\n";
            }
    
            $sqlContent = "DROP TABLE IF EXISTS tbl_menus;\n\n" . $createTableSQL . $insertSQL;
    
            File::put($filePath, $sqlContent);
    
            return response()->json([
                'message' => 'tbl_menus exported successfully',
                'file_path' => $filePath
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error exporting tbl_menus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
