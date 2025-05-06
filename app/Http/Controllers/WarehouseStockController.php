<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\ProjectStock;
use App\Models\StockLog;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseStockLog;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class WarehouseStockController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $this->authorize('View Warehouse Stocks');

        $warehouses = Warehouse::orderByDesc('created_at')->get();

        if ($request->ajax()) {
            $query = WarehouseStock::with(['warehouse', 'product', 'category']);

            if ($request->has('warehouse_id') && !empty($request->warehouse_id)) {
                $query->where('warehouse_id', $request->warehouse_id);
            }

            $data = $query->get();

            return DataTables::of($data)
                ->editColumn('quantity', static function($row) {
                    return number_format($row->quantity, 2);
                })
                ->addColumn('last_updated', static function($row) {
                    return $row->updated_at->format('d-m-Y H:i') .
                        ($row->last_transaction_type ? ' (' . $row->last_transaction_type . ')' : '');
                })
                ->make(true);
        }

        return view('admin.warehouse_stocks.index', compact('warehouses'));
    }

    /**
     * @return View|Factory|Application
     * @throws AuthorizationException
     */
    public function projectReturn(): View|Factory|Application
    {
        $this->authorize('Create Warehouse Stocks');

        $projects = Project::all();
        $warehouses = Warehouse::all();
        $categories = ProductCategory::all();

        return view('admin.warehouse_stocks.project_return', compact('projects', 'warehouses', 'categories'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function projectReturnStore(Request $request): RedirectResponse
    {
        $this->authorize('Create Warehouse Stocks');

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'warehouse_id' => 'required|exists:warehouses,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Fetch Project and Warehouse stock
            $project_stock = ProjectStock::with('project')->where('project_id', $request->project_id)
                ->where('product_id', $request->product_id)
                ->first();

            $warehouse_stock = WarehouseStock::with('warehouse')->where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->first();
            $project_name = $project_stock->project->name ?? Project::find($request->project_id)?->name ?? 'Unknown Project';
            $warehouse_name = $warehouse_stock->warehouse->name ?? Warehouse::find($request->warehouse_id)->name ?? 'New Warehouse';


            if (!$project_stock || $project_stock->quantity < $request->quantity) {
                throw new RuntimeException("Insufficient stock available in project.");
            }

            // Reduce stock from project
            $projectPreviousQuantity = $project_stock->quantity;
            $projectBalanceQuantity = $projectPreviousQuantity - $request->quantity;

            $project_stock->quantity = $projectBalanceQuantity;
            $project_stock->last_updated_by = Auth::id();
            $project_stock->last_transaction_type = 'RETURN to - ' . $warehouse_name . ' by - ' . Auth::user()->name;
            $project_stock->save();

            // Create StockLog for project
            StockLog::create([
                'project_id' => $request->project_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $projectPreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $projectBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => 'Return to Warehouse',
                'time' => now(),
                'remarks' => 'Returned to Warehouse: ' . $warehouse_name . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            // Add to warehouse
            if ($warehouse_stock) {
                $warehousePreviousQuantity = $warehouse_stock->quantity;
                $warehouse_stock->quantity += $request->quantity;
            } else {
                $warehousePreviousQuantity = 0;
                $warehouse_stock = new WarehouseStock();
                $warehouse_stock->warehouse_id = $request->warehouse_id;
                $warehouse_stock->category_id = $request->category_id;
                $warehouse_stock->product_id = $request->product_id;
                $warehouse_stock->quantity = $request->quantity;
            }

            $warehouseBalanceQuantity = $warehouse_stock->quantity;
            $warehouse_stock->last_updated_by = Auth::id();
            $warehouse_stock->last_transaction_type = 'RETURN received from - ' . $project_name . ' by - ' . Auth::user()->name;
            $warehouse_stock->save();

            // Create WarehouseStockLog
            WarehouseStockLog::create([
                'warehouse_id' => $request->warehouse_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $warehousePreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $warehouseBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => 'Project Return',
                'time' => now(),
                'remarks' => 'Received from Project: ' . $project_name . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            DB::commit();

            return redirect()->route('warehouse-stocks.index')->with('success', 'Stock returned successfully from project to warehouse!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategories(Request $request): JsonResponse
    {
        $projectId = $request->project_id;

        // Get categories that have products with stock in this project
        $categories = ProductCategory::whereHas('products.projectStocks', static function($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->get();

        return response()->json($categories);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getProducts(Request $request)
    {
        $projectId = $request->project_id;
        $categoryId = $request->category_id;

        $products = Product::where('category_id', $categoryId)
            ->whereHas('projectStocks', static function($query) use ($projectId) {
                $query->where('project_id', $projectId)
                    ->where('quantity', '>', 0);
            })
            ->get();

        return response()->json($products);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getStock(Request $request): JsonResponse
    {
        $projectId = $request->project_id;
        $productId = $request->product_id;

        $stock = ProjectStock::where('project_id', $projectId)
            ->where('product_id', $productId)
            ->first();

        $quantity = $stock ? number_format($stock->quantity, 2) : '0.00';

        return response()->json(compact('quantity'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getWarehouseProducts(Request $request): JsonResponse
    {
        $warehouseId = $request->warehouse_id;
        $categoryId = $request->category_id;

        $products = Product::where('category_id', $categoryId)
            ->whereHas('warehouseStocks', static function($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->get();

        return response()->json($products);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getWarehouseStock(Request $request): JsonResponse
    {
        $warehouseId = $request->warehouse_id;
        $productId = $request->product_id;

        $stock = WarehouseStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        $quantity = $stock ? number_format($stock->quantity, 2) : '0.00';

        return response()->json(compact('quantity'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function adjustStock(Request $request): JsonResponse
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'adjustment_type' => 'required|in:add,subtract,set',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $stock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$stock && $request->adjustment_type === 'subtract') {
                throw new RuntimeException("Cannot subtract from non-existent stock.");
            }

            if (!$stock) {
                // Create new stock if it doesn't exist
                $stock = new WarehouseStock([
                    'warehouse_id' => $request->warehouse_id,
                    'product_id' => $request->product_id,
                    'category_id' => $request->category_id,
                    'quantity' => 0,
                ]);
            }
            $previousQuantity = $stock->quantity;

            // Apply adjustment
            switch ($request->adjustment_type) {
                case 'add':
                    $stock->quantity += $request->quantity;
                    break;
                case 'subtract':
                    if ($stock->quantity < $request->quantity) {
                        throw new RuntimeException("Cannot subtract more than available stock.");
                    }
                    $stock->quantity -= $request->quantity;
                    break;
                case 'set':
                    $stock->quantity = $request->quantity;
                    break;
            }

            $stock->last_updated_by = Auth::id();
            $stock->last_transaction_type = 'Manual Adjustment: ' . $request->reason;
            $stock->save();

            WarehouseStockLog::create([
                'warehouse_id' => $request->warehouse_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $previousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $stock->quantity,
                'user_id' => Auth::id(),
                'type' => $request->adjustment_type === 'subtract' ? 'Manual Adjustment - Out' : 'Manual Adjustment - In',
                'time' => now(),
                'remarks' => 'Manual Adjustment: ' . $request->reason,
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
