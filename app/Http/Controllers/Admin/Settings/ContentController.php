<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContentRequest;
use App\Models\Content;
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
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ContentController extends Controller
{
    use AuthorizesRequests;

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Contents');

        if ($request->ajax()) {
            $query = Content::withTrashed();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('status', static function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })
                ->addColumn('action', static function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\''.route('contents.restore', $data->id).'\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="'.route('contents.edit', $data->id).'" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\''.route('contents.destroy', $data->id).'\')" class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.settings.contents.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Contents');
        return view('admin.settings.contents.data', ['content' => '']);
    }

    /**
     * @throws AuthorizationException
     */
    public function store(ContentRequest $request): RedirectResponse
    {
        $this->authorize('Create Contents');
        DB::beginTransaction();
        try {
            Content::create($request->validated());
            DB::commit();
            return redirect()->route('contents.index')->with('success', 'Content created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@ContentController@store - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(Content $content): View|Factory|Application
    {
        $this->authorize('Edit Contents');
        return view('admin.settings.contents.data', compact('content'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(ContentRequest $request, Content $content): RedirectResponse
    {
        $this->authorize('Edit Contents');
        DB::beginTransaction();
        try {
            $content->update($request->validated());
            DB::commit();
            return redirect()->route('contents.index')->with('success', 'Content updated successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            info('Error::Place@ContentController@update - ' . $exception->getMessage());
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Contents');
        try {
            $content = Content::findOrFail($id);
            $content->delete();
            return response(['status' => 'warning', 'message' => 'Content deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@ContentController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Contents');
        try {
            Content::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Content restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@ContentController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong: " . $exception->getMessage());
        }
    }
}
