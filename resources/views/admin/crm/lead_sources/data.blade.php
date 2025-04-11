@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = $lead_source ? 'Edit Lead Source' : 'Create Lead Source';
        $ActiveMenuName = "Lead-Source";
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">CRM</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid col-md-8">
        <div class="card">
            <div class="card-header text-center">
                <h5>{{ $PageTitle }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ $lead_source ? route('lead_sources.update', $lead_source->id) : route('lead_sources.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($lead_source) @method('PUT') @endisset

                    <div class="d-flex justify-content-center align-items-center">
                        <div class="text-center">
                            <label class="d-block">Lead Source Image <span class="text-danger">*</span></label>
                            <div id="image-dropzone" class="image-box border rounded d-flex align-items-center justify-content-center flex-column text-center"
                                 style="width: 200px; height: 200px; cursor: pointer; background: #f8f9fa; border: 2px dashed #ccc;">
                                <i class="fa fa-upload fa-2x text-secondary"></i>
                                <p class="text-muted m-0">Drag &amp; drop a file here or click</p>
                                <img id="image-preview" src="" class="img-fluid d-none" style="max-width: 100%; max-height: 100%;" alt="">
                            </div>
                            <input type="file" id="image-input" name="image" class="d-none" accept="image/*">
                        </div>
                    </div>
                    <div class="row mt-15">
                        <div class="col-md-6">
                            <label for="name">Lead Source Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $lead_source->name ?? '') }}" required>
                            @error('name')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active" class="form-control">
                                <option value="1" {{ $lead_source && $lead_source->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $lead_source && !$lead_source->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-15 text-end">
                        <div>
                            <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                            @if(!$lead_source)
                                @can('Create Lead Source')
                                    <button type="submit" class="btn btn-primary">Save</button>
                                @endcan
                            @else
                                @can('Edit Lead Source')
                                    <button type="submit" class="btn btn-primary">Update</button>
                                @endcan
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function () {
            @if($lead_source && $lead_source->image)
                $("#image-preview").removeClass("d-none").attr("src", "{{ Storage::url($lead_source->image) }}");
                $("#image-dropzone i, #image-dropzone p").hide();
            @endif
        });
    </script>
@endsection
