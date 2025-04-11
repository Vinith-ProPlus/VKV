<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\ProjectStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class ProjectStockController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::all();

        if ($request->ajax()) {
            $query = ProjectStock::with(['project', 'product', 'category']);

            if ($request->has('project_id') && !empty($request->project_id)) {
                $query->where('project_id', $request->project_id);
            }

            $data = $query->get();

            return DataTables::of($data)
                ->editColumn('quantity', function($row) {
                    return number_format($row->quantity, 2);
                })
                ->addColumn('last_updated', function($row) {
                    return $row->updated_at->format('d-m-Y H:i') .
                        ($row->last_transaction_type ? ' (' . $row->last_transaction_type . ')' : '');
                })
                ->make(true);
        }

        return view('admin.project_stocks.index', compact('projects'));
    }

    /**
     * Get categories for a project
     */
    public function getCategories(Request $request)
    {
        $projectId = $request->project_id;

        // Get categories that have products with stock in this project
        $categories = ProductCategory::whereHas('products.projectStocks', function($query) use ($projectId) {
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
            ->whereHas('projectStocks', function($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->get();

        return response()->json($products);
    }

    /**
     * Get current stock for a product in a project
     */
    public function getStock(Request $request)
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
     * Adjust stock quantity
     */
    public function adjust(Request $request)
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

            $product = Product::findOrFail($request->product_id);

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

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
