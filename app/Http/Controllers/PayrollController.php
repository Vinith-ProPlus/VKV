<?php
namespace App\Http\Controllers;

use App\Models\Admin\Labor\LaborDesignation;
use App\Models\Payroll;
use App\Models\Labor;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class PayrollController extends Controller {
    use AuthorizesRequests;
    public function getUnpaidLabor(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string']);

        $laborRecords = DB::table('labors')
            ->select('labors.id', 'labors.salary', 'project_labor_dates.date')
            ->join('project_labor_dates', 'labors.project_labor_date_id', '=', 'project_labor_dates.id')
            ->where('labors.mobile', $request->mobile)
            ->whereNotExists(static function ($query) {
                $query->select(DB::raw(1))
                    ->from('payrolls')
                    ->whereColumn('labors.id', 'payrolls.labor_id')
                    ->whereNull('payrolls.deleted_at');
            })
            ->whereNull('labors.deleted_at')
            ->get();

        if ($laborRecords->isEmpty()) {
            return response()->json(['message' => 'No unpaid records found'], 404);
        }

        $totalAmount = $laborRecords->sum('salary');

        return response()->json(['labor_records' => $laborRecords, 'total_amount' => $totalAmount]);
    }

    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'selected_labor_ids' => 'required|array',
            'selected_labor_ids.*' => 'exists:labors,id',
        ]);

        DB::transaction(static function () use ($request) {
            foreach ($request->selected_labor_ids as $laborId) {
                $labor = Labor::with('projectLaborDate')->findOrFail($laborId);

                Payroll::create([
                    'labor_id' => $labor->id,
                    'date' => $labor->projectLaborDate->date,
                    'amount' => $labor->salary,
                ]);

                $labor->update(['paid' => 1]);
            }
        });

        return response()->json(['message' => 'Payment processed successfully']);
    }

    /**
     * @throws AuthorizationException
     */
    public function payrollHistory(Request $request)
    {
        $this->authorize('View Payrolls');
        if ($request->ajax()) {
            $data = Payroll::with('labor.projectLaborDate.project')->withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('name', static function ($data) {
                    return ucfirst(optional($data->labor)->name);
                })
                ->editColumn('mobile', static function ($data) {
                    return optional($data->labor)->mobile;
                })
                ->editColumn('project', static function ($data) {
                    return optional($data->labor->projectLaborDate->project)->name;
                })
                ->editColumn('work_date', static function ($data) {
                    return optional($data->labor->projectLaborDate)->date ? Carbon::parse(optional($data->labor->projectLaborDate)->date)->format('d-m-Y') : '-';
                })
                ->editColumn('paid_amount', static function ($data) {
                    return '₹ ' . number_format($data->amount, 2);
                })
                ->editColumn('paid_date', static function ($data) {
                    return optional($data->paid_at) ? Carbon::parse($data->paid_at)->format('d-m-Y h:i') : '-';
                })
                ->make(true);
        }
        return view('payroll.history');
    }
}
