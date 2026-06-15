@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Site Task";
        $ActiveMenuName = 'Site Tasks';
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
                        <h5>{{ $site_task ? 'Edit' : 'Create' }} {{$PageTitle}}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $site_task ? route('site_tasks.update', $site_task->id) : route('site_tasks.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @if($site_task)
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
                                <div class="col-md-6 col-12 mt-10">
                                    <div class="form-group">
                                        <label>Project</label>
                                        <select name="project_id" id="project_id" class="form-control select2 @error('project_id') is-invalid @enderror"
                                                data-selected='{{ $site_task ? old('project_id', $site_task->site?->project_id) : old('project_id') }}' required>
                                            <option value="">Select a Project</option>
                                        </select>
                                        @error('project_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 mt-10">
                                    <div class="form-group">
                                        <label>Site</label>
                                        <select name="site_id" id="site_id" class="form-control select2 @error('site_id') is-invalid @enderror"
                                                data-selected='{{ $site_task ? old('site_id', $site_task->site?->id) : old('site_id') }}' required>
                                            <option value="">Select a Site</option>
                                        </select>
                                        @error('site_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 mt-10">
                                    <div class="form-group">
                                        <label>Stage</label>
                                        <select name="stage_id" id="stage_id" class="form-control select2 @error('stage_id') is-invalid @enderror"
                                                data-selected='{{ $site_task ? old('stage_id', $site_task->stage?->id) : old('stage_id') }}' required>
                                            <option value="">Select a Stage</option>
                                        </select>
                                        @error('stage_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 mt-10">
                                    <div class="form-group">
                                        <label>Task Name</label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $site_task->name ?? '') }}" required>
                                        @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 col-12 mt-10">
                                    <div class="form-group">
                                        <label>Task Date</label>
                                        <input type="date" name="date" class="form-control" 
                                        @unless($site_task ?? false)
                                            min="{{ now()->format('Y-m-d') }}"
                                        @endunless
                                            value="{{ old('date', $site_task?->date ? \Carbon\Carbon::parse($site_task->date)->format('Y-m-d') : '') }}" required>
                                        @error('date')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6 mt-10">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" id="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                                            <option value="">Select a Status</option>
                                            @foreach(SITE_TASK_STATUSES as $status)
                                                <option value="{{ $status }}" {{ $status == ($site_task ? old('status', $site_task->status) : old('status')) ? 'selected' : '' }}>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 mt-10">
                                    <div class="form-group">
                                        <label>Task Description</label>
                                        <textarea name="description" class="form-control">{{ old('description', $site_task->description ?? '') }}</textarea>
                                        @error('description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                    <button type="submit" class="btn btn-primary">{{ $site_task ? 'Update' : 'Save' }}</button>
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
            @if($site_task && $site_task->image)
                $("#image-preview").removeClass("d-none").attr("src", "{{ Storage::url($site_task->image) }}");
                $("#image-dropzone i, #image-dropzone p").hide();
            @endif

            // Load projects on page load
            const getProjects = () => {
                let ProjectID = $('#project_id');
                let SelectedProject = ProjectID.attr('data-selected');
                ProjectID.select2('destroy');
                $('#project_id option').remove();
                ProjectID.append('<option value="">Select a Project</option>');
                $.ajax({
                    url: "{{ route('getProjects') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        response.forEach(function(item) {
                            if ((item.id == SelectedProject)) {
                                ProjectID.append('<option selected value="' + item.id + '">' + item.name + '</option>');
                            } else {
                                ProjectID.append('<option value="' + item.id + '">'  + item.name + '</option>');
                            }
                        });
                    },
                    error: function(e, x, settings, exception) {
                        // ajaxErrors(e, x, settings, exception);
                    },
                });
                ProjectID.select2();
                getSites();
            }

            // Load sites for selected project
            const getSites = () => {
                let SiteID = $('#site_id');
                let ProjectID = $('#project_id');
                let SelectedProjectID = ProjectID.val() ? ProjectID.val() : ProjectID.attr('data-selected');
                let SelectedSite = SiteID.attr('data-selected');
                SiteID.select2('destroy');
                SiteID.empty().append('<option value="">Select a Site</option>');
                if (SelectedProjectID) {
                    $.ajax({
                        url: "{{ route('getSites') }}",
                        type: 'GET',
                        dataType: 'json',
                        data: { 'project_id': SelectedProjectID },
                        success: function(response) {
                            response.forEach(function(item) {
                                SiteID.append('<option value="' + item.id + '" ' + (item.id == SelectedSite ? 'selected' : '') + '>' + item.site_no + '</option>');
                            });
                        },
                        error: function(e, x, settings, exception) {
                            // ajaxErrors(e, x, settings, exception);
                        },
                    });
                }
                SiteID.select2();
                getProjectStages();
            }

            // When project changes, load sites
            $('#project_id').change(function() {
                getSites();
            });

            $('#site_id').change(function() {
                // Optionally clear stage dropdown
                $('#stage_id').empty().append('<option value="">Select a Stage</option>').select2();
                getProjectStages();
            });

            // When site changes, you may want to load stages for that site
            // $('#site_id').change(function() { ... });

            // If a project is already selected (edit mode), load sites
            if ($('#project_id').val()) {
                getSites();
            }
            $('#status').select2();

            const getProjectStages = () => {

                let StageID = $('#stage_id');
                let SiteID = $('#site_id');
                let ProjectID = $('#project_id');
                let SelectedSiteID = SiteID.val() ? SiteID.val() : SiteID.attr('data-selected');
                let SelectedStage = StageID.attr('data-selected');

                StageID.select2('destroy');
                StageID.empty().append('<option value="">Select a Stage</option>');

                if (SelectedSiteID) {
                    $.ajax({
                        url: "{{ route('getStages') }}",
                        type: 'GET',
                        dataType: 'json',
                        data: { 'SiteID': SelectedSiteID },
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
