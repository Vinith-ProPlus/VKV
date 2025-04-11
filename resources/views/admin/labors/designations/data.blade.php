@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Labor Designation";
        $ActiveMenuName='Labor-Designation';
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
            <div class="col-12 col-sm-12 col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row"><div class="col-sm-12 my-2 text-center">
                                <h5>{{ $labor_designation  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form
                                    action="{{ $labor_designation ? route('labor-designations.update', $labor_designation->id) : route('labor-designations.store') }}"
                                    method="POST">
                                    @csrf
                                    @if($labor_designation)
                                        @method('PUT')
                                    @endif
                                    <div class="form-group">
                                        <label>Labor Designation Name</label>
                                        <input type="text" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $labor_designation ? old('name', $labor_designation->name) : old('name') }}"
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
                                                value="1" {{ $labor_designation && $labor_designation->is_active ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option
                                                value="0" {{ $labor_designation && !$labor_designation->is_active ? 'selected' : '' }}>
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
                                            @if(!$labor_designation)
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
