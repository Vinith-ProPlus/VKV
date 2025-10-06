<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockLog;
use App\Models\User;
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

class StockLogController extends Controller
{
    use AuthorizesRequests;
    /**
     * @param Request $request
     * @return Factory|View|Application|JsonResponse
     * @throws Exception
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Project Stocks');
        $projects = Project::all();
        $categories = ProductCategory::all();
        $users = User::all();

        if ($request->ajax()) {
            $query = StockLog::with(['project', 'product', 'category', 'user'])->latest();

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
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('time', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('time', '<=', $request->date_to);
            }

            $data = $query->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('user_id_name', static function($row) {
                    if ($row->user) {
                        return $row->user->name;
                    }
                    return 'N/A';
                })
                ->editColumn('quantity', static function($row) {
                    return number_format($row->quantity, 2);
                })
                ->editColumn('previous_quantity', static function($row) {
                    return number_format($row->previous_quantity, 2);
                })
                ->editColumn('balance_quantity', static function($row) {
                    return number_format($row->balance_quantity, 2);
                })
                ->editColumn('time', static function($row) {
                    return $row->time->format('d-m-Y H:i');
                })
                ->make(true);
        }

        return view('admin.stock_log.index', compact('projects', 'categories', 'users'));
    }

    /**
     * @return View|Factory|Application
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Project Stocks');
        $projects = Project::all();
        $categories = ProductCategory::all();

        return view('admin.stock_log.create', compact('projects', 'categories'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('Create Project Stocks');
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'user_id' => 'required|exists:users,id',
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

            // Create stock log
            StockLog::create([
                'project_id' => $request->project_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $previousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $balanceQuantity,
                'user_id' => $request->user_id,
                'type' => TAKEN_FOR_CONSTRUCTION,
                'time' => now(),
                'remarks' => $request->remarks,
            ]);

            // Update project stock
            $stock->quantity = $balanceQuantity;
            $stock->last_updated_by = Auth::id();
            $stock->last_transaction_type = TAKEN_FOR_CONSTRUCTION.' by : ' . $request->user_id;
            $stock->save();

            DB::commit();
            return redirect()->route('stock-logs.index')->with('success', 'Stock logged successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductsByCategory(Request $request): JsonResponse
    {
        $categoryId = $request->category_id;
        $projectId = $request->project_id;

        $query = Product::where('category_id', $categoryId);

        // If project ID is provided, only show products that have stock in that project
        if ($projectId) {
            $query->whereHas('projectStocks', static function($q) use ($projectId) {
                $q->where('project_id', $projectId)->where('quantity', '>', 0);
            });
        }

        $products = $query->get();

        return response()->json($products);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductStock(Request $request): JsonResponse
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
