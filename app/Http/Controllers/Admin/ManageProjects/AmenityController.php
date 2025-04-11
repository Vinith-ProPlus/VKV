<?php

namespace App\Http\Controllers\Admin\ManageProjects;

use App\Http\Controllers\Controller;
use App\Http\Requests\AmenityRequest;
use App\Models\Amenity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AmenityController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Amenities');
        if ($request->ajax()) {
            $data = Amenity::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('amenities.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('amenities.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('amenities.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.manage_projects.amenities.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('Create Amenities');
        return view('admin.manage_projects.amenities.data', ['amenity' => '']);
    }

    public function store(AmenityRequest $request)
    {
        $this->authorize('Create Amenities');
        try {
            Amenity::create($request->all());
            return redirect()->route('amenities.index')->with('success', 'Amenity created successfully.');
        } catch (\Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@AmenityController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(Amenity $amenity)
    {
        $this->authorize('Edit Amenities');
        return view('admin.manage_projects.amenities.data', compact('amenity'));
    }

    public function update(AmenityRequest $request, Amenity $amenity)
    {
        $this->authorize('Edit Amenities');
        try {
            $amenity->update($request->validated());
            return redirect()->route('amenities.index')->with('success', 'Amenity updated successfully.');
        } catch (\Exception $exception) {
            info('Error::Place@AmenityController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('Delete Amenities');
        try {
            $category = Amenity::findOrFail($id);
            $category->delete();
            return response(['status' => 'warning', 'message' => 'Amenity deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@AmenityController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function restore($id)
    {
        $this->authorize('Restore Amenities');
        try {
            Amenity::withTrashed()->findOrFail($id)->restore();
            return response(['status' => 'success', 'message' => 'Amenity restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@AmenityController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
