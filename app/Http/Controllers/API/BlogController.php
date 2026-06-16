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
                'remarks' => 'required|string|max:500',
                'site_id' => 'required|exists:sites,id',
                'stage_ids' => 'required|array|min:1',
                'stage_ids.*' => 'exists:site_stages,id',
            ]);

            $siteId = $request->site_id;
            $attachments = [];

            if ($request->hasFile('attachments')) {
                $files = is_array($request->file('attachments')) ? $request->file('attachments') : [$request->file('attachments')];
                foreach ($files as $file) {
                    $filename = generateUniqueFileName($file);
                    $path = store_public_upload_as($file, 'documents', $filename);
                    $attachments[] = [
                        'title' => 'Blog Attachment',
                        'description' => '',
                        'module_name' => 'Blog',
                        'file_path' => $path,
                        'file_name' => $filename,
                        'uploaded_by' => Auth::id(),
                    ];
                }
            }

            $blog = null;
            foreach ($request->stage_ids as $stageId) {
                $blog = Blog::create([
                    'user_id' => Auth::id(),
                    'site_id' => $siteId,
                    'site_stage_id' => $stageId,
                    'remarks' => $request->remarks,
                    'is_damaged' => $request->is_damaged ?? 0,
                ]);

                foreach ($attachments as $attachment) {
                    Document::create(array_merge($attachment, ['module_id' => $blog->id]));
                }

                Cache::forget("blog_dates:{$siteId}:{$stageId}:month");
                Cache::forget("blog_dates:{$siteId}:{$stageId}:date");
            }

            Cache::forget("blog_dates:{$siteId}::month");
            Cache::forget("blog_dates:{$siteId}::date");
            DB::commit();

            return $this->successResponse($blog, 'Blog created successfully!');
        } catch (Exception $exception) {
            DB::rollBack();
            warning('Error::Place@Api\BlogController@store - ' . $exception->getMessage());

            return $this->errorResponse($exception->getMessage(), 'Failed to create blog!', 500);
        }
    }

    public function getBlogDateMonth(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|integer|exists:sites,id',
            'stage_id' => 'nullable|integer|exists:site_stages,id',
            'type' => 'required|in:month,date',
        ]);

        $siteId = $request->input('site_id');
        $stageId = $request->input('stage_id');
        $type = $request->input('type');
        $cacheKey = "blog_dates:{$siteId}:{$stageId}:{$type}";

        $dates = Cache::remember($cacheKey, now()->addMinutes(30), static function () use ($siteId, $stageId, $type) {
            $query = Blog::where('site_id', $siteId);

            if ($stageId) {
                $query->where('site_stage_id', $stageId);
            }

            $createdAts = $query->orderBy('created_at', 'desc')->pluck('created_at');

            return $createdAts->map(function ($createdAt) use ($type) {
                $date = Carbon::parse($createdAt);

                return $type === 'month' ? $date->format('m/Y') : $date->format('d/m/Y');
            })->unique()->values();
        });

        return $this->successResponse($dates, 'Blog dates fetched successfully!');
    }

    public function getBlogData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'stage_id' => ['nullable', 'integer', 'exists:site_stages,id'],
            'is_damaged' => ['nullable', 'boolean', Rule::in([0, 1])],
            'type' => ['required', Rule::in(['month', 'date'])],
            'value' => ['required'],
        ]);

        $siteId = $validated['site_id'];
        $stageId = $request->input('stage_id');
        $isDamaged = $request->input('is_damaged');
        $type = $validated['type'];
        $value = $validated['value'];

        try {
            $query = Blog::with([
                'site:id,site_no,project_id',
                'site.project:id,name',
                'siteStage:id,name',
                'user:id,name',
                'documents',
            ])->where('site_id', $siteId);

            if ($stageId) {
                $query->where('site_stage_id', $stageId);
            }

            if ($isDamaged === 1) {
                $query->where('is_damaged', 1);
            } elseif ($isDamaged === 0) {
                $query->where('is_damaged', 0);
            }

            if ($type === 'month') {
                $monthDate = Carbon::createFromFormat('F, Y', $value, 'Asia/Kolkata')?->startOfMonth();
                $query->whereBetween('created_at', [$monthDate->copy()->startOfDay(), $monthDate->copy()->endOfMonth()->endOfDay()]);
            } elseif ($type === 'date') {
                $date = Carbon::createFromFormat('d/m/Y', $value, 'Asia/Kolkata');
                $query->whereDate('created_at', $date?->toDateString());
            }

            $blogs = $query->orderByDesc('created_at');
            $filteredData = dataFilter($blogs, $request);

            return $this->successResponse(dataFormatter($filteredData), 'Blogs fetched successfully!');
        } catch (\Exception $e) {
            Log::error('Error fetching blog data', ['error' => $e->getMessage(), 'input' => $request->all()]);

            return response()->json(['error' => 'An error occurred while fetching the blog data.'], 500);
        }
    }

    public function getCompletedTaskData(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'nullable|integer|exists:projects,id',
            'site_id' => 'nullable|integer|exists:sites,id',
            'stage_id' => 'required|integer|exists:site_stages,id',
            'type' => ['required', Rule::in(['month', 'date'])],
            'value' => 'required',
        ]);

        $userId = auth()->id();
        $type = $request->input('type');
        $value = $request->input('value');

        $query = ProjectTask::withApiRelations()
            ->forSupervisor($userId)
            ->whereNotNull('completed_at');

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('project_id')) {
            $query->forProject($request->project_id);
        }
        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        if ($type === 'month') {
            try {
                $monthDate = Carbon::createFromFormat('F, Y', $value, 'Asia/Kolkata')->startOfMonth();
                $query->whereBetween('completed_at', [
                    $monthDate->copy()->setTime(0, 0, 0),
                    $monthDate->copy()->endOfMonth()->setTime(23, 59, 59),
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid month format. Please use "Month, Year" (e.g., March, 2025).'], 422);
            }
        } elseif ($type === 'date') {
            try {
                $date = Carbon::createFromFormat('d/m/Y', $value);
                $query->whereDate('completed_at', $date->toDateString());
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use "DD/MM/YYYY" (e.g., 01/04/2025).'], 422);
            }
        }

        $tasks = $query->orderByDesc('completed_at');
        $filteredData = dataFilter($tasks, $request);

        $filteredData->transform(static function ($task) {
            $task->project = $task->site?->project;
            $task->image = generate_file_url($task->image);

            return $task;
        });

        return $this->successResponse(dataFormatter($filteredData), 'Completed tasks fetched successfully!');
    }
}
