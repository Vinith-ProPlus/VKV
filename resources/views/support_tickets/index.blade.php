@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Support Ticket";
        $ActiveMenuName='Support-Tickets';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">User</li>
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
                                @can('Create Support Tickets')
                                    <a class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn"
                                       href="{{ route('support_tickets.create') }}">Add New Support Ticket</a>
                                @endcan
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-center">
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">Support Type</label>
                                    <div id="divSupportType">
                                        <select class="form-control form-control-sm text-center" id="support_type_id">
                                            <option value="">Select a Support Type</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">User</label>
                                    <div id="divUser">
                                        <select class="form-control form-control-sm text-center" id="user_id">
                                            <option value="">Select a User</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">Status</label>
                                    <div id="divStatus">
                                        <select class="form-control form-control-sm text-center" id="status">
                                            <option value="">Select a Status</option>
                                            @foreach(SUPPORT_TICKET_STATUSES as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label class="mb-0">From Date</label>
                                    <div id="divFromDate">
                                        <input type="date" class="form-control form-control-sm text-center" id="from_date" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label class="mb-0">To Date</label>
                                    <div id="divToDate">
                                        <input type="date" class="form-control form-control-sm text-center" id="to_date" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2 d-flex align-items-center justify-content-center">
                                <button class="btn btn-sm btn-danger mt-3" id="clearFilters">Clear Filters</button>
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
                                            <th>Ticket Number</th>
                                            <th>Ticket Type</th>
                                            <th>Name</th>
                                            <th>Date</th>
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
        @can('View Support Tickets')
        $(document).ready(function () {
            $('#clearFilters').click(clearFilter);

            function initMultiSelect() {
                $('#support_type_id, #user_id, #status').multiselect({
                    buttonClass: 'btn btn-link',
                    enableFiltering: true,
                    maxHeight: 250,
                });
            }

            function reloadTable() {
                $('#list_table').DataTable().ajax.reload();
            }

            // Initialize DataTable
            $('#list_table').DataTable({
                "columnDefs": [{"className": "dt-center", "targets": "_all"}],
                serverSide: true,
                iDisplayLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                ajax: {
                    url: '{{ route("support_tickets.index") }}',
                    type: 'GET',
                    data: function (d) {
                        d.support_type_id = $('#support_type_id').val();
                        d.user_id = $('#user_id').val();
                        d.status = $('#status').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'ticket_number'},
                    {data: 'support_type'},
                    {data: 'user_name'},
                    {data: 'created_on'},
                    {data: 'status'},
                    {data: 'action', orderable: false},
                ]
            });

            // Fetch support types dynamically
            function getSupportType() {
                let SupportTypeID = $('#support_type_id');
                let SelectedSupportType = SupportTypeID.attr('data-selected');

                $.ajax({
                    url: "{{ route('getSupportTypes') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        let options = '<option value="">Select a Support Type</option>';
                        response.forEach(item => {
                            options += `<option value="${item.id}" ${item.id == SelectedSupportType ? 'selected' : ''}>${item.name}</option>`;
                        });
                        SupportTypeID.html(options).multiselect('rebuild');
                    },
                    error: function () {
                        console.error("Error fetching support types.");
                    }
                });
            }

            // Fetch users dynamically
            function getUsers() {
                let UserID = $('#user_id');
                let SelectedUserID = UserID.attr('data-selected');

                $.ajax({
                    url: "{{ route('getUsers') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        let options = '<option value="">Select a User</option>';
                        response.forEach(item => {
                            options += `<option value="${item.id}" ${item.id == SelectedUserID ? 'selected' : ''}>${item.name}</option>`;
                        });
                        UserID.html(options).multiselect('rebuild');
                    },
                    error: function () {
                        console.error("Error fetching users.");
                    }
                });
            }

            $('#support_type_id, #user_id, #status, #from_date, #to_date').on('change', reloadTable);

            function clearFilter() {
                $('#support_type_id').val('').multiselect('refresh');
                $('#user_id').val('').multiselect('refresh');
                $('#status').val('').multiselect('refresh');
                $('#from_date').val('');
                $('#to_date').val('');
                reloadTable();
            }


            getSupportType();
            getUsers();
            initMultiSelect();
        });
        @endcan
    </script>
@endsection

