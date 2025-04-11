<?php

namespace App\Http\Controllers\Admin\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadSourceRequest;
use App\Models\LeadSource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class LeadSourceController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Lead Source');

        if ($request->ajax()) {
            $data = LeadSource::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', fn($data) => $data->is_active ? 'Active' : 'Inactive')
                ->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('lead_sources.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('lead_sources.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('lead_sources.destroy', $data->id) . '\')" class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }
        return view('admin.crm.lead_sources.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('Create Lead Source');
        return view('admin.crm.lead_sources.data', ['lead_source' => '']);
    }

    public function store(LeadSourceRequest $request)
    {
        $this->authorize('Create Lead Source');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('lead_sources', 'public');
            }

            LeadSource::create($data);
            DB::commit();
            return redirect()->route('lead_sources.index')->with('success', 'Lead source created successfully.');
        } catch (\Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            info('Error::Place@LeadSourceController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(LeadSource $lead_source)
    {
        $this->authorize('Edit Lead Source');
        return view('admin.crm.lead_sources.data', compact('lead_source'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(LeadSourceRequest $request, LeadSource $lead_source)
    {
        $this->authorize('Edit Lead Source');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $oldImage = $lead_source->image;
                $newImage = $data['image'] = $request->file('image')->store('lead_sources', 'public');
            }

            $lead_source->update($data);
            DB::commit();
            if (isset($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            return redirect()->route('lead_sources.index')->with('success', 'Lead source updated successfully.');
        } catch (\Exception $exception) {
            DB::rollBack();
            if(isset($newImage)){
                Storage::disk('public')->delete($newImage);
            }
            info('Error::Place@LeadSourceController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $this->authorize('Delete Lead Source');
        try {
            $lead_source = LeadSource::findOrFail($id);
            $lead_source->delete();
            return response(['status' => 'warning', 'message' => 'Lead source deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@LeadSourceController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id)
    {
        $this->authorize('Restore Lead Source');
        try {
            LeadSource::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Lead source restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@LeadSourceController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
