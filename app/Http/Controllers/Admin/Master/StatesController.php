<?php

namespace App\Http\Controllers\Admin\Master;


use App\Http\Controllers\Controller;
use App\Http\Requests\StateRequest;
use App\Models\Admin\Master\State;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StatesController extends Controller{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View States');
        if ($request->ajax()) {
            $data = State::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('states.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('states.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('states.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.master.states.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('Create States');
        return view('admin.master.states.data', ['state' => '']);
    }

    public function store(StateRequest $request)
    { 
        $this->authorize('Create States');
        try {
            State::create($request->all());
            return redirect()->route('states.index')->with('success', 'State created successfully.');
        } catch (\Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@StatesController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(State $state)
    {
        $this->authorize('Edit States');
        return view('admin.master.states.data', compact('state'));
    }

    public function update(StateRequest $request, State $state)
    {
        $this->authorize('Edit States');
        try {
            $state->update($request->validated());
            return redirect()->route('states.index')->with('success', 'State updated successfully.');
        } catch (\Exception $exception) {
            info('Error::Place@StatesController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('Delete States');
        try {
            $category = State::findOrFail($id);
            $category->delete();
            return response(['status' => 'success', 'message' => 'State deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@StatesController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function restore($id)
    {
        $this->authorize('Restore States');
        try {
            State::withTrashed()->findOrFail($id)->restore();
            return response(['status' => 'success', 'message' => 'State restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@StatesController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
