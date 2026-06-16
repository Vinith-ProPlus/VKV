<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin\Labor\SiteLaborDate;
use App\Models\ContractLabor;
use App\Models\Labor;
use App\Models\LaborReallocation;
use App\Models\SiteContract;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LaborController extends Controller
{
    use ApiResponse;

    public function getLaborDates(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|integer|exists:sites,id',
        ]);

        $siteId = $request->input('site_id');

        SiteLaborDate::firstOrCreate([
            'site_id' => $siteId,
            'date' => today()->format('Y-m-d'),
        ]);

        $query = SiteLaborDate::where('site_id', $siteId)
            ->withCount(['labors as labor_count']);
        $query = dataFilter($query, $request);
        $query->getCollection()->transform(function ($date) {
            return [
                'id' => $date->id,
                'date' => Carbon::parse($date->date)->format('d/m/Y'),
                'labor_count' => $date->labor_count,
            ];
        });

        return $this->successResponse(dataFormatter($query), 'Labor dates fetched successfully!');
    }

    public function getLaborData(Request $request): JsonResponse
    {
        $request->validate([
            'site_labor_date_id' => 'required|integer|exists:site_labor_dates,id',
        ]);

        $siteLaborDate = SiteLaborDate::with([
            'labors.labor_designation',
            'contractLabors.siteContract.user:id,name',
            'contractLabors.siteContract.contract_type:id,name',
        ])->findOrFail($request->input('site_labor_date_id'));
        $siteLaborDate->count = $siteLaborDate->labors()->count() + (int) $siteLaborDate->contractLabors()->sum('count');

        foreach ($siteLaborDate->contractLabors as $contractLabor) {
            $user = optional($contractLabor->siteContract->user)->name;
            $type = optional($contractLabor->siteContract->contract_type)->name;
            $contractLabor->contractor_name = "$user - $type";
            unset($contractLabor->siteContract);
        }

        return $this->successResponse($siteLaborDate, 'Labor data fetched successfully!');
    }

    public function getTodayLaborData(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|integer|exists:sites,id',
        ]);

        $siteLaborDate = SiteLaborDate::with(['labors.labor_designation'])
            ->where('site_id', $request->input('site_id'))
            ->where('date', now()->format('Y-m-d'))
            ->first();

        if ($siteLaborDate) {
            return $this->successResponse($siteLaborDate, 'Labor data fetched successfully!');
        }

        return $this->errorResponse([], 'Labor data not found!');
    }

    public function getLabors(Request $request): JsonResponse
    {
        if ($request->ajax()) {
            $labors = Labor::select('id', 'name')->orderBy('name')->get();

            return response()->json([
                'status' => true,
                'data' => $labors,
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid request',
        ], 400);
    }

    public function storeMultipleLabors(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'labors' => 'required|array|min:1',
                'labors.*.site_labor_date_id' => 'required|exists:site_labor_dates,id',
                'labors.*.labor_type' => 'required|in:Self,Contract',
                'labors.*.site_contract_id' => [
                    'required_if:labors.*.labor_type,Contract',
                    'nullable',
                    'exists:site_contracts,id',
                    static function ($attribute, $value, $fail) use ($request) {
                        foreach ($request->labors as $labor) {
                            if ($labor['labor_type'] === 'Contract') {
                                $exists = ContractLabor::where('site_labor_date_id', $labor['site_labor_date_id'])
                                    ->where('site_contract_id', $value)
                                    ->exists();
                                if ($exists) {
                                    $fail('This contractor is already assigned for the selected site labor date.');
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
                                $exists = Labor::where('site_labor_date_id', $labor['site_labor_date_id'])
                                    ->where('mobile', $value)
                                    ->exists();
                                if ($exists) {
                                    $fail("Mobile number {$value} is already registered for this site labor date.");
                                }
                            }
                        }
                    },
                ],
                'labors.*.labor_designation_id' => 'required_if:labors.*.labor_type,Self|exists:labor_designations,id',
                'labors.*.salary' => 'required_if:labors.*.labor_type,Self|numeric',
                'labors.*.count' => 'required_if:labors.*.labor_type,Contract|numeric|min:1',
            ]);

            $createdLabors = [];
            foreach ($request->labors as $labor) {
                if ($labor['labor_type'] === 'Self') {
                    $createdLabors[] = Labor::create([
                        'site_labor_date_id' => $labor['site_labor_date_id'],
                        'name' => $labor['name'],
                        'mobile' => $labor['mobile'],
                        'salary' => $labor['salary'],
                        'labor_designation_id' => $labor['labor_designation_id'],
                    ]);
                } else {
                    $createdLabors[] = ContractLabor::create([
                        'site_labor_date_id' => $labor['site_labor_date_id'],
                        'site_contract_id' => $labor['site_contract_id'],
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
            Log::error('Error in LaborController@storeMultipleLabors: ' . $exception->getMessage());

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
                    },
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
            Log::error('Error in LaborController@deleteLabor: ' . $exception->getMessage());

            return $this->errorResponse([], 'Something went wrong: ' . $exception->getMessage());
        }
    }

    public function getLaborsByProject(Request $request): JsonResponse
    {
        $request->validate(['site_id' => 'required|integer|exists:sites,id']);

        $siteLaborDate = SiteLaborDate::firstOrCreate([
            'site_id' => $request->input('site_id'),
            'date' => today()->format('Y-m-d'),
        ]);

        $query = Labor::where('site_labor_date_id', $siteLaborDate->id)->get();

        return $this->successResponse($query, 'Labors fetched successfully!');
    }

    public function reallocateLabors(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'from_site_id' => 'required|exists:sites,id',
                'to_site_id' => 'required|exists:sites,id',
                'labors' => 'required|array',
                'labors.*' => 'exists:labors,id',
                'remarks' => 'nullable|string',
            ]);

            $fromSiteLaborDate = SiteLaborDate::firstOrCreate([
                'site_id' => $request->from_site_id,
                'date' => today()->format('Y-m-d'),
            ]);
            $toSiteLaborDate = SiteLaborDate::firstOrCreate([
                'site_id' => $request->to_site_id,
                'date' => today()->format('Y-m-d'),
            ]);

            $labors = Labor::whereIn('id', $request->labors)->get();
            $existingLabors = Labor::where('site_labor_date_id', $toSiteLaborDate->id)
                ->whereIn('mobile', $labors->pluck('mobile'))
                ->pluck('name')
                ->toArray();

            if (!empty($existingLabors)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Some labors (' . implode(', ', $existingLabors) . ') already exist in the selected site.',
                ], 422);
            }

            foreach ($labors as $labor) {
                LaborReallocation::create([
                    'labor_id' => $labor->id,
                    'from_site_labor_date_id' => $fromSiteLaborDate->id,
                    'to_site_labor_date_id' => $toSiteLaborDate->id,
                    'remarks' => $request->remarks,
                    'reallocated_by' => Auth::id(),
                ]);
                $labor->update(['site_labor_date_id' => $toSiteLaborDate->id]);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Labors reallocated successfully.',
            ], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error('Error in reallocateLabors: ' . $exception->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $exception->getMessage(),
            ], 500);
        }
    }
}
