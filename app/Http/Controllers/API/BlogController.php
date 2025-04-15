<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Blog;
use App\Models\Document;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use function Laravel\Prompts\warning;

class BlogController extends Controller
{
    use ApiResponse;
    public function createBlog(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'remarks'      => 'required|string|max:500',
                'project_id' => 'required|exists:projects,id',
                'stage_ids' => 'required',
            ]);
            $project_id = $request->project_id;

            $attachments = [];
            if ($request->hasFile('attachments')) {
                $files = is_array($request->file('attachments')) ? $request->file('attachments') : [$request->file('attachments')];
                foreach ($files as $file) {
                    $filename = generateUniqueFileName($file);
                    $path = $file->storeAs('documents', $filename, 'public');
                    $attachments[] = [
                        'title'       => 'Blog Attachment',
                        'description' => '',
                        'module_name' => 'Blog',
                        'file_path'   => $path,
                        'file_name'   => $filename,
                        'uploaded_by' => Auth::id(),
                    ];
                }
            }

            foreach ($request->stage_ids as $stage_id) {
                $blog = Blog::create([
                    'user_id'          => Auth::id(),
                    'project_id'       => $project_id,
                    'project_stage_id' => $stage_id,
                    'remarks'          => $request->remarks,
                    'is_damaged'        => $request->is_damaged ?? 0,
                ]);

                foreach ($attachments as $attachment) {
                    Document::create(array_merge($attachment, ['module_id' => $blog->id]));
                }

                Cache::forget("blog_dates:{$project_id}:{$stage_id}:month");
                Cache::forget("blog_dates:{$project_id}:{$stage_id}:date");
            }
            Cache::forget("blog_dates:{$project_id}::month");
            Cache::forget("blog_dates:{$project_id}::date");
            DB::commit();
            return $this->successResponse($blog, "Blog created successfully!");
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            warning('Error::Place@Api\BlogController@store - ' . $ErrMsg);
            return $this->errorResponse($exception->getMessage(), "Failed to create blog!", 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBlogDateMonth(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'stage_id'   => 'nullable|integer|exists:project_stages,id',
            'type'       => 'required|in:month,date',
        ]);

        $projectId = $request->input('project_id');
        $stageId   = $request->input('stage_id');
        $type      = $request->input('type');

        $cacheKey = "blog_dates:{$projectId}:{$stageId}:{$type}";

        $dates = Cache::remember($cacheKey, now()->addMinutes(30), static function () use ($projectId, $stageId, $type) {
            $query = Blog::where('project_id', $projectId);

            if ($stageId) {
                $query->where('project_stage_id', $stageId);
            }

            $createdAts = $query->orderBy('created_at', 'desc')->pluck('created_at');

            return $createdAts->map(function ($createdAt) use ($type) {
                $date = Carbon::parse($createdAt);
                if ($type === 'month') {
                    return $date->format('m/Y');
                }
                return $date->format('d/m/Y');
            })->unique()->values();
        });

        return $this->successResponse($dates, "Blog dates fetched successfully!");
    }

    public function getBlogData(Request $request): JsonResponse
    {
        // Validate the incoming request
        $validated = $request->validate([
            'project_id'  => ['required', 'integer', 'exists:projects,id'],
            'stage_id'    => ['nullable', 'integer', 'exists:project_stages,id'],
            'is_damaged'  => ['nullable', 'integer', Rule::in([0, 1])],
            'type'        => ['required', Rule::in(['month', 'date'])],
            'value'       => ['required'],
        ], [
            'project_id.required' => 'Project ID is required.',
            'project_id.integer'  => 'Project ID must be an integer.',
            'project_id.exists'   => 'The selected project does not exist.',
            'stage_id.integer'    => 'Stage ID must be an integer.',
            'stage_id.exists'     => 'The selected stage does not exist.',
            'is_damaged.integer'  => 'Damaged flag must be 0 or 1.',
            'is_damaged.in'       => 'Invalid value for damage filter. Use 0 or 1.',
            'type.required'       => 'Type is required (month or date).',
            'type.in'             => 'Type must be either "month" or "date".',
            'value.required'      => 'Value is required based on the selected type.',
        ]);

        $projectId   = $validated['project_id'];
        $stageId     = $validated['stage_id'] ?? null;
        $isDamaged   = $validated['is_damaged'];
        $type        = $validated['type'];
        $value       = $validated['value'];

        try {
            // Start building the query
            $query = Blog::with(['project:id,name', 'stage:id,name', 'user:id,name', 'documents'])->where('project_id', $projectId);

            // Apply filters
            if ($stageId) {
                $query->where('project_stage_id', $stageId);
            }

            if ($isDamaged === 1) {
                $query->where('is_damaged', 1);
            } elseif ($isDamaged === 0) {
                $query->where('is_damaged', 0);
            }

            // Apply date/month-based filter
            if ($type === 'month') {
                Log::error($value);
                $monthDate = Carbon::createFromFormat('F, Y', $value, 'Asia/Kolkata')?->startOfMonth();
                $query->whereBetween('created_at', [$monthDate->copy()->startOfDay(), $monthDate->copy()->endOfMonth()->endOfDay()]);
            } elseif ($type === 'date') {
                $date = Carbon::createFromFormat('d/m/Y', $value, 'Asia/Kolkata');
                $query->whereDate('created_at', $date?->toDateString());
            }

            // Fetch, filter and format data
            $blogs = $query->orderByDesc('created_at');
            $filteredData = dataFilter($blogs, $request);
            return $this->successResponse(dataFormatter($filteredData), 'Blogs fetched successfully!');
        } catch (\Exception $e) {
            Log::error('Error fetching blog data', ['error'  => $e->getMessage(), 'input'  => $request->all()]);
            return response()->json(['error' => 'An error occurred while fetching the blog data.'], 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCompletedTaskData(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'stage_id'   => 'required|integer|exists:project_stages,id',
            'type'       => ['required', Rule::in(['month', 'date'])],
            'value'      => 'required',
        ]);

        $user = auth()->user();
        $userId = $user->id;
        $type = $request->input('type');
        $value = $request->input('value');

        // Start building the query for completed tasks
        $query = ProjectTask::with(['project:id,name', 'stage:id,name', 'created_by:id,name'])
            ->whereHas('project.site.supervisors', fn($q) => $q->where('users.id', $userId))
            ->whereNotNull('completed_at');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        // Filter based on type and value
        if ($type === 'month') {
            try {
                $monthDate = Carbon::createFromFormat('F, Y', $value, 'Asia/Kolkata')->startOfMonth();
                $startOfMonth = $monthDate->copy()->setTime(0, 0, 0);
                $endOfMonth = $monthDate->copy()->endOfMonth()->setTime(23, 59, 59);
                $query->whereBetween('completed_at', [$startOfMonth, $endOfMonth]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid month format. Please use "Month, Year" (e.g., March, 2025).'], 422);
            }
        } elseif ($type === 'date') {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $value);
                $formattedDate = $date->toDateString();
                $query->whereDate('completed_at', $formattedDate);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use "DD/MM/YYYY" (e.g., 01/04/2025).'], 422);
            }
        }

        $tasks = $query->orderByDesc('completed_at');

        // Apply additional data filtering if needed
        $filteredData = dataFilter($tasks, $request);

        // Add file URL conversion
        $filteredData->transform(static function ($task) {
            $task->image = generate_file_url($task->image);
            return $task;
        });

        return $this->successResponse(dataFormatter($filteredData), "Completed tasks fetched successfully!");
    }
}
