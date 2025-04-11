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

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="order_date" class="form-label">Order Date</label>
                                        <input type="date" name="order_date" class="form-control" required value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="project_id" class="form-label">Project</label>
                                        @if($purchaseRequest)
                                            <input type="text" class="form-control" value="{{ $project->name ?? 'N/A' }}" readonly>
                                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                                        @else
                                            <select name="project_id" class="form-control" required>
                                                <option value="">Select Project</option>
                                                @foreach($projects as $proj)
                                                    <option value="{{ $proj->id }}">{{ $proj->name }}</option>
                                                @endforeach
                                            </select>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="supervisor_id" class="form-label">Supervisor</label>
                                        <select name="supervisor_id" class="form-control" required>
                                            <option value="">Select Supervisor</option>
                                            @foreach(App\Models\User::whereHas('roles', function($q) { $q->where('name', 'Supervisor'); })->get() as $supervisor)
                                                <option value="{{ $supervisor->id }}" {{ $purchaseRequest && $purchaseRequest->supervisor_id == $supervisor->id ? 'selected' : '' }}>
                                                    {{ $supervisor->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="remarks" class="form-label">Remarks (Optional)</label>
                                    <textarea name="remarks" class="form-control" rows="2">{{ $purchaseRequest->remarks ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="mb-3">
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
                                                    <input type="number" step="0.01" class="form-control rate" name="products[{{ $index }}][rate]" min="0.01" required />
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input gst-applicable" name="products[{{ $index }}][gst_applicable]" value="1" />
                                                </td>
                                                <td>
                                                    <input type="number" step="0.1" class="form-control gst-percentage" name="products[{{ $index }}][gst_percentage]" disabled />
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
        <div class="modal-dialog modal-lg" role="document">
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
                    <div class="form-group">
                        <label>Product</label>
                        <select class="form-control" id="productSelect" disabled>
                            <option value="">Select Product</option>
                        </select>
                    </div>
                    <div class="form-group">
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

            // Calculate totals on input change
            $(document).on('input change', '.quantity, .rate, .gst-applicable, .gst-percentage', function () {
                let row = $(this).closest('tr');
                calculateRowTotals(row);
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

            // Show add product modal
            $('#addProductBtn').click(function() {
                $('#categorySelect').val('');
                $('#productSelect').val('').prop('disabled', true);
                $('#productQuantity').val(1);
                $('#productModal').modal('show');
            });

            // Load products when category is selected
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
                            <input type="number" step="0.01" class="form-control rate" name="products[${newProductIndex}][rate]" min="0.01" required>
                        </td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input gst-applicable" name="products[${newProductIndex}][gst_applicable]" value="1">
                        </td>
                        <td>
                            <input type="number" step="0.1" class="form-control gst-percentage" name="products[${newProductIndex}][gst_percentage]" disabled>
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
            });

            // Remove product row
            $(document).on('click', '.remove-product', function() {
                $(this).closest('tr').remove();

                // Reindex the remaining rows
                reindexProductRows();
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
        });
    </script>
@endsection
