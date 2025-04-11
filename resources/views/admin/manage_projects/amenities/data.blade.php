@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Amenity";
        $ActiveMenuName='Amenity';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Projects</li>
                        <li class="breadcrumb-item">{{$PageTitle}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-sm-12 col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2">
                                <h5>{{ $amenity  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form
                                    action="{{ $amenity ? route('amenities.update', $amenity->id) : route('amenities.store') }}"
                                    method="POST">
                                    @csrf
                                    @if($amenity)
                                        @method('PUT')
                                    @endif
                                    <div class="form-group">
                                        <label>Amenity Name</label>
                                        <input type="text" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $amenity ? old('name', $amenity->name) : old('name') }}"
                                               required>
                                        @error('name')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group mt-15">
                                        <label>Active Status</label>
                                        <select name="is_active"
                                                class="form-control @error('is_active') is-invalid @enderror">
                                            <option
                                                value="1" {{ $amenity && $amenity->is_active ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option
                                                value="0" {{ $amenity && !$amenity->is_active ? 'selected' : '' }}>
                                                Inactive
                                            </option>
                                        </select>
                                        @error('is_active')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" type="button"
                                               class="btn btn-warning">Back</a>
                                            @if(!$amenity)
                                                @can('Create Amenities')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Amenities')
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
