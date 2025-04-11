<?php

namespace App\Http\Controllers\Admin\ProjectReports;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Admin\ManageProjects\ProjectStage;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\ProjectContract;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\Admin\Labor\ProjectLaborDate;

class ProjectReportsController extends Controller
{
    private $projects; 

    public function __construct()
    {
        $this->projects = Project::withoutTrashed(); 
    }
    
    public function index(){
        $projects = $this->projects->get();
        return view('admin.project_reports.index', compact('projects'));
    }

    public function create(Request $request){
        $project = $this->projects->where('id',$request->input('project'))->first();
        $stages = $project->stages; 
        $contracts = $project->contracts;
        $amenities = $project->amenities; 
        return view('report', compact('project','stages','contracts','amenities'));
    }

    public function getProjectTasks(Request $request){
        $projectsTasks = ProjectTask::withoutTrashed(); 
        
        if($request->input('stage_id')){
            $projectsTasks->where('stage_id', $request->input('stage_id'));
        }

        return $projectsTasks->get();
    }

    public function tasksTableLists(Request $request)
    { 

        if ($request->ajax()) {
            $query = ProjectTask::with('project', 'stage')->withTrashed()
            
                ->when($request->get('stage_id'), static function ($q) use ($request) {
                    $q->where('stage_id', $request->stage_id);
                });

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('project_name', static function ($data) {
                    return $data->project?->name;
                })
                ->editColumn('date', static function ($data) {
                    return Carbon::parse($data->stage?->date)->format('d-m-Y');
                })
                ->editColumn('stage_name', static function ($data) {
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
            $query = ProjectContract::with('project', 'user', 'contract_type','user.city', 'user.state', 'user.role')->withTrashed()
            
                ->when($request->get('project_id'), static function ($q) use ($request) {
                    $q->where('project_id', $request->project_id);
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
                    $jsonData = htmlspecialchars(json_encode($data->user->load('city', 'state', 'roles')), ENT_QUOTES, 'UTF-8');

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
            $query = ProjectLaborDate::with(['project', 'labors', 'contractLabors'])->withTrashed();
    
            // Project
            if ($request->filled('project_id')) {
                $query->whereIn('project_id', $request->project_id);
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
                ->addColumn('project_name', fn($data) => $data->project->name ?? 'N/A')
                ->addColumn('labor_count', fn($data) => $data->labors->count())
                ->addColumn('contract_labor_count', fn($data) => $data->contractLabors->sum('count'))
                ->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    $button .= '<a href="' . route('labors.create', ['project_id' => $data->project_id, 'date' => $data->date]) . '" class="btn btn-outline-warning btn-sm m-1"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
}
