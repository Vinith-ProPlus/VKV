<?php

namespace App\Http\Controllers\Admin\ManageProjects;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteRequest;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\ManageProjects\Site;
use App\Models\User;
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
use Yajra\DataTables\Facades\DataTables;

class SiteController extends Controller{
    use AuthorizesRequests;
    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Sites');

        if ($request->ajax()) {
            $query = Site::all();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', static function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })
                ->addColumn('action', static function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('sites.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('sites.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('sites.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.manage_projects.sites.index');
    }



    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Sites');
        return view('admin.manage_projects.sites.data', ['site' => '']);
    }
    /**
     * @throws AuthorizationException
     */
    public function store(SiteRequest $request): RedirectResponse
    {
        $this->authorize('Create Sites');
        try {
            $site = Site::create($request->only(['name', 'location', 'latitude', 'longitude', 'is_active']));

            $site->supervisors()->attach($request->site_supervisor_id);

            return redirect()->route('sites.index')->with('success', 'Site created successfully!');
        } catch (Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@SiteController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Site $site): View|Factory|Application
    {
        $this->authorize('Edit Sites');
        $supervisors = User::whereHas('sites', function ($query) use ($site) {
            $query->where('site_id', $site->id);
        })->pluck('id')->toArray();

        return view('admin.manage_projects.sites.data', compact('site', 'supervisors'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(SiteRequest $request, Site $site): RedirectResponse
    {
        $this->authorize('Edit Sites');
        try {
            $site->update($request->only(['name', 'location', 'latitude', 'longitude']));

            $site->supervisors()->sync($request->site_supervisor_id);

            return redirect()->route('sites.index')->with('success', 'Site updated successfully.');
        } catch (Exception $exception) {
            info('Error::Place@SiteController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Sites');
        try {
            $site = Site::findOrFail($id);
            $site->delete();
            return response(['status' => 'warning', 'message' => 'Site deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@SiteController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Sites');
        try {
            Site::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Site restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@SiteController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
