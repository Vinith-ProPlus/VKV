<?php

namespace App\Http\Controllers\Admin\ProjectReports;

use App\Http\Controllers\Controller;
use App\Models\Admin\Labor\SiteLaborDate;
use App\Models\Admin\ManageProjects\SiteTask;
use App\Models\ContractLabor;
use App\Models\Labor;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Site;
use App\Models\SiteContract;
use App\Models\SiteStock;
use App\Models\StockLog;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ProjectReportsController extends Controller
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('View Project Reports');

        $projects = Project::withoutTrashed()->with('sites')->get();

        return view('admin.project_reports.index', compact('projects'));
    }

    /**
     * @throws AuthorizationException
     */
    public function create(Request $request): View
    {
        $this->authorize('View Project Reports');

        $request->validate([
            'site' => 'required|exists:sites,id',
        ]);

        $site = Site::withoutTrashed()
            ->where('id', $request->input('site'))
            ->with(['project.amenities.amenity', 'stages', 'engineer', 'siteLeadMapping.lead'])
            ->firstOrFail();

        $project = $site->project;
        $stages = $site->stages;
        $summary = $this->buildSiteSummary($site);

        return view('report', compact('site', 'project', 'stages', 'summary'));
    }

    public function getProjectTasks(Request $request): JsonResponse
    {
        $this->authorize('View Project Reports');

        $query = SiteTask::withoutTrashed();

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        return response()->json($query->get());
    }

    public function tasksTableLists(Request $request)
    {
        $this->authorize('View Project Reports');

        if ($request->ajax()) {
            $query = SiteTask::with('site', 'stage')->withTrashed()
                ->when($request->filled('site_id'), static function ($q) use ($request) {
                    $q->where('site_id', $request->site_id);
                })
                ->when($request->filled('stage_id'), static function ($q) use ($request) {
                    $q->where('stage_id', $request->stage_id);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('site_name', static fn ($data) => $data->site?->site_no ?? 'N/A')
                ->addColumn('date', static function ($data) {
                    return $data->date ? Carbon::parse($data->date)->format('d-m-Y') : '-';
                })
                ->addColumn('stage_name', static fn ($data) => $data->stage?->name ?? '-')
                ->editColumn('status', static fn ($data) => $data->status)
                ->addColumn('action', static function ($data) {
                    $jsonData = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');

                    return '<div class="d-flex justify-content-center">'
                        . '<a class="btn btn-outline-warning btnTaskView" data-tdata="' . $jsonData . '" id="openModal"><i class="fa fa-eye"></i></a>'
                        . '</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function contractsTableLists(Request $request)
    {
        $this->authorize('View Project Reports');

        if ($request->ajax()) {
            $query = SiteContract::with('site.project', 'user', 'contract_type', 'user.area', 'user.state', 'user.role')
                ->withTrashed()
                ->when($request->filled('site_id'), static function ($q) use ($request) {
                    $q->where('site_id', $request->site_id);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('contract_type_id', static fn ($data) => $data->contract_type?->name ?? '-')
                ->editColumn('user_id', static fn ($data) => $data->user?->name ?? '-')
                ->editColumn('amount', static fn ($data) => number_format((float) $data->amount, 2))
                ->addColumn('action', static function ($data) {
                    if (!$data->user) {
                        return '-';
                    }

                    $jsonData = htmlspecialchars(json_encode($data->user->load('area', 'state', 'roles')), ENT_QUOTES, 'UTF-8');

                    return '<div class="d-flex justify-content-center">'
                        . '<a class="btn btn-outline-warning btnTaskView" data-tdata="' . $jsonData . '" id="openContractsModal"><i class="fa fa-eye"></i></a>'
                        . '</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function laborTableList(Request $request)
    {
        $this->authorize('View Project Reports');

        if ($request->ajax()) {
            $query = SiteLaborDate::with(['site', 'site.project', 'labors', 'contractLabors'])->withTrashed();

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            } elseif ($request->filled('project_id')) {
                $query->whereHas('site', static function ($q) use ($request) {
                    $q->whereIn('project_id', (array) $request->project_id);
                });
            }

            if ($request->filled('from_date')) {
                $query->whereDate('date', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $query->whereDate('date', '<=', $request->to_date);
            }

            if ($request->filled('paid_status')) {
                $query->whereHas('labors', static function ($q) use ($request) {
                    $q->where('paid_status', $request->paid_status);
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('site_name', static fn ($data) => $data->site?->site_no ?? 'N/A')
                ->addColumn('labor_count', static fn ($data) => $data->labors->count())
                ->addColumn('contract_labor_count', static fn ($data) => $data->contractLabors->sum('count'))
                ->addColumn('salary_total', static fn ($data) => number_format((float) $data->labors->sum('salary'), 2))
                ->addColumn('action', static function ($data) {
                    return '<div class="d-flex justify-content-center">'
                        . '<a href="' . route('labors.create', ['site_id' => $data->site_id, 'date' => $data->date]) . '" class="btn btn-outline-warning btn-sm m-1"><i class="fa fa-eye"></i></a>'
                        . '</div>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function purchaseTableList(Request $request)
    {
        $this->authorize('View Project Reports');

        if ($request->ajax()) {
            $query = PurchaseOrder::with(['supervisor', 'site', 'details'])->latest();

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            } elseif ($request->filled('project_id')) {
                $query->whereHas('site', static function ($q) use ($request) {
                    $q->whereIn('project_id', (array) $request->project_id);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('order_date', static fn ($data) => Carbon::parse($data->order_date)->format('d-m-Y'))
                ->addColumn('product_count', static fn ($data) => $data->details->count())
                ->editColumn('status', static function ($data) {
                    $deliveredCount = $data->details->where('status', 'Delivered')->count();
                    $total = $data->details->count();
                    $badgeClass = $total > 0 && $deliveredCount === $total ? 'success' : 'warning';

                    return '<span class="badge bg-' . $badgeClass . '">' . $deliveredCount . '/' . $total . ' Delivered</span>';
                })
                ->addColumn('action', static function ($data) {
                    return '<div class="d-flex justify-content-center">'
                        . '<a href="' . route('purchase-orders.show', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-eye"></i></a>'
                        . '</div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function stockTableList(Request $request)
    {
        $this->authorize('View Project Reports');

        if ($request->ajax()) {
            $query = SiteStock::with(['product', 'category'])
                ->when($request->filled('site_id'), static function ($q) use ($request) {
                    $q->where('site_id', $request->site_id);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('category_name', static fn ($row) => $row->category?->name ?? '-')
                ->addColumn('product_name', static fn ($row) => $row->product?->name ?? '-')
                ->editColumn('quantity', static fn ($row) => number_format((float) $row->quantity, 2))
                ->editColumn('updated_at', static fn ($row) => $row->updated_at?->format('d-m-Y H:i') ?? '-')
                ->make(true);
        }
    }

    public function getSitesByProject(Request $request): JsonResponse
    {
        $this->authorize('View Project Reports');

        $query = Site::withoutTrashed();

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json($query->get(['id', 'site_no', 'status', 'type']));
    }

    private function buildSiteSummary(Site $site): array
    {
        $siteId = $site->id;
        $purchaseOrderIds = PurchaseOrder::where('site_id', $siteId)->pluck('id');

        $tasksQuery = SiteTask::where('site_id', $siteId);
        $totalTasks = (clone $tasksQuery)->count();
        $completedTasks = (clone $tasksQuery)->where('status', 'Completed')->count();
        $inProgressTasks = (clone $tasksQuery)->where('status', 'In-progress')->count();

        $laborSalaryTotal = Labor::whereHas('siteLaborDate', static fn ($q) => $q->where('site_id', $siteId))->sum('salary');
        $contractLaborCount = ContractLabor::whereHas('siteLaborDate', static fn ($q) => $q->where('site_id', $siteId))->sum('count');
        $contractValue = SiteContract::where('site_id', $siteId)->sum('amount');

        $stockItems = SiteStock::where('site_id', $siteId)->count();
        $stockQuantity = SiteStock::where('site_id', $siteId)->sum('quantity');
        $stockLogCount = StockLog::where('site_id', $siteId)->count();

        $poCount = $purchaseOrderIds->count();
        $poProductCount = $purchaseOrderIds->isEmpty()
            ? 0
            : PurchaseOrderDetail::whereIn('purchase_order_id', $purchaseOrderIds)->count();
        $poDeliveredCount = $purchaseOrderIds->isEmpty()
            ? 0
            : PurchaseOrderDetail::whereIn('purchase_order_id', $purchaseOrderIds)->where('status', 'Delivered')->count();

        $investment = (float) ($site->investment_amount ?? 0);
        $sold = (float) ($site->sold_amount ?? 0);

        return [
            'completion_percentage' => $site->completion_percentage,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'pending_tasks' => max($totalTasks - $completedTasks - $inProgressTasks, 0),
            'stage_count' => $site->stages()->count(),
            'contractor_count' => SiteContract::where('site_id', $siteId)->count(),
            'contract_value' => $contractValue,
            'labor_days' => SiteLaborDate::where('site_id', $siteId)->count(),
            'labor_headcount' => Labor::whereHas('siteLaborDate', static fn ($q) => $q->where('site_id', $siteId))->count(),
            'contract_labor_count' => $contractLaborCount,
            'labor_salary_total' => $laborSalaryTotal,
            'purchase_orders' => $poCount,
            'purchase_products' => $poProductCount,
            'purchase_delivered' => $poDeliveredCount,
            'stock_items' => $stockItems,
            'stock_quantity' => $stockQuantity,
            'stock_log_count' => $stockLogCount,
            'investment_amount' => $investment,
            'sold_amount' => $sold,
            'net_gain' => $sold - $investment,
        ];
    }
}
