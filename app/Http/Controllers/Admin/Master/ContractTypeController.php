<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractTypeRequest;
use App\Models\ContractType;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContractTypeController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $this->authorize('View Contract Type');
        if ($request->ajax()) {
            $data = ContractType::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('contract_types.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('contract_types.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('contract_types.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.master.contract_types.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Contract Type');
        return view('admin.master.contract_types.data', ['contractType' => '']);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(ContractTypeRequest $request): RedirectResponse
    {
        $this->authorize('Create Contract Type');
        DB::beginTransaction();
        try {
            ContractType::create($request->validated());
            DB::commit();
            return redirect()->route('contract_types.index')->with('success', 'Contract Type created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            info('Error::Place@ContractTypeController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(ContractType $contractType): View|Factory|Application
    {
        $this->authorize('Edit Contract Type');
        return view('admin.master.contract_types.data', compact('contractType'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ContractTypeRequest $request, ContractType $contractType): RedirectResponse
    {
        $this->authorize('Edit Contract Type');
        DB::beginTransaction();
        try {
            $contractType->update($request->validated());
            DB::commit();
            return redirect()->route('contract_types.index')->with('success', 'Contract Type updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            info('Error::Place@ContractTypeController@update - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Contract Type');
        try {
            $category = ContractType::findOrFail($id);
            $category->delete();
            return response(['status' => 'warning', 'message' => 'Contract Type deleted Successfully!']);
        } catch (Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@ContractTypeController@destroy - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Contract Type');
        try {
            ContractType::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Contract Type restored Successfully!']);
        } catch (Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@ContractTypeController@restore - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }
}
