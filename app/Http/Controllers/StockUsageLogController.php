<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockUsageLog;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class StockUsageLogController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::all();
        $categories = ProductCategory::all();
        $users = User::all();

        if ($request->ajax()) {
            $query = StockUsageLog::with(['project', 'product', 'category', 'takenByUser']);

            if ($request->has('project_id') && !empty($request->project_id)) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('product_id') && !empty($request->product_id)) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('user_id') && !empty($request->user_id)) {
                $query->where('taken_by', $request->user_id);
            }

            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('taken_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('taken_at', '<=', $request->date_to);
            }

            $data = $query->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('taken_by_name', function($row) {
                    return $row->takenByUser ? $row->takenByUser->name : 'N/A';
                })
                ->editColumn('quantity', function($row) {
                    return number_format($row->quantity, 2);
                })
                ->editColumn('previous_quantity', function($row) {
                    return number_format($row->previous_quantity, 2);
                })
                ->editColumn('balance_quantity', function($row) {
                    return number_format($row->balance_quantity, 2);
                })
                ->editColumn('taken_at', function($row) {
                    return $row->taken_at->format('d-m-Y H:i');
                })
                ->make(true);
        }

        return view('admin.stock_usage.index', compact('projects', 'categories', 'users'));
    }

    public function create()
    {
        $projects = Project::all();
        $categories = ProductCategory::all();
        $products = [];

        return view('admin.stock_usage.create', compact('projects', 'categories', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'taken_by' => 'required|exists:users,id',
            'taken_at' => 'required|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Check if we have enough stock
            $stock = ProjectStock::where('project_id', $request->project_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$stock || $stock->quantity < $request->quantity) {
                throw new RuntimeException("Insufficient stock available.");
            }

            // Get previous quantity before updating
            $previousQuantity = $stock->quantity;

            // Calculate balance quantity
            $balanceQuantity = $previousQuantity - $request->quantity;

            // Create stock usage log
            $usage = new StockUsageLog([
                'project_id' => $request->project_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $previousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $balanceQuantity,
                'taken_by' => $request->taken_by,
                'taken_at' => $request->taken_at,
                'remarks' => $request->remarks,
            ]);
            $usage->save();

            // Update project stock
            $stock->quantity = $balanceQuantity;
            $stock->last_updated_by = Auth::id();
            $stock->last_transaction_type = 'Used by labor: ' . $request->taken_by;
            $stock->save();

            DB::commit();
            return redirect()->route('stock-usages.index')->with('success', 'Stock usage logged successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function getProductsByCategory(Request $request)
    {
        $categoryId = $request->category_id;
        $projectId = $request->project_id;

        $query = Product::where('category_id', $categoryId);

        // If project ID is provided, only show products that have stock in that project
        if ($projectId) {
            $query->whereHas('projectStocks', function($q) use ($projectId) {
                $q->where('project_id', $projectId)->where('quantity', '>', 0);
            });
        }

        $products = $query->get();

        return response()->json($products);
    }

    public function getProductStock(Request $request)
    {
        $projectId = $request->project_id;
        $productId = $request->product_id;

        $stock = ProjectStock::where('project_id', $projectId)
            ->where('product_id', $productId)
            ->first();

        $availableStock = $stock ? $stock->quantity : 0;

        return response()->json(['available_stock' => $availableStock]);
    }
}
