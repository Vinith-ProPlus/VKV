<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use App\Models\Document;
use App\Models\User;
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
use function Laravel\Prompts\warning;

class BlogController extends Controller{
    use AuthorizesRequests;

    /**
     * @param Request $request
     * @return Factory|Application|View|JsonResponse
     * @throws AuthorizationException
     * @throws Exception
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Blogs');

        if ($request->ajax()) {
            $query = Blog::with(['user', 'project', 'stage'])
                ->withTrashed()
                ->orderByDesc('created_at')
                ->when($request->project_id, static fn($q) => $q->whereHas('project', static fn($sub) => $sub->where('id', $request->project_id)))
                ->when($request->stage_id, static fn($q) => $q->whereHas('stage', static fn($sub) => $sub->where('id', $request->stage_id)))
                ->when($request->user_id, static fn($q) => $q->whereHas('user', static fn($sub) => $sub->where('id', $request->user_id)))
                ->when($request->from_date, static fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
                ->when($request->to_date, static fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
                ->get();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('user_name', static fn($data) => $data->user?->name)
                ->editColumn('project_name', static fn($data) => $data->project?->name)
                ->editColumn('stage_name', static fn($data) => $data->stage?->name)
                ->editColumn('created_on', static fn($data) => $data->created_at->format('d-m-Y'))
                ->addColumn('action', static function ($data) {
                    $buttons = '<div class="d-flex justify-content-center">';
                    if ($data->trashed()) {
                        $buttons .= '<a onclick="commonRestore(\'' . route('blogs.restore', $data->id) . '\')" class="btn btn-outline-warning" title="Restore"><i class="fa fa-undo"></i></a>';
                    } else {
                        $buttons .= '<a href="' . route('blogs.show', $data->id) . '" class="btn btn-outline-success btn-sm m-1" title="View">
                                    <i class="fa fa-eye"></i></a>
                                    <a onclick="commonDelete(\'' . route('blogs.destroy', $data->id) . '\')" class="btn btn-outline-danger btn-sm m-1" title="Delete">
                                    <i class="fa fa-trash text-danger"></i></a>';
                    }

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('blogs.index');
    }

    /**
     * @return View|Factory|Application
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Blogs');
        return view('blogs.data', ['blog' => '']);
    }

    /**
     * @param BlogRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(BlogRequest $request): RedirectResponse
    {
        $this->authorize('Create Blogs');
        DB::beginTransaction();
        try {
            $attachments = [];
            if ($request->hasFile('attachments')) {
                $files = is_array($request->file('attachments')) ? $request->file('attachments') : [$request->file('attachments')];
                foreach ($files as $file) {
                    $filename = generateUniqueFileName($file);
                    $path = $file->storeAs('documents', $filename, 'public');
                    $attachments[] = [
                        'title'       => 'Blog Attachment',
                        'description' => '',
                        'module_name' => 'Blog',
                        'file_path'   => $path,
                        'file_name'   => $filename,
                        'uploaded_by' => $request->user_id,
                    ];
                }
            }

            foreach ($request->stage_ids as $stage_id) {
                $blog = Blog::create([
                    'user_id'          => $request->user_id,
                    'project_id'       => $request->project_id,
                    'project_stage_id' => $stage_id,
                    'remarks'          => $request->remarks,
                    'is_damaged'        => $request->is_damaged,
                ]);

                foreach ($attachments as $attachment) {
                    Document::create(array_merge($attachment, ['module_id' => $blog->id]));
                }
            }
            DB::commit();
            return redirect()->route('blogs.index')->with('success', 'Blog created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            warning('Error::Place@BlogController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong: " . $ErrMsg);
        }
    }

    /**
     * @param Blog $blog
     * @return View|Factory|Application
     */
    public function show(Blog $blog): View|Factory|Application
    {
        return view('blogs.show', compact('blog'));
    }

    /**
     * @param $id
     * @return Application|Response|RedirectResponse|ResponseFactory
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Blogs');
        try {
            $ticket = Blog::findOrFail($id);
            $ticket->delete();
            return response(['status' => 'warning', 'message' => 'Blog deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@BlogController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return Application|Response|RedirectResponse|ResponseFactory
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Blogs');
        try {
            Blog::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Blog restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@BlogController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
