@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = 'Project Reports';
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
                            <div class="col-sm-4 my-2">
                                <h5>{{ $PageTitle }}</h5>
                            </div>
                            <div class="col-sm-4 my-2 text-right">
                            </div>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('project_reports.create') }}">
                        <div class="card-body">
                            <div class="mt-20">
                                <label for="">Project</label>
                                <select class="form-control" id="project" name="project" required>
                                    <option value="">Select a Project</option>
                                    @if ($projects)
                                        @foreach ($projects as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mt-20">
                                <label for="">Site</label>
                                <select class="form-control" id="site" name="site" required>
                                    <option value="">Select a Site</option>
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
    <script>
        $(document).ready(function() {
            let currentRequest = null;

            $('#project').on('change', function() {
                let projectId = $(this).val();

                if (currentRequest) {
                    currentRequest.abort();
                }

                if (projectId) {
                    currentRequest = $.ajax({
                        url: "{{ route('project_reports.sitesList') }}",
                        type: "GET",
                        data: {
                            project_id: projectId
                        },
                        dataType: 'json',
                        success: loadSites
                    });
                }
            })

            function loadSites(data) {
                let select = $('#site');

                if (data) {
                    select.empty().append('<option value="">Select a Site</option>');

                    data.forEach(function(element) {
                        select.append(`<option value="${element.id}">${element.site_no}</option>`);
                    });
                }
            }
        });
    </script>
@endsection
