@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Warehouse";
        $ActiveMenuName='Warehouse';
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
                            <div class="col-sm-4 my-2"><h5>{{ $warehouse ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form action="{{ $warehouse ? route('warehouses.update', $warehouse->id) : route('warehouses.store') }}" method="POST">
                                    @csrf
                                    @if($warehouse) @method('PUT') @endif
                                    <div class="form-group">
                                        <label>Warehouse Name</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $warehouse ? old('name', $warehouse->name) : old('name') }}" required>
                                        @error('name')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-10">
                                        <label>Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                                               required>{{ $warehouse ? old('address', $warehouse->address) : old('address') }}</textarea>
                                        @error('address')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-6 form-group mt-10">
                                            <label>State</label>
                                            <select name="state_id" id="state" class="form-control select2 @error('state_id') is-invalid @enderror"
                                                    data-selected='{{ $warehouse ? old('state_id', $warehouse->state_id) : old('state_id') }}' required>
                                                <option value="">Select a State</option>
                                            </select>
                                            @error('state_id')
                                            <span class="error invalid-feedback">{{$message}}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 form-group mt-10">
                                            <label>District</label>
                                            <select name="district_id" id="district" class="form-control select2 @error('district_id') is-invalid @enderror"
                                                    data-selected='{{ $warehouse ? old('district_id', $warehouse->district_id) : old('district_id') }}' required>
                                                <option value="">Select a District</option>
                                            </select>
                                            @error('district_id')
                                            <span class="error invalid-feedback">{{$message}}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 form-group mt-10">
                                            <label>City</label>
                                            <select name="city_id" id="city" class="form-control select2 @error('city_id') is-invalid @enderror"
                                                    data-selected='{{ $warehouse ? old('city_id', $warehouse->city_id) : old('city_id') }}' required>
                                                <option value="">Select a City</option>
                                            </select>
                                            @error('city_id')
                                            <span class="error invalid-feedback">{{$message}}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 form-group mt-10">
                                            <label>Pincode</label>
                                            <select name="pincode_id" id="pincode" class="form-control select2 @error('pincode_id') is-invalid @enderror"
                                                    data-selected='{{ $warehouse ? old('pincode_id', $warehouse->pincode_id) : old('pincode_id') }}' required>
                                                <option value="">Select a Pincode</option>
                                            </select>
                                            @error('pincode_id')
                                            <span class="error invalid-feedback">{{$message}}</span>
                                            @enderror
                                        </div>
                                        <div class="col-6 form-group mt-10">
                                            <label>Active Status</label>
                                            <select name="is_active"
                                                    class="form-control @error('is_active') is-invalid @enderror">
                                                <option value="1" {{ $warehouse && $warehouse->is_active ? 'selected' : '' }}>Active</option>
                                                <option value="0" {{ $warehouse && !$warehouse->is_active ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('is_active')
                                            <span class="error invalid-feedback">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                                            @if(!$warehouse)
                                                @can('Create Warehouse')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Warehouse')
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

        // --------------------------------- event listners

        $('#state').change(() => getDistricts());

        $('#district').change(() => getCities());

        $('#city').change(() => getPincodes());

        // ------------------------------- get dropdowns

        const getStates = () =>{
            let StateID = $('#state').attr('data-selected');
            $('#state').select2('destroy');
            $('#state option').remove();
            $('#state').append('<option value="">Select a State</option>');

            $.ajax({
                url:"{{route('getStates')}}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    response.forEach(function(item) {
                        if ((item.id == StateID)) {
                            $('#state').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#state').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                },
                error: function(e, x, settings, exception) {
                    // ajaxErrors(e, x, settings, exception);
                },
            });
            $('#state').select2();
            getDistricts();
        }

        const getDistricts = () =>{

            let districtID = $('#district').attr('data-selected');
            let stateID = $('#state').val();
            $('#district').select2('destroy');
            $('#district option').remove();
            $('#district').append('<option value="">Select a District</option>');

            $.ajax({
                url:"{{route('getDistricts')}}",
                type: 'GET',
                dataType: 'json',
                data: { 'state_id':stateID },
                success: function(response) {
                    console.log(response);
                    response.forEach(function(item) {
                        if ((item.id == districtID)) {
                            $('#district').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#district').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                },
                error: function(e, x, settings, exception) {
                    // ajaxErrors(e, x, settings, exception);
                },
            });
            $('#district').select2();
            getCities();
        }

        const getCities = () =>{

            let cityID = $('#city').attr('data-selected');
            let districtID = $('#district').val();
            $('#city').select2('destroy');
            $('#city option').remove();
            $('#city').append('<option value="">Select a City</option>');

            $.ajax({
                url:"{{route('getCities')}}",
                type: 'GET',
                dataType: 'json',
                data: { 'district_id':districtID },
                success: function(response) {
                    response.forEach(function(item) {
                        if ((item.id == cityID)) {
                            $('#city').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#city').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                },
                error: function(e, x, settings, exception) {
                    // ajaxErrors(e, x, settings, exception);
                },
            });
            $('#city').select2();
            getPincodes();
        }

        const getPincodes = () =>{

            let StateID = $('#pincode').attr('data-selected');
            let districtID = $('#district').val();
            $('#pincode').select2('destroy');
            $('#pincode option').remove();
            $('#pincode').append('<option value="">Select a Pincode</option>');

            $.ajax({
                url:"{{route('getPinCodes')}}",
                type: 'GET',
                dataType: 'json',
                data: { 'district_id':districtID },
                success: function(response) {
                    response.forEach(function(item) {
                        if ((item.id == StateID)) {
                            $('#pincode').append('<option selected value="' + item.id
                                + '">' + item.pincode + '</option>');
                        } else {
                            $('#pincode').append('<option value="' + item.id
                                + '">'  + item.pincode + '</option>');
                        }
                    });
                },
                error: function(e, x, settings, exception) {
                    // ajaxErrors(e, x, settings, exception);
                },
            });
            $('#pincode').select2();
        }

        getStates();
    });
</script>
@endsection
