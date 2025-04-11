@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Project Task";
        $ActiveMenuName = 'Project Tasks';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Master</li>
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
                        <h5>{{ $project_task ? 'Edit' : 'Create' }} {{$PageTitle}}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $project_task ? route('project_tasks.update', $project_task->id) : route('project_tasks.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if($project_task)
                                @method('PUT')
                            @endif

                            <div class="d-flex justify-content-center align-items-center">
                                <div class="text-center">
                                    <label class="d-block">Task Image</label>
                                    <div id="image-dropzone" class="image-box border rounded d-flex align-items-center justify-content-center flex-column text-center"
                                         style="width: 200px; height: 200px; cursor: pointer; background: #f8f9fa; border: 2px dashed #ccc;">
                                        <i class="fa fa-upload fa-2x text-secondary"></i>
                                        <p class="text-muted m-0">Drag &amp; drop a file here or click</p>
                                        <img id="image-preview" src="" class="img-fluid d-none" style="max-width: 100%; max-height: 100%;" alt="">
                                    </div>
                                    <input type="file" id="image-input" name="image" class="d-none" accept="image/*">
                                </div>
                                @error('image')
                                <span class="error invalid-feedback">{{$message}}</span>
                                @enderror
                            </div>

                            <div class="row mt-10">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Project</label>
                                        <select name="project_id" id="project_id" class="form-control select2 @error('project_id') is-invalid @enderror"
                                                data-selected='{{ $project_task ? old('project_id', $project_task->project_id) : old('project_id') }}' required>
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
                                        <select name="stage_id" id="stage_id" class="form-control select2 @error('stage_id') is-invalid @enderror"
                                                data-selected='{{ $project_task ? old('stage_id', $project_task->stage_id) : old('stage_id') }}' required>
                                            <option value="">Select a Stage</option>
                                        </select>
                                        @error('stage_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Task Name</label>
                                        <input type="text" name="name" class="form-control"
                                               value="{{ old('name', $project_task->name ?? '') }}" required>
                                        @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Task Date</label>
                                        <input type="date" name="date" class="form-control" min="{{ now()->format('Y-m-d') }}"
                                               value="{{ Carbon\Carbon::parse(old('date', $project_task->date ?? ''))->format('Y-m-d') }}" required>
                                        @error('date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-10">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Task Description</label>
                                        <textarea name="description" class="form-control">{{ old('description', $project_task->description ?? '') }}</textarea>
                                        @error('description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 mt-10">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" id="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                                            <option value="">Select a Status</option>
                                            @foreach(PROJECT_TASK_STATUSES as $status)
                                                <option value="{{ $status }}" {{ $status == ($project_task ? old('status', $project_task->status) : old('status')) ? 'selected' : '' }}>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                    <button type="submit" class="btn btn-primary">{{ $project_task ? 'Update' : 'Save' }}</button>
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
            @if($project_task && $project_task->image)
                $("#image-preview").removeClass("d-none").attr("src", "{{ Storage::url($project_task->image) }}");
                $("#image-dropzone i, #image-dropzone p").hide();
            @endif

            $('#project_id').change(() => getProjectStages());
            $('#status').select2();

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
                        // ajaxErrors(e, x, settings, exception);
                    },
                });
                ProjectID.select2();
                getProjectStages();
            }

            const getProjectStages = () => {
                let StageID = $('#stage_id');
                let ProjectID = $('#project_id');
                let SelectedProjectID = ProjectID.val() ? ProjectID.val() : ProjectID.attr('data-selected');
                let SelectedStage = StageID.attr('data-selected');

                StageID.select2('destroy');
                StageID.empty().append('<option value="">Select a Stage</option>');

                console.log("SelectedProjectID: " + SelectedProjectID);

                if (SelectedProjectID) {
                    $.ajax({
                        url: "{{ route('getStages') }}",
                        type: 'GET',
                        dataType: 'json',
                        data: { 'ProjectID': SelectedProjectID },
                        success: function(response) {
                            response.forEach(function(item) {
                                StageID.append('<option value="' + item.id + '" ' +
                                    (item.id == SelectedStage ? 'selected' : '') + '>' +
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
        });
    </script>
@endsection
