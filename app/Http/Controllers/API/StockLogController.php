<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StockLog;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockLogController extends Controller
{
    use ApiResponse;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getStockLogDates(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id'
        ]);

        $projectId = $request->input('project_id');

        // Get unique dates from stock logs for the specified project
        $dates = StockLog::where('project_id', $projectId)
            ->select(DB::raw('DATE(time) as date'))
            ->distinct();
        $query = dataFilter($dates, $request);
        $query->getCollection()->transform(function ($date) {
            return [
                'date' => Carbon::parse($date->date)->format('d/m/Y')
            ];
        });

        return $this->successResponse(dataFormatter($query), "Stock dates fetched successfully!");
    }

    /**
     * Get stock data for a specific project and date, categorized by type
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStockLogData(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|integer|exists:stock_logs,project_id',
            'date' => ['required', 'date_format:d/m/Y'],
        ]);

        $projectId = $request->input('project_id');
        $date = Carbon::createFromFormat('d/m/Y', $request->input('date'))?->format('Y-m-d');

        // Filter stock logs by project, date, and desired types
        $stockLogs = StockLog::with(['category', 'product'])
            ->where('project_id', $projectId)
            ->whereDate('time', $date)
            ->whereIn('type', ['Re-Allocation - Transfer', 'Re-Allocation - Received', 'Taken for construction', 'Manual Adjustment - Out'])
            ->get();
        logger($stockLogs);
        $transferredData = $stockLogs->whereIn('type', ['Re-Allocation - Transfer', 'Re-Allocation - Received'])->values();
        $usedData = $stockLogs->whereIn('type', ['Taken for construction', 'Manual Adjustment - Out'])->values();

        $formattedData = [
            'used_data' => $usedData->map(fn($log) => $this->formatStockLog($log)),
            'transferred_data' => $transferredData->map(fn($log) => $this->formatStockLog($log)),
        ];

        return $this->successResponse($formattedData, "Stock data fetched successfully!");
    }

    /**
     * Format a stock log entry with relevant details
     *
     * @param $log
     * @return array
     */
    private function formatStockLog($log): array
    {
        $remarks = $log->remarks;
        $transferType = null;
        $transferProject = null;

        $baseData = [
            'category' => $log->category->name ?? 'N/A',
            'product' => $log->product->name ?? 'N/A',
            'quantity' => $log->quantity,
        ];

        if (($log->type === 'Re-Allocation - Transfer' || $log->type === 'Re-Allocation - Received') && !empty($remarks)) {
            if (str_contains($remarks, 'Transferred to Project:')) {
                $parts = explode('Transferred to Project:', $remarks);
                if (isset($parts[1])) {
                    $projectParts = explode('|', $parts[1]);
                    $transferType = "Sent";
                    $transferProject = trim($projectParts[0]);
                }
            } elseif (str_contains($remarks, 'Received from Project:')) {
                $parts = explode('Received from Project:', $remarks);
                if (isset($parts[1])) {
                    $projectParts = explode('|', $parts[1]);
                    $transferType = "Received";
                    $transferProject = trim($projectParts[0]);
                }
            }

            return [...$baseData, 'transfer_type' => $transferType, 'transfer_project' => $transferProject, 'remarks' => $remarks];
        }

        return $baseData;
    }
}
