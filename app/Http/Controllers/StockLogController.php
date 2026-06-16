<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\SiteStock;
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
            $query = StockLog::with(['site.project', 'product', 'category', 'user'])->latest();

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            } elseif ($request->filled('project_id')) {
                $query->whereHas('site', static function ($q) use ($request) {
                    $q->where('project_id', $request->project_id);
                });
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
                ->addColumn('site_label', static function ($row) {
                    if (!$row->site) {
                        return 'N/A';
                    }

                    $label = $row->site->site_no;
                    if ($row->site->project) {
                        $label .= ' (' . $row->site->project->name . ')';
                    }

                    return $label;
                })
                ->addColumn('user_id_name', static function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->editColumn('quantity', static function ($row) {
                    return number_format($row->quantity, 2);
                })
                ->editColumn('previous_quantity', static function ($row) {
                    return number_format($row->previous_quantity, 2);
                })
                ->editColumn('balance_quantity', static function ($row) {
                    return number_format($row->balance_quantity, 2);
                })
                ->editColumn('time', static function ($row) {
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
            'site_id' => 'required|exists:sites,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'user_id' => 'required|exists:users,id',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $stock = SiteStock::where('site_id', $request->site_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$stock || $stock->quantity < $request->quantity) {
                throw new RuntimeException('Insufficient stock available.');
            }

            $previousQuantity = $stock->quantity;
            $balanceQuantity = $previousQuantity - $request->quantity;

            StockLog::create([
                'site_id' => $request->site_id,
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

            $stock->quantity = $balanceQuantity;
            $stock->last_updated_by = Auth::id();
            $stock->last_transaction_type = TAKEN_FOR_CONSTRUCTION . ' by : ' . $request->user_id;
            $stock->save();

            DB::commit();

            return redirect()->route('stock-logs.index')->with('success', 'Stock logged successfully!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function getProductsByCategory(Request $request): JsonResponse
    {
        $categoryId = $request->category_id;
        $siteId = $request->site_id;

        $query = Product::where('category_id', $categoryId);

        if ($siteId) {
            $query->whereHas('siteStocks', static function ($q) use ($siteId) {
                $q->where('site_id', $siteId)->where('quantity', '>', 0);
            });
        }

        return response()->json($query->get());
    }

    public function getProductStock(Request $request): JsonResponse
    {
        $stock = SiteStock::where('site_id', $request->site_id)
            ->where('product_id', $request->product_id)
            ->first();

        $availableStock = $stock ? $stock->quantity : 0;

        return response()->json(['available_stock' => $availableStock]);
    }
}
