@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = $product ? 'Edit Product' : 'Create Product';
        $ActiveMenuName = "Product";
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header text-center">
                <h5>{{ $PageTitle }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ $product ? route('products.update', $product->id) : route('products.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($product) @method('PUT') @endisset

                    <div class="d-flex justify-content-center align-items-center">
                        <div class="text-center">
                            <label class="d-block">Product Image</label>
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
                            <label for="name">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name ?? '') }}" required>
                            @error('name')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="code">Product Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $product->code ?? '') }}" required>
                            @error('code')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mt-15">
                            <label for="category_id">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-control select2 @error('category_id') is-invalid @enderror" required></select>
                            @error('category_id')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mt-15 d-none">
                            <label for="tax_id">Tax <span class="text-danger">*</span></label>
                            <select name="tax_id" id="tax_id" class="form-control select2 @error('tax_id') is-invalid @enderror"></select>
                            @error('tax_id')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mt-15">
                            <label for="uom_id">Unit of Measurement <span class="text-danger">*</span></label>
                            <select name="uom_id" id="uom_id" class="form-control select2 @error('uom_id') is-invalid @enderror" required></select>
                            @error('uom_id')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-md-6 mt-15">
                            <label for="is_active">Status</label>
                            <select name="is_active" id="is_active" class="form-control @error('is_active') is-invalid @enderror">
                                <option value="1" {{ $product && $product->is_active ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ $product && !$product->is_active ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                            <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-15 text-end">
                        <div>
                            <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                            @if(!$product)
                                @can('Create Product')
                                    <button type="submit" class="btn btn-primary">Save</button>
                                @endcan
                            @else
                                @can('Edit Product')
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
            @if($product && $product->image)
                $("#image-preview").removeClass("d-none").attr("src", "{{ Storage::url($product->image) }}");
                $("#image-dropzone i, #image-dropzone p").hide();
            @endif

            $('.select2').select2({ width: '100%' });

            function loadOptions(url, elementId, selectedValue) {
                $.get(url, function (response) {
                    let options = '<option value="">Select</option>';
                    response.forEach(item => {
                        options += `<option value="${item.id}" ${selectedValue == item.id ? 'selected' : ''}>${item.name}</option>`;
                    });
                    $(elementId).html(options);
                });
            }

            loadOptions('{{ route("categories.list") }}', '#category_id', '{{ old('category_id', $product->category_id ?? '') }}');
            {{--loadOptions('{{ route("taxes.list") }}', '#tax_id', '{{ old('tax_id', $product->tax_id ?? '') }}');--}}
            loadOptions('{{ route("uoms.list") }}', '#uom_id', '{{ old('uom_id', $product->uom_id ?? '') }}');
        });

    </script>
@endsection
