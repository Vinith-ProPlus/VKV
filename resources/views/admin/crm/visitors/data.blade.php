@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Visitor";
        $ActiveMenuName = 'Visitor';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" title=""><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
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
                            <div class="col-sm-4 my-2"><h5>{{ $visitor ? 'Edit' : 'Create' }} {{ $PageTitle }}</h5></div>
                            <div class="col-sm-4 my-2 text-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form class="row" action="{{ $visitor ? route('visitors.update', $visitor->id) : route('visitors.store') }}" method="POST">
                                    @csrf
                                    @if($visitor) @method('PUT') @endif
                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Project</label>
                                        <select name="project_id" id="project_id" class="form-control select2 @error('project_id') is-invalid @enderror"
                                                data-selected='{{ $visitor ? old('project_id', $visitor->project_id) : old('project_id') }}' required>
                                            <option value="">Select a Project</option>
                                        </select>
                                        @error('project_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Rating <span class="text-danger">*</span></label>
                                        <select name="rating" class="form-control select2">
                                            @foreach(range(1, 5) as $value)
                                                <option value="{{ $value }}" {{ old('rating', $project->rating ?? '') == $value ? 'selected' : '' }}>
                                                    {{ $value }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('rating')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Visitor Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $visitor ? old('name', $visitor->name) : old('name') }}" required>
                                        @error('name')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Mobile Number <span class="text-danger">*</span></label>
                                        <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                               value="{{ $visitor ? old('mobile', $visitor->mobile) : old('mobile') }}" required>
                                        @error('mobile')
                                        <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-12 col-lg-12 mt-15">
                                        <label>Feedback</label>
                                        <textarea name="feedback" class="form-control">{{ old('feedback', $visitor->feedback ?? '') }}</textarea>
                                        @error('feedback')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                            @if(!$visitor)
                                                @can('Create Visitors')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Visitors')
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                @endcan
                                            @endif
                                        </div>
                                    </div>
                                </form>
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
        $(document).ready(function () {
            $('#select2').select2();

            const getProjects = () => {
                let ProjectID = $('#project_id');
                let SelectedProject = ProjectID.attr('data-selected');
                ProjectID.select2('destroy');
                $('#project_id option').remove();
                ProjectID.append('<option value="">Select a Project</option>');

                $.ajax({
                    url: "{{route('getProjects')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == SelectedProject)) {
                                ProjectID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                ProjectID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                    },
                    error: function (e, x, settings, exception) {
                        // ajaxErrors(e, x, settings, exception);
                    },
                });
                ProjectID.select2();
            }
            getProjects();
        });
    </script>
@endsection
