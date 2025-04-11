<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductCategoryController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request)
    {
        $this->authorize('View Product Category');
        if ($request->ajax()) {
            $data = ProductCategory::withTrashed()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('is_active', function ($data) {
                    return $data->is_active ? 'Active' : 'Inactive';
                })->addColumn('action', function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button = '<a onclick="commonRestore(\'' . route('product_categories.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('product_categories.edit', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('product_categories.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.master.product_categories.index');
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('Create Product Category');
        return view('admin.master.product_categories.data', ['productCategory' => '']);
    }

    public function store(ProductCategoryRequest $request)
    {
        $this->authorize('Create Product Category');
        try {
            ProductCategory::create($request->all());
            return redirect()->route('product_categories.index')->with('success', 'Category created successfully.');
        } catch (\Exception $exception) {
            $ErrMsg = $exception->getMessage();
            info('Error::Place@ProductCategoryController@store - ' . $ErrMsg);
            return redirect()->back()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    public function edit(ProductCategory $productCategory)
    {
        $this->authorize('Edit Product Category');
        return view('admin.master.product_categories.data', compact('productCategory'));
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $this->authorize('Edit Product Category');
        try {
            $productCategory->update($request->validated());
            return redirect()->route('product_categories.index')->with('success', 'Category updated successfully.');
        } catch (\Exception $exception) {
            info('Error::Place@ProductCategoryController@update - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('Delete Product Category');
        try {
            $category = ProductCategory::findOrFail($id);
            $category->delete();
            return response(['status' => 'warning', 'message' => 'Category deleted Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@ProductCategoryController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    public function restore($id)
    {
        $this->authorize('Restore Product Category');
        try {
            ProductCategory::withTrashed()->findOrFail($id)->restore();
            return response(['status' => 'success', 'message' => 'Category restored Successfully!']);
        } catch (\Exception $exception) {
            info('Error::Place@ProductCategoryController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }
}
