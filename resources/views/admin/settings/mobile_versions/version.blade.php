@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Mobile Version";
        $ActiveMenuName = 'Mobile-version';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Settings</li>
                        <li class="breadcrumb-item">Mobile Version</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-sm-12 col-lg-8">
                <div class="card">
                    <div class="card-header text-center"><h5 class="mt-10">{{$PageTitle}}</h5></div>
                    <div class="card-body">
                        <form action="{{ route('mobile_version.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="current_version">Current Version <span class="text-danger">*</span></label>
                                        <input type="text" name="current_version" id="current_version" class="form-control @error('current_version') is-invalid @enderror" value="{{ old('current_version', $version->current_version ?? '') }}" required>
                                        @error('current_version')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="new_version">New Version <span class="text-danger">*</span></label>
                                        <input type="text" name="new_version" id="new_version" class="form-control @error('new_version') is-invalid @enderror" value="{{ old('new_version', $version->new_version ?? '') }}" required>
                                        @error('new_version')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="title">Update Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $version->title ?? '') }}" required>
                                        @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="logo">Update Image</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" name="logo" id="logo" class="custom-file-input @error('logo') is-invalid @enderror">
                                            </div>
                                        </div>
                                        @error('logo')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                        @if(isset($version) && $version->logo)
                                            <div class="mt-2">
                                                <img src="{{ $version->UpdateImageUrl }}" alt="Update Image" class="img-thumbnail" style="max-height: 100px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Update Description <span class="text-danger">*</span></label>
                                <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $version->description ?? '') }}</textarea>
                                @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="android_link">Android App Link <span class="text-danger">*</span></label>
                                        <input type="url" name="android_link" id="android_link" class="form-control @error('android_link') is-invalid @enderror" value="{{ old('android_link', $version->android_link ?? '') }}" required>
                                        @error('android_link')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="ios_link">iOS App Link <span class="text-danger">*</span></label>
                                        <input type="url" name="ios_link" id="ios_link" class="form-control @error('ios_link') is-invalid @enderror" value="{{ old('ios_link', $version->ios_link ?? '') }}" required>
                                        @error('ios_link')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="submit_text">Submit Button Text <span class="text-danger">*</span></label>
                                        <input type="text" name="submit_text" id="submit_text" class="form-control @error('submit_text') is-invalid @enderror" value="{{ old('submit_text', $version->submit_text ?? 'Update Now') }}" required>
                                        @error('submit_text')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="ignore_text">Ignore Button Text <span class="text-danger">*</span></label>
                                        <input type="text" name="ignore_text" id="ignore_text" class="form-control @error('ignore_text') is-invalid @enderror" value="{{ old('ignore_text', $version->ignore_text ?? 'Later') }}" required>
                                        @error('ignore_text')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="update_type">Update Type <span class="text-danger">*</span></label>
                                        <select name="update_type" id="update_type" class="form-control @error('update_type') is-invalid @enderror" required>
                                            <option value="">Select Update Type</option>
                                            <option value="force" {{ (old('update_type', $version->update_type ?? '') == 'force') ? 'selected' : '' }}>Force Update</option>
                                            <option value="optional" {{ (old('update_type', $version->update_type ?? '') == 'optional') ? 'selected' : '' }}>Optional Update</option>
                                        </select>
                                        @error('update_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6 mt-15">
                                    <div class="form-group">
                                        <label for="update_to">Update To Platform <span class="text-danger">*</span></label>
                                        <select name="update_to" id="update_to" class="form-control @error('update_to') is-invalid @enderror" required>
                                            <option value="">Select Platform</option>
                                            <option value="android" {{ (old('update_to', $version->update_to ?? '') == 'android') ? 'selected' : '' }}>Android Only</option>
                                            <option value="ios" {{ (old('update_to', $version->update_to ?? '') == 'ios') ? 'selected' : '' }}>iOS Only</option>
                                            <option value="both" {{ (old('update_to', $version->update_to ?? '') == 'both') ? 'selected' : '' }}>Both Platforms</option>
                                        </select>
                                        @error('update_to')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                    <button type="submit" class="btn btn-primary">{{ $version ? 'Update' : 'Save' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')

@endsection
