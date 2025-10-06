<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PurchaseRequestApiController extends Controller
{
    use ApiResponse;

    /**
     * Get all purchase requests
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = PurchaseRequest::with(['supervisor:id,name', 'project:id,name']);
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            $purchaseRequests = dataFilter($query, $request);

            $purchaseRequests->getCollection()->transform(static function($request) {
                if ($request->deleted_at) {
                    $request->status_display = 'Deleted';
                } elseif ($request->status === 'converted') {
                    $request->status_display = 'Converted to PO';
                } else {
                    $request->status_display = ucfirst($request->status);
                }
                return $request;
            });

            return $this->successResponse(dataFormatter($purchaseRequests),"Purchase requests fetched successfully!");
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseRequestApiController@index - ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), "Failed to fetch purchase requests", 500);
        }
    }

    /**
     * Get a specific purchase request with details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:purchase_requests,id'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), "Validation failed", 422);
            }

            $purchaseRequest = PurchaseRequest::with([
                'supervisor:id,name',
                'project:id,name',
                'details.category:id,name',
                'details.product:id,name'
            ])->findOrFail($request->id);

            if($purchaseRequest) {
                if ($purchaseRequest->deleted_at) {
                    $purchaseRequest->status_display = 'Deleted';
                } elseif ($purchaseRequest->status === 'converted') {
                    $purchaseRequest->status_display = 'Converted to PO';
                } else {
                    $purchaseRequest->status_display = ucfirst($purchaseRequest->status);
                }
            }

            return $this->successResponse(
                compact('purchaseRequest'),
                "Purchase request fetched successfully!"
            );
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseRequestApiController@show - ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), "Failed to fetch purchase request", 500);
        }
    }

    /**
     * Create a new purchase request
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'products' => 'required|array',
            'products.*.category_id' => 'required|exists:product_categories,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), "Validation failed", 422);
        }

        $supervisorId = auth()->id();
        DB::beginTransaction();
        try {
            $purchaseRequest = PurchaseRequest::create([
                'supervisor_id' => $supervisorId,
                'project_id' => $request->project_id,
                'product_count' => count($request->products),
                'status' => PENDING,
                'remarks' => $request->remarks ?? null,
            ]);

            foreach ($request->products as $product) {
                PurchaseRequestDetail::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'category_id' => $product['category_id'],
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                ]);
            }

            DB::commit();
            $purchaseRequest->load('details.category', 'details.product', 'project', 'supervisor');
            return $this->successResponse(compact('purchaseRequest'),"Purchase request created successfully!");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error::API@PurchaseRequestApiController@store - ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), "Failed to create purchase request", 500);
        }
    }
}
