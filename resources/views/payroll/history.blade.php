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
                            <a href="javascript:void(0)" onclick="window.history.back()" type="button"
                               class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <div class="table-responsive">
                                    <table class="table text-center border rounded" id="list_table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>S.No</th>
                                            <th>Name</th>
                                            <th>Mobile Number</th>
                                            <th>Project</th>
                                            <th>Work Date</th>
                                            <th>Paid Amount</th>
                                            <th>Paid Date</th>
                                        </tr>
                                        </thead>
                                        <tbody class="small">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        @can('View Payrolls')
        $(function () {
            $('#list_table').DataTable({
                "columnDefs": [
                    {"className": "dt-center", "targets": "_all"}
                ],
                serverSide: true,
                iDisplayLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                ajax: {
                    url: '{{ route("payroll.history") }}',
                    type: 'GET'
                },
                columns: [
                    {data: 'DT_RowIndex'},
                    {data: 'name'},
                    {data: 'mobile'},
                    {data: 'project'},
                    {data: 'work_date'},
                    {data: 'paid_amount'},
                    {data: 'paid_date'},
                ]
            });
        });
        @endcan
    </script>
@endsection
