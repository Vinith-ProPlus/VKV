<?php

namespace App\Http\Controllers\Admin\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class LeadController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Lead');

        if ($request->ajax()) {
            $query = Lead::with('area', 'owner')->withTrashed();
            
            // Filter by current user if not super admin
            if (!auth()->user()->hasRole('Super Admin')) {
                $query->where('lead_owner_id', auth()->id());
            } else if ($request->has('lead_owner_id') && !empty($request->lead_owner_id)) {
                // Filter by selected owners if super admin and filter is applied
                $query->whereIn('lead_owner_id', $request->lead_owner_id);
            }
            
            $data = $query->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('area_name', fn($data) => optional($data->area)->name ?? '-')
                ->editColumn('lead_owner_id', fn($data) => optional($data->owner)->name ?? '-')
                ->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center gap-2">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('leads.restore', $data->id) . '\')" class="btn btn-outline-warning btn-sm"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('leads.followups.view', $data->id) . '" class="btn btn-outline-info btn-sm" title="View Followups"><i class="fa fa-eye"></i></a>';
                        $button .= '<a href="' . route('followups.create', ['lead_id' => $data->id]) . '" class="btn btn-outline-primary btn-sm" title="Create Followup"><i class="fa fa-plus"></i></a>';
                        $button .= '<a href="' . route('leads.edit', $data->id) . '" class="btn btn-outline-success btn-sm" title="Edit Lead"><i class="fa fa-pencil"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('leads.destroy', $data->id) . '\')" class="btn btn-outline-danger btn-sm" title="Delete Lead"><i class="fa fa-trash"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        
        $isSuperAdmin = auth()->user()->hasRole('Super Admin');
        $users = $isSuperAdmin ? User::where('id', '!=', auth()->id())
            ->whereHas('roles', function($query) {
                $query->whereIn('name', ['CRM', 'crm']);
            })
            ->select('id', 'name')
            ->get() : [];
        
        return view('admin.crm.leads.index', compact('isSuperAdmin', 'users'));
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('Create Lead');
        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['CRM', 'crm']);
        })->select('id', 'name')->get();
        return view('admin.crm.leads.data', ['lead' => '', 'users' => $users]);
    }

    public function store(LeadRequest $request)
    {
        $this->authorize('Create Lead');
        DB::beginTransaction();
        try {
            $data = $request->all();
            if (!isset($data['lead_owner_id']) || empty($data['lead_owner_id'])) {
                $data['lead_owner_id'] = auth()->id();
            }
            if ($request->hasFile('image')) {
                $newImage = $data['image'] = store_public_upload($request->file('image'), 'leads');
            }
            Lead::create($data);
            DB::commit();
            return redirect()->route('leads.index')->with('success', 'Lead created successfully.');
        } catch (\Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            if(isset($newImage)){
                Storage::disk('public')->delete($newImage);
            }
            info('Error::Place@LeadController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(Lead $lead)
    {
        $this->authorize('Edit Lead');
        $users = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['CRM', 'crm']);
        })->select('id', 'name')->get();
        return view('admin.crm.leads.data', compact('lead', 'users'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(LeadRequest $request, Lead $lead)
    {
        $this->authorize('Edit Lead');
        DB::beginTransaction();
        try {
            $data = $request->all();
            if (!isset($data['lead_owner_id']) || empty($data['lead_owner_id'])) {
                $data['lead_owner_id'] = auth()->id();
            }
            $newImage = null;
            $oldImage = null;
            if ($request->hasFile('image')) {
                $oldImage = $lead->image;
                $newImage = $data['image'] = store_public_upload($request->file('image'), 'leads');

                info("Saved new image");
            }
            info($data);

            $lead->update($data);
            DB::commit();
            if ($oldImage) {
                info("deleting old image");
                Storage::disk('public')->delete($oldImage);
            }
            return redirect()->route('leads.index')->with('success', 'Lead updated successfully.');
        } catch (\Exception $exception) {
            DB::rollBack();
            if($newImage){
                info("deleting new image");
                Storage::disk('public')->delete($newImage);
            }
            info('Error::Place@LeadController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $this->authorize('Delete Lead');
        try {
            $leads = Lead::findOrFail($id);
            $leads->delete();
            return response(['status' => 'warning', 'message' => 'Lead deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@LeadController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id)
    {
        $this->authorize('Restore Lead');
        try {
            Lead::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Lead restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@LeadController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
