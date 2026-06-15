<?php

namespace App\Http\Controllers\Admin\ManageProjects;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteTaskRequest;
use App\Models\Admin\ManageProjects\SiteTask;
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

class SiteTaskController extends Controller{
    use AuthorizesRequests;
    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
    $this->authorize('View Site Tasks');

        if ($request->ajax()) {
            $query = SiteTask::with('site', 'stage')->withTrashed()
                ->when($request->get('site_id'), fn($q) => $q->where('site_id', $request->site_id))
                ->when($request->get('stage_id'), fn($q) => $q->where('stage_id', $request->stage_id))
                ->when($request->get('status'), fn($q) => $q->where('status', $request->status))
                ->when($request->get('date'), fn($q) => $q->whereDate('date', $request->date));


            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('task_name', function ($data) {
                    return $data->name;
                })
                ->editColumn('site_name', function ($data) {
                    return $data->site?->site_no;
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
                        $button .= '<a onclick="commonRestore(\'' . route('site_tasks.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('site_tasks.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('site_tasks.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

    return view('admin.manage_projects.site_tasks.index');
    }



    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
    $this->authorize('Create Site Tasks');
    return view('admin.manage_projects.site_tasks.data', ['site_task' => null]);
    }
    /**
     * @throws AuthorizationException
     */
    public function store(SiteTaskRequest $request): RedirectResponse
    {
        $this->authorize('Create Site Tasks');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->input('status') == 'Completed') {
                $data['completed_at'] = now();
            }
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')?->store('site_tasks', 'public');
            }
            $data['created_by_id'] = auth()->id();
            SiteTask::create($data);
            DB::commit();
            return redirect()->route('site_tasks.index')->with('success', 'Site Task created successfully.');
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
    public function edit(SiteTask $site_task): View|Factory|Application
    {
        $this->authorize('Edit Site Tasks');
        $site_task->load(['site','stage']);
        return view('admin.manage_projects.site_tasks.data', compact('site_task'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(SiteTaskRequest $request, SiteTask $site_task): RedirectResponse
    {
        $this->authorize('Edit Site Tasks');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->input('status') == 'Completed') {
                $data['completed_at'] = now();
            }
            if ($request->hasFile('image')) {
                $oldImage = $site_task->image;
                $newImage = $data['image'] = $request->file('image')?->store('site_tasks', 'public');
            }
            $site_task->update($data);
            DB::commit();
            if (isset($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            return redirect()->route('site_tasks.index')->with('success', 'Site Task updated successfully.');
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
        $this->authorize('Delete Site Tasks');
        try {
            $category = SiteTask::findOrFail($id);
            $category->delete();
            return response(['status' => 'warning', 'message' => 'Site Task deleted Successfully!']);
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
        $this->authorize('Restore Site Tasks');
        try {
            SiteTask::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Site Task restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@ProjectTaskController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
