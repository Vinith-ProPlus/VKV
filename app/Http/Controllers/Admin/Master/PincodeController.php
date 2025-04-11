<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\PincodeRequest;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\City;
use App\Models\Admin\Master\State;
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
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PincodeController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $this->authorize('View Pincodes');
        if ($request->ajax()) {
        $data = Pincode::withTrashed()->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('is_active', static function ($data) {
                return $data->is_active ? 'Active' : 'Inactive';
            })
            ->addColumn('city_name', static fn($data) => $data->city ? $data->city->name : 'N/A')
            ->addColumn('action', static function ($data) {
                $button = '<div class="d-flex justify-content-center">';
                if ($data->deleted_at) {
                    $button = '<a onclick="commonRestore(\'' . route('pincodes.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                } else {
                    $button .= '<a href="' . route('pincodes.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    $button .= '<a onclick="commonDelete(\'' . route('pincodes.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                }
                $button .= '</div>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('admin.master.pincodes.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Pincodes');
        return view('admin.master.pincodes.data', ['pincode' => '', 'city'=>'', 'district'=>'', 'state'=>'']);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(PincodeRequest $request): RedirectResponse
    {
        $this->authorize('Create Pincodes');
        try {
            Pincode::create($request->all());
            return redirect()->route('pincodes.index')->with('success', 'Pincode created successfully.');
        } catch (Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@PincodeController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Pincode $pincode): View|Factory|Application
    {
        $this->authorize('Edit Pincodes');

        $city = $pincode->city_id ? City::find($pincode->city_id) : null;
        $district = $city && $city->district_id ? District::find($city->district_id) : null;
        $state = $district && $district->id ? State::find($district->id) : null;

        return view('admin.master.pincodes.data', compact('pincode', 'city', 'district', 'state'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(PincodeRequest $request, Pincode $pincode): RedirectResponse
    {
        $this->authorize('Edit Pincodes');
        try {
            $pincode->update($request->validated());
            return redirect()->route('pincodes.index')->with('success', 'Pincode updated successfully.');
        } catch (Exception $exception) {
            info('Error::Place@PincodeController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return Application|Response|RedirectResponse|ResponseFactory
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Pincodes');
        try {
            $category = Pincode::findOrFail($id);
            $category->delete();
            return response(['status' => 'success', 'message' => 'Pincode deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@PincodeController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Pincodes');
        try {
            Pincode::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Pincode restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@PincodeController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
