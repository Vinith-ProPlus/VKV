@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Blog";
        $ActiveMenuName = 'Blog';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">User</li>
                        <li class="breadcrumb-item">{{$PageTitle}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>{{ $blog ? 'Edit' : 'Create' }} {{$PageTitle}}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $blog ? route('blogs.update', $blog->id) : route('blogs.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if($blog)
                                @method('PUT')
                            @endif

                            <div class="row mt-10">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Project</label>
                                        <select name="project_id" id="project_id" class="form-control select2 @error('project_id') is-invalid @enderror"
                                                data-selected='{{ $blog ? old('project_id', $blog->project_id) : old('project_id') }}' required>
                                            <option value="">Select a Project</option>
                                        </select>
                                        @error('project_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Stage</label>
                                        <select name="stage_ids[]" id="stage_ids" class="form-control select2 @error('stage_ids') is-invalid @enderror"
                                                data-selected='{{ json_encode(old('stage_ids', $blog->stage_ids ?? [])) }}' multiple required>
                                            <option value="">Select a Stage</option>
                                        </select>
                                        @error('stage_ids')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Blog For</label>
                                        <select name="user_id" id="user_id" class="form-control select2 @error('user_id') is-invalid @enderror"
                                                data-selected='{{ $blog ? old('user_id', $blog->user_id) : old('user_id') }}' required>
                                            <option value="">Select a User</option>
                                        </select>
                                        @error('user_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Mark as Damaged</label>
                                        <select name="is_damaged" id="is_damaged" class="form-control select2 @error('is_damaged') is-invalid @enderror"
                                                data-selected='{{ $blog ? old('is_damaged', $blog->is_damaged) : old('is_damaged') }}' required>
                                            <option value="0" {{ old('is_damaged', $project->is_damaged ?? 0) == 0 ? 'selected' : '' }}>No</option>
                                            <option value="1" {{ old('is_damaged', $project->is_damaged ?? 1) == 1 ? 'selected' : '' }}>Yes</option>
                                        </select>
                                        @error('is_damaged')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea type="text" name="remarks" class="form-control" required>{{ old('remarks', $blog->remarks ?? '') }}</textarea>
                                        @error('remarks')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Upload Documents</label>
                                        <input type="file" name="attachments[]" class="form-control @error('attachments') is-invalid @enderror" multiple accept=".pdf,.doc,.docx,.jpg,.png">
                                        @error('attachments')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                    <button type="submit" class="btn btn-primary">{{ $blog ? 'Update' : 'Save' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('#project_id').change(() => getProjectStages());

            const getProjects = () =>{
                let ProjectID = $('#project_id');
                let SelectedProject = ProjectID.attr('data-selected');
                ProjectID.select2('destroy');
                $('#project_id option').remove();
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
                        console.error("Error fetching projects: ", exception);
                    },
                });
                ProjectID.select2();
                getProjectStages();
            }
            const getUsers = () =>{
                let UserID = $('#user_id');
                let SelectedUser = UserID.attr('data-selected');
                UserID.select2('destroy');
                $('#user_id option').remove();
                UserID.append('<option value="">Select a User</option>');

                $.ajax({
                    url:"{{route('getUsers')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        response.forEach(function(item) {
                            if ((item.id == SelectedUser)) {
                                UserID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                UserID.append('<option value="' + item.id
                                    + '">'  + item.name + '</option>');
                            }
                        });
                    },
                    error: function(e, x, settings, exception) {
                        console.error("Error fetching users: ", exception);
                    },
                });
                UserID.select2();
            }

            const getProjectStages = () => {
                let StageID = $('#stage_ids');
                let ProjectID = $('#project_id');
                let SelectedProjectID = ProjectID.val() ? ProjectID.val() : ProjectID.attr('data-selected');
                let SelectedStage = StageID.attr('data-selected');

                StageID.select2('destroy');
                StageID.empty().append('<option value="">Select a Stage</option>');

                if (SelectedProjectID) {
                    $.ajax({
                        url: "{{ route('getStages') }}",
                        type: 'GET',
                        dataType: 'json',
                        data: { 'ProjectID': SelectedProjectID },
                        success: function(response) {
                            response.forEach(function(item) {
                                StageID.append('<option value="' + item.id + '" ' +
                                    (SelectedStage.includes(item.id.toString()) ? 'selected' : '') + '>' +
                                    item.name + '</option>');
                            });
                        },
                        error: function(e, x, settings, exception) {
                            console.error("Error fetching stages: ", exception);
                        }
                    });
                }
                StageID.select2();
            };

            getProjects();
            getUsers();
        });
    </script>
@endsection

