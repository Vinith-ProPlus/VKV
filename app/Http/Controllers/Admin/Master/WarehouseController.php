<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseRequest;
use App\Models\Warehouse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Warehouse');
        if ($request->ajax()) {
            $data = Warehouse::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('city_name', fn($data) => $data->city ? $data->city->name : 'N/A')
                ->editColumn('district_name', fn($data) => $data->district ? $data->district->name : 'N/A')
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('warehouses.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('warehouses.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('warehouses.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.master.warehouses.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('Create Warehouse');
        return view('admin.master.warehouses.data', ['warehouse' => '']);
    }

    public function store(WarehouseRequest $request)
    {
        $this->authorize('Create Warehouse');
        try {
            Warehouse::create($request->all());
            return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
        } catch (\Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@WarehouseController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(Warehouse $warehouse)
    {
        $this->authorize('Edit Warehouse');
        return view('admin.master.warehouses.data', compact('warehouse'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $this->authorize('Edit Warehouse');
        try {
            $warehouse->update($request->validated());
            return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
        } catch (\Exception $exception) {
            info('Error::Place@WarehouseController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $this->authorize('Delete Warehouse');
        try {
            $warehouse = Warehouse::findOrFail($id);
            $warehouse->delete();
            return response(['status' => 'warning', 'message' => 'Warehouse deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@WarehouseController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id)
    {
        $this->authorize('Restore Warehouse');
        try {
            Warehouse::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Warehouse restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@WarehouseController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
