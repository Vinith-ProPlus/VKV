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

    public function getStockLogDates(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|integer|exists:sites,id',
        ]);

        $siteId = $request->input('site_id');

        $dates = StockLog::where('site_id', $siteId)
            ->select(DB::raw('DATE(time) as date'))
            ->distinct();
        $query = dataFilter($dates, $request);
        $query->getCollection()->transform(function ($date) {
            return [
                'date' => Carbon::parse($date->date)->format('d/m/Y'),
            ];
        });

        return $this->successResponse(dataFormatter($query), 'Stock dates fetched successfully!');
    }

    public function getStockLogData(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|integer|exists:stock_logs,site_id',
            'date' => ['required', 'date_format:d/m/Y'],
        ]);

        $siteId = $request->input('site_id');
        $date = Carbon::createFromFormat('d/m/Y', $request->input('date'))?->format('Y-m-d');

        $stockLogs = StockLog::with(['category', 'product'])
            ->where('site_id', $siteId)
            ->whereDate('time', $date)
            ->whereIn('type', ['Re-Allocation - Transfer', 'Re-Allocation - Received', 'Taken for construction', 'Manual Adjustment - Out'])
            ->get();

        $transferredData = $stockLogs->whereIn('type', ['Re-Allocation - Transfer', 'Re-Allocation - Received'])->values();
        $usedData = $stockLogs->whereIn('type', ['Taken for construction', 'Manual Adjustment - Out'])->values();

        $formattedData = [
            'used_data' => $usedData->map(fn ($log) => $this->formatStockLog($log)),
            'transferred_data' => $transferredData->map(fn ($log) => $this->formatStockLog($log)),
        ];

        return $this->successResponse($formattedData, 'Stock data fetched successfully!');
    }

    private function formatStockLog($log): array
    {
        $remarks = $log->remarks;
        $transferType = null;
        $transferSite = null;

        $baseData = [
            'category' => $log->category->name ?? 'N/A',
            'product' => $log->product->name ?? 'N/A',
            'quantity' => $log->quantity,
        ];

        if (($log->type === 'Re-Allocation - Transfer' || $log->type === 'Re-Allocation - Received') && !empty($remarks)) {
            if (str_contains($remarks, 'Transferred to Site:')) {
                $parts = explode('Transferred to Site:', $remarks);
                if (isset($parts[1])) {
                    $siteParts = explode('|', $parts[1]);
                    $transferType = 'Sent';
                    $transferSite = trim($siteParts[0]);
                }
            } elseif (str_contains($remarks, 'Received from Site:')) {
                $parts = explode('Received from Site:', $remarks);
                if (isset($parts[1])) {
                    $siteParts = explode('|', $parts[1]);
                    $transferType = 'Received';
                    $transferSite = trim($siteParts[0]);
                }
            } elseif (str_contains($remarks, 'Transferred to Project:')) {
                $parts = explode('Transferred to Project:', $remarks);
                if (isset($parts[1])) {
                    $siteParts = explode('|', $parts[1]);
                    $transferType = 'Sent';
                    $transferSite = trim($siteParts[0]);
                }
            } elseif (str_contains($remarks, 'Received from Project:')) {
                $parts = explode('Received from Project:', $remarks);
                if (isset($parts[1])) {
                    $siteParts = explode('|', $parts[1]);
                    $transferType = 'Received';
                    $transferSite = trim($siteParts[0]);
                }
            }

            return [...$baseData, 'transfer_type' => $transferType, 'transfer_site' => $transferSite, 'remarks' => $remarks];
        }

        return $baseData;
    }
}
