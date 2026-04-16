<?php

namespace App\Http\Controllers\Admin\CRM;

use App\Models\Lead;
use App\Models\User;
use App\Models\Project;
use App\Models\Followup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class FollowupsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of followups
     */
    public function index(Request $request)
    {
        $leads = Lead::get();

        if ($request->ajax()) {
            $query = Followup::withTrashed()->with('lead', 'project', 'site');

            // Filter by lead_id if provided
            if ($request->has('lead_id') && $request->lead_id != '') {
                $query->where('customer_id', $request->lead_id);
            }

            $data = $query->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('customer_name', function ($data) {
                    $name = $data->lead->name ?? 'N/A';
                    $mobile = $data->lead->mobile_number ?? 'N/A';
                    return $name . ' (' . $mobile . ')';
                })
                ->addColumn('project_name', function ($data) {
                    return $data->project->name ?? 'N/A';
                })
                ->addColumn('site_number', function ($data) {
                    return $data->site->site_no ?? 'N/A';
                })
                ->addColumn('status_badge', function ($data) {
                    $badgeClass = match ($data->status) {
                        'new' => 'badge-primary',
                        'under followup' => 'badge-warning',
                        'visited' => 'badge-info',
                        'closed' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($data->status) . '</span>';
                })
                ->addColumn('action', function ($data) {
                    if ($data->trashed()) {
                        // Show restore button for deleted followups
                        return '<a onclick="restoreFollowup(\'' . route('followups.restore', $data->id) . '\')" class="btn btn-sm btn-success"><i class="fa fa-undo"></i></a>';
                    }
                    // Show edit and delete for active followups
                    $actions = '<a href="' . route('followups.edit', $data->id) . '" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a> ';
                    $actions .= '<a onclick="commonDelete(\'' . route('followups.destroy', $data->id) . '\')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>';
                    return $actions;
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('admin.crm.followups.index', compact('leads'));
    }

    /**
     * Show the form for creating a new followup
     *
     * @throws AuthorizationException
     */
    public function create()
    {
        $followup = null;
        $customers = Lead::get();
        $projects = Project::all();

        return view('admin.crm.followups.data', compact('followup', 'customers', 'projects'));
    }

    /**
     * Store a newly created followup in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:leads,id',
            'project_id' => 'required|exists:projects,id',
            'site_id' => 'nullable|exists:sites,id',
            'status' => 'required|in:new,under followup,visited,closed',
            'remarks' => 'nullable|string',
        ]);

        Followup::create($validated);

        return redirect()->route('followups.index')->with('success', 'Followup created successfully.');
    }

    /**
     * Show the form for editing the specified followup
     */
    public function edit(Followup $followup)
    {
        $customers = Lead::get();
        $projects = Project::all();
        $sites = $followup->project->sites ?? [];

        return view('admin.crm.followups.data', compact('followup', 'customers', 'projects', 'sites'));
    }

    /**
     * Update the specified followup in storage
     */
    public function update(Request $request, Followup $followup)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:leads,id',
            'project_id' => 'required|exists:projects,id',
            'site_id' => 'nullable|exists:sites,id',
            'status' => 'required|in:new,under followup,visited,closed',
            'remarks' => 'nullable|string',
        ]);

        $followup->update($validated);

        return redirect()->route('followups.index')->with('success', 'Followup updated successfully.');
    }

    /**
     * Remove the specified followup from storage
     *
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        Followup::find($id)->delete();

        return response(['status' => 'success', 'message' => 'Followup deleted successfully!']);
    }

    /**
     * Restore the specified followup
     *
     * @throws AuthorizationException
     */
    public function restore($id)
    {
        Followup::withTrashed()->find($id)->restore();

        return redirect()->route('followups.index')->with('success', 'Followup restored successfully.');
    }

    public function getLastFollowup(Request $req)
    {
        $followup = Followup::where('customer_id', $req->customer_id)->latest()->first();
        return response()->json(['success' => true, 'data' => $followup]);
    }
}
