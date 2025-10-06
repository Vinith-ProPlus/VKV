<!-- resources/views/admin/warehouse_stocks/project_return.blade.php -->
@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Project Return to Warehouse";
        $ActiveMenuName = 'Warehouse-Stock-Management';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Transactions</li>
                        <li class="breadcrumb-item"><a href="{{ route('warehouse-stocks.index') }}">Warehouse Stock Management</a></li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-sm-4 text-left">
                                <a href="{{ route('warehouse-stocks.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Warehouse Stock
                                </a>
                            </div>
                            <div class="col-sm-4 my-2"><h5>{{$PageTitle}}</h5></div>
                            <div class="col-sm-4"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('warehouse-stocks.project-return-store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="project_id">Project <span class="text-danger">*</span></label>
                                        <select id="project_id" name="project_id" class="form-control select2" required>
                                            <option value="">Select Project</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('project_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="category_id">Category <span class="text-danger">*</span></label>
                                        <select id="category_id" name="category_id" class="form-control select2" required disabled>
                                            <option value="">Select Category</option>
                                        </select>
                                        @error('category_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="product_id">Product <span class="text-danger">*</span></label>
                                        <select id="product_id" name="product_id" class="form-control select2" required disabled>
                                            <option value="">Select Product</option>
                                        </select>
                                        @error('product_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="current_quantity">Current Project Quantity</label>
                                        <input type="text" id="current_quantity" class="form-control" readonly>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="quantity">Return Quantity <span class="text-danger">*</span></label>
                                        <input type="number" id="quantity" name="quantity" class="form-control" min="0.01" step="0.01" required>
                                        @error('quantity')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-3">
                                        <label for="warehouse_id">Return To Warehouse <span class="text-danger">*</span></label>
                                        <select id="warehouse_id" name="warehouse_id" class="form-control select2" required>
                                            <option value="">Select Warehouse</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('warehouse_id')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <div class="form-group">
                                        <label for="remarks">Remarks</label>
                                        <textarea id="remarks" name="remarks" class="form-control" rows="3" maxlength="255"></textarea>
                                        @error('remarks')
                                        <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 mt-4 text-center">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-check-circle"></i> Submit Return
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2();

            // When project is selected, load categories
            $('#project_id').change(function() {
                var projectId = $(this).val();
                var $categorySelect = $('#category_id');
                var $productSelect = $('#product_id');

                // Reset category and product dropdowns
                resetSelect($categorySelect);
                resetSelect($productSelect);
                $('#current_quantity').val('');

                if (projectId) {
                    $categorySelect.prop('disabled', false);

                    // Fetch categories for the selected project
                    $.ajax({
                        url: "{{ route('warehouse-stocks.get-categories') }}",
                        type: 'GET',
                        data: { project_id: projectId },
                        dataType: 'json',
                        success: function(data) {
                            $categorySelect.empty().append('<option value="">Select Category</option>');

                            $.each(data, function(key, category) {
                                $categorySelect.append(
                                    '<option value="' + category.id + '">' + category.name + '</option>'
                                );
                            });

                            // Re-initialize Select2
                            $categorySelect.select2();
                        },
                        error: function() {
                            toastr.error('Error loading categories');
                        }
                    });
                } else {
                    $categorySelect.prop('disabled', true);
                    $productSelect.prop('disabled', true);
                }
            });

            // When category is selected, load products
            $('#category_id').change(function() {
                var projectId = $('#project_id').val();
                var categoryId = $(this).val();
                var $productSelect = $('#product_id');

                // Reset product dropdown
                resetSelect($productSelect);
                $('#current_quantity').val('');

                if (categoryId && projectId) {
                    $productSelect.prop('disabled', false);

                    // Fetch products for the selected category and project
                    $.ajax({
                        url: "{{ route('warehouse-stocks.get-products') }}",
                        type: 'GET',
                        data: {
                            project_id: projectId,
                            category_id: categoryId
                        },
                        dataType: 'json',
                        success: function(data) {
                            $productSelect.empty().append('<option value="">Select Product</option>');

                            $.each(data, function(key, product) {
                                $productSelect.append(
                                    '<option value="' + product.id + '">' + product.name + '</option>'
                                );
                            });

                            // Re-initialize Select2
                            $productSelect.select2();
                        },
                        error: function() {
                            toastr.error('Error loading products');
                        }
                    });
                } else {
                    $productSelect.prop('disabled', true);
                }
            });

            // When product is selected, fetch current quantity
            $('#product_id').change(function() {
                var projectId = $('#project_id').val();
                var productId = $(this).val();

                if (projectId && productId) {
                    // Fetch current stock quantity
                    $.ajax({
                        url: "{{ route('warehouse-stocks.get-stock') }}",
                        type: 'GET',
                        data: {
                            project_id: projectId,
                            product_id: productId
                        },
                        dataType: 'json',
                        success: function(data) {
                            $('#current_quantity').val(data.quantity);

                            // Set max value for quantity input
                            $('#quantity').attr('max', parseFloat(data.quantity));
                        },
                        error: function() {
                            toastr.error('Error loading stock information');
                        }
                    });
                } else {
                    $('#current_quantity').val('');
                }
            });

            // Validate quantity doesn't exceed available stock
            $('#quantity').on('input', function() {
                var currentQty = parseFloat($('#current_quantity').val().replace(/,/g, '')) || 0;
                var returnQty = parseFloat($(this).val()) || 0;

                if (returnQty > currentQty) {
                    toastr.warning('Return quantity cannot exceed available project stock');
                    $(this).val(currentQty);
                }
            });

            // Helper function to reset select elements
            function resetSelect($select) {
                $select.empty().append('<option value="">Select</option>');
                $select.prop('disabled', true);
                $select.select2();
            }
        });
    </script>
@endsection
