<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectTaskRequest;
use App\Http\Requests\VisitorRequest;
use App\Models\Admin\Labor\LaborDesignation;
use App\Models\Admin\ManageProjects\ProjectStage;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\ManageProjects\SiteStage;
use App\Models\Admin\ManageProjects\SiteTask;
use App\Models\Admin\Master\Area;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use App\Models\Content;
use App\Models\Document;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\MobileUserAttendance;
use App\Models\MobileVersion;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Site;
use App\Models\SiteContract;
use App\Models\SiteStock;
use App\Models\StockLog;
use App\Models\SupportType;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\UserDeviceLocation;
use App\Models\Visitor;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockLog;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function getAreas(Request $request): JsonResponse
    {
        $query = Area::where('is_active', 1);

        $query->when($request->filled('district_id'), function ($q) use ($request) {
            $q->where('district_id', $request->district_id);
        });

        $areas = dataFilter($query, $request, ['name']);

        return $this->successResponse(dataFormatter($areas), "Areas fetched successfully!");
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

        $query->when($request->filled('area_id'), function ($q) use ($request) {
            $q->where('area_id', $request->area_id);
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
        try {
            $query = Project::with(['sites:id,project_id,site_no', 'sites.stages:id,site_id,name,order_no']);

            $projects = dataFilter($query, $request, ['name']);

            return $this->successResponse(dataFormatter($projects), 'Project fetched successfully!');
        } catch (Throwable $e) {
            return $this->apiExceptionResponse($e, 'getProjects');
        }
    }

    public function getSites(Request $request): JsonResponse
    {
        try {
            $query = Site::select('id', 'project_id', 'site_no', 'location', 'is_active');

            if ($request->filled('project_id')) {
                $projectIds = $request->project_id;
                $query->whereIn('project_id', is_array($projectIds) ? $projectIds : [$projectIds]);
            }

            $sites = dataFilter($query, $request, ['site_no', 'location']);

            return $this->successResponse(dataFormatter($sites), 'Sites fetched successfully!');
        } catch (Throwable $e) {
            return $this->apiExceptionResponse($e, 'getSites');
        }
    }

    public function getSupervisorProjects(Request $request): JsonResponse
    {
        return $this->getTaskProjects($request);
    }

    public function getTaskProjects(Request $request): JsonResponse
    {
        try {
            $query = Project::with(['sites:id,project_id,site_no', 'sites.stages:id,site_id,name,order_no'])
                ->whereHas(
                    'supervisors',
                    static fn($q) => $q->where('users.id', Auth::id())
                );

            $query = dataFilter($query, $request, ['name']);

            return $this->successResponse(dataFormatter($query), 'Projects fetched successfully!');
        } catch (Throwable $e) {
            return $this->apiExceptionResponse($e, 'getTaskProjects');
        }
    }

    public function getStages(Request $request): JsonResponse
    {
        $query = SiteStage::with(['tasks' => fn($q) => $q->whereIn('status', ['Created', 'In-progress', 'Completed']), 'site:id,project_id'])
            ->when($request->filled('project_id'), fn($q) => $q->whereHas('site', fn($s) => $s->where('project_id', $request->project_id)));

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
                'site_id' => $stage->site_id,
                'project_id' => $stage->site->project_id ?? null,
                'name' => $stage->name,
                'order_no' => $stage->order_no,
                'status' => $status,
                'completion_percentage' => $completionPercentage,
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

    public function getWarehouses(Request $request): JsonResponse
    {
        $query = Warehouse::Active();

        $query = dataFilter($query, $request, ['name']);
        return $this->successResponse(dataFormatter($query), "Warehouses fetched successfully!");
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
        $task = ProjectTask::withApiRelations()
            ->forSupervisor($userId)
            ->where('id', $request->task_id)->first();
        if ($task) {
            $task->project = $task->site?->project;
            $task->image = generate_file_url($task->image);
            $task->is_in_progress = in_array($task->status, [ON_HOLD, COMPLETED, DELETED], true) ? 0 : 1;
        }
        return $this->successResponse(compact('task'), "Task fetched successfully!");
    }

    public function getTasks(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $today = today();
            $tasks = ProjectTask::withApiRelations()
                ->forSupervisor($userId)
                ->when($request->filled('project_id'), static fn($q) => $q->forProject($request->project_id))
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
                $task->project = $task->site?->project;
                $task->image = generate_file_url($task->image);
                return $task;
            });

            return $this->successResponse(dataFormatter($tasks), 'Tasks fetched successfully!');
        } catch (Throwable $e) {
            return $this->apiExceptionResponse($e, 'getTasks');
        }
    }

    public function createProjectTask(ProjectTaskRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = store_public_upload($request->file('image'), 'project_tasks');
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
        try {
            $user = auth()->user();
            if (!$user) {
                return $this->errorResponse([], 'Unauthenticated', 401);
            }

            $user->role_name = Role::find($user->role_id)?->name ?? 'N/A';
            $user->image = generate_file_url($user->image);

            $userId = $user->id;
            $todayTasksQuery = SiteTask::query()
                ->whereHas(
                    'site.project.supervisors',
                    static fn($q) => $q->where('users.id', $userId)
                )
                ->whereDate('date', today())
                ->whereIn('status', ['Created', 'In-progress', 'Completed']);

            $today_tasks = (clone $todayTasksQuery)
                ->with(['site.project:id,name', 'stage:id,name'])
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $today_tasks->transform(static function ($today_task) {
                $today_task->image = generate_file_url($today_task->image);
                $today_task->project = $today_task->site?->project;

                return $today_task;
            });

            $total_today_task = $today_tasks->count();
            $notification_count = 0;

            $lastAttendance = MobileUserAttendance::where('user_id', $userId)
                ->whereDate('time', Carbon::today())
                ->latest('time')
                ->first();

            $check_in_status = $lastAttendance && $lastAttendance->type === 'check_in';

            return $this->successResponse(
                compact('user', 'today_tasks', 'total_today_task', 'notification_count', 'check_in_status'),
                'Home Screen data fetched successfully!'
            );
        } catch (Throwable $e) {
            return $this->apiExceptionResponse($e, 'HomeScreen');
        }
    }

    public function getVisitors(Request $request): JsonResponse
    {
        $query = Visitor::query();

        $query->when($request->filled('site_id'), function ($q) use ($request) {
            $q->where('site_id', $request->site_id);
        });
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

    public function deleteVisitor(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id' => 'required|exists:visitors,id',
            ]);

            $visitor = Visitor::findOrFail($request->id);
            $visitor->delete();
            DB::commit();
            return $this->successResponse([], "Visitor deleted successfully!");
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            warning('Error::Place@GeneralController@deleteVisitor - ' . $ErrMsg);
            return $this->errorResponse($ErrMsg, "Visitor deletion failed!", 500);
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
                            'url' => generate_file_url($document->file_path),
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

    public function getSiteContractors(Request $request): JsonResponse
    {
        $request->validate(['site_id' => 'required|exists:sites,id']);

        $contractors = SiteContract::with('user:id,name', 'contract_type:id,name')
            ->where('site_id', $request->site_id)
            ->get()
            ->map(static fn ($contractor) => [
                'id' => $contractor->id,
                'contractor_name' => $contractor->user?->name,
                'contract_type' => $contractor->contract_type?->name,
            ]);

        return $this->successResponse($contractors, 'Site contractors fetched successfully!');
    }

    public function mobile_version()
    {
        return MobileVersion::first();
    }

    public function getNotifications(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', Auth::id());
        $notifications = dataFilter($notifications, $request);
        return $this->successResponse(dataFormatter($notifications), "Notifications fetched successfully!");
    }

    public function markAsReadNotification(Request $request): JsonResponse
    {
        $notification = Notification::findOrFail($request->id);
        if($notification) {
            $notification->update(['is_read' => true]);
            return $this->successResponse($notification, "Notification marked as read successfully!");
        }
        return $this->errorResponse("", "Failed to mark this notification as marked!", 404);
    }

    public function getSiteStocks(Request $request): JsonResponse
    {
        $request->validate(['site_id' => 'required|exists:sites,id']);
        $request->merge(['per_page' => 1000000, 'sort_order' => 'asc', 'sort_by' => 'name']);
        $siteStocks = $this->getStocksBySiteId($request->site_id);

        return $this->successResponse($siteStocks, 'Site stocks fetched successfully!');
    }

    public function adjustProductStock(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|exists:site_stocks,site_id',
            'category_id' => 'required|exists:site_stocks,category_id',
            'product_id' => 'required|exists:site_stocks,product_id',
            'quantity' => 'required|numeric|min:1',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $stock = SiteStock::where('site_id', $request->site_id)->where('product_id', $request->product_id)->first();

            if (!$stock) {
                return $this->errorResponse('Stock not found for the given product and site.', '', 404);
            }

            if ($stock->quantity < $request->quantity) {
                return $this->errorResponse('Cannot subtract more than available stock.', '', 404);
            }

            $previousQuantity = $stock->quantity;
            $balanceQuantity = $previousQuantity - $request->quantity;

            $stock->quantity -= $request->quantity;
            $stock->last_updated_by = Auth::id();
            $stock->last_transaction_type = 'Taken for today use ' . $request->remarks;
            $stock->save();

            StockLog::create([
                'site_id' => $request->site_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $previousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $balanceQuantity,
                'user_id' => Auth::id(),
                'time' => now(),
                'type' => TAKEN_FOR_CONSTRUCTION,
                'remarks' => $request->remarks,
            ]);
            DB::commit();

            return $this->successResponse($stock, 'Stock updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Cannot subtract the product stock.', $e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stocksReAllocation(Request $request): JsonResponse
    {
        $request->validate([
            'from_site_id' => 'required|exists:sites,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'to_site_id' => 'required|exists:sites,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $fromSiteStock = SiteStock::with('site')->where('site_id', $request->from_site_id)
                ->where('product_id', $request->product_id)
                ->first();

            $toSiteStock = SiteStock::with('site')->where('site_id', $request->to_site_id)
                ->where('product_id', $request->product_id)
                ->first();
            $fromSiteName = $fromSiteStock?->site?->site_no ?? Site::find($request->from_site_id)?->site_no ?? 'Unknown Site';
            $toSiteName = $toSiteStock?->site?->site_no ?? Site::find($request->to_site_id)?->site_no ?? 'Unknown Site';

            if (!$fromSiteStock || $fromSiteStock->quantity < $request->quantity) {
                return $this->errorResponse('Insufficient stock available.', '', 404);
            }

            $fromPreviousQuantity = $fromSiteStock->quantity;
            $fromBalanceQuantity = $fromPreviousQuantity - $request->quantity;

            $fromSiteStock->quantity = $fromBalanceQuantity;
            $fromSiteStock->last_updated_by = Auth::id();
            $fromSiteStock->last_transaction_type = RE_ALLOCATION . ' to - ' . $toSiteName . ' by - ' . Auth::user()->name;
            $fromSiteStock->save();

            StockLog::create([
                'site_id' => $request->from_site_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $fromPreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $fromBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => RE_ALLOCATION . ' - Transfer',
                'time' => now(),
                'remarks' => 'Transferred to Site: ' . $toSiteName . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            if ($toSiteStock) {
                $toPreviousQuantity = $toSiteStock->quantity;
                $toSiteStock->quantity += $request->quantity;
            } else {
                $toPreviousQuantity = 0;
                $toSiteStock = new SiteStock();
                $toSiteStock->site_id = $request->to_site_id;
                $toSiteStock->category_id = $request->category_id;
                $toSiteStock->product_id = $request->product_id;
                $toSiteStock->quantity = $request->quantity;
            }

            $toBalanceQuantity = $toSiteStock->quantity;
            $toSiteStock->last_updated_by = Auth::id();
            $toSiteStock->last_transaction_type = RE_ALLOCATION . ' received from - ' . $fromSiteName . ' by - ' . Auth::user()->name;
            $toSiteStock->save();

            StockLog::create([
                'site_id' => $request->to_site_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $toPreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $toBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => RE_ALLOCATION . ' - Received',
                'time' => now(),
                'remarks' => 'Received from Site: ' . $fromSiteName . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            DB::commit();
            $from_site_stocks = $this->getStocksBySiteId($request->from_site_id);
            $to_site_stocks = $this->getStocksBySiteId($request->to_site_id);

            return $this->successResponse(compact('from_site_stocks', 'to_site_stocks'), 'Stock re-allocated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Cannot re-allocate the product stock.', $e->getMessage(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function stocksReturn(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $siteStock = SiteStock::with('site')->where('site_id', $request->site_id)
                ->where('product_id', $request->product_id)
                ->first();

            $warehouse_stock = WarehouseStock::with('warehouse')->where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->first();

            $siteName = $siteStock?->site?->site_no ?? Site::find($request->site_id)?->site_no ?? 'Unknown Site';
            $warehouse_name = $warehouse_stock->warehouse->name ?? Warehouse::find($request->warehouse_id)?->name ?? 'New Warehouse';

            if (!$siteStock || $siteStock->quantity < $request->quantity) {
                return $this->errorResponse('Insufficient site stock available.', '', 404);
            }

            $sitePreviousQuantity = $siteStock->quantity;
            $siteBalanceQuantity = $sitePreviousQuantity - $request->quantity;

            $siteStock->quantity = $siteBalanceQuantity;
            $siteStock->last_updated_by = Auth::id();
            $siteStock->last_transaction_type = 'RETURN to - ' . $warehouse_name . ' by - ' . Auth::user()->name;
            $siteStock->save();

            StockLog::create([
                'site_id' => $request->site_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $sitePreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $siteBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => 'Return to Warehouse',
                'time' => now(),
                'remarks' => 'Returned to Warehouse: ' . $warehouse_name . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            if ($warehouse_stock) {
                $warehousePreviousQuantity = $warehouse_stock->quantity;
                $warehouse_stock->quantity += $request->quantity;
            } else {
                $warehousePreviousQuantity = 0;
                $warehouse_stock = new WarehouseStock();
                $warehouse_stock->warehouse_id = $request->warehouse_id;
                $warehouse_stock->category_id = $request->category_id;
                $warehouse_stock->product_id = $request->product_id;
                $warehouse_stock->quantity = $request->quantity;
            }

            $warehouseBalanceQuantity = $warehouse_stock->quantity;
            $warehouse_stock->last_updated_by = Auth::id();
            $warehouse_stock->last_transaction_type = 'RETURN received from - ' . $siteName . ' by - ' . Auth::user()->name;
            $warehouse_stock->save();

            WarehouseStockLog::create([
                'warehouse_id' => $request->warehouse_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $warehousePreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $warehouseBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => 'Site Return',
                'time' => now(),
                'remarks' => 'Received from Site: ' . $siteName . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);
            DB::commit();

            return $this->successResponse([], 'Stock successfully returned from site to warehouse.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error::GeneralController@stocksReturn - ' . $e->getMessage());

            return $this->errorResponse('Stock return failed.', $e->getMessage(), 500);
        }
    }

    public function getStocksBySiteId($site_id): mixed
    {
        return ProductCategory::Active()->select('id', 'name')->whereHas('products.siteStocks', static function ($query) use ($site_id) {
            $query->where('site_id', $site_id)->where('quantity', '>', 0);
        })->with(['products' => static function ($query) use ($site_id) {
            $query->select('id', 'name', 'image', 'category_id')->active()->whereHas('siteStocks', static function ($subQuery) use ($site_id) {
                $subQuery->where('site_id', $site_id)->where('quantity', '>', 0);
            })->with(['siteStocks' => static function ($stockQuery) use ($site_id) {
                $stockQuery->select('site_id', 'product_id', 'quantity')->where('site_id', $site_id)->where('quantity', '>', 0);
            }]);
        }])->get();
    }

    private function apiExceptionResponse(Throwable $e, string $action): JsonResponse
    {
        Log::error("{$action} failed", [
            'user_id' => auth()->id(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return $this->errorResponse(
            [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            "{$action} failed: {$e->getMessage()}",
            500
        );
    }
}
