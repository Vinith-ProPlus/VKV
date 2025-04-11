<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Mail\PasswordChangedMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Application|Factory|View|JsonResponse
    {
        $this->authorize('View Users');
        if ($request->ajax()) {
        $data = User::withTrashed()->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('is_active', static fn($data) => $data->is_active ? 'Active' : 'Inactive')
            ->addColumn('role_name', static fn($data) => optional($data->role)->name)
            ->addColumn('action', static function ($data): string {
                $button = '<div class="d-flex justify-content-center">';
                if ($data->deleted_at) {
                    $button = '<a onclick="commonRestore(\'' . route('users.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                } else {
                    $button .= '<a href="' . route('users.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                    $button .= '<a onclick="commonDelete(\'' . route('users.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                }
                $button .= '</div>';
                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('admin.users.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Users');
        return view('admin.users.data', ['user' => '']);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('Create Users');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $plainPassword = $data['password'];
            $data['password'] = Hash::make($data['password']);
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')?->store('users', 'public');
            }
            $user = User::create($data);
            $role = Role::findOrFail($data['role_id']);
            $user->assignRole($role->name);
            DB::commit();
            Mail::to($user->email)->send(new WelcomeMail($user, $plainPassword));
            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            info('Error::Place@UserController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $ErrMsg);
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(User $user): View|Factory|Application
    {
        $this->authorize('Edit Users');
        return view('admin.users.data', compact('user'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('Edit Users');
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $newPassword = null;
            if (!empty($data['password'])) {
                $newPassword = $data['password'];
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }
            if ($request->hasFile('image')) {
                $oldImage = $user->image;
                $newImage = $data['image'] = $request->file('image')?->store('users', 'public');
            }

            $user->update($data);
            $role = Role::findOrFail($data['role_id']);
            $user->assignRole($role->name);
            DB::commit();
            if (isset($oldImage)) {
                Storage::disk('public')->delete($oldImage);
            }
            if ($newPassword) {
                Mail::to($user->email)->send(new PasswordChangedMail($user, $newPassword));
            }
            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            if(isset($newImage)){
                Storage::disk('public')->delete($newImage);
            }
            info('Error::Place@UserController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Users');
        try {
            $user = User::findOrFail($id);
            $user->update(['deleted_by' => Auth::id(), 'remember_token'=> null]);
            $user->delete();
            return response(['status' => 'success', 'message' => 'User deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@UserController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Users');
        try {
            $user = User::withTrashed()->findOrFail($id);
            if ($user) {
                $user->restore();
                $user->update(['deleted_by' => null]);
            }
            return response(['status' => 'success', 'message' => 'User restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@UserController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
