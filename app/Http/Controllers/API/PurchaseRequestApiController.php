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

    public function index(Request $request): JsonResponse
    {
        try {
            $query = PurchaseRequest::with([
                'supervisor:id,name',
                'site:id,site_no,project_id',
                'site.project:id,name',
            ]);

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            }
            if ($request->filled('project_id')) {
                $query->whereHas('site', static fn($q) => $q->where('project_id', $request->project_id));
            }

            $purchaseRequests = dataFilter($query, $request);

            $purchaseRequests->getCollection()->transform(static function ($purchaseRequest) {
                if ($purchaseRequest->deleted_at) {
                    $purchaseRequest->status_display = 'Deleted';
                } elseif ($purchaseRequest->status === 'converted') {
                    $purchaseRequest->status_display = 'Converted to PO';
                } else {
                    $purchaseRequest->status_display = ucfirst($purchaseRequest->status);
                }

                return $purchaseRequest;
            });

            return $this->successResponse(dataFormatter($purchaseRequests), 'Purchase requests fetched successfully!');
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseRequestApiController@index - ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 'Failed to fetch purchase requests', 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:purchase_requests,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation failed', 422);
            }

            $purchaseRequest = PurchaseRequest::with([
                'supervisor:id,name',
                'site:id,site_no,project_id',
                'site.project:id,name',
                'details.category:id,name',
                'details.product:id,name',
            ])->findOrFail($request->id);

            if ($purchaseRequest->deleted_at) {
                $purchaseRequest->status_display = 'Deleted';
            } elseif ($purchaseRequest->status === 'converted') {
                $purchaseRequest->status_display = 'Converted to PO';
            } else {
                $purchaseRequest->status_display = ucfirst($purchaseRequest->status);
            }

            return $this->successResponse(compact('purchaseRequest'), 'Purchase request fetched successfully!');
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseRequestApiController@show - ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 'Failed to fetch purchase request', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'products' => 'required|array',
            'products.*.category_id' => 'required|exists:product_categories,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation failed', 422);
        }

        DB::beginTransaction();
        try {
            $purchaseRequest = PurchaseRequest::create([
                'supervisor_id' => auth()->id(),
                'site_id' => $request->site_id,
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
            $purchaseRequest->load([
                'details.category',
                'details.product',
                'site:id,site_no,project_id',
                'site.project:id,name',
                'supervisor:id,name',
            ]);

            return $this->successResponse(compact('purchaseRequest'), 'Purchase request created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error::API@PurchaseRequestApiController@store - ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 'Failed to create purchase request', 500);
        }
    }
}
