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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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

        $with = ['engineer'];
        if (Schema::hasTable('site_stages')) {
            $with[] = 'stages';
        }
        if (Schema::hasTable('project_amenities')) {
            $with[] = 'project.amenities.amenity';
        } else {
            $with[] = 'project';
        }
        if (Schema::hasTable('site_lead_mappings')) {
            $with[] = 'siteLeadMapping.lead';
        }

        $site = Site::withoutTrashed()
            ->where('id', $request->input('site'))
            ->with($with)
            ->firstOrFail();

        $project = $site->project;
        if (!$project) {
            abort(404, 'Project not found for the selected site.');
        }

        $stages = Schema::hasTable('site_stages') ? ($site->stages ?? collect()) : collect();
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
        $investment = (float) ($site->investment_amount ?? 0);
        $sold = (float) ($site->sold_amount ?? 0);

        $defaults = [
            'completion_percentage' => $this->safeCompletionPercentage($site),
            'total_tasks' => 0,
            'completed_tasks' => 0,
            'in_progress_tasks' => 0,
            'pending_tasks' => 0,
            'stage_count' => $site->relationLoaded('stages') ? $site->stages->count() : 0,
            'contractor_count' => 0,
            'contract_value' => 0,
            'labor_days' => 0,
            'labor_headcount' => 0,
            'contract_labor_count' => 0,
            'labor_salary_total' => 0,
            'purchase_orders' => 0,
            'purchase_products' => 0,
            'purchase_delivered' => 0,
            'stock_items' => 0,
            'stock_quantity' => 0,
            'stock_log_count' => 0,
            'investment_amount' => $investment,
            'sold_amount' => $sold,
            'net_gain' => $sold - $investment,
        ];

        try {
            $siteId = $site->id;
            $purchaseOrderIds = $this->purchaseOrderIdsForSite($site);

            if (Schema::hasTable('site_tasks')) {
                $tasksQuery = SiteTask::where('site_id', $siteId);
                $defaults['total_tasks'] = (clone $tasksQuery)->count();
                $defaults['completed_tasks'] = (clone $tasksQuery)->where('status', 'Completed')->count();
                $defaults['in_progress_tasks'] = (clone $tasksQuery)->where('status', 'In-progress')->count();
                $defaults['pending_tasks'] = max(
                    $defaults['total_tasks'] - $defaults['completed_tasks'] - $defaults['in_progress_tasks'],
                    0
                );
            }

            if (Schema::hasTable('site_labor_dates') && Schema::hasColumn('site_labor_dates', 'site_id')) {
                $defaults['labor_days'] = SiteLaborDate::where('site_id', $siteId)->count();
            }

            if (Schema::hasTable('labors') && Schema::hasTable('site_labor_dates')) {
                $defaults['labor_headcount'] = Labor::whereHas(
                    'siteLaborDate',
                    static fn ($q) => $q->where('site_id', $siteId)
                )->count();
                $defaults['labor_salary_total'] = (float) Labor::whereHas(
                    'siteLaborDate',
                    static fn ($q) => $q->where('site_id', $siteId)
                )->sum('salary');
            }

            if (Schema::hasTable('contract_labors') && Schema::hasTable('site_labor_dates')) {
                $defaults['contract_labor_count'] = (int) ContractLabor::whereHas(
                    'siteLaborDate',
                    static fn ($q) => $q->where('site_id', $siteId)
                )->sum('count');
            }

            if (Schema::hasTable('site_contracts')) {
                $defaults['contractor_count'] = SiteContract::where('site_id', $siteId)->count();
                $defaults['contract_value'] = (float) SiteContract::where('site_id', $siteId)->sum('amount');
            }

            if (Schema::hasTable('site_stocks') && Schema::hasColumn('site_stocks', 'site_id')) {
                $defaults['stock_items'] = SiteStock::where('site_id', $siteId)->count();
                $defaults['stock_quantity'] = (float) SiteStock::where('site_id', $siteId)->sum('quantity');
            }

            $defaults['stock_log_count'] = $this->countStockLogsForSite($site);

            $defaults['purchase_orders'] = $purchaseOrderIds->count();
            if (Schema::hasTable('purchase_order_details') && $purchaseOrderIds->isNotEmpty()) {
                $defaults['purchase_products'] = PurchaseOrderDetail::whereIn('purchase_order_id', $purchaseOrderIds)->count();
                $defaults['purchase_delivered'] = PurchaseOrderDetail::whereIn('purchase_order_id', $purchaseOrderIds)
                    ->where('status', 'Delivered')
                    ->count();
            }

            if (Schema::hasTable('site_stages')) {
                $defaults['stage_count'] = $site->stages()->count();
            }
        } catch (\Throwable $e) {
            Log::error('Site report summary failed', [
                'site_id' => $site->id,
                'message' => $e->getMessage(),
            ]);
        }

        return $defaults;
    }

    private function safeCompletionPercentage(Site $site): string
    {
        try {
            return $site->completion_percentage;
        } catch (\Throwable) {
            return '0%';
        }
    }

    private function purchaseOrderIdsForSite(Site $site)
    {
        if (!Schema::hasTable('purchase_orders')) {
            return collect();
        }

        if (Schema::hasColumn('purchase_orders', 'site_id')) {
            return PurchaseOrder::where('site_id', $site->id)->pluck('id');
        }

        if (Schema::hasColumn('purchase_orders', 'project_id') && $site->project_id) {
            return PurchaseOrder::where('project_id', $site->project_id)->pluck('id');
        }

        return collect();
    }

    private function countStockLogsForSite(Site $site): int
    {
        if (Schema::hasColumn('stock_logs', 'site_id')) {
            return StockLog::where('site_id', $site->id)->count();
        }

        if (Schema::hasColumn('stock_logs', 'project_id') && $site->project_id) {
            return StockLog::where('project_id', $site->project_id)->count();
        }

        return 0;
    }
}
