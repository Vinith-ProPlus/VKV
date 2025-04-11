@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Blog Details";
        $ActiveMenuName = 'Blog';
    @endphp
    <style>
        .btn-green {
            background-color: #51bb2596 !important;
            border-color: #51bb2596 !important;
            color: white;
        }
        .btn-green:hover {
            background-color: #45a220 !important;
            border-color: #45a220 !important;
            color: white;
            font-weight: 600;
        }
        .btn-blue {
            background-color: #655af3 !important;
            border-color: #655af3 !important;
            color: white;
        }

        .btn-blue:hover {
            background-color: #3947f3 !important;
            border-color: #3947f3 !important;
            color: white;
            font-weight: 600;
        }
    </style>

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">User</li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>{{ $PageTitle }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h6>Project</h6>
                                <input class="form-control" value="{{ $blog->project->name ?? 'N/A' }}" readonly />
                            </div>
                            <div class="col-6">
                                <h6>Blog For</h6>
                                <input class="form-control" value="{{ $blog->user->name ?? 'N/A' }}" readonly />
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-6">
                                <h6>Stages</h6>
                                <input class="form-control" value="{{ $blog->stage->name ?? 'N/A' }}" readonly />
                            </div>
                            <div class="col-6">
                                <h6>Marked as Damaged</h6>
                                <input class="form-control" value="{{ $blog->is_damage ? 'Yes' : 'No' }}" readonly />
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-12">
                                <h6>Remarks</h6>
                                <textarea class="form-control" readonly>{{ $blog->remarks }}</textarea>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-12">
                                <h6>Attachments</h6>
                                <div class="row d-flex">
                                    @if($blog->documents->count() > 0)
                                        @foreach($blog->documents as $document)
                                            <div class="col-3 document-box border p-2 m-1" style="min-width: 100px; text-align: center;">
                                                <p class="text-truncate" style="max-width: 200px; margin-top: 5px; margin-bottom: 5px;">{{$document->file_name}}</p>
                                                <a href="{{ asset('storage/' . $document->file_path) }}" target="_blank" class="btn btn-sm btn-blue">View</a>
                                                <a href="{{ asset('storage/' . $document->file_path) }}" download class="btn btn-sm btn-green">Download</a>
                                            </div>
                                        @endforeach
                                    @else
                                        <p>No attachments available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4 text-end">
                            <div>
                                <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
