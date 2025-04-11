<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Factory|View|ApplicationAlias|JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|ApplicationAlias|View|JsonResponse
    {
        $this->authorize('View Roles and Permissions');
        if ($request->ajax()) {
            $data = Role::all();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('view', function ($data) {
                    return '<a href="' . route('role.show', $data->id) . '"><i class="fa fa-eye" style="color: green"></i></a>';
                })
                ->addColumn('edit', function ($data) {
                    if ($data->name === SUPER_ADMIN_ROLE_NAME) {
                        return '-';
                    }
                    return '<a href="' . route('role.edit', $data->id) . '"><i class="fa-solid fa-pen mr-3 editicons" ></i></a>';
                })
                ->addColumn('delete', function ($data) {
                    if (in_array($data->name, SYSTEM_ROLES, true)) {
                        return '-';
                    }
                    return '<a onclick="commonDelete(\'' . route('role.destroy', $data->id) . '\')">
                                <i class="fa fa-trash" style="color: red"></i></i>
                             </a>';
                })
                ->rawColumns(['view', 'edit', 'delete'])
                ->make(true);
        }

        return view('admin.role.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function create(): Factory|View|Application
    {
        $this->authorize('Create Roles and Permissions');
        $role = '';
        $permissions = Permission::all()->groupBy('model');
        return view('admin.role.create', compact('permissions', 'role'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RoleRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('Create Roles and Permissions');
        DB::beginTransaction();
        try {
            $role = Role::create(['name' => $request['name']]);
            $permissions = Permission::whereIn('id', $request['permissions'])->pluck('name')->toArray();
            $role->givePermissionTo($permissions);
            DB::commit();
            return redirect()->route("role.index")->with("success", "Role Created Successfully.");
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@RoleController@store - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function edit($id): Factory|View|Application
    {
        $this->authorize('Edit Roles and Permissions');
        $role = Role::find($id);
        $permissions = Permission::all()->groupBy('model');

        return view('admin.role.create', compact('role', 'permissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Application|Factory|View
     * @throws AuthorizationException
     */
    public function show($id): Factory|View|Application
    {
        $this->authorize('View Roles and Permissions');
        $role = Role::find($id);
        $permissions = Permission::all()->groupBy('model');

        return view('admin.role.show', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RoleRequest $request
     * @param $id
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(RoleRequest $request, $id): RedirectResponse
    {
        $this->authorize('Edit Roles and Permissions');
        DB::beginTransaction();
        try {
            $input = $request->only(['name']);
            $role = Role::find($id);
            $role->update($input);
            $permissions = Permission::whereIn('id', $request['permissions'])->pluck('name')->toArray();
            $role->syncPermissions($permissions);
            DB::commit();
            return redirect()->route("role.index")->with("success", "Role Updated Successfully.");
        } catch (Exception $exception) {
            DB::rollBack();
            info('Place@RoleController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return Application|ResponseFactory|Response
     * @throws AuthorizationException
     */
    public function destroy(Request $request): Response|Application|ResponseFactory
    {
        $this->authorize('Delete Roles and Permissions');
        DB::beginTransaction();
        try {
            $defaultRole = Role::where('name', USER_ROLE_NAME)->first();

            if ($defaultRole) {
                User::where('role_id', $request->id)->update(['role_id' => $defaultRole->id]);
            }
            Role::find($request->id)->delete();
            DB::commit();
            return response(['status' => 'warning', 'message' => 'Role Deleted Successfully!']);
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@RoleController@delete - ' . $exception->getMessage());
            return response(['message' => 'Something went wrong!']);
        }
    }
}
