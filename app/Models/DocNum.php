<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocNum extends Model
{
    public function DocNum($tableName)
    {
        $year = Carbon::now()->format('Y');
        $prefix = '';

        // Define prefix based on table name
        switch ($tableName) {
            case 'tbl_customer':
                $prefix = 'CU';
                break;
            case 'tbl_supplier':
                $prefix = 'SU';
                break;
            case 'tbl_order':
                $prefix = 'OR';
                break;
            case 'tbl_invoice':
                $prefix = 'IN';
                break;
            case 'tbl_payment':
                $prefix = 'PA';
                break;
            default:
                return "Table prefix not assigned";
        }

        $pattern = $prefix . $year . '-%';

        $latestDocNum = DB::table($tableName)
            ->where('DocNum', 'LIKE', $pattern)
            ->orderBy('DocNum', 'desc')
            ->value('DocNum');

        if ($latestDocNum) {
            $lastNumber = (int)substr($latestDocNum, -7);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $formattedNumber = str_pad($newNumber, 7, '0', STR_PAD_LEFT);

        return $prefix . $year . '-' . $formattedNumber;
    }
}
