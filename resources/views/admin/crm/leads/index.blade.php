@extends('layouts.admin')

@section('style')
<style>
    .multiselect-native-select button{
        border: none;
        border-radius: 0px;
    }
</style>
@endsection
@section('content')
    @php
        $PageTitle="Lead";
        $ActiveMenuName='Lead';
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
                                @can('Create Lead')
                                    <a class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn"
                                        href="{{ route('leads.create') }}">Add New Lead</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($isSuperAdmin)
                        <div class="row mb-4 justify-content-center">
                            <div class="col-auto text-center">
                                <label for="lead_owner_filter" class="form-label fw-bold mb-2">Lead Owner</label>
                                <br>
                                <select id="lead_owner_filter" class="form-multi-select" multiple data-coreui-search="true">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <div class="table-responsive">
                                    <table class="table text-center border rounded" id="list_table">
                                        <thead class="thead-light">
                                        <tr>
                                            <th>S.No</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Area</th>
                                            <th>Lead Source</th>
                                            @if($isSuperAdmin)
                                            <th>Lead Owner</th>
                                            @endif
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
@section('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/2.5.2/css/bootstrap-multiselect.min.css">
    <link>
        .multiselect-container {
            max-height: 300px;
            overflow-y: auto;
        }
        .multiselect-container > li > label > input {
            margin-right: 8px;
        }
        .multiselect {
            width: 100%;
            border-radius: 0.375rem;
        }
        .btn-default {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #495057;
        }
        .btn-default:hover {
            background-color: #e2e6ea;
        }
    </link>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/2.5.2/js/bootstrap-multiselect.min.js"></script>
    <script>
        @can('View Lead')
            $(function () {
                let table = $('#list_table').DataTable({
                    "columnDefs": [
                        {"className": "dt-center", "targets": "_all"}
                    ],
                    serverSide: true,
                    iDisplayLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    ajax: {
                        url: '{{ route("leads.index") }}',
                        type: 'GET',
                        data: function(d) {
                            d.lead_owner_id = $('#lead_owner_filter').val();
                        }
                    },
                    columns: [
                        {data: 'DT_RowIndex'},
                        {data: 'name'},
                        {data: 'mobile_number'},
                        {data: 'email'},
                        {data: 'area_name'},
                        {data: 'lead_source_id'},
                        @if($isSuperAdmin)
                        {data: 'lead_owner_id'},
                        @endif
                        {data: 'action', orderable: false},
                    ]
                });

                @if($isSuperAdmin)
                // Initialize Bootstrap Multiselect
                $('#lead_owner_filter').multiselect({
                    columns: 1,
                    placeholder: 'Select Lead Owners',
                    texts: {
                        placeholder: 'Select Lead Owners',
                        search: 'Search Lead Owners'
                    },
                    buttonClass: 'btn btn-outline-primary w-100',
                    containerClass: 'multiselect-container',
                    enableClickableOptGroups: true,
                    enableCollapsibleOptGroups: false,
                    selectableOptgroup: false,
                    onChange: function(element, option) {
                        table.draw();
                    },
                    onSelectAll: function(element) {
                        table.draw();
                    },
                    onDeselectAll: function(element) {
                        table.draw();
                    }
                });
                @endif
            });
        @endcan
    </script>
@endsection
