<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\AreaRequest;
use App\Models\Admin\Master\Area;
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
class AreaController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $this->authorize('View Areas');
        if ($request->ajax()) {
        $data = Area::withTrashed()->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('is_active', static function ($data) {
                return $data->is_active ? 'Active' : 'Inactive';
            })
            ->addColumn('district_name', static fn($data) => $data->district ? $data->district->name : 'N/A')
            ->addColumn('action', static function ($data) {
                $button = '<div class="d-flex justify-content-center">';
                if ($data->deleted_at) {
                    $button = '<a onclick="commonRestore(\'' . route('areas.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                } else {
                    $button .= '<a href="' . route('areas.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    $button .= '<a onclick="commonDelete(\'' . route('areas.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                }
                $button .= '</div>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('admin.master.areas.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
     {
        $this->authorize('Create Areas');
        return view('admin.master.areas.data', ['area' => '','state'=>'']);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(AreaRequest $request): RedirectResponse
    {
        $this->authorize('Create Areas');
        try {
            Area::create($request->all());
            return redirect()->route('areas.index')->with('success', 'Area created successfully.');
        } catch (Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@AreaController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Area $area): View|Factory|Application
    {
        $this->authorize('Edit Areas');
        $state = $area->district_id ? State::find($area->district_id) : null;
        return view('admin.master.areas.data', compact('area', 'state'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(AreaRequest $request, Area $area): RedirectResponse
    {
        $this->authorize('Edit Areas');
        try {
            $area->update($request->validated());
            return redirect()->route('areas.index')->with('success', 'Area updated successfully.');
        } catch (Exception $exception) {
            info('Error::Place@AreaController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Areas');
        try {
            $category = Area::findOrFail($id);
            $category->delete();
            return response(['status' => 'success', 'message' => 'Area deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@AreaController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Areas');
        try {
            Area::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Area restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@AreaController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
