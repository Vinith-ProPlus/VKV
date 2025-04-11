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
            <div class="col-4 text-center"><h5><strong>Re-Allocate Labor</strong></h5></div>
        </div>
        <div class="card-body">
            <div class="row mt-10">
                <div class="col-6">
                    <div class="form-group">
                        <label>From Project</label>
                        <select name="from_project" id="from_project" class="form-control select2 @error('from_project') is-invalid @enderror"
                                data-selected='{{ $laborReAllocate ? old('from_project', $laborReAllocate->from_project) : old('from_project') }}' required>
                            <option value="">Select a Project</option>
                        </select>
                        @error('from_project')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label>To Project</label>
                        <select name="to_project" id="to_project" class="form-control select2 @error('to_project') is-invalid @enderror"
                                data-selected='{{ $laborReAllocate ? old('to_project', $laborReAllocate->to_project) : old('to_project') }}' required>
                            <option value="">Select a Project</option>
                        </select>
                        @error('to_project')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-20">
                <div class="col-12">
                    <div class="form-group">
                        <label>Select Labor</label>
                        <select name="labors" id="labors" class="form-control select2 @error('labors') is-invalid @enderror"
                                data-selected='{{ $laborReAllocate ? old('labors', $laborReAllocate->labors) : old('labors') }}' required>
                            <option value="">Select Labors</option>
                        </select>
                        @error('labors')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row mt-20">
                <div class="col-12">
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control">{{ old('remarks', $laborReAllocate->remarks ?? '') }}</textarea>
                        @error('remarks')
                        <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#from_project').change(() => getLabors());

            const getProjects = () =>{
                let ProjectID = $('#from_project');
                let SelectedProject = ProjectID.attr('data-selected');
                ProjectID.select2('destroy');
                $('#from_project option').remove();
                ProjectID.append('<option value="">Select a Project</option>');

                $.ajax({
                    url:"{{route('getProjects')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        response.forEach(function(item) {
                            if ((item.id == SelectedProject)) {
                                ProjectID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                ProjectID.append('<option value="' + item.id
                                    + '">'  + item.name + '</option>');
                            }
                        });
                    },
                    error: function(e, x, settings, exception) {
                        // ajaxErrors(e, x, settings, exception);
                    },
                });
                ProjectID.select2();
                getLabors();
            }

            const getLabors = () => {
                let Labors = $('#labors');
                let ProjectID = $('#from_project');
                let SelectedProjectID = ProjectID.val() ? ProjectID.val() : ProjectID.attr('data-selected');
                let SelectedStage = Labors.attr('data-selected');

                Labors.select2('destroy');
                Labors.empty().append('<option value="">Select Labor</option>');

                console.log("SelectedProjectID: " + SelectedProjectID);

                if (SelectedProjectID) {
                    $.ajax({
                        url: "{{ route('getLaborsByProject') }}",
                        type: 'GET',
                        dataType: 'json',
                        data: { 'ProjectID': SelectedProjectID },
                        success: function(response) {
                            response.forEach(function(item) {
                                Labors.append('<option value="' + item.id + '" ' +
                                    (item.id == SelectedStage ? 'selected' : '') + '>' +
                                    item.name + '</option>');
                            });
                        },
                        error: function(e, x, settings, exception) {
                            console.error("Error fetching stages: ", exception);
                        }
                    });
                }
                Labors.select2();
            };

            getProjects();
        });
    </script>
@endsection
