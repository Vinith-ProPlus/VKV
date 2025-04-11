@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Labor";
        $ActiveMenuName='Labors';
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
                            <div class="col-sm-4 my-2 text-right text-md-right">
                                @can('Create Labors')
                                    <button class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#selectProjectModal">Create</button>
                                @endcan
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-center">
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">Projects</label>
                                    <div id="divProject">
                                        <select class="form-control form-control-sm text-center" id="multiselect_project_id" multiple>
                                            <option value="">Select a Project</option>
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
                                            <option value="0">Paid</option>
                                            <option value="1">Un Paid</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label class="mb-0">From Date</label>
                                    <div id="divFromDate">
                                        <input type="date" class="form-control form-control-sm text-center" id="from_date_filter" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label class="mb-0">To Date</label>
                                    <div id="divToDate">
                                        <input type="date" class="form-control form-control-sm text-center" id="to_date_filter" value="">
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
                                            <th>Project Name</th>
                                            <th>Date</th>
                                            <th>Labor Count</th>
                                            <th>Contract Labor Count</th>
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

    <div class="modal fade" id="selectProjectModal" tabindex="-1" aria-labelledby="selectProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectProjectModalLabel">Select Project and Date</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="selectProjectForm">
                        <div class="mb-3">
                            <label for="project_id" class="form-label">Project</label>
                            <select class="form-control" id="project_id" name="project_id">
                                <option value="">Select a Project</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" max="{{ \Carbon\Carbon::today()->toDateString() }}">
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        @can('View Labors')
        
        $(function() {
            // Initialize Select2 for the modal
            $('.select2').select2({ dropdownParent: $('#selectProjectModal') });
            
            // Initialize multiselect for filters
            initMultiSelect();
            
            // Setup event handlers
            $('#multiselect_project_id, #status, #from_date_filter, #to_date_filter').on('change', reloadTable);
            $('#clearFilters').click(clearFilter);
            
            // Initialize DataTable
            $('#list_table').DataTable({ 
                "columnDefs": [
                    {"className": "dt-center", "targets": "_all"}
                ],
                serverSide: true,
                iDisplayLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                dom: 'lBfrtip',
                buttons: [
                    'copy', 'excel', 'pdf', 'print'
                ],
                ajax: {
                    url: '{{ route("labors.index") }}',
                    type: 'GET',
                    data: function (d) {
                        d.project_id = $('#multiselect_project_id').val();
                        d.paid_status = $('#status').val();
                        d.from_date = $('#from_date_filter').val();
                        d.to_date = $('#to_date_filter').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'project_name' },
                    { data: 'date' },
                    { data: 'labor_count' },
                    { data: 'contract_labor_count' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });
            
            // Load projects
            getProjects();
        });

        function initMultiSelect() {
            // Initialize multiselect for dropdown filters
            $('#multiselect_project_id, #status').multiselect({
                buttonClass: 'btn btn-link',
                enableFiltering: true,
                maxHeight: 250,
                buttonWidth: '100%'
            });
        }
        
        function reloadTable() {
            $('#list_table').DataTable().ajax.reload();
        }
        
        function getProjects() {
            const $multiSelect = $('#multiselect_project_id'); 
            const $modalSelect = $('#selectProjectModal #project_id'); 
            const selectedMultiProject = $multiSelect.attr('data-selected');
            const selectedModalProject = $modalSelect.attr('data-selected');

            $.ajax({
                url: "{{ route('getProjects') }}",
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    let options = '<option value="">Select a Project</option>';
                    response.forEach(item => {
                        options += `<option value="${item.id}" ${(item.id == selectedMultiProject || item.id == selectedModalProject) ? 'selected' : ''}>${item.name}</option>`;
                    });

                    // Update and rebuild multiselect
                    $multiSelect.html(options);
                    $multiSelect.multiselect('rebuild');

                    // Update and reinitialize Select2
                    $modalSelect.html(options);
                    $modalSelect.select2({ dropdownParent: $('#selectProjectModal') });
                },
                error: function () {
                    console.error("Error fetching projects.");
                }
            });
        }


        function getLaborStatus() {
            let statusSelect = $('#status');
            let selectedProject = statusSelect.attr('data-selected');

            $.ajax({
                url: "{{ route('getLaborStatus') }}",
                type: 'GET',
                dataType: 'json',
                data: {'status':0},
                success: function (response) {
                    console.log(response);
                }
            })
        };

        getLaborStatus();

        $('#selectProjectForm').submit(function(e) {
            e.preventDefault();
            let projectId = $('#selectProjectModal #project_id').val();
            let date = $('#date').val();
            if (projectId && date) {
                window.location.href = '/admin/manage-projects/labors/create?project_id=' + projectId + '&date=' + date;
            } else {
                alert('Please fill all required fields.');
            }
        });

        function clearFilter() {
            $('#multiselect_project_id').val('').multiselect('refresh');
            $('#status').val('').multiselect('refresh');
            $('#from_date_filter').val('');
            $('#to_date_filter').val('');
            reloadTable();
        }

        @endcan
    </script>
@endsection
