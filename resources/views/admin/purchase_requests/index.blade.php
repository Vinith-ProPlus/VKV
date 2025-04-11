@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Purchase Requests";
        $ActiveMenuName = 'Purchase-Requests';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Purchase Requests</li>
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
                                @can('Create Purchase Requests')
                                    <a class="btn btn-sm btn-primary add-btn" href="{{ route('purchase-requests.create') }}">Add New Request</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table text-center border rounded" id="list_table">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Supervisor</th>
                                    <th>Project</th>
                                    <th>Product Count</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody class="small"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        @can('View Purchase Requests')
        $(function () {
            $('#list_table').DataTable({
                "columnDefs": [{ "className": "dt-center", "targets": "_all" }],
                serverSide: true,
                iDisplayLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                ajax: {
                    url: '{{ route("purchase-requests.index") }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'DT_RowIndex' },
                    { data: 'supervisor.name', defaultContent: '-' },
                    { data: 'project.name', defaultContent: '-' },
                    { data: 'product_count' },
                    { data: 'status' },
                    { data: 'action', orderable: false },
                ]
            });
        });
        @endcan
    </script>
@endsection
