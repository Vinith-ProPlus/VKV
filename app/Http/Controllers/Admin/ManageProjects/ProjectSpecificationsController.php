<?php

namespace App\Http\Controllers\Admin\ManageProjects;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectSpecificationsRequest;
use App\Models\Admin\ManageProjects\ProjectSpecifications;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProjectSpecificationsController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Project Specifications');
        if ($request->ajax()) {
            $data = ProjectSpecifications::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('project_specifications.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('project_specifications.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('project_specifications.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.manage_projects.project_specifications.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('Create Project Specifications');
        return view('admin.manage_projects.project_specifications.data', ['projectSpecification' => '']);
    }

    public function store(ProjectSpecificationsRequest $request)
    {
        $this->authorize('Create Project Specifications');
        try {
            $request->validate([
                'spec_name' => 'required|unique:project_specifications,spec_name',
                'spec_values' => 'required',
                'is_active' => 'required|boolean',
            ]);

            ProjectSpecifications::create($request->all());
            // return redirect()->route('project_specifications.index')->with('success', 'Project Specification created successfully.');
            return ['status'=>true,'message'=>"Project Specification Created Successfully"];
        } catch (\Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@ProjectSpecificationsController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(ProjectSpecifications $projectSpecification)
    {
        $this->authorize('Edit Project Specifications');
        return view('admin.manage_projects.project_specifications.data', compact('projectSpecification'));
    }

    public function update(ProjectSpecificationsRequest $request, ProjectSpecifications $projectSpecification)
    {
        $this->authorize('Edit Project Specifications');
        try {
            $projectSpecification->update($request->validated());
            // return redirect()->route('project_specifications.index')->with('success', 'Project Specification updated successfully.');
            return ['status'=>true,'message'=>"Project Specification Updated Successfully"];
        } catch (\Exception $exception) {
            info('Error::Place@ProjectSpecificationsController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('Delete Project Specifications');
        try {
            $projectSpecification = ProjectSpecifications::findOrFail($id);
            $projectSpecification->delete();
            return response(['status' => 'warning', 'message' => 'Project Specification deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@ProjectSpecificationsController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function restore($id)
    {
        $this->authorize('Restore Project Specifications');
        try {
            ProjectSpecifications::withTrashed()->findOrFail($id)->restore();
            return response(['status' => 'success', 'message' => 'Project Specification restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@ProjectSpecificationsController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
