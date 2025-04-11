@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Purchase Request";
        $ActiveMenuName = 'Purchase-Requests';
        $isEdit = $purchaseRequest && $purchaseRequest->id;
        $isConverted = $isEdit && $purchaseRequest->status === 'converted';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Purchases</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-sm-12 col-lg-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>{{ $isEdit ? ($isConverted ? 'View' : 'Edit') : 'Create' }} {{ $PageTitle }}</h5>
                    </div>

                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form
                            action="{{ $isEdit ? route('purchase-requests.update', $purchaseRequest->id) : route('purchase-requests.store') }}"
                            method="POST" id="purchaseRequestForm">
                            @csrf
                            @if($isEdit)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-md-4">
                                    <label for="project_id">Project <span class="text-danger">*</span></label>
                                    <select class="form-control select2 @error('project_id') is-invalid @enderror" name="project_id" id="project_id" required {{ $isConverted ? 'disabled' : '' }}>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option
                                                value="{{ $project->id }}" {{ $isEdit && $purchaseRequest->project_id == $project->id ? 'selected' : '' }}>
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                @if($isEdit)
                                    <div class="col-md-4">
                                        <label>Status</label>
                                        <div class="form-control">{{ ucfirst($purchaseRequest->status) }}</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label>Created By</label>
                                        <div class="form-control">{{ $purchaseRequest->supervisor->name ?? 'N/A' }}</div>
                                    </div>
                                @endif
                            </div>

                            <hr>
                            @if(!$isConverted)
                                <div class="card">
                                    <div class="row"
                                         style="background-color: #7167f430;padding: 20px;border-radius: 15px;box-shadow: 1px 10px 40px #e4e2fde3;">
                                        <div class="col-4">
                                            <label for="category_id"><strong>Category</strong></label>
                                            <select class="form-control select2" id="category_id">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <label for="product_id"><strong>Product</strong></label>
                                            <select class="form-control select2" id="product_id">
                                                <option value="">Select a Product</option>
                                            </select>
                                        </div>
                                        <div class="col-3">
                                            <label for="txtQuantity"><strong>Quantity</strong></label>
                                            <input class="form-control" id="txtQuantity" type="number" step="1" min="1">
                                        </div>
                                        <div class="col-2 align-self-end p-0">
                                            <a class="btn btn-sm" id="addProducts"
                                               style="background-color: #7167f4;color: #fff;">Add</a>
                                            <a class="btn btn-sm btn-warning d-none" id="updateProducts">Update</a>
                                            <a class="btn btn-sm ml-1 btn-danger" id="clearProducts">Clear</a>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <table
                                class="table table-hover {{ ($isEdit && count($purchaseRequest->details) > 0) ? '' : 'd-none' }} mt-20 form-group"
                                id="productsTable">
                                <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Category</th>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    @if(!$isConverted)
                                        <th>Actions</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody id="tblProducts">
                                @if($isEdit)
                                    @foreach($purchaseRequest->details as $index => $detail)
                                        <tr data-new="false">
                                            <td>{{ $index + 1 }}</td>
                                            <td data-category="{{ $detail->category->name }}">
                                                {{ $detail->category->name }}
                                                <input type="hidden" value="{{ $detail->category_id }}"
                                                       name="products[{{ $detail->id }}][category_id]"
                                                       data-id="{{ $detail->id }}">
                                            </td>
                                            <td data-product="{{ $detail->product->name }}">
                                                {{ $detail->product->name }}
                                                <input type="hidden" value="{{ $detail->product_id }}"
                                                       name="products[{{ $detail->id }}][product_id]"
                                                       data-id="{{ $detail->id }}">
                                            </td>
                                            <td data-quantity="{{ $detail->quantity }}">
                                                {{ $detail->quantity }}
                                                <input type="hidden" value="{{ $detail->quantity }}"
                                                       name="products[{{ $detail->id }}][quantity]"
                                                       data-id="{{ $detail->id }}">
                                            </td>
                                            @if(!$isConverted)
                                                <td>
                                                    <a class="btn btn-outline-primary editProducts"><i
                                                            class="fa fa-pencil"></i></a>
                                                    <a class="btn btn-danger deleteProducts"><i
                                                            class="fa fa-trash"></i></a>
                                                </td>
                                                <td data-product-data='{{ json_encode(["category_id" => $detail->category_id, "category_name" => $detail->category->name, "product_id" => $detail->product_id, "product_name" => $detail->product->name, "quantity" => $detail->quantity], JSON_THROW_ON_ERROR) }}'
                                                    class="d-none"></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                                </tbody>
                            </table>

                            <div class="text-center mt-15">
                                @if(!$isConverted)
                                    <button type="submit" class="btn btn-success">{{ $isEdit ? 'Update' : 'Submit' }}
                                        Request
                                    </button>
                                @endif
                                <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">Back</a>
                                @if($isEdit && !$isConverted)
                                    <button type="button" class="btn btn-primary" id="convertToPO"
                                            data-request-id="{{ $purchaseRequest->id }}">
                                        Convert to Purchase Order
                                    </button>
                                @elseif($isEdit && $isConverted)
                                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-info">
                                        View Purchase Orders
                                    </a>
                                @endif
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
        $(document).ready(function () {
            let tableEditId = 1;
            let deletedDocuments = [];
            let selectedProductRow = null;
            let productCellId = {{ $isEdit ? ($purchaseRequest->details->count() > 0 ? $purchaseRequest->details->max('id') + 1 : 0) : 0 }};
            let productUpdateId = 0;
            let isConverted = {{ $isConverted ? 'true' : 'false' }};

            $('.select2').select2();

            // Handle convert to purchase order button
            $('#convertToPO').on('click', function(e) {
                e.preventDefault();

                // First save the purchase request if there are any changes
                const formData = $('#purchaseRequestForm').serialize();

                $.ajax({
                    url: $('#purchaseRequestForm').attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Now redirect to purchase order create with the request ID
                        var requestId = $('#convertToPO').data('request-id');
                        var route = "{{ route('purchase-orders.create') }}";
                        window.location.href = route + '?request_id=' + requestId;
                    },
                    error: function(xhr) {
                        console.error('Error saving purchase request:', xhr);
                        alert('Please fix the errors in the form before converting to Purchase Order.');
                    }
                });
            });

            // Function to get product categories
            const getCategories = () => {
                let categoryID = $('#category_id');
                let selectedCategoryID = categoryID.attr('data-selected');
                if (categoryID.hasClass("select2-hidden-accessible")) {
                    categoryID.select2('destroy');
                }
                $('#category_id option').remove();
                categoryID.append('<option value="">--Select a Category--</option>');

                $.ajax({
                    url: "{{route('getCategories')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == selectedCategoryID)) {
                                categoryID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                categoryID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        categoryID.select2();
                    },
                    error: function (xhr) {
                        console.error('Error loading categories:', xhr);
                    }
                });
            }

            // Function to get products by category
            const getProducts = () => {
                let productID = $('#product_id');
                let selectedProductID = productID.attr('data-selected');
                if (productID.hasClass("select2-hidden-accessible")) {
                    productID.select2('destroy');
                }
                $('#product_id option').remove();
                productID.append('<option value="">--Select a Product--</option>');
                let categoryId = $('#category_id').val();

                if (!categoryId) return;

                $.ajax({
                    url: "{{route('getProductsByCategory')}}",
                    type: 'GET',
                    data: {category_id: categoryId},
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == selectedProductID)) {
                                productID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                productID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        productID.select2();
                    },
                    error: function (xhr) {
                        console.error('Error loading products:', xhr);
                    }
                });
            }

            // Skip initialization if already converted to PO
            if (!isConverted) {
                // Initialize categories dropdown
                getCategories();

                // Load products when category changes
                $('#category_id').change(function () {
                    getProducts();
                });

                // Add product to table
                $('#addProducts').on('click', function () {
                    let category = $('#category_id').find('option:selected');
                    let product = $('#product_id').find('option:selected');
                    let quantity = parseFloat($('#txtQuantity').val());

                    if (category.val() && product.val() && !isNaN(quantity)) {
                        let status = true;
                        let table = $('#tblProducts');

                        table.find('tr').each(function () {
                            let categoryId = $(this).find('td[data-category] input').val();
                            let productId = $(this).find('td[data-product] input').val();

                            if (categoryId === category.val() && productId === product.val()) {
                                status = false;
                                alert('This product already exists in the list.');
                                return false;
                            }
                        });

                        if (status) {
                            const obj = {
                                'category_id': category.val(),
                                'category_name': category.text(),
                                'product_id': product.val(),
                                'product_name': product.text(),
                                'quantity': quantity
                            };

                            let html = `
                            <tr data-new="true">
                                <td>*</td>
                                <td data-category="${category.text()}">
                                    ${category.text()}
                                    <input type="hidden" value="${category.val()}" name="products[${productCellId}][category_id]" data-id="${productCellId}">
                                </td>
                                <td data-product="${product.text()}">
                                    ${product.text()}
                                    <input type="hidden" value="${product.val()}" name="products[${productCellId}][product_id]" data-id="${productCellId}">
                                </td>
                                <td data-quantity="${quantity}">
                                    ${quantity}
                                    <input type="hidden" value="${quantity}" name="products[${productCellId}][quantity]" data-id="${productCellId}">
                                </td>
                                <td>
                                    <a class="btn btn-outline-primary editProducts"><i class="fa fa-pencil"></i></a>
                                    <a class="btn btn-danger deleteProducts"><i class="fa fa-trash"></i></a>
                                </td>
                                <td data-product-data='${JSON.stringify(obj)}' class="d-none">${JSON.stringify(obj)}</td>
                            </tr>`;

                            table.append(html);
                            productCellId++;
                            serializeTable('#tblProducts');

                            $('#productsTable').removeClass('d-none');
                            clearProductFields();
                        }
                    } else {
                        alert('Please select a category, product, and enter a valid quantity.');
                    }
                });

                // Clear form fields
                $('#clearProducts').on('click', function () {
                    $('#updateProducts').addClass('d-none');
                    $('#addProducts').removeClass('d-none');
                    clearProductFields();
                });

                // Edit row data
                $(document).on('click', '.editProducts', function () {
                    selectedProductRow = $(this).closest('tr');
                    productUpdateId = selectedProductRow.find('td:nth-child(2) input').attr('data-id');

                    let tdata;
                    try {
                        tdata = JSON.parse(selectedProductRow.find('td[data-product-data]').attr('data-product-data'));
                    } catch (e) {
                        console.error('Error parsing JSON data:', e);
                        return;
                    }

                    $('#category_id').val(tdata.category_id).attr('data-selected', tdata.category_id).trigger('change');

                    // Need to wait for category dropdown to load products
                    setTimeout(function () {
                        $('#product_id').attr('data-selected', tdata.product_id);
                        getProducts();

                        // Need to wait for products to load before setting value
                        setTimeout(function () {
                            $('#product_id').val(tdata.product_id).trigger('change');
                            $('#txtQuantity').val(tdata.quantity);
                        }, 500);
                    }, 500);

                    $('#addProducts').addClass('d-none');
                    $('#updateProducts').removeClass('d-none');
                });

                // Delete row
                $(document).on('click', '.deleteProducts', function () {
                    $(this).closest('tr').remove();
                    serializeTable('#tblProducts');

                    let isRowEmpty = $('#tblProducts').find('tr').length;
                    if (!isRowEmpty) {
                        $('#productsTable').addClass('d-none');
                    }
                });

                // Update row data
                $('#updateProducts').on('click', function () {
                    let category = $('#category_id').find('option:selected');
                    let product = $('#product_id').find('option:selected');
                    let quantity = parseFloat($('#txtQuantity').val());

                    if (category.val() && product.val() && !isNaN(quantity)) {
                        let status = true;
                        let table = $('#tblProducts');

                        table.find('tr').not(selectedProductRow).each(function () {
                            let categoryId = $(this).find('td:nth-child(2) input').val();
                            let productId = $(this).find('td:nth-child(3) input').val();

                            if (categoryId === category.val() && productId === product.val()) {
                                status = false;
                                alert('This product already exists in another row.');
                                return false;
                            }
                        });

                        if (status) {
                            const obj = {
                                'category_id': category.val(),
                                'category_name': category.text(),
                                'product_id': product.val(),
                                'product_name': product.text(),
                                'quantity': quantity
                            };

                            selectedProductRow.find('td:nth-child(2)').attr('data-category', category.text())
                                .html(`${category.text()}<input type="hidden" value="${category.val()}" name="products[${productUpdateId}][category_id]" data-id="${productUpdateId}">`);

                            selectedProductRow.find('td:nth-child(3)').attr('data-product', product.text())
                                .html(`${product.text()}<input type="hidden" value="${product.val()}" name="products[${productUpdateId}][product_id]" data-id="${productUpdateId}">`);

                            selectedProductRow.find('td:nth-child(4)').attr('data-quantity', quantity)
                                .html(`${quantity}<input type="hidden" value="${quantity}" name="products[${productUpdateId}][quantity]" data-id="${productUpdateId}">`);

                            selectedProductRow.find('td[data-product-data]').attr('data-product-data', JSON.stringify(obj)).text(JSON.stringify(obj));

                            $('#updateProducts').addClass('d-none');
                            $('#addProducts').removeClass('d-none');

                            selectedProductRow = null;
                            clearProductFields();
                        }
                    } else {
                        alert('Please select a category, product, and enter a valid quantity.');
                    }
                });
            }

            // Helper function to clear form fields
            const clearProductFields = () => {
                $('#category_id').attr('disabled', false);
                $('#product_id').attr('disabled', false);
                $('#category_id, #product_id').val(null).trigger('change');
                $('#txtQuantity').val('');
            };

            // Helper function to renumber rows
            const serializeTable = (selector) => {
                let i = 1;
                $(`${selector} tr`).each(function () {
                    $(this).find('td:first').text(i++);
                });
            };

            // Make sure table is visible if it has rows
            if ($('#tblProducts tr').length > 0) {
                $('#productsTable').removeClass('d-none');
            }
        });
    </script>
@endsection
