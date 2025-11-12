<?php

namespace App\Http\Controllers\Admin\ManageProjects;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Project;
use App\Models\ProjectAmenity;
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

class ProjectController extends Controller{
    use AuthorizesRequests;
    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Projects');

        if ($request->ajax()) {
            $query = Project::all();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', static function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })
                ->addColumn('action', static function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('projects.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('projects.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('projects.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.manage_projects.projects.index');
    }



    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Projects');
        return view('admin.manage_projects.projects.data', ['project' => '']);
    }
    /**
     * @throws AuthorizationException
     */
    public function store(ProjectRequest $request): RedirectResponse
    {
        $this->authorize('Create Projects');
        try {
            $project = Project::create($request->only(['name', 'location', 'latitude', 'longitude', 'is_active']));

            $project->supervisors()->attach($request->site_supervisor_id);
            
            $amenities = $request->amenities ?? [];

            foreach ($amenities as $amenity) {
                ProjectAmenity::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'amenity_id' => $amenity['amenity_id'],
                    ],
                    [
                        'description' => $amenity['description']
                    ]
                );
            }

            return redirect()->route('projects.index')->with('success', 'Project created successfully!');
        } catch (Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@ProjectController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Project $project): View|Factory|Application
    {
        $this->authorize('Edit Projects');
        $supervisors = User::whereHas('projects', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->pluck('id')->toArray();

        return view('admin.manage_projects.projects.data', compact('project', 'supervisors'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('Edit Projects');
        try {
            $project->update($request->only(['name', 'location', 'latitude', 'longitude']));

            $project->supervisors()->sync($request->site_supervisor_id);
 
            $amenities = $request->amenities ?? [];

            foreach ($amenities as $amenity) {
                ProjectAmenity::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'amenity_id' => $amenity['amenity_id'], 
                    ],
                    [
                        'description' => $amenity['description']
                    ]
                );
            }

            if ($request->deletedAmenities) {
                $deletedIds = json_decode($request->deletedAmenities, true);
            
                if (is_array($deletedIds) && count($deletedIds) > 0) {
                    ProjectAmenity::whereIn('id', $deletedIds)->delete();
                }
            }

            return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
        } catch (Exception $exception) {
            info('Error::Place@ProjectController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Projects');
        try {
            $project = Project::findOrFail($id);
            $project->delete();
            return response(['status' => 'warning', 'message' => 'Project deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@ProjectController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Projects');
        try {
            Project::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Project restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@ProjectController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
