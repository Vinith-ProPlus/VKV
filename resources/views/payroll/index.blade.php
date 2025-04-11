@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Payroll Management";
        $ActiveMenuName = 'Payroll';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Payroll</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-sm-12 col-lg-8">
                <div class="card">
                    <div class="row card-header text-center">
                        <div class="col-sm-4"></div>
                        <div class="col-sm-4 my-2"><h5>{{$PageTitle}}</h5></div>
                        <div class="col-sm-4 my-2 text-right text-md-right">
                            @can('View Payrolls')
                                <a class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn"
                                   href="{{ route('payroll.history') }}">History</a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <form id="payrollSearchForm">
                                    <div class="form-group">
                                        <div class="text-center">
                                        <label>Enter Labor Mobile Number</label>
                                        </div>
                                        <input type="text" id="mobile" name="mobile" class="form-control" required>
                                    </div>
                                    <div class="mt-15 text-center">
                                        <button type="button" id="payrollBtn" class="btn btn-primary w-25">Search</button>
                                    </div>
                                </form>
                                <div id="payrollData" class="mt-15" style="display: none;">
                                    <h5><b>Unpaid Records</b></h5>
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Select</th>
                                            <th>Date</th>
                                            <th>Salary</th>
                                        </tr>
                                        </thead>
                                        <tbody id="laborRecords"></tbody>
                                    </table>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <strong>Total Amount: </strong> <span id="totalAmount">0</span>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <button class="btn btn-success" id="confirmPayment" data-bs-toggle="modal" data-bs-target="#paymentModal" disabled>Proceed to Payment</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to process this payment?</p>
                    <input type="hidden" id="selectedLaborIds">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="processPayment">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('#payrollBtn').on('click', function (e) {
                e.preventDefault();
                let mobile = $('#mobile').val();

                $.ajax({
                    url: "{{ route('payroll.getUnpaidLabor') }}",
                    method: 'POST',
                    data: { mobile: mobile, _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        let tableBody = '';
                        response.labor_records.forEach(record => {
                            tableBody += `<tr>
                            <td><input type="checkbox" class="select-labor" data-id="${record.id}" data-salary="${record.salary}"></td>
                            <td>${record.date}</td>
                            <td>${record.salary}</td>
                        </tr>`;
                        });
                        $('#laborRecords').html(tableBody);
                        $('#payrollData').show();
                    },
                    error: function () {
                        alert('No unpaid records found');
                    }
                });
            });

            $(document).on('change', '.select-labor', function () {
                let totalAmount = 0;
                let selectedIds = [];
                $('.select-labor:checked').each(function () {
                    totalAmount += parseFloat($(this).data('salary'));
                    selectedIds.push($(this).data('id'));
                });
                $('#totalAmount').text(totalAmount);
                $('#selectedLaborIds').val(selectedIds.join(','));
                $('#confirmPayment').prop('disabled', selectedIds.length === 0);
            });

            $('#processPayment').on('click', function () {
                let selectedIds = $('#selectedLaborIds').val().split(',');

                $.ajax({
                    url: "{{ route('payroll.processPayment') }}",
                    method: 'POST',
                    data: { selected_labor_ids: selectedIds, _token: "{{ csrf_token() }}" },
                    success: function () {
                        alert('Payment processed successfully');
                        location.reload();
                    },
                    error: function () {
                        alert('Error processing payment');
                    }
                });
            });
        });
    </script>
@endsection
