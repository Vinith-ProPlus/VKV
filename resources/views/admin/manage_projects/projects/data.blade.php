@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Projects";
        $ActiveMenuName = 'Projects';
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
                        <h5>{{ $project ? 'Edit' : 'Create' }} {{$PageTitle}}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $project ? route('projects.update', $project->id) : route('projects.store') }}"
                              method="POST">
                            @csrf
                            @if($project)
                                @method('PUT')
                            @endif

                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Project name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $project->name ?? '') }}" required>
                                        @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Location</label>
                                        <input type="text" name="location" class="form-control" value="{{ old('location', $project->location ?? '') }}" required>
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
                                        <input type="text" name="latitude" class="form-control" value="{{ old('latitude', $project->latitude ?? '') }}" required>
                                        @error('latitude')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Longitude</label>
                                        <input type="text" name="longitude" class="form-control" value="{{ old('longitude', $project->longitude ?? '') }}" required>
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
                                            <option value="1" {{ $project && $project->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $project && !$project->is_active ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                        @error('is_active')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-10">
                                <div class="col-12">
                                    <div class="card" style="box-shadow: none;">
                                        <div class="row" style="background-color: #7167f430;padding: 20px;border-radius: 15px;box-shadow: 1px 10px 40px #e4e2fde3; width:90%; margin:0px auto;">
                                            <div class="col-4">
                                                <label for="amenity_id"><strong>Amenities</strong></label>
                                                <select  class="form-control select2" id="amenity_id">
                                                    <option value="">Select</option>
                                                </select>
                                                <div id="amenity-required-error" class="text-danger mt-1" style="display:none;">Amenity and description are required.</div>
                                                <div id="amenity-duplicate-error" class="text-danger mt-1" style="display:none;">This amenity already exists in the list.</div>
                                            </div>
                                            <div class="col-6">
                                                <label for="amenty_description"><strong>Description</strong></label>
                                                <textarea class="form-control" id="amenty_description" cols="10" rows="1"></textarea>
                                            </div>
                                            <div class="col-2 align-self-end">
                                                <a class="btn" id="addAmenities" style="background-color: #7167f4;color: #fff;">Add</a>
                                                <a class="btn btn-warning d-none" id="updateAmenities">Update</a>
                                                <a class="btn mx-2 btn-danger" id="clearAmenities">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-hover {{ (is_object($project) && count($project->amenities) > 0) ? '' : 'd-none' }} mt-20 form-group">
                                        <thead>
                                            <tr>
                                                <th>Sno</th>
                                                <th>Amenities</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblAmenity">
                                            @php
                                                $Sa_Num = 1;
                                            @endphp
                                            @if ($project)
                                                @foreach ($project->amenities as $amenity)
                                                <tr data-new="false">
                                                    <td>{{$Sa_Num++}}</td>
                                                    <td data-amenity="{{ $amenity->amenity->name }}">
                                                            {{ $amenity->amenity->name }}
                                                            <input class="d-none" value="{{ $amenity->amenity_id }}" name="amenities[{{ $amenity->id }}][amenity_id]" data-id="{{ $amenity->id }}">
                                                        </td>
                                                        <td data-description="{{ $amenity->description }}">
                                                            {{ $amenity->description }}
                                                            <input class="d-none" value="{{ $amenity->description }}" name="amenities[{{ $amenity->id }}][description]" data-id="{{ $amenity->id }}">
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-outline-primary editAmenities"><i class="fa fa-pencil"></i></a>
                                                            <a class="btn btn-danger deleteAmenities"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                        <td data-tdata="{{ json_encode($amenity) }}" class="d-none">{{ json_encode($amenity) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    
                                    <input type="text" class="d-none" name="deletedAmenities" id="deletedAmenities">
                                </div>
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()"
                                       class="btn btn-warning">Back</a>
                                    <button type="submit"
                                            class="btn btn-primary">{{ $project ? 'Update' : 'Save' }}</button>
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

            
            // -----------------------------Amenities functionalities

            let amenityCellId = 0;
            let amenityUpdateId = 0;
            let amenityDeletedId = [];

            const serializeTable = (selector) => {
                let i = 1;
                $(`${selector} tr`).each(function () {
                    $(this).find('td:first').text(i++);
                });
            };

            const getAmenities = () => {
                let AmenityID = $('#amenity_id');
                let SelectedAmenityID = AmenityID.attr('data-selected');
                AmenityID.select2('destroy');
                $('#amenity_id option').remove();
                AmenityID.append('<option value="">--Select an Amenity--</option>');

                $.ajax({
                    url: "{{route('getAmenities')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == SelectedAmenityID)) {
                                AmenityID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                AmenityID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        AmenityID.select2();
                    },
                    error: function (xhr) {
                    }
                });
            }


            $('#addAmenities').on('click',function(){
                let amenity = $('#amenity_id').find('option:selected');
                let description = $('#amenty_description').val();
                $('#amenity-required-error').hide();
                $('#amenity-duplicate-error').hide();

                if(!amenity.val() || !description){
                    $('#amenity-required-error').show();
                    return;
                }

                let status = true;
                let table = $('#tblAmenity');

                table.find('tr').each(function () {
                    let amenityData = $(this).find('td[data-amenity]').data('amenity');
                    if (amenityData === amenity.text()) {
                        status = false;
                        return false;
                    }
                });

                if(!status){
                    $('#amenity-duplicate-error').show();
                    return;
                }

                const obj = {
                    'amenity_id':amenity.val(),
                    'amenity_name':amenity.text(),
                    'description':description,
                };

                let rowLength = $('#tblAmenity').find('tr').length;

                if(rowLength){
                    amenityCellId = $('#tblAmenity').find('tr:last td[data-amenity] input').attr('data-id');
                    amenityCellId++;
                }

                let html = `
                <tr data-new="true">
                    <td>*</td>
                    <td data-amenity="${amenity.text()}">${amenity.text()}<input class="d-none" value="${amenity.val()}" name="amenities[${amenityCellId}][amenity_id]" data-id="${amenityCellId}"></td>
                    <td data-description="${description}">${description}<input class="d-none" value="${description}" name="amenities[${amenityCellId}][description]" data-id="${amenityCellId}"></td>
                    <td>
                        <a class="btn btn-outline-primary editAmenities"><i class="fa fa-pencil"></i></a>
                        <a class="btn btn-danger deleteAmenities"><i class="fa fa-trash"></i></a>
                    </td>
                    <td data-tdata='${JSON.stringify(obj)}' class="d-none">${JSON.stringify(obj)}</td>
                </tr>`;

                table.append(html);
                serializeTable('#tblAmenity');

                $('#tblAmenity').closest('table').removeClass('d-none');
                clearAmenityFields();
            });

            $('#clearAmenities').on('click',function(){
                $('#updateAmenities').addClass('d-none');
                $('#addAmenities').removeClass('d-none');
                clearAmenityFields();
            })

            $(document).on('click','.editAmenities',function(){
                selectedAmenityRow = $(this).closest('tr');

                contractUpdateId = selectedAmenityRow.find('td[data-amenity] input').attr('data-id');

                let tdata = JSON.parse(selectedAmenityRow.find('td[data-tdata]').attr('data-tdata'));

                $('#amenity_id').val(tdata.amenity_id).trigger('change');
                $('#amenty_description').val(tdata.description);

                $('#addAmenities').addClass('d-none');
                $('#updateAmenities').removeClass('d-none');

            });


            $(document).on('click','.deleteAmenities', function(){
                let id = $(this).closest('tr[data-new="false"]').find('td[data-amenity] input').attr('data-id');

                if(id) amenityDeletedId.push(id);

                $('#deletedAmenities').val(JSON.stringify(amenityDeletedId));

                $(this).closest('tr').remove();
                let isRowEmpty = $('#tblAmenity').find('tr').length;

                if(!isRowEmpty){
                    $('#tblAmenity').closest('table').addClass('d-none');
                }
            })

            $('#updateAmenities').on('click', function () {

                let amenity = $('#amenity_id').find('option:selected');
                let description = $('#amenty_description').val();

                if (amenity.val() && description) {
                    let status = true;
                    let table = $('#tblAmenity');

                    table.find(`tr`).not(selectedAmenityRow).each(function () {
                        let amenityData = $(this).find('td[data-amenity]').attr('data-amenity');

                        if (amenityData === amenity.text() ) {
                            status = false;
                            return false;
                        }
                    });

                    if (status) {
                        const obj = {
                            'amenity_id': amenity.val(),
                            'amenity_name': amenity.text(),
                            'description': description
                        };

                        selectedAmenityRow.each(function () {
                            $(this).find('td[data-amenity]').attr('data-amenity', amenity.text()).html(amenity.text()+`<input class="d-none" value="${amenity.val()}" name="amenities[${amenityUpdateId}][amenity_id]" data-id="${amenityUpdateId}">`);
                            $(this).find('td[data-description]').attr('data-description', description).html(description+`<input class="d-none" value="${description}" name="amenities[${amenityUpdateId}][description]" data-id="${amenityUpdateId}">`);
                            $(this).find('td[data-tdata]').attr('data-tdata', JSON.stringify(obj)).text(JSON.stringify(obj));
                        });

                        $('#updateAmenities').addClass('d-none');
                        $('#addAmenities').removeClass('d-none');

                        selectedAmenityRow = null;
                        clearAmenityFields();
                    }
                }
            });

            const clearAmenityFields = () => {
                $('#amenity_id').val(null).trigger('change');
                $('#amenty_description').val('');
            }
            // -----------------------------end Amenities functionalities

            getSiteSupervisors();
            getAmenities();
        });

        
    </script>
@endsection
