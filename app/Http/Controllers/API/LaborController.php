<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin\Labor\ProjectLaborDate;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Blog;
use App\Models\ContractLabor;
use App\Models\Document;
use App\Models\Labor;
use App\Models\LaborReallocation;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use function Laravel\Prompts\warning;

class LaborController extends Controller
{
    use ApiResponse;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLaborDates(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id'
        ]);

        $projectId = $request->input('project_id');

        ProjectLaborDate::firstOrCreate([
            'project_id' => $projectId,
            'date' => today()->format('Y-m-d'),
        ]);

        $query = ProjectLaborDate::where('project_id', $projectId)
            ->withCount(['labors as labor_count']);
        $query = dataFilter($query, $request);
        $query->getCollection()->transform(function ($date) {
            return [
                'id' => $date->id,
                'date' => Carbon::parse($date->date)->format('d/m/Y'),
                'labor_count' => $date->labor_count
            ];
        });

        return $this->successResponse(dataFormatter($query), "Labor dates fetched successfully!");
    }

    public function getLaborData(Request $request): JsonResponse
    {
        $request->validate([
            'project_labor_date_id' => 'required|integer|exists:project_labor_dates,id',
        ]);

        $projectLaborDate = ProjectLaborDate::with(['labors.labor_designation', 'contractLabors.projectContract.user:name', 'contractLabors.projectContract.contract_type'])
            ->findOrFail($request->input('project_labor_date_id'));
        $projectLaborDate->count = $projectLaborDate->labor_count + $projectLaborDate->contract_count;

        foreach ($projectLaborDate->contractLabors as $contractLabor) {
            $user = optional($contractLabor->projectContract->user)->name;
            $type = optional($contractLabor->projectContract->contract_type)->name;

            // Add formatted string
            $contractLabor->contractor_name = "$user - $type";

            // Remove original nested objects
            unset($contractLabor->projectContract->user);
            unset($contractLabor->projectContract->contract_type);
        }


        return $this->successResponse($projectLaborDate, "Labor data fetched successfully!");
    }
    public function getTodayLaborData(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
        ]);
        $projectLaborDate = ProjectLaborDate::with(['labors.labor_designation'])
            ->where('project_id', $request->input('project_id'))->where('date', now()->format('Y-m-d'))->first();
        if ($projectLaborDate) {
            return $this->successResponse($projectLaborDate, "Labor data fetched successfully!");
        }
        return $this->errorResponse([], "Labor data not found!");
    }

    public function storeMultipleLabors(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'labors' => 'required|array|min:1',
                'labors.*.project_labor_date_id' => 'required|exists:project_labor_dates,id',
                'labors.*.labor_type' => 'required|in:Self,Contract',
                'labors.*.project_contract_id' => [
                    'required_if:labors.*.labor_type,Contract',
                    'nullable',
                    static function ($attribute, $value, $fail) use ($request) {
                        foreach ($request->labors as $labor) {
                            if ($labor['labor_type'] === 'Contract') {
                                $exists = ContractLabor::where('project_labor_date_id', $labor['project_labor_date_id'])
                                    ->where('project_contract_id', $value)
                                    ->exists();
                                if ($exists) {
                                    $fail('This contractor is already assigned for the selected project labor date.');
                                }
                            }
                        }
                    },
                ],
                'labors.*.name' => 'required_if:labors.*.labor_type,Self',
                'labors.*.mobile' => [
                    'required_if:labors.*.labor_type,Self',
                    'digits:10',
                    static function ($attribute, $value, $fail) use ($request) {
                        foreach ($request->labors as $labor) {
                            if ($labor['labor_type'] === 'Self') {
                                $exists = Labor::where('project_labor_date_id', $labor['project_labor_date_id'])
                                    ->where('mobile', $value)
                                    ->exists();
                                if ($exists) {
                                    $fail("Mobile number {$value} is already registered for this project labor date.");
                                }
                            }
                        }
                    },
                ],
                'labors.*.labor_designation_id' => 'required_if:labors.*.labor_type,Self|exists:labor_designations,id',
                'labors.*.salary' => 'required_if:labors.*.labor_type,Self|numeric',
                'labors.*.count' => 'required_if:labors.*.labor_type,Contract|numeric|min:1',
            ], [
                'labors.required' => 'Labors array is required.',
                'labors.min' => 'At least one labor entry is required.',
                'labors.*.project_labor_date_id.required' => 'Project labor date is required.',
                'labors.*.project_labor_date_id.exists' => 'Invalid project labor date.',
                'labors.*.labor_type.required' => 'Labor type is required.',
                'labors.*.labor_type.in' => 'Invalid labor type.',
                'labors.*.project_contract_id.required_if' => 'Contractor is required for contract labor.',
                'labors.*.name.required_if' => 'Labor name is required for self labor.',
                'labors.*.mobile.required_if' => 'Mobile number is required for self labor.',
                'labors.*.mobile.digits' => 'Mobile number must be 10 digits.',
                'labors.*.labor_designation_id.required' => 'Designation is required.',
                'labors.*.salary.required_if' => 'Salary is required for self labor.',
                'labors.*.salary.numeric' => 'Salary must be numeric.',
                'labors.*.count.required_if' => 'Contract labor count is required.',
                'labors.*.count.numeric' => 'Count must be numeric.',
                'labors.*.count.min' => 'Contract labor count must be at least 1.',
            ]);

            $createdLabors = [];
            foreach ($request->labors as $labor) {
                if ($labor['labor_type'] === 'Self') {
                    $createdLabors[] = Labor::create([
                        'project_labor_date_id' => $labor['project_labor_date_id'],
                        'name' => $labor['name'],
                        'mobile' => $labor['mobile'],
                        'salary' => $labor['salary'],
                        'labor_designation_id' => $labor['labor_designation_id'],
                    ]);
                } else {
                    $createdLabors[] = ContractLabor::create([
                        'project_labor_date_id' => $labor['project_labor_date_id'],
                        'project_contract_id' => $labor['project_contract_id'],
                        'count' => $labor['count'],
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Labors added successfully!',
                'data' => $createdLabors,
            ]);

        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("Error in ProjectLaborDateController@storeMultipleLabors: " . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $exception->getMessage(),
            ]);
        }
    }
    public function deleteLabor(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'labor_type' => ['required', Rule::in(['Self', 'Contract'])],
                'labor_id' => [
                    'required',
                    'integer',
                    static function ($attribute, $value, $fail) use ($request) {
                        if ($request->labor_type === 'Self' && !Labor::where('id', $value)->exists()) {
                            return $fail('The selected labor ID is invalid for Self labor.');
                        }
                        if ($request->labor_type === 'Contract' && !ContractLabor::where('id', $value)->exists()) {
                            return $fail('The selected labor ID is invalid for Contract labor.');
                        }
                    }
                ],
            ]);

            $model = $request->labor_type === 'Self' ? Labor::class : ContractLabor::class;
            $labor = $model::find($request->labor_id);

            if (!$labor) {
                return $this->errorResponse([], 'Labor not found', 404);
            }

            $labor->delete();
            DB::commit();
            return $this->successResponse([], 'Labor deleted successfully');
        } catch (Exception $exception) {
            DB::rollBack();
            $error = $exception->getMessage();
            Log::error("Error in ProjectLaborDateController@deleteLabor: " .$error);
            return $this->errorResponse([], 'Something went wrong: ' . $error);
        }
    }

    public function getLaborsByProject(Request $request): JsonResponse
    {
        $request->validate(['project_id' => 'required|integer|exists:projects,id']);

        $projectId = $request->input('project_id');

        $projectLaborDate = ProjectLaborDate::firstOrCreate([
            'project_id' => $projectId,
            'date' => today()->format('Y-m-d'),
        ]);

        $query = Labor::where('project_labor_date_id', $projectLaborDate->id)->get();

        return $this->successResponse($query, "Labors fetched successfully!");

    }
    public function reallocateLabors(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'from_project_id' => 'required|exists:projects,id',
                'to_project_id' => 'required|exists:projects,id',
                'labors' => 'required|array',
                'labors.*' => 'exists:labors,id',
                'remarks' => 'nullable|string',
            ]);

            $fromProjectId = $request->from_project_id;
            $fromProjectLaborDate = ProjectLaborDate::firstOrCreate([
                'project_id' => $fromProjectId,
                'date' => today()->format('Y-m-d'),
            ]);
            $fromProjectLaborDateId = $fromProjectLaborDate->id;
            $toProjectId = $request->to_project_id;
            $toProjectLaborDate = ProjectLaborDate::firstOrCreate([
                'project_id' => $toProjectId,
                'date' => today()->format('Y-m-d'),
            ]);

            $toProjectLaborDateId = $toProjectLaborDate->id;
            $selectedLaborIds = $request->labors;
            $labors = Labor::whereIn('id', $selectedLaborIds)->get();
            $existingLabors = Labor::where('project_labor_date_id', $toProjectLaborDateId)
                ->whereIn('mobile', $labors->pluck('mobile'))
                ->pluck('name')
                ->toArray();

            if (!empty($existingLabors)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Some labors (' . implode(', ', $existingLabors) . ') already exist in the selected project.',
                ], 422);
            }

            foreach ($labors as $labor) {
                LaborReallocation::create([
                    'labor_id' => $labor->id,
                    'from_project_labor_date_id' => $fromProjectLaborDateId,
                    'to_project_labor_date_id' => $toProjectLaborDateId,
                    'remarks' => $request->remarks,
                    'reallocated_by' => Auth::id(),
                ]);
                $labor->update(['project_labor_date_id' => $toProjectLaborDateId]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Labors reallocated successfully.',
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Error in reallocateLabors: " . $exception->getMessage());
            return response()->json([
                'status' => false,
                'message' => "Something went wrong: " . $exception->getMessage(),
            ], 500);
        }
    }
}
