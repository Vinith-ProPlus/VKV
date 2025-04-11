@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Labor";
        $ActiveMenuName='Labors';
    @endphp
    <style>
        .swal2-container {
            z-index: 2056 !important; /* Higher than Bootstrap modal */
        }
    </style>
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Projects</li>
                        <li class="breadcrumb-item">{{$PageTitle}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
    <div class="card">
        <div class="card-header row">
            <div class="col-4 text-left"><h5>Project: <strong>{{ $labor->project?->name }}</strong></h5></div>
            <div class="col-4 text-center"><h5><strong>Manage Labor</strong></h5></div>
            <div class="col-4 text-right"><h5>Date: <strong>{{ $labor->date }}</strong></h5></div>
        </div>
        <div class="card-body">
            <input type="hidden" id="project_labor_date_id" value="{{ $labor->id }}">
            <input type="hidden" id="project_id" value="{{ $labor->project_id }}">
            <input type="hidden" id="date" value="{{ $labor->date }}">
            <div class="text-center mt-20 mb-20">
                <button class="btn btn-success my-2" id="openLaborModelBtn">Add Labor</button>
            </div>

            <div class="card">
                <h5 class="text-center mt-10"><strong>Labors</strong></h5>
                <table class="table table-bordered" id="laborTable">
                    <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Mobile</th>
                        <th>Salary</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="card">
                <h5 class="text-center mt-10"><strong>Contract Labors</strong></h5>
                <table class="table table-bordered" id="contractLaborTable">
                    <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Contractor</th>
                        <th>Labor Count</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="addLaborModal" tabindex="-1" aria-labelledby="addLaborModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Labor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="submitLaborForm" action="{{ route('labors.store') }}">
                        <div class="mb-3" id="labor_type_div">
                            <label for="labor_type">Labor Type</label>
                            <select class="form-control" id="labor_type">
                                <option value="Self">Self</option>
                                <option value="Contract">Contract</option>
                            </select>
                        </div>
                        <div id="selfLaborFields">
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" class="form-control" id="name">
                            </div>
                            <div class="mb-3">
                                <label>Designation</label>
                                <select class="form-control" id="designation_id">
                                    <option value="">Select Designation</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Mobile</label>
                                <input type="text" class="form-control" id="mobile">
                            </div>
                            <div class="mb-3">
                                <label>Salary</label>
                                <input type="number" class="form-control" id="salary" min="0">
                            </div>
                        </div>
                        <div id="contractLaborFields" style="display: none;">
                            <div class="mb-3">
                                <label>Contractor</label>
                                <select class="form-control" id="project_contract_id">
                                    <option value="">Select Contractor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Labor Count</label>
                                <input type="number" class="form-control" id="count">
                            </div>
                        </div>
                        <div class="text-end mt-10">
                            <button type="submit" id="saveLaborBtn" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $(function () {
                $('#laborTable').DataTable({
                    "columnDefs": [
                        {"className": "dt-center", "targets": "_all"}
                    ],
                    serverSide: true,
                    iDisplayLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    ajax: {
                        url: '{{ route("laborsList") }}',
                        type: 'GET',
                        data: function (d) {
                            d.project_labor_date_id = $('#project_labor_date_id').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name' },
                        { data: 'designation' },
                        { data: 'mobile' },
                        { data: 'salary' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                });

                $('#contractLaborTable').DataTable({
                    "columnDefs": [
                        {"className": "dt-center", "targets": "_all"}
                    ],
                    serverSide: true,
                    iDisplayLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    ajax: {
                        url: '{{ route("contractLaborsList") }}',
                        type: 'GET',
                        data: function (d) {
                            d.project_labor_date_id = $('#project_labor_date_id').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'contractor_name' },
                        { data: 'count' },
                        { data: 'action', orderable: false, searchable: false }
                    ]
                });
            });

            $('#labor_type').change(function() {
                if ($(this).val() === 'Self') {
                    $('#selfLaborFields').show();
                    $('#contractLaborFields').hide();
                } else {
                    $('#selfLaborFields').hide();
                    $('#contractLaborFields').show();
                }
            });
            function clearLaborForm(){
                $('#submitLaborForm')[0].reset();
                $('#modalTitle').text('Add Labor');
                $('#saveLaborBtn').text('Save');
                $('#labor_type').val('Self').trigger('change').attr('disabled', false);
                $('#project_contract_id').attr('disabled', false).trigger('change');
                $('#designation_id').val('').trigger('change');
                $('#submitLaborForm').attr('action', '{{ route("labors.store") }}');
            }

            const getContracts = () => {
                let ContractorID = $('#project_contract_id');
                let ProjectID = $('#project_id');
                let SelectedProjectID = ProjectID.val();
                let SelectedContractor = ContractorID.attr('data-selected');

                if (ContractorID.length) {
                    if ($.fn.select2 && ContractorID.hasClass("select2-hidden-accessible")) {
                        ContractorID.select2('destroy');
                    }
                    ContractorID.empty().append('<option value="">Select a Contractor</option>');

                    if (SelectedProjectID) {
                        $.ajax({
                            url: "{{ route('getProjectContractors') }}",
                            type: 'GET',
                            dataType: 'json',
                            data: { 'project_id': SelectedProjectID },
                            success: function (response) {
                                response.forEach(function (item) {
                                    ContractorID.append(
                                        '<option value="' + item.id + '" ' +
                                        (item.id == SelectedContractor ? 'selected' : '') + '>' +
                                        item.user.name +' - '+item.contract_type.name +
                                        '</option>'
                                    );
                                });
                            },
                            error: function (e, x, settings, exception) {
                                console.error("Error fetching contractor: ", exception);
                            }
                        });
                    }
                }

                ContractorID.select2({ dropdownParent: $('#addLaborModal') });
            };
            const getDesignations = () => {
                let DesignationID = $('#designation_id');
                let SelectedContractor = DesignationID.attr('data-selected');

                if (DesignationID.length) {
                    if ($.fn.select2 && DesignationID.hasClass("select2-hidden-accessible")) {
                        DesignationID.select2('destroy');
                    }
                    DesignationID.empty().append('<option value="">Select Designation</option>');

                    $.ajax({
                        url: "{{ route('getLaborDesignations') }}",
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            response.forEach(function (item) {
                                DesignationID.append(
                                    '<option value="' + item.id + '" ' + (item.id == SelectedContractor ? 'selected' : '') + '>' +item.name +'</option>'
                                );
                            });
                        },
                        error: function (e, x, settings, exception) {
                            console.error("Error fetching contractor: ", exception);
                        }
                    });
                }

                DesignationID.select2({ dropdownParent: $('#addLaborModal') });
            };


            $('#submitLaborForm').submit(function(e) {
                e.preventDefault();

                let laborType = $('#labor_type').val();
                let projectLaborDateId = $('#project_labor_date_id').val();
                let isValid = true;

                $('.error-message').remove(); // Remove previous validation messages

                if (!projectLaborDateId) {
                    $('#project_labor_date_id').after('<span class="text-danger error-message">This field is required</span>');
                    isValid = false;
                }

                let data = {};

                if (laborType === 'Self') {
                    let name = $('#name').val();
                    let designation = $('#designation_id').val();
                    let mobile = $('#mobile').val();
                    let salary = $('#salary').val();

                    if (!name) {
                        $('#name').after('<span class="text-danger error-message">This field is required</span>');
                        isValid = false;
                    }
                    if (!designation) {
                        $('#designation_id').after('<span class="text-danger error-message">This field is required</span>');
                        isValid = false;
                    }
                    if (!mobile) {
                        $('#mobile').after('<span class="text-danger error-message">This field is required</span>');
                        isValid = false;
                    }
                    if (!salary) {
                        $('#salary').after('<span class="text-danger error-message">This field is required</span>');
                        isValid = false;
                    }

                    data = {
                        project_labor_date_id: projectLaborDateId,
                        name: name,
                        labor_designation_id: designation,
                        mobile: mobile,
                        salary: salary,
                        labor_type: laborType
                    };
                } else {
                    let projectContractId = $('#project_contract_id').val();
                    let count = $('#count').val();

                    if (!projectContractId) {
                        $('#project_contract_id').after('<span class="text-danger error-message">This field is required</span>');
                        isValid = false;
                    }
                    if (!count) {
                        $('#count').after('<span class="text-danger error-message">This field is required</span>');
                        isValid = false;
                    }

                    data = {
                        project_labor_date_id: projectLaborDateId,
                        project_contract_id: projectContractId,
                        count: count,
                        labor_type: laborType
                    };
                }

                if (!isValid) return; // Stop submission if validation fails

                let http_method = '';
                http_method = ("{{ route('labors.store') }}" !== $('#submitLaborForm').attr('action')) ? 'PUT' : "POST";
                $.ajax({
                    url: $('#submitLaborForm').attr('action'),
                    type: http_method,
                    data: data,
                    headers: { 'X-CSRF-Token': $('meta[name=_token]').attr('content') },
                    success: function(response) {
                        if(response.success) {
                            $('#addLaborModal').modal('hide');
                            clearLaborForm();
                            if (laborType === 'Self') {
                                $('#laborTable').DataTable().ajax.reload();
                            } else {
                                $('#contractLaborTable').DataTable().ajax.reload();
                            }
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 2000,
                                color: 'black',
                                background: 'white',
                            });
                        } else {
                            Swal.fire({
                                position: 'center',
                                icon: 'warning',
                                title: response.message,
                                showConfirmButton: true,
                                color: 'black',
                                background: 'white',
                            })
                        }
                    },error: function (error) {
                        Swal.fire({
                            position: 'center',
                            icon: 'warning',
                            title: error.message,
                            showConfirmButton: true,
                            color:'black',
                            background:'white',
                        })
                    }
                });
            });

            $(document).on('click','.editLabor',function(){
                clearLaborForm();
                let id = $(this).data('id');
                let type = $(this).data('type');
                $('#labor_type').val(type).trigger('change').attr('disabled', true);
                $.ajax({
                    url: '{{ route("labors.edit", ":id") }}'.replace(':id', id),
                    type: 'GET',
                    data: {
                        id: id,
                        labor_type: type
                    },
                    success: function(response) {
                        $('#modalTitle').text('Edit Labor'); // Change modal title
                        $('#saveLaborBtn').text('Update'); // Change button
                        $('#submitLaborForm').attr('action', '{{ route("labors.update", ":id") }}'.replace(':id', id));

                        $('#project_labor_date_id').val(response.project_labor_date_id);
                        if (type === 'Self') {
                            $('#name').val(response.name);
                            $('#designation_id').val(response.labor_designation_id).trigger('change');
                            $('#mobile').val(response.mobile);
                            $('#salary').val(response.salary);
                        } else {
                            $('#project_contract_id').val(response.project_contract_id).trigger('change').attr('disabled', true);
                            $('#count').val(response.count);
                        }
                        $('#addLaborModal').modal('show');
                    }
                });
            });

            $(document).on('click','#openLaborModelBtn',function(){
                clearLaborForm();
                $('#addLaborModal').modal('show');
            });

            // Delete Self Labor Record
            $(document).on('click','.deleteSelfLabor',function(){
                let id = $(this).data('id');

                if (confirm("Are you sure you want to delete this labor?")) {
                    $.ajax({
                        url: '{{ route("labors.destroySelfLabor", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                        success: function() {
                            $('#laborTable').DataTable().ajax.reload();
                        }
                    });
                }
            });

            // Delete Contract Labor Record
            $(document).on('click','.deleteContractLabor',function(){
                let id = $(this).data('id');

                if (confirm("Are you sure you want to delete this labor?")) {
                    $.ajax({
                        url: '{{ route("labors.destroyContractLabor", ":id") }}'.replace(':id', id),
                        type: 'DELETE',
                        headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                        success: function() {
                            $('#contractLaborTable').DataTable().ajax.reload();
                        }
                    });
                }
            });
            getContracts();
            getDesignations();
        });
    </script>
@endsection
