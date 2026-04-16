@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = 'Followups';
        $ActiveMenuName = 'Followups';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">CRM</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2">
                                <h5>{{ $PageTitle }}</h5>
                            </div>
                            <div class="col-sm-4 my-2 text-right text-md-right">
                                @can('Create Followups')
                                    <a class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn"
                                        href="{{ route('followups.create') }}">Add New Followup</a>
                                @endcan
                            </div>
                            <div class="row align-items-end justify-content-center">
                                <div class="col-sm-2">
                                    <div class="form-group text-center mh-60">
                                        <label style="margin-bottom: 0px;">Lead</label>
                                        <div id="divStatus">
                                            <select class="select2 form-control form-control-sm text-center" id="lead">
                                                <option value="">Select a Lead</option>
                                                @foreach ($leads as $data)
                                                    <option value="{{ $data->id }}">{{ $data->name }} -
                                                        {{ $data->mobile_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-auto">
                                    <button class="btn btn-md btn-danger mt-3" id="clearFilters">Clear</button>
                                </div>
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
                                                <th>Lead</th>
                                                <th>Project</th>
                                                <th>Site</th>
                                                <th>Status</th>
                                                <th>Remarks</th>
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
        @can('View Followups')
            let table;
            $(function() {
                table = $('#list_table').DataTable({
                    "columnDefs": [{
                        "className": "dt-center",
                        "targets": "_all"
                    }],
                    serverSide: true,
                    iDisplayLength: 10,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    ajax: {
                        url: '{{ route('followups.index') }}',
                        type: 'GET',
                        data: function(d) {
                            d.lead_id = $('#lead').val();
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex'
                        },
                        {
                            data: 'customer_name'
                        },
                        {
                            data: 'project_name'
                        },
                        {
                            data: 'site_number'
                        },
                        {
                            data: 'status_badge'
                        },
                        {
                            data: 'remarks'
                        },
                        {
                            data: 'action',
                            orderable: false
                        },
                    ]
                });

                $('#lead').select2({
                    placeholder: 'Select a Lead',
                    allowClear: true
                });

                // Filter on lead change
                $('#lead').on('change', function() {
                    table.draw();
                });

                // Clear filters
                $('#clearFilters').on('click', function() {
                    $('#lead').val('').trigger('change');
                    table.draw();
                });
            });
        @endcan
    </script>
@endsection
