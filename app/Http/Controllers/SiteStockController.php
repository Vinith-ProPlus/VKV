<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\SiteStock;
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

class SiteStockController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $this->authorize('View Project Stocks');
        $projects = Project::all();

        if ($request->ajax()) {
            $query = SiteStock::with(['site.project', 'product', 'category']);

            if ($request->filled('site_id')) {
                $query->where('site_id', $request->site_id);
            } elseif ($request->filled('project_id')) {
                $query->whereHas('site', static function ($q) use ($request) {
                    $q->where('project_id', $request->project_id);
                });
            }

            $data = $query->get();

            return DataTables::of($data)
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
                ->editColumn('quantity', static function ($row) {
                    return number_format($row->quantity, 2);
                })
                ->addColumn('last_updated', static function ($row) {
                    return $row->updated_at->format('d-m-Y H:i') .
                        ($row->last_transaction_type ? ' (' . $row->last_transaction_type . ')' : '');
                })
                ->make(true);
        }

        return view('admin.site_stocks.index', compact('projects'));
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

        return view('admin.site_stocks.re_allocation', compact('projects', 'categories'));
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
            'from_site_id' => 'required|exists:sites,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'to_site_id' => 'required|exists:sites,id|different:from_site_id',
            'remarks' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $fromSiteStock = SiteStock::with('site')->where('site_id', $request->from_site_id)
                ->where('product_id', $request->product_id)
                ->first();

            $toSiteStock = SiteStock::with('site')->where('site_id', $request->to_site_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$fromSiteStock || $fromSiteStock->quantity < $request->quantity) {
                throw new RuntimeException('Insufficient stock available.');
            }

            $fromPreviousQuantity = $fromSiteStock->quantity;
            $fromBalanceQuantity = $fromPreviousQuantity - $request->quantity;
            $toSiteLabel = $toSiteStock?->site?->site_no ?? 'New Site';

            $fromSiteStock->quantity = $fromBalanceQuantity;
            $fromSiteStock->last_updated_by = Auth::id();
            $fromSiteStock->last_transaction_type = RE_ALLOCATION . ' to - ' . $toSiteLabel . ' by - ' . Auth::user()->name;
            $fromSiteStock->save();

            StockLog::create([
                'site_id' => $request->from_site_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $fromPreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $fromBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => RE_ALLOCATION . ' - Transfer',
                'time' => now(),
                'remarks' => 'Transferred to Site: ' . $toSiteLabel . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            if ($toSiteStock) {
                $toPreviousQuantity = $toSiteStock->quantity;
                $toSiteStock->quantity += $request->quantity;
            } else {
                $toPreviousQuantity = 0;
                $toSiteStock = new SiteStock([
                    'site_id' => $request->to_site_id,
                    'category_id' => $request->category_id,
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ]);
            }

            $fromSiteLabel = $fromSiteStock->site->site_no ?? 'Site';
            $toBalanceQuantity = $toSiteStock->quantity;
            $toSiteStock->last_updated_by = Auth::id();
            $toSiteStock->last_transaction_type = RE_ALLOCATION . ' received from - ' . $fromSiteLabel . ' by - ' . Auth::user()->name;
            $toSiteStock->save();

            StockLog::create([
                'site_id' => $request->to_site_id,
                'category_id' => $request->category_id,
                'product_id' => $request->product_id,
                'previous_quantity' => $toPreviousQuantity,
                'quantity' => $request->quantity,
                'balance_quantity' => $toBalanceQuantity,
                'user_id' => Auth::id(),
                'type' => RE_ALLOCATION . ' - Received',
                'time' => now(),
                'remarks' => 'Received from Site: ' . $fromSiteLabel . ($request->remarks ? ' | ' . $request->remarks : ''),
            ]);

            DB::commit();

            return redirect()->route('site-stocks.index')->with('success', 'Stock re-allocated successfully!');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function getCategories(Request $request): JsonResponse
    {
        $siteId = $request->site_id;

        $categories = ProductCategory::whereHas('products.siteStocks', static function ($query) use ($siteId) {
            $query->where('site_id', $siteId);
        })->get();

        return response()->json($categories);
    }

    public function getProducts(Request $request): JsonResponse
    {
        $siteId = $request->site_id;
        $categoryId = $request->category_id;

        $products = Product::where('category_id', $categoryId)
            ->whereHas('siteStocks', static function ($query) use ($siteId) {
                $query->where('site_id', $siteId);
            })
            ->get();

        return response()->json($products);
    }

    public function getStock(Request $request): JsonResponse
    {
        $stock = SiteStock::where('site_id', $request->site_id)
            ->where('product_id', $request->product_id)
            ->first();

        $quantity = $stock ? number_format($stock->quantity, 2) : '0.00';

        return response()->json(compact('quantity'));
    }

    public function adjust(Request $request): JsonResponse
    {
        $this->authorize('Edit Project Stocks');

        $request->validate([
            'site_id' => 'required|exists:sites,id',
            'category_id' => 'required|exists:product_categories,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'adjustment_type' => 'required|in:add,subtract,set',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $stock = SiteStock::where('site_id', $request->site_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$stock && $request->adjustment_type === 'subtract') {
                throw new RuntimeException('Cannot subtract from non-existent stock.');
            }

            if (!$stock) {
                $stock = new SiteStock([
                    'site_id' => $request->site_id,
                    'product_id' => $request->product_id,
                    'category_id' => $request->category_id,
                    'quantity' => 0,
                ]);
            }

            $previousQuantity = $stock->quantity;

            switch ($request->adjustment_type) {
                case 'add':
                    $stock->quantity += $request->quantity;
                    break;
                case 'subtract':
                    if ($stock->quantity < $request->quantity) {
                        throw new RuntimeException('Cannot subtract more than available stock.');
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
                'site_id' => $request->site_id,
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
