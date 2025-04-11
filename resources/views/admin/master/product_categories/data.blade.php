@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Product Category";
        $ActiveMenuName='Product-Category';
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
            <div class="col-12 col-sm-12 col-lg-8">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-sm-12 my-2">
                                <h5>{{ $productCategory  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form
                                    action="{{ $productCategory ? route('product_categories.update', $productCategory->id) : route('product_categories.store') }}"
                                    method="POST">
                                    @csrf
                                    @if($productCategory)
                                        @method('PUT')
                                    @endif
                                    <div class="form-group">
                                        <label>Category Name</label>
                                        <input type="text" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $productCategory ? old('name', $productCategory->name) : old('name') }}"
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
                                                value="1" {{ $productCategory && $productCategory->is_active ? 'selected' : '' }}>
                                                Active
                                            </option>
                                            <option
                                                value="0" {{ $productCategory && !$productCategory->is_active ? 'selected' : '' }}>
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
                                            @if(!$productCategory)
                                                @can('Create Product Category')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Product Category')
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
