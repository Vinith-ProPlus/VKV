@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Roles and Permissions";
        $ActiveMenuName='Roles-and-Permissions';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
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
                                @can('Create Roles and Permissions')
                                    <a class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn"
                                       href="{{ route('role.create') }}">Add Roles</a>
                                @endCan
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
                                            <th><b>S.No</b></th>
                                            <th><b>Roles</b></th>
                                            <th><b>View</b></th>
                                            <th><b>Edit</b></th>
                                            <th><b>Delete</b></th>
                                        </tr>
                                        </thead>
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
        @can('View Roles and Permissions')
            $(function () {
            $('#list_table').DataTable({
                columnDefs: [
                    { className: "dt-center", targets: "_all" }
                ],
                serverSide: true,
                iDisplayLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                ajax: {
                    url: '{{ route('role.index') }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'DT_RowIndex' },
                    { data: 'name' },
                    { data: 'view', orderable: false },
                    { data: 'edit', orderable: false },
                    { data: 'delete', orderable: false }
                ]
            });

        });
        @endcan
    </script>
@endsection

