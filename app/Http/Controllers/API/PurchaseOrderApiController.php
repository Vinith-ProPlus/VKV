<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use App\Models\SiteStock;
use App\Models\StockLog;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderApiController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = PurchaseOrder::select('id', 'order_id', 'order_date', 'site_id', 'supervisor_id', 'status')
                ->with([
                    'site:id,site_no,project_id',
                    'site.project:id,name',
                    'details' => static function ($query) {
                        $query->select('id', 'purchase_order_id', 'category_id', 'product_id', 'quantity', 'status', 'remarks')
                            ->with([
                                'category:id,name',
                                'product:id,name',
                                'documents',
                            ]);
                    },
                ]);

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            }
            if ($request->filled('project_id')) {
                $query->whereHas('site', static fn($q) => $q->where('project_id', $request->project_id));
            }
            if ($request->filled('supervisor_id')) {
                $query->where('supervisor_id', $request->supervisor_id);
            }

            $purchaseOrders = dataFilter($query, $request);

            $purchaseOrders->getCollection()->transform(function ($order) {
                $details = $order->details;
                $totalCount = $details->count();
                $deliveredCount = $details->where('status', 'Delivered')->count();
                $order->total_product_quantity = $details->sum('quantity');

                $order->details->transform(static function ($detail) {
                    if ($detail->status !== 'Delivered') {
                        $detail->documents = [];
                        $detail->remarks = null;
                    }

                    $detail->setVisible([
                        'id',
                        'product_id',
                        'category_id',
                        'quantity',
                        'status',
                        'remarks',
                        'documents',
                        'product',
                        'category',
                    ]);

                    return $detail;
                });

                if ($totalCount > 0) {
                    $deliveryPercentage = ($deliveredCount / $totalCount) * 100;

                    if ($deliveryPercentage == 0) {
                        $order->status_display = 'Pending';
                    } elseif ($deliveryPercentage == 100) {
                        $order->status_display = 'Delivered';
                    } else {
                        $order->status_display = 'Partially Delivered';
                    }

                    $order->delivery_status = "$deliveredCount/$totalCount Delivered";
                    $order->delivery_percentage = $deliveryPercentage;
                } else {
                    $order->status_display = 'No Items';
                    $order->delivery_status = '0/0';
                    $order->delivery_percentage = 0;
                }

                $order->formatted_order_date = Carbon::parse($order->order_date)->format('d-m-Y');

                return $order;
            });

            return $this->successResponse(dataFormatter($purchaseOrders), 'Purchase orders fetched successfully!');
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseOrderApiController@index - ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 'Failed to fetch purchase orders', 500);
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:purchase_orders,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation failed', 422);
            }

            $purchaseOrder = PurchaseOrder::with([
                'supervisor:id,name',
                'site:id,site_no,project_id',
                'site.project:id,name',
                'details.category:id,name',
                'details.product:id,name',
                'details.documents',
            ])->findOrFail($request->id);

            $details = $purchaseOrder->details;
            $deliveredCount = $details->where('status', 'Delivered')->count();
            $totalCount = $details->count();

            if ($totalCount > 0) {
                $deliveryPercentage = ($deliveredCount / $totalCount) * 100;

                if ($deliveryPercentage == 0) {
                    $purchaseOrder->status_display = 'Pending';
                } elseif ($deliveryPercentage == 100) {
                    $purchaseOrder->status_display = 'Delivered';
                } else {
                    $purchaseOrder->status_display = 'Partially Delivered';
                }

                $purchaseOrder->delivery_status = "$deliveredCount/$totalCount Delivered";
                $purchaseOrder->delivery_percentage = $deliveryPercentage;
            } else {
                $purchaseOrder->status_display = 'No Items';
                $purchaseOrder->delivery_status = '0/0';
                $purchaseOrder->delivery_percentage = 0;
            }

            $purchaseOrder->formatted_order_date = Carbon::parse($purchaseOrder->order_date)->format('d-m-Y');

            $purchaseOrder->details->transform(function ($detail) {
                $detail->formatted_delivery_date = $detail->delivery_date
                    ? Carbon::parse($detail->delivery_date)->format('d-m-Y')
                    : null;

                return $detail;
            });

            return $this->successResponse(compact('purchaseOrder'), 'Purchase order fetched successfully!');
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseOrderApiController@show - ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 'Failed to fetch purchase order', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'site_id' => 'required|exists:sites,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.category_id' => 'required|exists:product_categories,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.rate' => 'required|numeric|min:0.01',
            'products.*.gst_applicable' => 'nullable|boolean',
            'products.*.gst_percentage' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation failed', 422);
        }

        DB::beginTransaction();
        try {
            $purchaseRequestId = $request->purchase_request_id;
            $currentUserId = Auth::id();

            if (empty($purchaseRequestId)) {
                $purchaseRequest = PurchaseRequest::create([
                    'supervisor_id' => $currentUserId,
                    'site_id' => $request->site_id,
                    'product_count' => count($request->products),
                    'remarks' => $request->remarks,
                    'status' => 'Approved',
                ]);

                foreach ($request->products as $product) {
                    PurchaseRequestDetail::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'category_id' => $product['category_id'],
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity'],
                    ]);
                }

                $purchaseRequestId = $purchaseRequest->id;
                $supervisorId = $currentUserId;
            } else {
                $purchaseRequest = PurchaseRequest::findOrFail($purchaseRequestId);
                $supervisorId = $purchaseRequest->supervisor_id;
                $purchaseRequest->status = 'Converted';
                $purchaseRequest->save();
            }

            $order = PurchaseOrder::create([
                'purchase_request_id' => $purchaseRequestId,
                'site_id' => $request->site_id,
                'supervisor_id' => $supervisorId,
                'remarks' => $request->remarks,
            ]);

            foreach ($request->products as $product) {
                $quantity = (float) $product['quantity'];
                $rate = (float) $product['rate'];
                $total = $quantity * $rate;
                $gstApplicable = !empty($product['gst_applicable']);
                $gstPercentage = $gstApplicable ? (float) ($product['gst_percentage'] ?? 0) : 0;
                $gstValue = $gstApplicable ? ($total * $gstPercentage / 100) : 0;

                PurchaseOrderDetail::create([
                    'purchase_order_id' => $order->id,
                    'category_id' => $product['category_id'],
                    'product_id' => $product['product_id'],
                    'quantity' => $quantity,
                    'rate' => $rate,
                    'gst_applicable' => $gstApplicable,
                    'gst_percentage' => $gstApplicable ? $gstPercentage : null,
                    'gst_value' => $gstValue,
                    'total_amount' => $total,
                    'total_amount_with_gst' => $total + $gstValue,
                    'status' => 'Pending',
                ]);
            }

            DB::commit();
            $order->load([
                'site:id,site_no,project_id',
                'site.project:id,name',
                'supervisor:id,name',
                'details.category:id,name',
                'details.product:id,name',
            ]);

            return $this->successResponse(compact('order'), 'Purchase order created successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error::API@PurchaseOrderApiController@store - ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 'Failed to create purchase order', 500);
        }
    }

    public function markAsDelivered(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_detail_id' => 'required|exists:purchase_order_details,id',
                'remarks' => 'nullable|string',
                'attachments' => 'nullable|array',
                'attachments.*' => allowed_document_validation(),
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation failed', 422);
            }

            DB::beginTransaction();

            $detail = PurchaseOrderDetail::findOrFail($request->order_detail_id);
            $detail->status = 'Delivered';
            $detail->remarks = $request->remarks ?? '';
            $detail->delivery_date = Carbon::now();

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
                        '_' . now()->timestamp . '_' . random_int(1000, 9999) .
                        '.' . $file->getClientOriginalExtension();
                    $path = store_public_upload_as($file, 'documents', $filename);

                    Document::create([
                        'title' => 'Purchase Order Detail Attachment',
                        'description' => $request->remarks ?? '',
                        'module_name' => 'Purchase Order Detail',
                        'module_id' => $detail->id,
                        'file_path' => $path,
                        'file_name' => $filename,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            $purchaseOrder = $detail->purchaseOrder;

            $this->updateSiteStock(
                $purchaseOrder->site_id,
                $detail->product_id,
                $detail->category_id,
                $detail->quantity,
                auth()->id(),
                PO_ITEM_DELIVERED,
                $detail->remarks
            );

            $detail->save();

            $pendingDetails = PurchaseOrderDetail::where('purchase_order_id', $purchaseOrder->id)
                ->where('status', '!=', 'Delivered')
                ->count();

            if ($pendingDetails === 0) {
                $purchaseOrder->status = 'Completed';
                $purchaseOrder->save();
            }

            DB::commit();
            $detail->load(['category', 'product', 'documents']);

            return $this->successResponse(compact('detail'), 'Item marked as delivered successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error::API@PurchaseOrderApiController@markAsDelivered - ' . $e->getMessage());

            return $this->errorResponse($e->getMessage(), 'Failed to mark item as delivered', 500);
        }
    }

    private function updateSiteStock(
        int $siteId,
        int $productId,
        int $categoryId,
        float $quantity,
        int $updatedBy,
        string $transactionType,
        string $remarks = ''
    ): void {
        $stock = SiteStock::where('site_id', $siteId)->where('product_id', $productId)->first();
        $previousQuantity = 0;

        if ($stock) {
            $previousQuantity = $stock->quantity;
            $stock->quantity += $quantity;
            $stock->last_updated_by = $updatedBy;
            $stock->last_transaction_type = $transactionType;
            $stock->save();
        } else {
            $stock = SiteStock::create([
                'site_id' => $siteId,
                'product_id' => $productId,
                'category_id' => $categoryId,
                'quantity' => $quantity,
                'last_updated_by' => $updatedBy,
                'last_transaction_type' => $transactionType,
            ]);
        }

        StockLog::create([
            'site_id' => $siteId,
            'category_id' => $categoryId,
            'product_id' => $productId,
            'previous_quantity' => $previousQuantity,
            'quantity' => $quantity,
            'balance_quantity' => $stock->quantity,
            'user_id' => $updatedBy,
            'type' => $transactionType,
            'time' => now(),
            'remarks' => $remarks,
        ]);
    }
}
