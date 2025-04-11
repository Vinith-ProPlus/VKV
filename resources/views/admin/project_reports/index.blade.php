@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Project Reports";
        $ActiveMenuName = 'Project Reports';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-6 col-sm-6 col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2"><h5>{{ $PageTitle }}</h5></div>
                            <div class="col-sm-4 my-2 text-right">
                                @can('Create Project Reports')
                                    <a class="btn btn-sm btn-primary add-btn" href="{{ route('purchase-requests.create') }}">Add New Request</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                    <form method="GET" action="{{route('project_reports.create')}}">
                        <div class="card-body"> 
                            <div class="mt-20"> 
                                <label for="">Project</label>
                                <select class="form-control" name="project" required>
                                    <option value="">Select a Project</option>
                                    @if($projects)
                                        @foreach ($projects as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="text-center mt-20">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script') 
@endsection
