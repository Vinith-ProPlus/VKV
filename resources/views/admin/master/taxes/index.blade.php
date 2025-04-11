@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Taxes";
        $ActiveMenuName='Tax';
    @endphp

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2"><h5>{{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right">
                                @can('Create Tax')
                                    <a class="btn btn-sm btn-primary" href="{{ route('taxes.create') }}">Add New Tax</a>
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
                                    <th>Tax Name</th>
                                    <th>Percentage (%)</th>
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
        @can('View Tax')
        $(function () {
            $('#list_table').DataTable({
                "columnDefs": [
                    {"className": "dt-center", "targets": "_all"}
                ],
                serverSide: true,
                ajax: '{{ route("taxes.index") }}',
                columns: [
                    {data: 'DT_RowIndex'},
                    {data: 'name'},
                    {data: 'percentage'},
                    {data: 'is_active'},
                    {data: 'action', orderable: false},
                ]
            });
        });
        @endcan
    </script>
@endsection
