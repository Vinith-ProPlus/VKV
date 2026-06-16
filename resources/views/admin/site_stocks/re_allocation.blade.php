@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Stock Re-Allocation";
        $ActiveMenuName = 'Site-Stock-Management';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Transactions</li>
                        <li class="breadcrumb-item"><a href="{{ route('site-stocks.index') }}">Site Stock</a></li>
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
                        <h5>Stock Re-Allocation</h5>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('site-stocks.re_allocation.store') }}">
                            @csrf
                            <div class="row mb-15">
                                <div class="col-md-6">
                                    <label class="form-label">From Project <span class="text-danger">*</span></label>
                                    <select id="from_project_id" class="form-control" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">From Site <span class="text-danger">*</span></label>
                                    <select name="from_site_id" id="from_site_id" class="form-control @error('from_site_id') is-invalid @enderror" required>
                                        <option value="">Select Site</option>
                                    </select>
                                    @error('from_site_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-15">
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
                                <div class="col-md-6">
                                    <label class="form-label">Product <span class="text-danger">*</span></label>
                                    <select name="product_id" id="product_id" class="form-control @error('product_id') is-invalid @enderror" data-selected="{{ old('product_id') }}" required>
                                        <option value="">Select Product</option>
                                    </select>
                                    @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-15">
                                <div class="col-md-6">
                                    <label class="form-label">Available Stock</label>
                                    <input type="text" class="form-control" id="available_stock" readonly disabled value="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" step="0.01" min="0.01" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" required>
                                    @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-15">
                                <div class="col-md-6">
                                    <label class="form-label">To Project <span class="text-danger">*</span></label>
                                    <select id="to_project_id" class="form-control" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">To Site <span class="text-danger">*</span></label>
                                    <select name="to_site_id" id="to_site_id" class="form-control @error('to_site_id') is-invalid @enderror" required>
                                        <option value="">Select Site</option>
                                    </select>
                                    @error('to_site_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-15">
                                <div class="col-md-12">
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
                                    <button type="submit" class="btn btn-primary" id="submit_btn"><i class="fa fa-save"></i> Re-Allocate</button>
                                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning"><i class="fa fa-times"></i> Cancel</a>
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
            $('#from_project_id, #from_site_id, #category_id, #product_id, #to_project_id, #to_site_id').select2({
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });

            function loadSites(projectId, $target, placeholder, selectedId) {
                $target.empty().append('<option value="">' + placeholder + '</option>');

                if (!projectId) {
                    $target.trigger('change.select2');
                    return;
                }

                $.ajax({
                    url: "{{ route('getSites') }}",
                    data: { project_id: projectId },
                    success: function(data) {
                        $.each(data, function(key, site) {
                            const selected = selectedId && String(selectedId) === String(site.id) ? ' selected' : '';
                            $target.append('<option value="' + site.id + '"' + selected + '>' + site.site_no + '</option>');
                        });
                        $target.trigger('change.select2');
                    }
                });
            }

            $('#from_project_id').change(function() {
                loadSites($(this).val(), $('#from_site_id'), 'Select Site', "{{ old('from_site_id') }}");
                $('#product_id').html('<option value="">Select Product</option>').prop('disabled', true);
                $('#available_stock').val('0');
            });

            $('#to_project_id').change(function() {
                loadSites($(this).val(), $('#to_site_id'), 'Select Site', "{{ old('to_site_id') }}");
            });

            $('#from_site_id, #category_id').change(function() {
                const fromSiteId = $('#from_site_id').val();
                const categoryId = $('#category_id').val();
                const selectedProduct = $('#product_id').attr('data-selected');

                if (fromSiteId && categoryId) {
                    $.ajax({
                        url: "{{ route('stock-logs.get-products-by-category') }}",
                        data: {
                            site_id: fromSiteId,
                            category_id: categoryId
                        },
                        success: function(data) {
                            let options = '<option value="">Select Product</option>';

                            if (data.length > 0) {
                                $.each(data, function(key, product) {
                                    if(String(product.id) === String(selectedProduct)){
                                        options += '<option value="' + product.id + '" selected>' + product.name + '</option>';
                                    } else {
                                        options += '<option value="' + product.id + '">' + product.name + '</option>';
                                    }
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
                        }
                    });
                } else {
                    $('#product_id').html('<option value="">Select Product</option>');
                    $('#product_id').prop('disabled', true);
                    $('#available_stock').val('0');
                    $('#stock_alert').hide();
                }
            });

            $('#to_site_id, #from_site_id').change(function () {
                if ($('#to_site_id').val() && $('#to_site_id').val() === $('#from_site_id').val()) {
                    alert('From and To Site cannot be the same.');
                    $('#to_site_id').val(null).trigger('change');
                }
            });

            $('#product_id').change(function() {
                const fromSiteId = $('#from_site_id').val();
                const productId = $(this).val();

                if (fromSiteId && productId) {
                    $.ajax({
                        url: "{{ route('stock-logs.get-product-stock') }}",
                        data: {
                            site_id: fromSiteId,
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
                        }
                    });
                } else {
                    $('#available_stock').val('0');
                    $('#stock_alert').hide();
                }
            });

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
        });
    </script>
@endsection
