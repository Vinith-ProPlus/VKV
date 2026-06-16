<?php

namespace App\Http\Controllers;

use App\Models\Admin\Labor\LaborDesignation;
use App\Models\Admin\ManageProjects\ProjectStage;
use App\Models\Admin\ManageProjects\ProjectTask;
use App\Models\Admin\ManageProjects\SiteStage;
use App\Models\Admin\ManageProjects\SiteTask;
use App\Models\Admin\Master\Area;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use App\Models\Amenity;
use App\Models\ContractType;
use App\Models\Document;
use App\Models\Labor;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\MobileUserAttendance;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Site;
use App\Models\SiteContract;
use App\Models\SupportType;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class GeneralController extends Controller
{
    public function getAreas(Request $req)
    {
        $areas = Area::where('is_active', '1');

        if ($req->filled('area')) {
            $areas->where('id', $req->area);
        }

        if ($req->filled('district_id')) {
            $areas->where('district_id', $req->district_id);
        }

        return response()->json($areas->get());
    }

    public function getAreaDetails(Request $request): JsonResponse
    {
        if ($request->filled('area_id')) {
            $area = Area::find($request->area_id);
            return response()->json($area);
        }
        return response()->json(null);
    }

    public function getDistrictDetails(Request $request): JsonResponse
    {
        if ($request->filled('district_id')) {
            $district = District::find($request->district_id);
            return response()->json($district);
        }
        return response()->json(null);
    }

    public function getStates(): JsonResponse
    {
        $state = State::where('is_active', '1')->get();
        return response()->json($state);
    }

    public function getPinCodes(Request $request): JsonResponse
    {
        $pincode = Pincode::where('is_active', '1');

        if ($request->filled('pincode_id')) {
            return response()->json($pincode->where('id', $request->pincode_id)->get());
        }
        
        if ($request->filled('pincode')) {
            $pincode->where('pincode', 'LIKE', '%' . $request->pincode . '%');
        }

        if ($request->filled('area_id')) {
            $pincode->where('area_id', $request->area_id);
        }

        if ($request->filled('pincode')) {
            $pincode->limit(10);
        }
        return response()->json($pincode->get());
    }

    public function getLeadSource(): JsonResponse
    {
        return response()->json(LeadSource::where('is_active', '1')->get());
    }

    public function getLeadStatus(): JsonResponse
    {
        return response()->json(LeadStatus::where('is_active', '1')->get());
    }

    public function getUsers(): JsonResponse
    {
        return response()->json(User::where('active_status', 'Active')->get());
    }
    public function getSiteSupervisors(): JsonResponse
    {
        $supervisor_role_id = Role::where('name', SITE_SUPERVISOR_ROLE_NAME)->pluck('id')->first();

        if ($supervisor_role_id) {
            $supervisors = User::where('active_status', 'Active')
                ->whereHas('roles', function ($query) use ($supervisor_role_id) {
                    $query->where('role_id', $supervisor_role_id);
                })
                ->get();
            return response()->json($supervisors);
        }

        return response()->json([]);
    }
    public function getEngineers(): JsonResponse
    {
        $engineer_role_id = Role::where('name', ENGINEER_ROLE_NAME)->pluck('id')->first();

        if ($engineer_role_id) {
            $engineers = User::where('active_status', 'Active')
                ->whereHas('roles', static function ($query) use ($engineer_role_id) {
                    $query->where('role_id', $engineer_role_id);
                })
                ->get();
            return response()->json($engineers);
        }

        return response()->json([]);
    }

    public function getRoles(): JsonResponse
    {
        return response()->json(Role::all());
    }

    public function getProjects(): JsonResponse
    {
        return response()->json(Project::all());
    }
    public function getStages(Request $request)
    {
        if ($request->filled('SiteID')) {
            return response()->json(SiteStage::where('site_id', $request->SiteID)->get());
        }

        return response()->json([]);
    }

    public function getSites(Request $request)
    {
        $query = Site::query();
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        return response()->json($query->get());
    }

    public function getSitesByProjectID(Request $request)
    {
        $query = Site::query();

        if ($request->filled('project_id')) {
            $projectIds = $request->project_id;

            if (!is_array($projectIds)) {
                $projectIds = [$projectIds];
            }

            $query->whereIn('project_id', $projectIds);
        }

        return response()->json($query->get());
    }


    public function getSupportTypes(): JsonResponse
    {
        return response()->json(SupportType::where('is_active', 1)->get());
    }

    public function getDistricts(Request $req)
    {
        $districts = District::where('is_active', '1');

        if ($req->filled('state_id')) {
            $districts->where('state_id', $req->state_id);
        }

        return response()->json($districts->get());
    }

    public function getDocuments(Request $request): JsonResponse
    {
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

        return response()->json($documents);
    }


    public function documentHandler(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'module_name' => 'required|string',
                'module_id' => 'required|integer',
                'images.*' => allowed_document_validation(false),
            ]);

            $uploadedFiles = [];
            $title = $request->input('title', 'Untitled Document');
            $description = $request->input('description', '');

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
                        '_' . now()->timestamp . '_' . random_int(1000, 9999) .
                        '.' . $file->getClientOriginalExtension();
                    $path = store_public_upload_as($file, 'documents', $filename);

                    $document = Document::create([
                        'title' => $title,
                        'description' => $description,
                        'module_name' => $validated['module_name'],
                        'module_id' => $validated['module_id'],
                        'file_path' => $path,
                        'file_name' => $filename,
                        'uploaded_by' => auth()->id()
                    ]);

                    $uploadedFiles[] = [
                        'id' => $document->id,
                        'name' => $filename,
                        'path' => generate_file_url($path),
                        'extension' => $file->getClientOriginalExtension()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'files' => $uploadedFiles
            ]);
        } catch (\Throwable $exception) {
            Log::error("Error::Place@GeneralController@documentHandler - " . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during upload',
                'error' => $exception->getMessage()
            ], 500);
        }
    }
    public function updateDocuments(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'documentIds' => 'required',
                'title' => 'required|string',
                'description' => 'nullable|string',
                'deletedDocuments' => 'nullable',
            ]);
            $documentIds = is_array($request->documentIds) ? $request->documentIds : [$request->documentIds];
            $deletedDocuments = is_array($request->deletedDocuments) ? $request->deletedDocuments : [$request->deletedDocuments];

            Document::whereIn('id', $documentIds)->update([
                'title' => $request->input('title'),
                'description' => $request->input('description', ''),
            ]);

            if (!empty($deletedDocuments)) {
                $documents = Document::whereIn('id', $deletedDocuments)->get();
                foreach ($documents as $document) {
                    $filePath = 'project_documents/' . $document->file_name;
                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                    $document->delete();
                }
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error::GeneralController@updateDocuments - ' . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during update',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    public function deleteDocuments(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'id' => 'required',
            ]);
            $documentIds = is_array($request->id) ? $request->id : [$request->id];
            $documents = Document::whereIn('id', $documentIds)->get(['id', 'file_path']);
            if ($documents->isEmpty()) {
                return response()->json(['message' => 'No documents found to delete'], 404);
            }
            $deletedFiles = $documents->pluck('file_path')->toArray();
            Document::whereIn('id', $documentIds)->delete();
            Storage::disk('public')->delete($deletedFiles);
            DB::commit();
            return response()->json([
                'message' => 'Documents deleted successfully'
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error("Error::Place@GeneralController@deleteDocuments - " . $exception->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during delete',
                'error' => $exception->getMessage()
            ], 500);
        }
    }

    public function getContractTypes(Request $request): JsonResponse
    {
        return response()->json(ContractType::where('is_active', '1')->get());
    }

    public function getVendors(): JsonResponse
    {
        $vendor_role_id = Role::where('name', VENDOR_ROLE_NAME)->pluck('id')->first();
        return response()->json(User::where('role_id', $vendor_role_id)->get());
    }
    public function getContractors(): JsonResponse
    {
        $contractor_role_id = Role::where('name', CONTRACTOR_ROLE_NAME)->pluck('id')->first();
        return response()->json(User::where('role_id', $contractor_role_id)->get());
    }
    public function getSiteContractors(Request $request): JsonResponse
    {
        $request->validate([
            'site_id' => 'required|exists:sites,id'
        ]);
        return response()->json(SiteContract::with('user:id,name', 'contract_type:id,name')->where('site_id', $request->site_id)->get());
    }
    public function getLaborDesignations(Request $request): JsonResponse
    {
        return response()->json(LaborDesignation::whereIsActive('1')->get());
    }

    public function getAmenities(Request $request): JsonResponse
    {
        return response()->json(Amenity::where('is_active', '1')->get());
    }

    public function getAllProjects()
    {
        return Project::whereNull('deleted_at')->get();
    }

    public function getProjectTasks(Request $req)
    {
        return SiteTask::whereHas('site', function ($query) use ($req) {
            $query->where('project_id', $req->id);
        })->with('site:id,project_id,site_no', 'stage:id,name')->get();
    }

    public function getSupervisors()
    {
        return User::where('role_id', 4)->where('active_status', 'Active')->get();
    }

    public function getCheckedInSupervisors()
    {
        $checkedInUsers = MobileUserAttendance::whereDate('time', now())
            ->orderBy('time', 'desc')
            ->get()
            ->unique('user_id')
            ->filter(function ($record) {
                return $record->type === 'check_in';
            });

        return $checkedInUsers;
    }

    public function getCategories(Request $request): JsonResponse
    {
        $categories = ProductCategory::query()
            ->select('id', 'name')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function getProductsByCategory(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'required|exists:product_categories,id',
        ]);

        $products = Product::query()
            ->select('id', 'name', 'category_id', 'code')
            ->where('is_active', 1)
            ->where('category_id', $request->category_id)
            ->orderBy('name')
            ->get();

        return response()->json($products);
    }

    public function getLaborStatus(Request $request): JsonResponse
    {
        return response()->json(Labor::wherePaidStatus($request->status)->get());
    }

}
