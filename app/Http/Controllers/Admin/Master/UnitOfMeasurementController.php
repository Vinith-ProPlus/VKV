<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitOfMeasurementRequest;
use App\Models\UnitOfMeasurement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UnitOfMeasurementController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Unit of Measurement');

        if ($request->ajax()) {
            $data = UnitOfMeasurement::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', fn($data) => $data->is_active ? 'Active' : 'Inactive')
                ->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('units.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('units.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('units.destroy', $data->id) . '\')" class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.master.unit_of_measurements.index');
    }

    public function create()
    {
        return view('admin.master.unit_of_measurements.data', ['unit' => null]);
    }

    public function store(UnitOfMeasurementRequest $request)
    {
        UnitOfMeasurement::create($request->validated());
        return redirect()->route('units.index')->with('success', 'Unit of Measurement added successfully.');
    }

    public function edit(UnitOfMeasurement $unit)
    {
        return view('admin.master.unit_of_measurements.data', compact('unit'));
    }

    public function update(UnitOfMeasurementRequest $request, UnitOfMeasurement $unit)
    {
        $unit->update($request->validated());
        return redirect()->route('units.index')->with('success', 'Unit of Measurement updated successfully.');
    }

    public function destroy(UnitOfMeasurement $unit)
    {
        $unit->delete();
        return response(['status' => 'warning', 'message' => 'Unit of Measurement deleted successfully!']);
    }

    public function restore($id)
    {
        $unit = UnitOfMeasurement::withTrashed()->findOrFail($id);
        $unit?->restore();
        return response(['status' => 'success', 'message' => 'Unit of Measurement restored successfully!']);
    }
}
