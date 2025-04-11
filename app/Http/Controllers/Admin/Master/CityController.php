<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CityRequest;
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
class CityController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $this->authorize('View Cities');
        if ($request->ajax()) {
        $data = City::withTrashed()->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('is_active', static function ($data) {
                return $data->is_active ? 'Active' : 'Inactive';
            })
            ->addColumn('district_name', static fn($data) => $data->district ? $data->district->name : 'N/A')
            ->addColumn('action', static function ($data) {
                $button = '<div class="d-flex justify-content-center">';
                if ($data->deleted_at) {
                    $button = '<a onclick="commonRestore(\'' . route('cities.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                } else {
                    $button .= '<a href="' . route('cities.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    $button .= '<a onclick="commonDelete(\'' . route('cities.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                }
                $button .= '</div>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('admin.master.cities.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
     {
        $this->authorize('Create Cities');
        return view('admin.master.cities.data', ['city' => '','state'=>'']);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(CityRequest $request): RedirectResponse
    {
        $this->authorize('Create Cities');
        try {
            City::create($request->all());
            return redirect()->route('cities.index')->with('success', 'City created successfully.');
        } catch (Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@CityController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(City $city): View|Factory|Application
    {
        $this->authorize('Edit Cities');
        $state = $city->district_id ? State::find($city->district_id) : null;
        return view('admin.master.cities.data', compact('city', 'state'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $this->authorize('Edit Cities');
        try {
            $city->update($request->validated());
            return redirect()->route('cities.index')->with('success', 'City updated successfully.');
        } catch (Exception $exception) {
            info('Error::Place@CityController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Cities');
        try {
            $category = City::findOrFail($id);
            $category->delete();
            return response(['status' => 'success', 'message' => 'City deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@CityController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Cities');
        try {
            City::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'City restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@CityController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
