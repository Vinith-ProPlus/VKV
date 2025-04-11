<?php

namespace App\Http\Controllers\Admin\ManageProjects;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectTaskRequest;
use App\Models\Admin\ManageProjects\ProjectTask;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use function Laravel\Prompts\warning;

class ProjectTaskController extends Controller{
    use AuthorizesRequests;
    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Project Tasks');

        if ($request->ajax()) {
            $query = ProjectTask::with('project', 'stage')->withTrashed()
                ->when($request->get('project_id'), static function ($q) use ($request) {
                    $q->where('project_id', $request->project_id);
                })
                ->when($request->get('stage_id'), static function ($q) use ($request) {
                    $q->where('stage_id', $request->stage_id);
                })
                ->when($request->get('status'), static function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->get('date'), static function ($q) use ($request) {
                    $q->whereDate('date', $request->date); // Ensure filter_date is used correctly
                });


            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('project_name', static function ($data) {
                    return $data->project?->name;
                })
                ->editColumn('date', static function ($data) {
                    return Carbon::parse($data->stage?->date)->format('d-m-Y');
                })
                ->editColumn('stage_name', static function ($data) {
                    return $data->stage?->name;
                })
                ->editColumn('status', static function ($data) {
                    return $data->status;
                })
                ->addColumn('action', static function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('project_tasks.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('project_tasks.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('project_tasks.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.manage_projects.project_tasks.index');
    }



    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Project Tasks');
        return view('admin.manage_projects.project_tasks.data', ['project_task' => '']);
    }
    /**
     * @throws AuthorizationException
     */
    public function store(ProjectTaskRequest $request): RedirectResponse
    {
        $this->authorize('Create Project Tasks');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->input('status') == 'Completed') {
                $data['completed_at'] = now();
            }
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')?->store('project_tasks', 'public');
            }
            $data['created_by_id'] = auth()->id();
            ProjectTask::create($data);
            DB::commit();
            return redirect()->route('project_tasks.index')->with('success', 'Project Task created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            warning('Error::Place@ProjectTaskController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(ProjectTask $project_task): View|Factory|Application
    {
        $this->authorize('Edit Project Tasks');
        return view('admin.manage_projects.project_tasks.data', compact('project_task'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ProjectTaskRequest $request, ProjectTask $project_task): RedirectResponse
    {
        $this->authorize('Edit Project Tasks');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->input('status') == 'Completed') {
                $data['completed_at'] = now();
            }
            if ($request->hasFile('image')) {
                $oldImage = $project_task->image;
                $newImage = $data['image'] = $request->file('image')?->store('project_tasks', 'public');
            }
            $project_task->update($data);
            DB::commit();
            if (isset($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            return redirect()->route('project_tasks.index')->with('success', 'Project Task updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            if(isset($newImage)){
                Storage::disk('public')->delete($newImage);
            }
            info('Error::Place@ProjectTaskController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Project Tasks');
        try {
            $category = ProjectTask::findOrFail($id);
            $category->delete();
            return response(['status' => 'warning', 'message' => 'Project Task deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@ProjectTaskController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Project Tasks');
        try {
            ProjectTask::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Project Task restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@ProjectTaskController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
