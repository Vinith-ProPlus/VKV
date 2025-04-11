<?php

namespace App\Http\Controllers\Admin\Labor;

use App\Http\Controllers\Controller;
use App\Http\Requests\LaborDesignationRequest;
use App\Models\Admin\Labor\LaborDesignation;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class LaborDesignationController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): View|Factory|Application|JsonResponse
    {
        $this->authorize('View Labor Designations');
        if ($request->ajax()) {
            $data = LaborDesignation::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('labor-designations.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('labor-designations.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('labor-designations.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.labors.designations.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): Application|Factory|View
    {
        $this->authorize('Create Labor Designations');
        return view('admin.labors.designations.data', ['labor_designation' => '']);
    }

    /**
     * @param LaborDesignationRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(LaborDesignationRequest $request)
    {
        $this->authorize('Create Labor Designations');
        DB::beginTransaction();
        try {
            LaborDesignation::create($request->all());
            DB::commit();
            return redirect()->route('labor-designations.index')->with('success', 'Labor Designation created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            info('Error::Place@LaborDesignationController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @param LaborDesignation $labor_designation
     * @return Factory|View|Application
     * @throws AuthorizationException
     */
    public function edit(LaborDesignation $labor_designation)
    {
        $this->authorize('Edit Labor Designations');
        return view('admin.labors.designations.data', compact('labor_designation'));
    }

    /**
     * @param LaborDesignationRequest $request
     * @param LaborDesignation $labor_designation
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(LaborDesignationRequest $request, LaborDesignation $labor_designation)
    {
        $this->authorize('Edit Labor Designations');
        DB::beginTransaction();
        try {
            $labor_designation->update($request->validated());
            DB::commit();
            return redirect()->route('labor-designations.index')->with('success', 'Labor Designation updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@LaborDesignationController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return ResponseFactory|Application|RedirectResponse|Response
     * @throws AuthorizationException
     */
    public function destroy($id)
    {
        $this->authorize('Delete Labor Designations');
        try {
            $category = LaborDesignation::findOrFail($id);
            $category->delete();
            return response(['status' => 'warning', 'message' => 'Labor Designation deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@LaborDesignationController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return Application|Response|RedirectResponse|ResponseFactory
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Labor Designations');
        try {
            LaborDesignation::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Labor Designation restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@LaborDesignationController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
