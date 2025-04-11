<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PurchaseRequestController extends Controller
{
    use AuthorizesRequests;

    /**
     * @param Request $request
     * @return Factory|Application|View|JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Purchase Requests');

        if ($request->ajax()) {
            $data = PurchaseRequest::withTrashed()->with(['supervisor', 'project'])->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', static function($data) {
                    if ($data->deleted_at) {
                        return 'Deleted';
                    } elseif ($data->status === 'converted') {
                        return 'Converted to PO';
                    } else {
                        return ucfirst($data->status);
                    }
                })
                ->addColumn('action', static function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('purchase-requests.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('purchase-requests.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil"></i></a>';

                        // Only allow deletion if not converted to PO
                        if ($data->status !== 'converted') {
                            $button .= '<a onclick="commonDelete(\'' . route('purchase-requests.destroy', $data->id) . '\')" class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                        }

                        // Only show convert button if not already converted
                        if ($data->status !== 'converted') {
                            $button .= '<a href="' . route('purchase-orders.create', ['request_id' => $data->id]) . '" class="btn btn-outline-primary btn-sm m-1" title="Convert to PO"><i class="fa fa-exchange"></i></a>';
                        }
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.purchase_requests.index');
    }

    /**
     * @return View|Factory|\Illuminate\Foundation\Application
     * @throws AuthorizationException
     */
    public function create(): View|Factory|\Illuminate\Foundation\Application
    {
        $this->authorize('Create Purchase Requests');
        $projects = Project::whereNot('status', COMPLETED)->get();
        return view('admin.purchase_requests.data', ['purchaseRequest' => '', 'projects' => $projects]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'products' => 'required|array',
            'products.*.category_id' => 'required|exists:product_categories,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0',
        ]);
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

            // Check if this is a quick conversion to PO (from AJAX)
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Purchase request created successfully.']);
            }

            return redirect()->route('purchase-requests.index')
                ->with('success', 'Purchase request created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create purchase request. ' . $e->getMessage()], 422);
            }
            return redirect()->back()
                ->with('error', 'Failed to create purchase request. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * @param PurchaseRequest $purchaseRequest
     * @return View|Factory|\Illuminate\Foundation\Application
     */
    public function edit(PurchaseRequest $purchaseRequest): View|Factory|\Illuminate\Foundation\Application
    {
        $purchaseRequest->load('details.category', 'details.product', 'project', 'supervisor');
        $projects = Project::all();
        return view('admin.purchase_requests.data', compact('purchaseRequest', 'projects'));
    }

    /**
     * @param Request $request
     * @param PurchaseRequest $purchaseRequest
     * @return RedirectResponse|JsonResponse
     */
    public function update(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse|JsonResponse
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'products' => 'required|array',
            'products.*.category_id' => 'required|exists:product_categories,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0',
        ]);

        // Don't allow updates if already converted to PO
        if ($purchaseRequest->status === 'converted') {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cannot update a purchase request that has been converted to a purchase order.'], 403);
            }
            return redirect()->back()
                ->with('error', 'Cannot update a purchase request that has been converted to a purchase order.');
        }

        DB::beginTransaction();

        try {
            $purchaseRequest->update([
                'project_id' => $request->project_id,
                'product_count' => count($request->products),
                'remarks' => $request->remarks ?? null,
            ]);
            $purchaseRequest->details()->delete();
            foreach ($request->products as $product) {
                PurchaseRequestDetail::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'category_id' => $product['category_id'],
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                ]);
            }
            DB::commit();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Purchase request updated successfully.']);
            }

            return redirect()->route('purchase-requests.index')
                ->with('success', 'Purchase request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating purchase request: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update purchase request. ' . $e->getMessage()], 422);
            }
            return redirect()->back()
                ->with('error', 'Failed to update purchase request. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mark purchase request as converted to purchase order
     *
     * @param PurchaseRequest $purchaseRequest
     * @return JsonResponse
     */
    public function markAsConverted(PurchaseRequest $purchaseRequest): JsonResponse
    {
        if ($purchaseRequest->status === 'converted') {
            return response()->json(['success' => false, 'message' => 'Purchase request is already converted to a purchase order.'], 400);
        }

        try {
            $purchaseRequest->update(['status' => 'converted']);
            return response()->json(['success' => true, 'message' => 'Purchase request marked as converted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking purchase request as converted: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to convert purchase request to PO. ' . $e->getMessage()], 422);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function restore($id): JsonResponse
    {
        $this->authorize('Restore Purchase Requests');
        PurchaseRequest::withTrashed()->findOrFail($id)?->restore();
        return response()->json(['success' => 'Purchase Request restored successfully!']);
    }

    /**
     * @param $id
     * @return \Illuminate\Foundation\Application|Response|RedirectResponse|ResponseFactory
     * @throws AuthorizationException
     */
    public function destroy($id): \Illuminate\Foundation\Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Purchase Requests');
        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);
            $purchaseRequest->details()->delete();
            $purchaseRequest->delete();
            return response(['status' => 'warning', 'message' => 'Purchase Request deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@PurchaseRequestController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
