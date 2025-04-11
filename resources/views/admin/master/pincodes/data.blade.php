@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Pincodes";
        $ActiveMenuName='Pincodes';
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
                            <div class="col-sm-4 my-2"><h5>{{ $pincode  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form action="{{ $pincode ? route('pincodes.update', $pincode->id) : route('pincodes.store') }}" method="POST">
                                    @csrf
                                    @if($pincode) @method('PUT') @endif
                                    <div class="form-group">
                                        <label>Pincode</label>
                                        <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror"
                                               value="{{ $pincode ? old('pincode', $pincode->pincode) : old('pincode') }}" required>
                                        @error('pincode')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>State</label>
                                        <select id="state_id" class="form-control select2"
                                        data-selected='{{ $state ? old('id', $state->id) : "" }}' required>
                                            <option value="">--Select a State--</option>
                                        </select>
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>District</label>
                                        <select id="district_id" class="form-control select2"
                                        data-selected='{{ $district ? old('id', $district->id) : "" }}' required>
                                            <option value="">--Select a District--</option>
                                        </select>
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>City</label>
                                        <select id="city_id" name="city_id" class="form-control select2 @error('city_id') is-invalid @enderror"
                                        data-selected='{{ $pincode ? old('city_id', $pincode->city_id) : old('city_id') }}' required>
                                            <option value="">--Select a City--</option>
                                        </select>
                                        @error('city_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>Active Status</label>
                                        <select name="is_active"
                                                class="form-control @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ $pincode && $pincode->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $pincode && !$pincode->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                                            @if(!$pincode)
                                                @can('Create Pincodes')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Pincodes')
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
    $(document).ready(function(){

        const destroySelect2 = (selector) => {
            if ($.fn.select2 && $(selector).hasClass("select2-hidden-accessible")) {
                $(selector).select2('destroy');
            }
        };

        const getStates = () =>{

            let StateID = $('#state_id').attr('data-selected');

            destroySelect2('#state_id');
            $('#state_id').empty().append('<option value="">--Select a State--</option>');

            $.ajax({
                url:"{{route('getStates')}}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    response.forEach(function(item) {
                        if ((item.id == StateID)) {
                            $('#state_id').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#state_id').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                    $('#state_id').select2();
                    @if($pincode)
                        getDistricts();
                    @endif
                },
                error: function(xhr) {}
            });
        }

        const getDistricts = () =>{
            let StateID = $('#state_id').val();
            let DistrictID = $('#district_id').attr('data-selected');

            destroySelect2('#district_id');
            $('#district_id').empty().append('<option value="">--Select a District--</option>');

            $.ajax({
                url:"{{route('getDistricts')}}",
                type: 'GET',
                dataType: 'json',
                data:{'state_id':StateID},
                success: function(response) {
                    response.forEach(function(item) {
                        if ((item.id == DistrictID)) {
                            $('#district_id').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#district_id').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                    $('#district_id').select2();
                    @if($pincode)
                        getCities();
                    @endif
                },
                error: function(xhr) {}
            });
        }

        const getCities = () =>{

            let DistrictID = $('#district_id').val();
            let CityID = $('#city_id').attr('data-selected');

            destroySelect2('#city_id');
            $('#city_id').empty().append('<option value="">--Select a City--</option>');

            $.ajax({
                url:"{{route('getCities')}}",
                type: 'GET',
                dataType: 'json',
                data:{'district_id':DistrictID},
                success: function(response) {
                    response.forEach(function(item) {
                        if ((item.id == CityID)) {
                            $('#city_id').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#city_id').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                    $('#city_id').select2();

                },
                error: function(xhr) {}
            });
        }

        $('#state_id').change(getDistricts);
        $('#district_id').change(getCities);


        const init = () => {

            $('#state_id, #district_id, #city_id').select2();

            getStates();
        }

        init();
    });
</script>
@endsection
