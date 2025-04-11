@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Purchase Order";
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
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('purchase-orders.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="order_date" class="form-label">Order Date</label>
                                <input type="date" name="order_date" class="form-control" required>
                            </div>

                            <table class="table table-bordered">
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
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($products as $index => $item)
                                    <tr>
                                        <td>{{ $item->product->category->name }}</td>
                                        <td>{{ $item->product->name }}</td>
                                        <td>
                                            <input type="number" step="1" class="form-control quantity" name="products[{{ $index }}][quantity]" value="{{ $item->quantity }}">
                                            <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                            <input type="hidden" name="products[{{ $index }}][category_id]" value="{{ $item->product->category_id }}">
                                        </td>
                                        <td><input type="number" step="0.01" class="form-control rate" name="products[{{ $index }}][rate]" /></td>
                                        <td><input type="checkbox" class="form-check-input gst-applicable" name="products[{{ $index }}][gst_applicable]" /></td>
                                        <td><input type="number" step="0.1" class="form-control gst-percentage" name="products[{{ $index }}][gst_percentage]" /></td>
                                        <td><input type="text" readonly class="form-control total-amount" name="products[{{ $index }}][total_amount]" /></td>
                                        <td><input type="text" readonly class="form-control gst-value" name="products[{{ $index }}][gst_value]" /></td>
                                        <td><input type="text" readonly class="form-control total-with-gst" name="products[{{ $index }}][total_with_gst]" /></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            <button type="submit" class="btn btn-success">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).on('input change', '.quantity, .rate, .gst-applicable, .gst-percentage', function () {
            let row = $(this).closest('tr');

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
        });
    </script>
@endsection
