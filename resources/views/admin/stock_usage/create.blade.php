@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Log Stock Usage";
        $ActiveMenuName = 'Stock-Usage';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Stock</li>
                        <li class="breadcrumb-item"><a href="{{ route('stock-usages.index') }}">Stock Usage Logs</a></li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5>Log New Stock Usage</h5>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('stock-usages.store') }}">
                            @csrf
                            <div class="row mb-15">
                                <div class="col-md-6">
                                    <label class="form-label">Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-15">
                                <div class="col-md-6">
                                    <label class="form-label">Product <span class="text-danger">*</span></label>
                                    <select name="product_id" id="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Available Stock</label>
                                    <input type="text" class="form-control" id="available_stock" readonly disabled value="0">
                                </div>
                            </div>

                            <div class="row mb-15">
                                <div class="col-md-6">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" step="0.01" min="0.01" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                                    @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Taken By <span class="text-danger">*</span></label>
                                    <select name="taken_by" class="form-control @error('taken_by') is-invalid @enderror" required>
                                        <option value="">Select User</option>
                                        @foreach(\App\Models\User::all() as $user)
                                            <option value="{{ $user->id }}" {{ old('taken_by') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('taken_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-15">
                                <div class="col-md-6">
                                    <label class="form-label">Taken At <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="taken_at" class="form-control @error('taken_at') is-invalid @enderror" value="{{ old('taken_at') ?? now()->format('Y-m-d\TH:i') }}" required>
                                    @error('taken_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="3">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-4 d-none">
                                <div class="col-12">
                                    <div class="alert alert-info" id="stock_alert" style="display: none;">
                                        <i class="fa fa-info-circle"></i> <span id="stock_message"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <button type="submit" class="btn btn-primary" id="submit_btn">
                                        <i class="fa fa-save"></i> Log Stock Usage
                                    </button>
                                    <a href="{{ route('stock-usages.index') }}" class="btn btn-secondary">
                                        <i class="fa fa-times"></i> Cancel
                                    </a>
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
            $('#project_id, #category_id, #product_id').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });

            // When project or category changes, update product dropdown
            $('#project_id, #category_id').change(function() {
                const projectId = $('#project_id').val();
                const categoryId = $('#category_id').val();

                if (projectId && categoryId) {
                    $.ajax({
                        url: "{{ route('stock-usages.get-products-by-category') }}",
                        data: {
                            project_id: projectId,
                            category_id: categoryId
                        },
                        success: function(data) {
                            let options = '<option value="">Select Product</option>';

                            if (data.length > 0) {
                                $.each(data, function(key, product) {
                                    options += '<option value="' + product.id + '">' + product.name + '</option>';
                                });
                                $('#product_id').html(options);
                                $('#product_id').prop('disabled', false);
                            } else {
                                $('#product_id').html('<option value="">No products with stock available</option>');
                                $('#product_id').prop('disabled', true);
                                $('#available_stock').val('0');
                                $('#stock_alert').hide();
                            }
                            $('#product_id').trigger('change.select2');
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading products:", error);
                        }
                    });
                } else {
                    $('#product_id').html('<option value="">Select Product</option>');
                    $('#product_id').prop('disabled', true);
                    $('#available_stock').val('0');
                    $('#stock_alert').hide();
                }
            });

            // When product changes, update available stock
            $('#product_id').change(function() {
                const projectId = $('#project_id').val();
                const productId = $(this).val();

                if (projectId && productId) {
                    $.ajax({
                        url: "{{ route('stock-usages.get-product-stock') }}",
                        data: {
                            project_id: projectId,
                            product_id: productId
                        },
                        success: function(data) {
                            $('#available_stock').val(data.available_stock);

                            if (data.available_stock > 0) {
                                $('#stock_alert').removeClass('alert-danger').addClass('alert-success');
                                $('#stock_message').text('Available stock: ' + data.available_stock);
                                $('#stock_alert').show();
                                $('#submit_btn').prop('disabled', false);
                            } else {
                                $('#stock_alert').removeClass('alert-success').addClass('alert-danger');
                                $('#stock_message').text('No stock available for this product!');
                                $('#stock_alert').show();
                                $('#submit_btn').prop('disabled', true);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("Error loading stock:", error);
                        }
                    });
                } else {
                    $('#available_stock').val('0');
                    $('#stock_alert').hide();
                }
            });

            // Validate quantity against available stock
            $('input[name="quantity"]').on('input', function() {
                const quantity = parseFloat($(this).val()) || 0;
                const available = parseFloat($('#available_stock').val()) || 0;

                if (quantity > available) {
                    $('#stock_alert').removeClass('alert-success').addClass('alert-danger');
                    $('#stock_message').text('Requested quantity exceeds available stock (' + available + ')!');
                    $('#stock_alert').show();
                    $('#submit_btn').prop('disabled', true);
                } else if (quantity > 0 && quantity <= available) {
                    $('#stock_alert').removeClass('alert-danger').addClass('alert-success');
                    $('#stock_message').text('Available stock: ' + available);
                    $('#stock_alert').show();
                    $('#submit_btn').prop('disabled', false);
                } else {
                    $('#stock_alert').removeClass('alert-success').addClass('alert-danger');
                    $('#stock_message').text('Please enter a valid quantity!');
                    $('#stock_alert').show();
                    $('#submit_btn').prop('disabled', true);
                }
            });

            // Set default date to today
            if (!$('input[name="taken_at"]').val()) {
                const now = new Date();
                const localDatetime = now.toISOString().slice(0, 16);
                $('input[name="taken_at"]').val(localDatetime);
            }
        });
    </script>
@endsection
