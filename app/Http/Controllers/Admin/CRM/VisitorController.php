<?php

namespace App\Http\Controllers\Admin\CRM;

use Exception;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\VisitorRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VisitorController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Visitors');

        if ($request->ajax()) {
            $query = Visitor::with('project', 'customer', 'site')->withTrashed();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('project_name', static function ($data) {
                    return $data->project?->name;
                })
                ->editColumn('site_name', static function ($data) {
                    return $data->site?->site_no ?? 'N/A';
                })
                ->editColumn('lead_name', static function ($data) {
                    return $data->customer?->name ?? 'N/A';
                })
                ->editColumn('status', static function ($data) {
                    $badgeClass = match ($data->status) {
                        'new' => 'badge-primary',
                        'under followup' => 'badge-warning',
                        'visited' => 'badge-info',
                        'closed' => 'badge-danger',
                        default => 'badge-secondary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($data->status) . '</span>';
                })
                ->addColumn('action', static function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('visitors.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('visitors.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('visitors.destroy', $data->id) . '\')" class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('admin.crm.visitors.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Visitors');

        $visitor = null;
        $customers = Lead::get();
        $user = auth()->user();

        if ($user->role_id === 4) {
            $projects = $user->projects()->with('sites')->get();
        } else {
            $projects = Project::with('sites')->get();
        }

        $sites = $projects->pluck('sites')->flatten();

        return view('admin.crm.visitors.data', compact('visitor', 'customers', 'projects', 'sites'));
    }

    /**
     * @throws AuthorizationException
     */
    public function store(VisitorRequest $request): RedirectResponse
    {
        $this->authorize('Create Visitors');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Check if this is a new customer based on the flag
            $customerId = $data['customer_id'];
            if ($data['new_customer_flag'] === 'true') {
                // Create new Lead (Customer) with the mobile number and name
                $newCustomer = Lead::create([
                    'name' => $data['new_customer_name'] ?? 'Customer',
                    'mobile_number' => $customerId,
                ]);
                $customerId = $newCustomer->id;
            }
            
            // Create visitor with the customer ID (either existing or newly created)
            $visitorData = [
                'customer_id' => $customerId,
                'project_id' => $data['project_id'],
                'site_id' => $data['site_id'] ?? null,
                'status' => $data['status'],
                'remarks' => $data['remarks'] ?? null,
            ];
            
            Visitor::create($visitorData);
            DB::commit();
            return redirect()->route('visitors.index')->with('success', 'Visitor added successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@VisitorController@store - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Visitor $visitor): View|Factory|Application
    {
        $this->authorize('Edit Visitors');
        
        $customers = Lead::get();
        $user = auth()->user();

        if ($user->role_id === 4) {
            $projects = $user->projects()->with('sites')->get();
        } else {
            $projects = Project::with('sites')->get();
        }

        $sites = $projects->pluck('sites')->flatten();

        logger($sites);

        return view('admin.crm.visitors.data', compact('visitor', 'customers', 'projects', 'sites'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(VisitorRequest $request, Visitor $visitor): RedirectResponse
    {
        $this->authorize('Edit Visitors');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Ensure customer_id is numeric (existing customer only)
            $customerId = $data['customer_id'];
            if (!is_numeric($customerId)) {
                DB::rollBack();
                return redirect()->back()->withInput()->with("warning", "You can only select existing customers while editing.");
            }
            
            // Update visitor with validated data
            $visitorData = [
                'customer_id' => $customerId,
                'project_id' => $data['project_id'],
                'site_id' => $data['site_id'] ?? null,
                'status' => $data['status'],
                'remarks' => $data['remarks'] ?? null,
            ];
            
            $visitor->update($visitorData);
            DB::commit();
            return redirect()->route('visitors.index')->with('success', 'Visitor updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@VisitorController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Visitors');
        try {
            $visitor = Visitor::findOrFail($id);
            $visitor->delete();
            return response(['status' => 'warning', 'message' => 'Visitor deleted successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@VisitorController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Visitors');
        try {
            Visitor::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Visitor restored successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@VisitorController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }
}
