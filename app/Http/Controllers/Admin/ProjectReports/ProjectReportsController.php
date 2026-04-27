<?php

namespace App\Http\Controllers\Admin\ProjectReports;

use App\Http\Controllers\Controller;
use App\Models\Admin\Labor\ProjectLaborDate;
use App\Models\Admin\Labor\SiteLaborDate;
use App\Models\Admin\ManageProjects\ProjectStage;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\ManageProjects\SiteTask;
use App\Models\Project;
use App\Models\ProjectContract;
use App\Models\PurchaseOrder;
use App\Models\Site;
use App\Models\SiteContract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProjectReportsController extends Controller
{
    private $projects; 
    private $sites; 

    public function __construct()
    {
        $this->projects = Project::withoutTrashed(); 
        $this->sites = Site::withoutTrashed(); 
    }
     
    public function index(){
        $projects = $this->projects->with('sites')->get();
        return view('admin.project_reports.index', compact('projects'));
    }

    public function create(Request $request){
        $site = $this->sites->where('id',$request->input('site'))->with(['project','stages','contracts','engineer','siteLeadMapping.lead'])->first();
        $project = $site->project ?? '';
        $stages = $site->stages ?? ''; 
        $contracts = $site->contracts ?? '';
        $amenities = $site->project?->amenities ?? '';
        logger('stages --'.$stages); 
        return view('report', compact('site', 'project','stages','contracts','amenities'));
    }

    public function getProjectTasks(Request $request){
        $siteTasks = SiteTask::withoutTrashed(); 
        
        if($request->input('stage_id')){
            $siteTasks->where('stage_id', $request->input('stage_id'));
        }

        return $siteTasks->get();
    }

    public function tasksTableLists(Request $request)
    { 

        if ($request->ajax()) {
            $query = SiteTask::with('site', 'stage')->withTrashed()
            
                ->when($request->get('stage_id'), static function ($q) use ($request) {
                    $q->where('stage_id', $request->stage_id);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('site_name', static function ($data) {
                    return $data->site?->site_no;
                })
                ->addColumn('date', static function ($data) {
                    return Carbon::parse($data->stage?->date)->format('d-m-Y');
                })
                ->addColumn('stage_name', static function ($data) {
                    return $data->stage?->name;
                })
                ->editColumn('status', static function ($data) {
                    return $data->status;
                })
                ->addColumn('action', static function ($data) {
                    $jsonData = htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
    
                    $button  = '<div class="d-flex justify-content-center">';
                    $button .= '<a class="btn btn-outline-warning btnTaskView" data-tdata="' . $jsonData . '" id="openModal"><i class="fa fa-eye"></i></a>';
                    // if ($data->deleted_at) {
                    //     $button .= '<a onclick="commonRestore(\'' . route('project_tasks.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    // } else {
                    //     $button .= '<a href="' . route('project_tasks.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    //     $button .= '<a onclick="commonDelete(\'' . route('project_tasks.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    // }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

    }

    public function contractsTableLists(Request $request)
    { 

        if ($request->ajax()) {
            $query = SiteContract::with('site.project', 'user', 'contract_type','user.area', 'user.state', 'user.role')->withTrashed()
            
                ->when($request->get('site_id'), static function ($q) use ($request) {
                    $q->where('site_id', $request->site_id);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('contract_type_id', static function ($data) {
                    return $data->contract_type?->name;
                })
                ->editColumn('user_id', static function ($data) {
                    return $data->user?->name;
                })
                ->editColumn('amount', static function ($data) {
                    return $data->amount;
                }) 
                ->addColumn('action', static function ($data) {
                    $jsonData = htmlspecialchars(json_encode($data->user->load('area', 'state', 'roles')), ENT_QUOTES, 'UTF-8');

                    $button  = '<div class="d-flex justify-content-center">';
                    $button .= '<a class="btn btn-outline-warning btnTaskView" data-tdata="' . $jsonData . '" id="openContractsModal"><i class="fa fa-eye"></i></a>';
                  
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

    }


    public function laborTableList(Request $request)
    { 
        if ($request->ajax()) {
            $query = SiteLaborDate::with(['site', 'site.project', 'labors', 'contractLabors'])->withTrashed();
    
            // Project
            if ($request->filled('project_id')) {
                $query->whereHas('site', function ($q) use ($request) {
                    $q->whereIn('project_id', $request->project_id);
                });
            }
    
            // From and To Date
            if ($request->filled('from_date')) {
                $query->whereDate('date', '>=', $request->from_date);
            }
    
            if ($request->filled('to_date')) {
                $query->whereDate('date', '<=', $request->to_date);
            }
    
            // paid_status on related labors
            if ($request->filled('paid_status')) {
                $query->whereHas('labors', function ($q) use ($request) {
                    $q->where('paid_status', $request->paid_status);
                });
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('site_name', fn($data) => $data->site->site_no ?? 'N/A')
                ->addColumn('labor_count', fn($data) => $data->labors->count())
                ->addColumn('contract_labor_count', fn($data) => $data->contractLabors->sum('count'))
                ->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    $button .= '<a href="' . route('labors.create', ['site_id' => $data->site_id, 'date' => $data->date]) . '" class="btn btn-outline-warning btn-sm m-1"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function purchaseTableList(Request $request)
    {
        logger($request->project_id);
        if ($request->ajax()) {
            $data = PurchaseOrder::with(['supervisor', 'project', 'details'])->latest();

            if ($request->filled('project_id')) {
                $data->whereHas('site', function ($q) use ($request) {
                    $q->whereIn('project_id', $request->project_id);
                });
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('order_date', static fn($data): string => Carbon::parse($data->order_date)->format('d-m-Y'))
                ->addColumn('product_count', static fn($data) => $data->details->count())
                ->editColumn('status', static function ($data) {
                    $deliveredCount = $data->details->where('status', 'Delivered')->count();
                    $total = $data->details->count();
                    $badgeClass = $deliveredCount === $total ? 'success' : 'warning';
                    return '<span class="badge bg-' . $badgeClass . '">' . $deliveredCount . '/' . $total . ' Delivered</span>';
                })
                ->addColumn('action', static function ($data) {
                    return '<div class="d-flex justify-content-center">
                        <a href="' . route('purchase-orders.show', $data->id) . '" class="btn btn-outline-success btn-sm m-1">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }
    }

    public function getSitesByProject(Request $request){
        $sites = $this->sites;

        if($request->filled('project_id')){
            $sites->where('project_id', $request->project_id);
        }

        return response()->json($sites->get());
    }
}
