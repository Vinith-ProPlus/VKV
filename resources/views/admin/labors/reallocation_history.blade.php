@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Labor Re-Allocation History";
        $ActiveMenuName='Labor-Re-Allocation-History';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Projects</li>
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
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
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
                                            <th>Labor</th>
                                            <th>From Project</th>
                                            <th>To Project</th>
                                            <th>Reallocated By</th>
                                            <th>Remarks</th>
                                            <th>Date</th>
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
        @can('View Labors')
            $(function () {
                $('.select2').select2({ dropdownParent: $('#selectProjectModal') });
                $('#list_table').DataTable({
                    "columnDefs": [
                        {"className": "dt-center", "targets": "_all"}
                    ],
                    serverSide: true,
                    iDisplayLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    ajax: {
                        url: '{{ route("labor.reallocation.history") }}',
                        type: 'GET'
                    },
                    columns: [
                        { data: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'labor_name' },
                        { data: 'from_project_name' },
                        { data: 'to_project_name' },
                        { data: 'reallocatedBy' },
                        { data: 'remarks' },
                        { data: 'date' }
                    ]
                });
            });
        @endcan
    </script>
@endsection
