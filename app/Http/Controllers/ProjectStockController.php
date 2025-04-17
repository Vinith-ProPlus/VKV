<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\ProjectStock;
use App\Models\StockLog;
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

class ProjectStockController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $projects = Project::all();

        if ($request->ajax()) {
            $query = ProjectStock::with(['project', 'product', 'category']);

            if ($request->has('project_id') && !empty($request->project_id)) {
                $query->where('project_id', $request->project_id);
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

        return view('admin.project_stocks.index', compact('projects'));
    }

    /**
     * @return View|Factory|Application
     * @throws AuthorizationException
     */
    public function reAllocation(): View|Factory|Application
    {
        $this->authorize('Create Project Stocks');
        $projects = Project::all();
        $categories = ProductCategory::all();

        return view('admin.project_stocks.re_allocation', compact('projects', 'categories'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function reAllocationStore(Request $request): RedirectResponse
    {
        $this->authorize('Create Project Stocks');

        $request->validate([
            'from_project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'to_project_id' => 'required|exists:projects,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // Fetch From and To project stock
            $from_project_stock = ProjectStock::with('project')->where('project_id', $request->from_project_id)
                ->where('product_id', $request->product_id)
                ->first();

            $to_project_stock = ProjectStock::with('project')->where('project_id', $request->to_project_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$from_project_stock || $from_project_stock->quantity < $request->quantity) {
                throw new RuntimeException("Insufficient stock available.");
            }

            // Reduce stock from 'from' project
            $fromPreviousQuantity = $from_project_stock->quantity;
            $fromBalanceQuantity = $fromPreviousQuantity - $request->quantity;

            $from_project_stock->quantity = $fromBalanceQuantity;
            $from_project_stock->last_updated_by = Auth::id();
            $from_project_stock->last_transaction_type = RE_ALLOCATION . ' to - ' . ($to_project_stock->project->name ?? 'New Project') . ' by - ' . Auth::user()->name;
            $from_project_stock->save();

            // Create StockLog for from project
            StockLog::create([
                'project_id' => $request->from_project_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $fromPreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $fromBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => RE_ALLOCATION .' - Transfer',
                'time' => now(),
                'remarks' => 'Transferred to Project: ' . $to_project_stock->project->name . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            // Add to 'to' project
            if ($to_project_stock) {
                $toPreviousQuantity = $to_project_stock->quantity;
                $to_project_stock->quantity += $request->quantity;
            } else {
                $toPreviousQuantity = 0;
                $to_project_stock = new ProjectStock();
                $to_project_stock->project_id = $request->to_project_id;
                $to_project_stock->category_id = $request->category_id;
                $to_project_stock->product_id = $request->product_id;
                $to_project_stock->quantity = $request->quantity;
            }

            $toBalanceQuantity = $to_project_stock->quantity;
            $to_project_stock->last_updated_by = Auth::id();
            $to_project_stock->last_transaction_type = RE_ALLOCATION . ' received from - ' . $from_project_stock->project->name . ' by - ' . Auth::user()->name;
            $to_project_stock->save();

            // Create StockLog for to project
            StockLog::create([
                'project_id' => $request->to_project_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $toPreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $toBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => RE_ALLOCATION .' - Received',
                'time' => now(),
                'remarks' => 'Received from Project: ' . $from_project_stock->project->name . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            DB::commit();

            return redirect()->route('project-stocks.index')->with('success', 'Stock re-allocated successfully!');
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
     * Get products for a category within a project
     */
    public function getProducts(Request $request)
    {
        $projectId = $request->project_id;
        $categoryId = $request->category_id;

        $products = Product::where('category_id', $categoryId)
            ->whereHas('projectStocks', static function($query) use ($projectId) {
                $query->where('project_id', $projectId);
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

        return response()->json(['quantity' => $quantity]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function adjust(Request $request): JsonResponse
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'adjustment_type' => 'required|in:add,subtract,set',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $stock = ProjectStock::where('project_id', $request->project_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$stock && $request->adjustment_type === 'subtract') {
                throw new RuntimeException("Cannot subtract from non-existent stock.");
            }

            if (!$stock) {
                // Create new stock if it doesn't exist
                $stock = new ProjectStock([
                    'project_id' => $request->project_id,
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

            StockLog::create([
                'project_id' => $request->project_id,
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
