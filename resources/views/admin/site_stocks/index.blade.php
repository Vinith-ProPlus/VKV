@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Site Stock Management";
        $ActiveMenuName = 'Site-Stock-Management';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Transactions</li>
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
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2"><h5>{{ $PageTitle }}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right">
                                @can('Edit Project Stocks')
                                    <a href="{{ route('site-stocks.re_allocation') }}" type="button" class="btn btn-secondary btn-sm">
                                        <i class="fa fa-book"></i> Stock Re-Allocation </a>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="$('#adjustStockModal').modal('show');">
                                        <i class="fa fa-edit"></i> Adjust Stock </button>
                                @endcan
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-center">
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">Projects</label>
                                    <select class="form-control form-control-sm text-center" id="project_filter">
                                        <option value="">All Projects</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">Sites</label>
                                    <select class="form-control form-control-sm text-center" id="site_filter">
                                        <option value="">Select a Site</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2 d-flex align-items-center justify-content-center">
                                <button class="btn btn-sm btn-danger mt-3" id="clearFilters">Clear Filters</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="stocksTable" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th>Site</th>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Last Updated</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="adjustStockModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock</h5>
                    <button type="button" class="close" onclick="$('#adjustStockModal').modal('hide');">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="adjustStockForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Project</label>
                            <select id="adjust_project_id" class="form-control" required>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mt-15">
                            <label>Site</label>
                            <select name="site_id" class="form-control" required>
                                <option value="">Select Site</option>
                            </select>
                        </div>
                        <div class="form-group mt-15">
                            <label>Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                            </select>
                        </div>
                        <div class="form-group mt-15">
                            <label>Product</label>
                            <select name="product_id" class="form-control" required>
                                <option value="">Select Product</option>
                            </select>
                        </div>
                        <div class="form-group mt-15">
                            <label>Adjustment Type</label>
                            <select name="adjustment_type" class="form-control" required>
                                <option value="add">Add Quantity</option>
                                <option value="subtract">Subtract Quantity</option>
                                <option value="set">Set Exact Quantity</option>
                            </select>
                        </div>
                        <div class="form-group mt-15">
                            <label>Current Quantity</label>
                            <input type="text" id="current_quantity" class="form-control" readonly>
                        </div>
                        <div class="form-group mt-15">
                            <label>Quantity</label>
                            <input type="number" name="quantity" class="form-control" min="0.01" step="0.01" required>
                        </div>
                        <div class="form-group mt-15">
                            <label>Reason</label>
                            <textarea type="text" name="reason" class="form-control" maxlength="255" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="$('#adjustStockModal').modal('hide');">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#clearFilters').click(clearFilter);

            function loadSites(projectId, $target, placeholder) {
                $target.empty().append('<option value="">' + placeholder + '</option>');

                $.ajax({
                    url: "{{ route('getSites') }}",
                    type: 'GET',
                    data: { project_id: projectId || '' },
                    dataType: 'json',
                    success: function(data) {
                        $.each(data, function(key, site) {
                            $target.append('<option value="' + site.id + '">' + site.site_no + '</option>');
                        });
                        if ($target.is('#site_filter')) {
                            $target.multiselect('rebuild');
                        } else {
                            $target.trigger('change.select2');
                        }
                    }
                });
            }

            function initMultiSelect() {
                $('#project_filter, #site_filter').multiselect({
                    buttonClass: 'btn btn-link',
                    enableFiltering: true,
                    maxHeight: 250,
                });
                $('select[name="site_id"]').select2({ dropdownParent: $('#adjustStockModal') });
                $('select[name="category_id"]').select2({ dropdownParent: $('#adjustStockModal') });
                $('select[name="product_id"]').select2({ dropdownParent: $('#adjustStockModal') });
                $('#adjust_project_id').select2({ dropdownParent: $('#adjustStockModal') });
            }

            function clearFilter() {
                $('#project_filter').val('').multiselect('refresh');
                $('#site_filter').val('').multiselect('refresh');
                loadSites('', $('#site_filter'), 'Select a Site');
                table.ajax.reload();
            }

            initMultiSelect();
            loadSites('', $('#site_filter'), 'Select a Site');

            let table = $('#stocksTable').DataTable({
                "columnDefs": [{"className": "dt-center", "targets": "_all"}],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('site-stocks.index') }}",
                    data: function (d) {
                        d.project_id = $('#project_filter').val();
                        d.site_id = $('#site_filter').val();
                    }
                },
                columns: [
                    {data: 'site_label', name: 'site.site_no'},
                    {data: 'category.name', name: 'category.name'},
                    {data: 'product.name', name: 'product.name'},
                    {data: 'quantity', name: 'quantity'},
                    {data: 'last_updated', name: 'updated_at'}
                ]
            });

            $('#project_filter').change(function() {
                loadSites($(this).val(), $('#site_filter'), 'Select a Site');
                table.ajax.reload();
            });

            $('#site_filter').change(function() {
                table.ajax.reload();
            });

            $('#adjust_project_id').change(function() {
                loadSites($(this).val(), $('select[name="site_id"]'), 'Select Site');
                resetSelect($('select[name="category_id"]'));
                resetSelect($('select[name="product_id"]'));
                $('#current_quantity').val('');
            });

            $('select[name="site_id"]').change(function() {
                var siteId = $(this).val();
                var $categorySelect = $('select[name="category_id"]');
                var $productSelect = $('select[name="product_id"]');

                resetSelect($categorySelect);
                resetSelect($productSelect);
                $('#current_quantity').val('');

                if (siteId) {
                    $.ajax({
                        url: "{{ route('site-stocks.get-categories') }}",
                        type: 'GET',
                        data: { site_id: siteId },
                        dataType: 'json',
                        success: function(data) {
                            $categorySelect.empty().append('<option value="">Select Category</option>');
                            $.each(data, function(key, category) {
                                $categorySelect.append('<option value="' + category.id + '">' + category.name + '</option>');
                            });
                            $categorySelect.select2({ dropdownParent: $('#adjustStockModal') });
                        }
                    });
                }
            });

            $('select[name="category_id"]').change(function() {
                var siteId = $('select[name="site_id"]').val();
                var categoryId = $(this).val();
                var $productSelect = $('select[name="product_id"]');

                resetSelect($productSelect);
                $('#current_quantity').val('');

                if (categoryId && siteId) {
                    $.ajax({
                        url: "{{ route('site-stocks.get-products') }}",
                        type: 'GET',
                        data: { site_id: siteId, category_id: categoryId },
                        dataType: 'json',
                        success: function(data) {
                            $productSelect.empty().append('<option value="">Select Product</option>');
                            $.each(data, function(key, product) {
                                $productSelect.append('<option value="' + product.id + '">' + product.name + '</option>');
                            });
                            $productSelect.select2({ dropdownParent: $('#adjustStockModal') });
                        }
                    });
                }
            });

            $('select[name="product_id"]').change(function() {
                var siteId = $('select[name="site_id"]').val();
                var productId = $(this).val();

                if (siteId && productId) {
                    $.ajax({
                        url: "{{ route('site-stocks.get-stock') }}",
                        type: 'GET',
                        data: { site_id: siteId, product_id: productId },
                        dataType: 'json',
                        success: function(data) {
                            $('#current_quantity').val(data.quantity);
                        }
                    });
                } else {
                    $('#current_quantity').val('');
                }
            });

            function resetSelect($select) {
                if ($select.hasClass("select2-hidden-accessible")) {
                    $select.select2('destroy');
                }
                $select.empty().append('<option value="">Select</option>');
                $select.select2({ dropdownParent: $('#adjustStockModal') });
            }

            $('#adjustStockForm').submit(function(e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('site-stocks.adjust') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if(response.success) {
                            $('#adjustStockModal').modal('hide');
                            $('#adjustStockForm')[0].reset();
                            table.ajax.reload();
                            toastr.success('Stock adjusted successfully');
                        }
                    },
                    error: function(xhr) {
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Error adjusting stock');
                        }
                    }
                });
            });

            $('#adjustStockModal').on('hidden.bs.modal', function() {
                $('#adjustStockForm')[0].reset();
                resetSelect($('select[name="category_id"]'));
                resetSelect($('select[name="product_id"]'));
                $('select[name="site_id"]').empty().append('<option value="">Select Site</option>');
                $('#current_quantity').val('');
            });
        });
    </script>
@endsection
