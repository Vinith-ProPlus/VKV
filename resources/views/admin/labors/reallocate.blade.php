@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Labor";
        $ActiveMenuName = 'Labors';
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
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-header row">
                <div class="col-4 text-left"><h5>Project: <strong>{{ $ProjectLabourDate->project?->name }}</strong></h5></div>
                <div class="col-4 text-center"><h5><strong>Re-Allocate Labor</strong></h5></div>
                <div class="col-4 text-right"><h5>Date: <strong>{{ $ProjectLabourDate->date }}</strong></h5></div>
            </div>
            <div class="card-body">
                <form id="re-allocate-labor-form" action="{{ route('labors.reallocateStore') }}" method="POST">
                    @csrf
                    <input type="hidden" name="project_labor_date_id" value="{{ old('project_labor_date_id', $ProjectLabourDate->id) }}">
                    <input type="hidden" name="from_project_id" value="{{ old('from_project_id', $ProjectLabourDate->project_id) }}">
                    <input type="hidden" name="date" value="{{ old('date', $ProjectLabourDate->date) }}">

                    <div class="row mt-10">
                        <div class="col-12">
                            <div class="form-group">
                                <label>To Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-control select2 @error('project_id') is-invalid @enderror" required>
                                    <option value="">Select a Project</option>
                                </select>
                                @error('project_id')
                                <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row mt-20">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Select Labor <span class="text-danger">*</span></label>
                                <select name="labors[]" id="labors" class="form-control select2 @error('labors') is-invalid @enderror" multiple required>
                                    <option value="">Select Labors</option>
                                    @foreach($ProjectLabourDate->labors as $labor)
                                        <option value="{{ $labor->id }}" @if(in_array($labor->id, old('labors', $laborReAllocate->labors ?? []))) selected @endif>
                                            {{ $labor->name .' - '. $labor->mobile }}
                                        </option>
                                    @endforeach
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
                    <div class="row mt-15 text-end">
                        <div>
                            <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                            <button type="submit" class="btn btn-primary">Re-Allocate</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            const getProjects = () =>{
                let FromProjectID = $('input[name="from_project_id"]').val();
                let ProjectID = $('#project_id');
                let SelectedProject = '{{ old('project_id') }}';
                ProjectID.select2('destroy');
                $('#project_id option').remove();
                ProjectID.append('<option value="">Select a Project</option>');

                $.ajax({
                    url:"{{ route('getProjects') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        response.forEach(function(item) {
                            if(FromProjectID != item.id) {
                                if (item.id == SelectedProject) {
                                    ProjectID.append('<option selected value="' + item.id + '">' + item.name + '</option>');
                                } else {
                                    ProjectID.append('<option value="' + item.id + '">' + item.name + '</option>');
                                }
                            }
                        });
                    },
                    error: function(e, x, settings, exception) {
                        console.error("Error fetching projects", e);
                    },
                });
                ProjectID.select2();
            }
            getProjects();
        });
    </script>
@endsection
