<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectTaskRequest;
use App\Http\Requests\VisitorRequest;
use App\Models\Admin\Labor\LaborDesignation;
use App\Models\Admin\ManageProjects\ProjectStage;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\Master\City;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use App\Models\Content;
use App\Models\Document;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\MobileUserAttendance;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\ProjectContract;
use App\Models\SupportType;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserDeviceLocation;
use App\Models\Visitor;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Throwable;
use function Laravel\Prompts\warning;

class GeneralController extends Controller
{
    use ApiResponse;

    public function getCities(Request $request): JsonResponse
    {
        $query = City::where('is_active', 1);

        $query->when($request->filled('district_id'), function ($q) use ($request) {
            $q->where('district_id', $request->district_id);
        });

        $cities = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($cities), "Cities fetched successfully!");
    }

    public function getStates(Request $request): JsonResponse
    {
        $query = State::where('is_active', 1);

        $states = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($states), "States fetched successfully!");
    }

    public function getPinCodes(Request $request): JsonResponse
    {
        $query = Pincode::where('is_active', 1);

        $query->when($request->filled('city_id'), function ($q) use ($request) {
            $q->where('city_id', $request->city_id);
        });

        $pinCodes = dataFilter($query, $request, ['pincode']);

        return $this->successResponse(dataFormatter($pinCodes), "Pincodes fetched successfully!");
    }


    public function getLeadSource(Request $request): JsonResponse
    {
        $query = LeadSource::where('is_active', 1);

        $states = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($states), "Lead Source fetched successfully!");
    }

    public function getLeadStatus(Request $request): JsonResponse
    {
        $query = LeadStatus::where('is_active', 1);

        $states = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($states), "Lead Status fetched successfully!");
    }

    public function getUsers(Request $request): JsonResponse
    {
        $query = User::where('active_status', 'Active');

        $states = dataFilter($query, $request, ['name', 'email']);

        return $this->successResponse(dataFormatter($states), "User fetched successfully!");
    }

    public function getRoles(Request $request): JsonResponse
    {
        $query = Role::query();

        $roles = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($roles), "Roles fetched successfully!");
    }

    public function getProjects(Request $request): JsonResponse
    {
        $query = Project::with('stages', 'engineer:id,name', 'site:id,name');

        $roles = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($roles), "Project fetched successfully!");
    }
    public function getTaskProjects(Request $request): JsonResponse
    {
        $query = Project::with('stages', 'engineer:id,name', 'site:id,name')->whereHas('site', static function ($q) {
            $q->whereHas('supervisors', static function ($supervisorQuery) {
                $supervisorQuery->where('users.id', Auth::id());
            });
        });

        $query = dataFilter($query, $request);
        return $this->successResponse(dataFormatter($query), "Projects fetched successfully!");
    }

    public function getStages(Request $request): JsonResponse
    {
        $query = ProjectStage::with(['tasks' => fn($q) => $q->whereIn('status', ['Created', 'In-progress', 'Completed']), 'project:id,name'])
            ->when($request->filled('project_id'), fn($q) => $q->where('project_id', $request->project_id));

        $stages = dataFilter($query, $request, ['name']);

        $stages->getCollection()->transform(static function ($stage) {
            $totalTasks = $stage->tasks->count();
            $completedTasks = $stage->tasks->where('status', 'Completed')->count();
            $hasCompleted = $completedTasks > 0;
            $hasPending = $stage->tasks->whereNotIn('status', ['Completed'])->isNotEmpty();

            $status = match (true) {
                $hasCompleted && $hasPending => "In Progress",
                !$hasCompleted => "Not Started",
                default => "Completed",
            };

            $completionPercentage = ($totalTasks === 0 ? 0.0 : round(($completedTasks / $totalTasks) * 100, 2))."%";

            return [
                'id' => $stage->id,
                'project_id' => $stage->project_id,
                'project_name' => optional($stage->project)->name ?? "N/A",
                'name' => $stage->name,
                'order_no' => $stage->order_no,
                'status' => $status,
                'completion_percentage' => $completionPercentage,
//                'tasks' => $stage->tasks,
            ];
        });

        return $this->successResponse(dataFormatter($stages), "Project stages fetched successfully!");
    }

    public function getDistricts(Request $request): JsonResponse
    {
        $query = District::where('is_active', 1);

        $query->when($request->filled('state_id'), static function ($q) use ($request) {
            $q->where('state_id', $request->state_id);
        });

        $districts = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($districts), "Districts fetched successfully!");
    }

    public function getCategories(Request $request): JsonResponse
    {
        $query = ProductCategory::where('is_active', 1);

        $query = dataFilter($query, $request, ['name']);
        return $this->successResponse(dataFormatter($query), "Categories fetched successfully!");
    }

    public function getProducts(Request $request): JsonResponse
    {
        $query = Product::with('category', 'unit')->where('is_active', 1);

        $query->when($request->filled('category_id'), static function ($q) use ($request) {
            $q->where('category_id', $request->category_id);
        });

        $query = dataFilter($query, $request, ['name', 'code']);

        $query->getCollection()->transform(static function ($product) {
            $product->image = generate_file_url($product->image);
            return $product;
        });
        return $this->successResponse(dataFormatter($query), "Products fetched successfully!");
    }

    public function getTask(Request $request): JsonResponse
    {
        $user = auth()->user();
        $userId = $user->id;
        $task = ProjectTask::with('project:id,name', 'stage:id,name', 'created_by:id,name')
            ->whereHas('project.site.supervisors', static fn($q) => $q->where('users.id', $userId))
            ->where('id', $request->task_id)->first();
        if($task) {
            $task->image = generate_file_url($task->image);
            $task->is_in_progress = in_array($task->status, [ON_HOLD, COMPLETED, DELETED], true) ? 0 : 1;
        }
        return $this->successResponse(compact('task'), "Task fetched successfully!");
    }

    public function getTasks(Request $request): JsonResponse
    {
        $user = auth()->user();
        $userId = $user->id;
        $today = today();
        $tasks = ProjectTask::with('project:id,name', 'stage:id,name', 'created_by:id,name');
        $tasks->whereHas('project.site.supervisors', static fn($q) => $q->where('users.id', $userId))
            ->when($request->filled('project_id'), fn($q) => $q->where('project_id', $request->project_id))
            ->where(static function ($q) use ($today) {
                $q->where(static function ($subQuery) use ($today) {
                    $subQuery->where('date', '<', $today)
                        ->whereIn('status', ['Created', 'In-progress']);
                })->orWhere(static function ($subQuery) use ($today) {
                    $subQuery->where('date', $today)
                        ->whereIn('status', ['Created', 'In-progress', 'Completed']);
                });
            });
        $tasks = dataFilter($tasks, $request);

        $tasks->transform(static function ($task) {
            $task->image = generate_file_url($task->image);
            return $task;
        });
        return $this->successResponse(dataFormatter($tasks), "Tasks fetched successfully!");
    }

    public function createProjectTask(ProjectTaskRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')?->store('project_tasks', 'public');
            }
            $data['created_by_id'] = auth()->id();
            $task = ProjectTask::create($data);
            DB::commit();
            return $this->successResponse(compact('task'), "Task created successfully!");
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            warning('Error::Place@GeneralController@createProjectTask - ' . $ErrMsg);
            return $this->errorResponse($ErrMsg, "Task creation failed!", 500);
        }
    }

    public function updateTaskStatus(ProjectTask $task): JsonResponse
    {
        if (in_array($task->status, [ON_HOLD, COMPLETED, DELETED])) {
            return $this->errorResponse([], "Task status update failed!", 400);
        }

        DB::transaction(static function () use ($task) {
            $task->update(['status' => COMPLETED, 'completed_at' => now()]);
        });

        return $this->successResponse(compact('task'), "Task status updated successfully!");
    }


    public function HomeScreen(): JsonResponse
    {
        $user = auth()->user();
        $user->role_name = Role::find($user->role_id)->name ?? 'N/A';
        $user->image = generate_file_url($user->image);

        $userId = $user->id;
        $query = ProjectTask::with('project:id,name')
            ->whereHas('project.site.supervisors', static fn($q) => $q->where('users.id', $userId))
            ->whereDate('date', today());

        $today_tasks = (clone $query)->limit(2)->get();
        $today_tasks->transform(static function ($today_task) {
            $today_task->image = generate_file_url($today_task->image);
            return $today_task;
        });
        $total_today_task = $query->count();
        $notification_count = 0;
        // Check user's last attendance entry for today
        $lastAttendance = MobileUserAttendance::where('user_id', $userId)
            ->whereDate('time', Carbon::today())
            ->latest('time')
            ->first();

        // Determine if the user should check in or check out
        $check_in_status = !$lastAttendance || $lastAttendance->type === 'check_out';

        return $this->successResponse(
            compact('user', 'today_tasks', 'total_today_task', 'notification_count', 'check_in_status'),
            "Home Screen data fetched successfully!"
        );
    }

    public function getVisitors(Request $request): JsonResponse
    {
        $query = Visitor::query();

        $query->when($request->filled('project_id'), function ($q) use ($request) {
            $q->where('project_id', $request->project_id);
        });
        $query = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($query), "Visitors fetched successfully!");
    }

    public function getVisitor(Request $request): JsonResponse
    {
        $query = Visitor::query();

        $query->when($request->filled('visitor_id'), function ($q) use ($request) {
            $q->where('id', $request->visitor_id);
        });
        $query = dataFilter($query, $request);

        return $this->successResponse(dataFormatter($query), "Visitor fetched successfully!");
    }
    public function createVisitor(VisitorRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $visitor = Visitor::create($data);
            DB::commit();
            return $this->successResponse(compact('visitor'), "Visitor created successfully!");
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            warning('Error::Place@GeneralController@createVisitor - ' . $ErrMsg);
            return $this->errorResponse($ErrMsg, "Task creation failed!", 500);
        }
    }

    public function getContent(Request $request): JsonResponse
    {
        $query = Content::where('is_active', 1);

        $query->when($request->filled('content_id'), static function ($q) use ($request) {
            $q->where('id', $request->content_id);
        });

        $districts = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($districts), "Content fetched successfully!");
    }

    public function updateFcmToken(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'device_id' => ['required', 'string',
                    Rule::exists('user_devices', 'device_id')->where(static function ($query) {
                        $query->where('user_id', Auth::id());
                    }),
                ],
                'fcm_token' => 'required|string',
            ]);
            $device = UserDevice::where('user_id', Auth::id())->where('device_id', $request->device_id)
                ->update(['fcm_token' => $request->fcm_token]);
            DB::commit();
            return $this->successResponse($device,"Fcm Token updated successfully!");
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Error::GeneralController@updateFcmToken - ' . $exception->getMessage());
            return $this->errorResponse($exception->getMessage(), "Failed to update Fcm Token!", 500);
        }
    }

    public function recordLocationHistory(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'device_id' => ['required', 'string',
                    Rule::exists('user_devices', 'device_id')->where(static function ($query) {
                        $query->where('user_id', Auth::id());
                    }),
                ],
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);
            $user_id = Auth::id();
            $device = UserDevice::where('user_id', $user_id)->where('device_id', $request->device_id)->first();

            $device_location = UserDeviceLocation::create([
                'user_id' => $user_id,
                'user_device_id' => $device->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
            DB::commit();
            return $this->successResponse($device_location,"Location History recorded successfully!");
        } catch (Throwable $exception) {
            DB::rollBack();
            Log::error('Error::GeneralController@locationHistoryRecord - ' . $exception->getMessage());
            return $this->errorResponse($exception->getMessage(), "Failed to record location history!", 500);
        }
    }

    public function getDocuments(Request $request): JsonResponse
    {
        $request->validate([
            'module_name' => ['required', 'string', Rule::exists('documents', 'module_name')],
            'module_id' => ['required'],
        ]);
        $documents = Document::where('module_name', $request->module_name)
            ->where('module_id', $request->module_id)
            ->get()
            ->map(static function ($document) {
                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'description' => $document->description ?? '',
                    'images' => [
                        [
                            'filename' => $document->file_name,
                            'url' => asset("storage/$document->file_path"),
                        ]
                    ]
                ];
            });

        return $this->successResponse($documents, "Documents Fetched Successfully!");
    }

    public function getLaborDesignations(Request $request): JsonResponse
    {
        $query = LaborDesignation::whereIsActive('1');

        $query = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($query), "Labor Designation fetched successfully!");
    }

    public function getProjectContractors(Request $request): JsonResponse
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);

        $contractors = ProjectContract::with('user:id,name', 'contract_type:id,name')
            ->where('project_id', $request->project_id)
            ->get()
            ->map(static fn($contractor) => [
                'id' => $contractor->id,
                'contractor_name' => $contractor->user?->name,
                'contract_type' => $contractor->contract_type?->name,
            ]);

        return $this->successResponse($contractors, "Project Contractors fetched successfully!");
    }
}
