@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Sites";
        $ActiveMenuName = 'Sitess';
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
                        <h5>{{ $site ? 'Edit' : 'Create' }} {{$PageTitle}}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $site ? route('sites.update', $site->id) : route('sites.store') }}"
                              method="POST">
                            @csrf
                            @if($site)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Site name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $site->name ?? '') }}" required>
                                        @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input type="text" name="location" class="form-control" value="{{ old('location', $site->location ?? '') }}" required>
                                        @error('location')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Latitude</label>
                                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $site->latitude ?? '') }}" required>
                                        @error('latitude')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Longitude</label>
                                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $site->longitude ?? '') }}" required>
                                        @error('longitude')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-6 form-group">
                                    <label>Site Supervisors <span class="text-danger">*</span></label>
                                    <select name="site_supervisor_id[]" id="site_supervisor_id"
                                            class="form-control select2 @error('site_supervisor_id') is-invalid @enderror" multiple required
                                            data-selected="{{ json_encode(old('site_supervisor_id', $supervisors ?? []), JSON_THROW_ON_ERROR) }}">
                                        <option value="" disabled>Select Site Supervisors</option>
                                    </select>
                                    @error('site_supervisor_id')
                                    <span class="error invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-6">
                                    <div class="form-group">
                                        <label class="is_active">Active Status</label>
                                        <select class="form-control" name="is_active" id="is_active" required>
                                            <option value="1" {{ $site && $site->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $site && !$site->is_active ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                        @error('is_active')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()"
                                       class="btn btn-warning">Back</a>
                                    <button type="submit"
                                            class="btn btn-primary">{{ $site ? 'Update' : 'Save' }}</button>
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
            const getSiteSupervisors = () => {
                let selectedSupervisorID = $('#site_supervisor_id');
                let selectedSupervisors = selectedSupervisorID.attr('data-selected');
                selectedSupervisors = selectedSupervisors ? JSON.parse(selectedSupervisors).map(Number) : [];

                selectedSupervisorID.select2('destroy').empty().append('<option value="" disabled>Select Site Supervisors</option>');

                $.ajax({
                    url: "{{ route('getSiteSupervisors') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            let selected = selectedSupervisors.includes(item.id) ? 'selected' : '';
                            selectedSupervisorID.append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
                        });
                        selectedSupervisorID.select2();
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            }

            getSiteSupervisors();
        });
    </script>
@endsection
