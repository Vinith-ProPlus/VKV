<?php

namespace App\Http\Controllers\Admin\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisitorRequest;
use App\Models\Visitor;
use App\Models\Project;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class VisitorController extends Controller {
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Visitors');

        if ($request->ajax()) {
            $query = Visitor::with('project', 'user')->withTrashed();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('project_name', static function ($data) {
                    return $data->project?->name;
                })
                ->editColumn('rating', static function ($data) {
                    return $data->rating . ' / 5';
                })
                ->editColumn('created_by', static function ($data) {
                    return $data->user?->name;
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
                ->rawColumns(['action'])
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
        return view('admin.crm.visitors.data', ['visitor' => '']);
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
            $data['user_id'] = Auth::id();
            Visitor::create($data);
            DB::commit();
            return redirect()->route('visitors.index')->with('success', 'Visitor added successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@VisitorController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Visitor $visitor): View|Factory|Application
    {
        $this->authorize('Edit Visitors');
        $projects = Project::all();
        return view('admin.crm.visitors.data', compact('visitor', 'projects'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(VisitorRequest $request, Visitor $visitor): RedirectResponse
    {
        $this->authorize('Edit Visitors');
        DB::beginTransaction();
        try {
            $visitor->update($request->validated());
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
