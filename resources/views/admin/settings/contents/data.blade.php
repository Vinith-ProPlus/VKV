@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Content Management System";
        $ActiveMenuName = 'CMS';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" title=""><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
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
                        <h5>{{ $PageTitle }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $content ? route('contents.update', $content->id) : route('contents.store') }}" method="POST">
                            @csrf
                            @if($content)
                                @method('PUT')
                            @endif

                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $content->name ?? '') }}" required>
                                @error('name')
                                    <span class="error invalid-feedback">{{$message}}</span>
                                @enderror
                            </div>

                            <div class="form-group mt-15">
                                <label for="content">Content</label>
                                <textarea class="form-control" name="content" id="content">{{ old('content', $content->content ?? '') }}</textarea>
                                @error('content')
                                    <span class="error invalid-feedback">{{$message}}</span>
                                @enderror
                            </div>

                            <div class="form-group mt-15">
                                <label for="is_active">Active Status</label>
                                <select name="is_active" id="is_active" class="form-control">
                                    <option value="1" {{ old('is_active', $content->is_active ?? '') == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $content->is_active ?? '') == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <span class="error invalid-feedback">{{$message}}</span>
                                @enderror
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                    <button type="submit" class="btn btn-primary">{{ $content ? 'Update' : 'Save' }}</button>
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
{{--    <script src="https://cdn.ckeditor.com/4.18.0/basic/ckeditor.js"></script>--}}
    <script>
        CKEDITOR.replace('content', {
            removePlugins: 'cloudservices,easyimage',
            height: 300
        });
    </script>
@endsection
