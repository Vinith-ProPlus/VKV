<?php

namespace App\Http\Controllers\Admin\ManageProjects;

use App\Http\Controllers\Controller;
use App\Http\Requests\SiteRequest;
use App\Models\Admin\ManageProjects\SiteStage;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Site;
use App\Models\SiteContract;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SiteController extends Controller
{
    use AuthorizesRequests;
    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Sites');
        if ($request->ajax()) {
            $data = Site::with('project')->withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('project_name', function ($data) {
                    return $data->project->name ?? '-';
                })
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })
                ->addColumn('status', function ($data) {
                    $badges = [
                        'In-progress' => 'badge-info',
                        'On-hold' => 'badge-warning text-light',
                        'Completed' => 'badge-success',
                    ];

                    if (!isset($badges[$data->status])) {
                        return '-';
                    }

                    return '<span class="badge ' . $badges[$data->status] . '">' . e($data->status) . '</span>';
                })
                ->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('sites.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('sites.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('sites.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['status', 'action'])
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
        $leads = Lead::orderBy('name')->pluck('name', 'id');
        return view('admin.manage_projects.sites.data', ['site' => '', 'leads' => $leads]);
    }
    /**
     * @throws AuthorizationException
     */
    public function store(SiteRequest $request): RedirectResponse
    {
        $this->authorize('Create Sites');
        DB::beginTransaction();
        try {
            $site = Site::create($request->all());

            // Save Stages
            $stages = $request->stages ?? [];

            foreach ($stages as $stage) {
                SiteStage::create([
                    'site_id' => $site->id,
                    'name' => $stage['name'],
                    'order_no' => $stage['order_no'],
                ]);
            }
            Document::where('module_name', 'User-Project')->where('module_id', Auth::id())
                ->update(['module_name' => 'Site', 'module_id' => $site->id]);

            $contracts = $request->contracts ?? [];

            foreach ($contracts as $contract) {
                SiteContract::updateOrCreate(
                    [
                        'site_id' => $site->id,
                        'contract_type_id' => $contract['contract_type_id'],
                        'user_id' => $contract['user_id']
                    ],
                    [
                        'amount' => $contract['amount']
                    ]
                );
            }

            DB::commit();
            return redirect()->route('sites.index')->with('success', 'Site created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
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
        return view('admin.manage_projects.sites.data', compact('site'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(SiteRequest $request, Site $site): RedirectResponse
    {
        $this->authorize('Edit Sites');
        try {
            $site_id = $site->id;
            $site->update($request->validated());

            $existingStages = SiteStage::where('site_id', $site_id)->withTrashed()->get();

            $newStages = collect($request->stages ?? []);
            $existingStageIds = $existingStages->pluck('id')->toArray();

            foreach ($newStages as $stageData) {
                $stageId = $stageData['id'] ?? null;

                if ($stageId && in_array($stageId, $existingStageIds)) {
                    $stage = $existingStages->find($stageId);
                    if ($stage && $stage->trashed()) {
                        $stage->restore();
                    }
                    if ($stage) {
                        $stage->update([
                            'name' => $stageData['name'],
                            'order_no' => $stageData['order_no'],
                        ]);
                        if (!empty($stageData['deleted'])) {
                            $stage->delete();
                        } else {
                            $stage->restore();
                        }
                    }
                } else {
                    SiteStage::create([
                        'site_id' => $site_id,
                        'name' => $stageData['name'],
                        'order_no' => $stageData['order_no'],
                    ]);
                }
            }

            $contracts = $request->contracts ?? [];

            foreach ($contracts as $contract) {
                SiteContract::updateOrCreate(
                    [
                        'site_id' => $site_id,
                        'contract_type_id' => $contract['contract_type_id'],
                        'user_id' => $contract['user_id']
                    ],
                    [
                        'amount' => $contract['amount']
                    ]
                );
            }
            // Soft delete missing stages
            foreach ($existingStages as $stage) {
                if (!$newStages->pluck('id')->contains($stage->id)) {
                    $stage->delete();
                }
            }

            return redirect()->route('sites.index')->with('success', 'Site updated successfully.');
        } catch (Exception $exception) {
            info('Error::Place@SiteController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Sites');
        try {
            $category = Site::findOrFail($id);
            $category->delete();
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
