<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseRequest;
use App\Models\ProjectStock;
use App\Models\StockLog;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderApiController extends Controller
{
    use ApiResponse;

    /**
     * Get all purchase orders
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Only select the fields we need
            $query = PurchaseOrder::select('id', 'order_id', 'order_date', 'project_id', 'supervisor_id', 'status')
                ->with([
                    'details' => static function($query) {
                        $query->select('id', 'purchase_order_id', 'category_id', 'product_id', 'quantity', 'status', 'remarks')
                            ->with([
                                'category:id,name',
                                'product:id,name',
                                'documents'
                            ]);
                    }
                ]);

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('supervisor_id')) {
                $query->where('supervisor_id', $request->supervisor_id);
            }

            $purchaseOrders = dataFilter($query, $request);

            $purchaseOrders->getCollection()->transform(function($order) {
                $details = $order->details;
                $totalCount = $details->count();
                $deliveredCount = $details->where('status', 'Delivered')->count();
                $totalProductQuantity = $details->sum('quantity');
                $order->total_product_quantity = $totalProductQuantity;

                // Modify details to only include documents for delivered items
                $order->details->transform(static function($detail) {
                    if ($detail->status !== 'Delivered') {
                        $detail->documents = [];
                        $detail->remarks = null;
                    }

                    // Only keep necessary fields from details
                    $detail->setVisible([
                        'id',
                        'product_id',
                        'category_id',
                        'quantity',
                        'status',
                        'remarks',
                        'documents',
                        'product',
                        'category'
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
                    $order->delivery_status = "0/0";
                    $order->delivery_percentage = 0;
                }

                $order->formatted_order_date = Carbon::parse($order->order_date)->format('d-m-Y');

                return $order;
            });

            return $this->successResponse(dataFormatter($purchaseOrders), "Purchase orders fetched successfully!");
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseOrderApiController@index - ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), "Failed to fetch purchase orders", 500);
        }
    }



    /**
     * Get a specific purchase order with details
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:purchase_orders,id'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), "Validation failed", 422);
            }

            $purchaseOrder = PurchaseOrder::with([
                'supervisor:id,name',
                'project:id,name',
                'details.category:id,name',
                'details.product:id,name',
                'details.documents'
            ])->findOrFail($request->id);

            if ($purchaseOrder) {
                // Calculate delivery status
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
                    $purchaseOrder->delivery_status = "0/0";
                    $purchaseOrder->delivery_percentage = 0;
                }

                // Format date
                $purchaseOrder->formatted_order_date = Carbon::parse($purchaseOrder->order_date)->format('d-m-Y');

                // Format each purchase order detail
                $purchaseOrder->details->transform(function($detail) {
                    $detail->formatted_delivery_date = $detail->delivery_date ?
                        Carbon::parse($detail->delivery_date)->format('d-m-Y') : null;
                    return $detail;
                });
            }

            return $this->successResponse(compact('purchaseOrder'),"Purchase order fetched successfully!");
        } catch (Exception $e) {
            Log::error('Error::API@PurchaseOrderApiController@show - ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), "Failed to fetch purchase order", 500);
        }
    }

    /**
     * Mark a purchase order detail as delivered
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function markAsDelivered(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_detail_id' => 'required|exists:purchase_order_details,id',
                'remarks' => 'nullable|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:jpeg,png,jpg,pdf,doc,docx|max:10240',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), "Validation failed", 422);
            }

            DB::beginTransaction();

            $detail = PurchaseOrderDetail::findOrFail($request->order_detail_id);

            if(!$detail) {
                return $this->errorResponse(null, "Product not found!", 404);
            }

            $detail->status = 'Delivered';
            $detail->remarks = $request->remarks ?? '';
            $detail->delivery_date = Carbon::now();

            // Handle file attachments if present
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
                        '_' . now()->timestamp . '_' . random_int(1000, 9999) .
                        '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('documents', $filename, 'public');

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

            // Get the purchase order to access the project ID
            $purchaseOrder = $detail->purchaseOrder;

            // Update project stock when item is delivered
            $this->updateProjectStock(
                $purchaseOrder->project_id,
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
            return $this->successResponse(compact('detail'), "Item marked as delivered successfully!");
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error::API@PurchaseOrderApiController@markAsDelivered - ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), "Failed to mark item as delivered", 500);
        }
    }

    /**
     * Update project stock - add new stock or update existing
     *
     * @param int $projectId
     * @param int $productId
     * @param int $categoryId
     * @param float $quantity
     * @param int $updatedBy
     * @param string $transactionType
     * @param string $remarks
     * @return void
     */
    private function updateProjectStock($projectId, $productId, $categoryId, $quantity, $updatedBy, $transactionType, $remarks = ""): void
    {
        $stock = ProjectStock::where('project_id', $projectId)->where('product_id', $productId)->first();
        $previousQuantity = 0;

        if ($stock) {
            $previousQuantity = $stock->quantity;
            // Update existing stock
            $stock->quantity += $quantity;
            $stock->last_updated_by = $updatedBy;
            $stock->last_transaction_type = $transactionType;
            $stock->save();
        } else {
            // Create new stock record
            $stock = ProjectStock::create([
                'project_id' => $projectId,
                'product_id' => $productId,
                'category_id' => $categoryId,
                'quantity' => $quantity,
                'last_updated_by' => $updatedBy,
                'last_transaction_type' => $transactionType
            ]);
        }

        StockLog::create([
            'project_id' => $projectId,
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
