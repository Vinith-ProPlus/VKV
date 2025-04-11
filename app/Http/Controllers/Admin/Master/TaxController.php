<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaxController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('View Tax');

        if ($request->ajax()) {
            $data = Tax::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })
                ->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('taxes.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('taxes.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('taxes.destroy', $data->id) . '\')" class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.master.taxes.index');
    }

    public function create()
    {
        $this->authorize('Create Tax');
        return view('admin.master.taxes.data', ['tax' => '']);
    }

    public function store(Request $request)
    {
        $this->authorize('Create Tax');

        $request->validate([
            'name' => 'required|unique:taxes,name',
            'percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        Tax::create($request->all());

        return redirect()->route('taxes.index')->with('success', 'Tax created successfully.');
    }

    public function edit(Tax $tax)
    {
        $this->authorize('Edit Tax');
        return view('admin.master.taxes.data', compact('tax'));
    }

    public function update(Request $request, Tax $tax)
    {
        $this->authorize('Edit Tax');

        $request->validate([
            'name' => 'required|unique:taxes,name,' . $tax->id,
            'percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'required|boolean',
        ]);

        $tax->update($request->all());

        return redirect()->route('taxes.index')->with('success', 'Tax updated successfully.');
    }

    public function destroy($id)
    {
        $this->authorize('Delete Tax');

        $tax = Tax::findOrFail($id);
        $tax->delete();

        return response(['status' => 'warning', 'message' => 'Tax deleted successfully!']);
    }

    public function restore($id)
    {
        $this->authorize('Restore Tax');

        Tax::withTrashed()->findOrFail($id)->restore();

        return response(['status' => 'success', 'message' => 'Tax restored successfully!']);
    }
}
