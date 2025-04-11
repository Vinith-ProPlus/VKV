@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Project Task";
        $ActiveMenuName='Project Tasks';
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
                                @can('Create Project Tasks')
                                    <a class="btn btn-sm btnPrimaryCustomizeBlue btn-primary add-btn"
                                       href="{{ route('project_tasks.create') }}">Add New Project Task</a>
                                @endcan
                            </div>
                        </div>
                        <div class="row align-items-center justify-content-center">
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">Projects</label>
                                    <div id="divProject">
                                        <select class="form-control form-control-sm text-center" id="project_id">
                                            <option value="">Select a Project</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label style="margin-bottom: 0px;">Stages</label>
                                    <div id="divStage">
                                        <select class="form-control form-control-sm text-center" id="stage_id">
                                            <option value="">Select a Stage</option>
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
                                            @foreach(PROJECT_TASK_STATUSES as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-center mh-60">
                                    <label class="mb-0">Date</label>
                                    <div id="divDate">
                                        <input type="date" class="form-control form-control-sm text-center" id="date_filter" value="">
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
                                            <th>Task Name</th>
                                            <th>Project Name</th>
                                            <th>Date</th>
                                            <th>Stage</th>
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
        @can('View Project Tasks')
        $(document).ready(function () {
            $('#project_id').change(() => getProjectStages());
            $('#clearFilters').click(clearFilter);

            function initMultiSelect() {
                $('#project_id, #stage_id, #status, #lstReportStatus').multiselect({
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
                    url: '{{ route("project_tasks.index") }}',
                    type: 'GET',
                    data: function (d) {
                        d.project_id = $('#project_id').val();
                        d.stage_id = $('#stage_id').val();
                        d.status = $('#status').val();
                        d.date = $('#date_filter').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name'},
                    {data: 'project_name'},
                    {data: 'date'},
                    {data: 'stage_name'},
                    {data: 'status'},
                    {data: 'action', orderable: false},
                ]
            });

            // Fetch projects dynamically
            function getProjects() {
                let ProjectID = $('#project_id');
                let SelectedProject = ProjectID.attr('data-selected');

                $.ajax({
                    url: "{{ route('getProjects') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        let options = '<option value="">Select a Project</option>';
                        response.forEach(item => {
                            options += `<option value="${item.id}" ${item.id == SelectedProject ? 'selected' : ''}>${item.name}</option>`;
                        });
                        ProjectID.html(options).multiselect('rebuild');
                        getProjectStages();
                    },
                    error: function () {
                        console.error("Error fetching projects.");
                    }
                });
            }

            function getProjectStages() {
                let StageID = $('#stage_id');
                let ProjectID = $('#project_id');
                let SelectedProjectID = ProjectID.val() || ProjectID.attr('data-selected');
                let SelectedStage = StageID.attr('data-selected');

                if (SelectedProjectID) {
                    $.ajax({
                        url: "{{ route('getStages') }}",
                        type: 'GET',
                        dataType: 'json',
                        data: {'ProjectID': SelectedProjectID},
                        success: function (response) {
                            let options = '<option value="">Select a Stage</option>';
                            response.forEach(item => {
                                options += `<option value="${item.id}" ${item.id == SelectedStage ? 'selected' : ''}>${item.name}</option>`;
                            });
                            StageID.html(options).multiselect('rebuild');
                        },
                        error: function () {
                            console.error("Error fetching stages.");
                        }
                    });
                }
            }

            $('#project_id, #stage_id, #status, #date_filter, #lstReportStatus').on('change', reloadTable);

            function clearFilter() {
                $('#project_id').val('').multiselect('refresh');
                $('#stage_id').val('').multiselect('refresh');
                $('#status').val('').multiselect('refresh');
                $('#date_filter').val('');
                reloadTable();
            }

            getProjects();
            initMultiSelect();
        });
        @endcan
    </script>
@endsection

