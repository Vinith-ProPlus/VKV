<?php

namespace App\Http\Controllers\Admin\Master;


use App\Http\Controllers\Controller;
use App\Http\Requests\DistrictRequest;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DistrictController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Districts');
        if ($request->ajax()) {
        $data = District::withTrashed()->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('is_active', function ($data) {
                return $data->is_active ? 'Active' : 'Inactive';
            })
            ->addColumn('state_name', fn($data) => $data->state ? $data->state->name : 'N/A')
            ->addColumn('action', function ($data) {
                $button = '<div class="d-flex justify-content-center">';
                if ($data->deleted_at) {
                    $button = '<a onclick="commonRestore(\'' . route('districts.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                } else {
                    $button .= '<a href="' . route('districts.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    $button .= '<a onclick="commonDelete(\'' . route('districts.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                }
                $button .= '</div>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('admin.master.districts.index');
    }

    public function create()
    {
        $this->authorize('Create Districts');
        return view('admin.master.districts.data', ['district' => '']);
    }

    public function store(DistrictRequest $request)
    {
        $this->authorize('Create Districts');
        try {
            District::create($request->all());
            return redirect()->route('districts.index')->with('success', 'District created successfully.');
        } catch (\Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@DistrictController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(District $district)
    {
        $this->authorize('Edit Districts');
        return view('admin.master.districts.data', compact('district'));
    }

    public function update(DistrictRequest $request, District $district)
    {
        $this->authorize('Edit Districts');
        try {
            $district->update($request->validated());
            return redirect()->route('districts.index')->with('success', 'District updated successfully.');
        } catch (\Exception $exception) {
            info('Error::Place@DistrictController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('Delete Districts');
        try {
            $category = District::findOrFail($id);
            $category->delete();
            return response(['status' => 'success', 'message' => 'District deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@DistrictController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function restore($id)
    {
        $this->authorize('Restore Districts');
        try {
            District::withTrashed()->findOrFail($id)->restore();
            return response(['status' => 'success', 'message' => 'District restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@DistrictController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
