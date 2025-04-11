@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Districts";
        $ActiveMenuName='Districts';
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
                            <div class="col-sm-4 my-2"><h5>{{ $district  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form action="{{ $district ? route('districts.update', $district->id) : route('districts.store') }}" method="POST">
                                    @csrf
                                    @if($district) @method('PUT') @endif
                                    <div class="form-group">
                                        <label>District Name</label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $district ? old('name', $district->name) : old('name') }}" required>
                                        @error('name')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>State</label>
                                        <select id="lststate" name="state_id" class="form-control select2 @error('state_id') is-invalid @enderror"
                                        data-selected='{{ $district ? old('state_id', $district->state_id) : old('state_id') }}' required>
                                            <option value="">--Select a State--</option>
                                        </select>
                                        @error('state_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>Active Status</label>
                                        <select name="is_active"
                                                class="form-control @error('is_active') is-invalid @enderror">
                                            <option value="1" {{ $district && $district->is_active ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $district && !$district->is_active ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('is_active')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                                            @if(!$district)
                                                @can('Create Districts')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Districts')
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

        const getStates = () =>{

            let StateID = $('#lststate').attr('data-selected');
            $('#lststate').select2('destroy');
            $('#lststate option').remove();
            $('#lststate').append('<option value="">--Select a State--</option>');

            $.ajax({
                url:"{{route('getStates')}}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    response.forEach(function(item) {
                        if ((item.id == StateID)) {
                            $('#lststate').append('<option selected value="' + item.id
                                + '">' + item.name + '</option>');
                        } else {
                            $('#lststate').append('<option value="' + item.id
                                + '">'  + item.name + '</option>');
                        }
                    });
                    $('#lststate').select2();
                },
                error: function(xhr) {}
            });
        }

        const init = () => {
            getStates();
        }

        init();
    });
</script>
@endsection
