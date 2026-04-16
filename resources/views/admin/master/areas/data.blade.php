@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Areas";
        $ActiveMenuName='Areas';
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
                            <div class="col-sm-4 my-2"><h5>{{ $area  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form action="{{ $area ? route('areas.update', $area->id) : route('areas.store') }}" method="POST">
                                    @csrf
                                    @if($area) @method('PUT') @endif
                                    <div class="form-group">
                                        <label>Area Name</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $area ? old('name', $area->name) : old('name') }}" required>
                                        @error('name')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>State</label>
                                        <select id="state_id" name="state_id" class="form-control select2"
                                        data-selected='{{ $state ? old('district_id', $state->id) : "" }}' required>
                                            <option value="">--Select a State--</option>
                                        </select> 
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>District</label>
                                        <select id="district_id" name="district_id" class="form-control select2 @error('district_id') is-invalid @enderror"
                                        data-selected='{{ $area ? old('district_id', $area->district_id) : old('district_id') }}' required>
                                            <option value="">--Select a District--</option>
                                        </select>
                                        @error('district_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>Active Status</label>
                                        <select name="is_active"
                                                class="form-control @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ $area && $area->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $area && !$area->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                                            @if(!$area)
                                                @can('Create Areas')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Areas')
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
                    getDistricts();
                },
                error: function(xhr) {}
            });
        }


        const getDistricts = () =>{
            $('#district_id').select2();
            
            let StateID = $('#state_id').val();
            let DistritID = $('#district_id').attr('data-selected');

            destroySelect2('#district_id');
 
            $('#district_id').empty().append('<option value="">--Select a District--</option>');

            $.ajax({
                url:"{{route('getDistricts')}}",
                type: 'GET',
                dataType: 'json',
                data:{'state_id':StateID},
                success: function(response) {
                    response.forEach(function(item) {
                        if ((item.id == DistritID)) {
                            $('#district_id').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#district_id').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                    $('#district_id').select2();
                },
                error: function(xhr) {}
            });
        }
    
        //    ----------------------------event listeners

        $('#state_id').change(getDistricts);

        const init = () => {

            $('#district_id, #state_id').select2();

            getStates(); 
        }

        init();
    });
</script>
@endsection
