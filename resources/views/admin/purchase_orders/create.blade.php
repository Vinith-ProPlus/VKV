@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Create Purchase Order";
        $ActiveMenuName = 'Purchase-Orders';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Purchase Orders</li>
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
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2"><h5>{{ $PageTitle }}</h5></div>
                            <div class="col-sm-4 my-2 text-right">
                                <a href="{{ route('purchase-orders.index') }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-list mr-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('purchase-orders.store') }}" method="POST" id="purchaseOrderForm">
                            @csrf

                            <!-- Hidden field for purchase request ID -->
                            <input type="hidden" name="purchase_request_id" value="{{ $purchaseRequest ? $purchaseRequest->id : '' }}" required>

                            <div class="col-md-12">
                                <div class="mb-5">
                                    <label for="project_id" class="form-label">Project</label>
                                    @if($purchaseRequest)
                                        <input type="text" class="form-control" value="{{ $project->name ?? 'N/A' }}" readonly>
                                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                                    @else
                                        <select name="project_id" class="form-control" required>
                                            <option value="">Select Project</option>
                                            @foreach($projects as $proj)
                                                <option value="{{ $proj->id }}" {{ (old('project_id', $purchaseRequest->project_id ?? '') == $proj->id) ? 'selected' : '' }}>{{ $proj->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('project_id')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-5">
                                <div class="col-md-12">
                                    <label for="remarks" class="form-label">Remarks (Optional)</label>
                                    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $purchaseRequest->remarks ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-10 mb-10">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>Products</h5>
                                    <button type="button" class="btn btn-sm btn-primary" id="addProductBtn">
                                        <i class="fa fa-plus"></i> Add Product
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="productsTable">
                                    <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Rate</th>
                                        <th>GST?</th>
                                        <th>GST %</th>
                                        <th>Total</th>
                                        <th>GST Value</th>
                                        <th>Total w/ GST</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody id="productsList">
                                    @if(isset($products) && $products->count() > 0)
                                        @foreach ($products as $index => $item)
                                            <tr data-product-id="{{ $item->product_id }}">
                                                <td>
                                                    <input type="hidden" name="products[{{ $index }}][category_id]" value="{{ $item->product->category_id }}">
                                                    {{ $item->product->category->name }}
                                                </td>
                                                <td>
                                                    <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                    {{ $item->product->name }}
                                                </td>
                                                <td>
                                                    <input type="number" step="1" class="form-control quantity" name="products[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" step="1" class="form-control rate" name="products[{{ $index }}][rate]" min="1" required />
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input gst-applicable" name="products[{{ $index }}][gst_applicable]" value="1" />
                                                </td>
                                                <td>
                                                    <input type="number" step="1" class="form-control gst-percentage" name="products[{{ $index }}][gst_percentage]" disabled />
                                                </td>
                                                <td>
                                                    <input type="text" readonly class="form-control total-amount" name="products[{{ $index }}][total_amount]" />
                                                </td>
                                                <td>
                                                    <input type="text" readonly class="form-control gst-value" name="products[{{ $index }}][gst_value]" />
                                                </td>
                                                <td>
                                                    <input type="text" readonly class="form-control total-with-gst" name="products[{{ $index }}][total_with_gst]" />
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-product">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>

                            <!-- Order Summary Section -->
                            <div class="card mt-15 mb-10">
                                <div class="card-header bg-primary">
                                    <h5 class="mb-0">Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 offset-md-6">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td class="text-right"><strong>Total Items:</strong></td>
                                                    <td width="150">
                                                        <span id="totalItems">0</span>
                                                        <input type="hidden" name="total_items">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><strong>Total Amount (Without GST):</strong></td>
                                                    <td>
                                                        <span id="totalAmount">₹0.00</span>
                                                        <input type="hidden" name="total_amount">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-right"><strong>Total GST:</strong></td>
                                                    <td>
                                                        <span id="totalGST">₹0.00</span>
                                                        <input type="hidden" name="total_gst">
                                                    </td>
                                                </tr>
                                                <tr class="bg-light">
                                                    <td class="text-right"><strong>Grand Total:</strong></td>
                                                    <td>
                                                        <span id="grandTotal" class="font-weight-bold">₹0.00</span>
                                                        <input type="hidden" name="grand_total">
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 text-right">
                                    <button type="submit" class="btn btn-success" id="submitBtn">Create Purchase Order</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Selection Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Category</label>
                        <select class="form-control" id="categorySelect">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-15">
                        <label>Product</label>
                        <select class="form-control" id="productSelect" disabled>
                            <option value="">Select Product</option>
                        </select>
                    </div>
                    <div class="form-group mt-15">
                        <label>Quantity</label>
                        <input type="number" class="form-control" id="productQuantity" min="1" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmAddProduct">Add Product</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            // Product categories and products array
            let categories = @json($categories);

            // Counter for new products
            let newProductIndex = {{ isset($products) ? $products->count() : 0 }};

            $('#categorySelect, #productSelect').select2({
                dropdownParent: $('#productModal'),
                width: '100%',
                placeholder: 'Select an option',
                allowClear: true
            });

            // Calculate totals on input change
            $(document).on('input change', '.quantity, .rate, .gst-applicable, .gst-percentage', function () {
                let row = $(this).closest('tr');
                calculateRowTotals(row);
                updateOrderSummary();
            });

            // Toggle GST percentage field based on checkbox
            $(document).on('change', '.gst-applicable', function() {
                let row = $(this).closest('tr');
                let gstPercentageField = row.find('.gst-percentage');

                if ($(this).is(':checked')) {
                    gstPercentageField.prop('disabled', false).val(18); // Default GST percentage
                } else {
                    gstPercentageField.prop('disabled', true).val('');
                }

                calculateRowTotals(row);
                updateOrderSummary();
            });

            // Calculate row totals
            function calculateRowTotals(row) {
                let quantity = parseFloat(row.find('.quantity').val()) || 0;
                let rate = parseFloat(row.find('.rate').val()) || 0;
                let gstApplicable = row.find('.gst-applicable').is(':checked');
                let gstPercentage = gstApplicable ? (parseFloat(row.find('.gst-percentage').val()) || 0) : 0;

                let totalAmount = quantity * rate;
                let gstValue = (totalAmount * gstPercentage) / 100;
                let totalWithGst = totalAmount + gstValue;

                row.find('.total-amount').val(totalAmount.toFixed(2));
                row.find('.gst-value').val(gstValue.toFixed(2));
                row.find('.total-with-gst').val(totalWithGst.toFixed(2));
            }

            // Update order summary totals
            function updateOrderSummary() {
                let totalItems = 0;
                let totalAmount = 0;
                let totalGST = 0;
                let grandTotal = 0;

                // Calculate total from each row
                $('#productsList tr').each(function() {
                    let quantity = parseInt($(this).find('.quantity').val()) || 0;
                    let amount = parseFloat($(this).find('.total-amount').val()) || 0;
                    let gst = parseFloat($(this).find('.gst-value').val()) || 0;
                    let total = parseFloat($(this).find('.total-with-gst').val()) || 0;

                    totalItems += quantity;
                    totalAmount += amount;
                    totalGST += gst;
                    grandTotal += total;
                });

                // Update the summary fields
                $('#totalItems').text(totalItems);
                $('#totalAmount').text('₹' + totalAmount.toFixed(2));
                $('#totalGST').text('₹' + totalGST.toFixed(2));
                $('#grandTotal').text('₹' + grandTotal.toFixed(2));

                // Update hidden fields for form submission
                $('input[name="total_items"]').val(totalItems);
                $('input[name="total_amount"]').val(totalAmount.toFixed(2));
                $('input[name="total_gst"]').val(totalGST.toFixed(2));
                $('input[name="grand_total"]').val(grandTotal.toFixed(2));
            }

            // Show add product modal
            $('#addProductBtn').click(function() {
                $('#categorySelect').val('').trigger('change.select2');
                $('#productSelect').val('').prop('disabled', true).trigger('change.select2');
                $('#productQuantity').val(1);
                $('#productModal').modal('show');
            });

            $('#categorySelect').change(function() {
                let categoryId = $(this).val();
                let productSelect = $('#productSelect');

                productSelect.empty().append('<option value="">Select Product</option>');

                if (categoryId) {
                    // Find the category in our array
                    let category = categories.find(c => c.id == categoryId);
                    if (category && category.products) {
                        // Filter out products that are already in the table
                        let existingProductIds = [];
                        $('#productsList tr').each(function() {
                            existingProductIds.push($(this).data('product-id'));
                        });

                        let availableProducts = category.products.filter(p => !existingProductIds.includes(p.id));

                        // Add products to dropdown
                        $.each(availableProducts, function(index, product) {
                            productSelect.append(`<option value="${product.id}" data-name="${product.name}">${product.name}</option>`);
                        });

                        productSelect.prop('disabled', false);
                    }
                } else {
                    productSelect.prop('disabled', true);
                }

                // Refresh select2 after changing options
                productSelect.trigger('change.select2');
            });

            // Add product to table
            $('#confirmAddProduct').click(function() {
                let categoryId = $('#categorySelect').val();
                let productId = $('#productSelect').val();
                let quantity = $('#productQuantity').val();

                if (!categoryId || !productId || !quantity) {
                    alert('Please select a category, product, and quantity');
                    return;
                }

                // Check if product already exists
                if ($(`#productsList tr[data-product-id="${productId}"]`).length > 0) {
                    alert('This product is already in the list');
                    return;
                }

                let categoryName = $('#categorySelect option:selected').text();
                let productName = $('#productSelect option:selected').text();

                let newRow = `
                    <tr data-product-id="${productId}">
                        <td>
                            <input type="hidden" name="products[${newProductIndex}][category_id]" value="${categoryId}">
                            ${categoryName}
                        </td>
                        <td>
                            <input type="hidden" name="products[${newProductIndex}][product_id]" value="${productId}">
                            ${productName}
                        </td>
                        <td>
                            <input type="number" step="1" class="form-control quantity" name="products[${newProductIndex}][quantity]" value="${quantity}" min="1" required>
                        </td>
                        <td>
                            <input type="number" step="1" class="form-control rate" name="products[${newProductIndex}][rate]" min="1" required>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input gst-applicable" name="products[${newProductIndex}][gst_applicable]" value="1">
                        </td>
                        <td>
                            <input type="number" step="1" class="form-control gst-percentage" name="products[${newProductIndex}][gst_percentage]" disabled>
                        </td>
                        <td>
                            <input type="text" readonly class="form-control total-amount" name="products[${newProductIndex}][total_amount]">
                        </td>
                        <td>
                            <input type="text" readonly class="form-control gst-value" name="products[${newProductIndex}][gst_value]">
                        </td>
                        <td>
                            <input type="text" readonly class="form-control total-with-gst" name="products[${newProductIndex}][total_with_gst]">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-product">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;

                $('#productsList').append(newRow);
                newProductIndex++;

                $('#productModal').modal('hide');

                // Calculate totals for the new row
                calculateRowTotals($('#productsList tr:last'));
                updateOrderSummary();
            });

            // Remove product row
            $(document).on('click', '.remove-product', function() {
                $(this).closest('tr').remove();

                // Reindex the remaining rows
                reindexProductRows();

                // Update order summary after removing a product
                updateOrderSummary();
            });

            // Reindex product rows after removal
            function reindexProductRows() {
                $('#productsList tr').each(function(index) {
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        if (name) {
                            let newName = name.replace(/products\[\d+\]/, `products[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });
            }

            // Form validation before submit
            $('#purchaseOrderForm').submit(function(e) {
                let productRows = $('#productsList tr');

                if (productRows.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one product to the purchase order');
                    return false;
                }

                // Check that all required fields are filled
                let isValid = true;
                productRows.each(function() {
                    let rate = $(this).find('.rate').val();
                    if (!rate || rate <= 0) {
                        isValid = false;
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all product rates');
                    return false;
                }

                return true;
            });

            // Initialize calculations for existing products
            $('#productsList tr').each(function() {
                calculateRowTotals($(this));
            });

            // Initialize order summary
            updateOrderSummary();
        });
    </script>
@endsection
