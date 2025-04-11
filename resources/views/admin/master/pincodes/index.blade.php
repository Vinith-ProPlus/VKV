@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Pincodes";
        $ActiveMenuName='Pincodes';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item">{{$PageTitle}}</li>
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
                            <div class="col-sm-4 my-2"><h5>{{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right">
                                @can('Create Pincodes')
                                    <a class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn"
                                        href="{{ route('pincodes.create') }}">Add New Pincode</a>
                                @endcan
                            </div>
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
                                            <th>Pincode</th>
                                            <th>City</th>
                                            <th>Status</th>
                                            <th>Actions</th>
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
        @can('View Pincodes')
            $(function () {
                $('#list_table').DataTable({
                    "columnDefs": [
                        {"className": "dt-center", "targets": "_all"}
                    ],
                    serverSide: true,
                    iDisplayLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    ajax: {
                        url: '{{ route("pincodes.index") }}',
                        type: 'GET'
                    },
                    columns: [
                        {data: 'DT_RowIndex'},
                        {data: 'pincode'},
                        {data: 'city_name'},
                        {data: 'is_active'},
                        {data: 'action', orderable: false},
                    ]
                });
            });
        @endcan
    </script>
@endsection
