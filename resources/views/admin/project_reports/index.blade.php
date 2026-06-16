@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = 'Site Reports';
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
            <div class="col-12 col-lg-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>{{ $PageTitle }}</h5>
                        <p class="text-muted mb-0 small">Select a project and site to generate a detailed site report</p>
                    </div>
                    <form method="GET" action="{{ route('project_reports.create') }}">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="project">Project <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="project" name="project" required>
                                    <option value="">Select a Project</option>
                                    @foreach ($projects as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mt-20">
                                <label for="site">Site <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="site" name="site" required disabled>
                                    <option value="">Select a Site</option>
                                </select>
                                <small id="siteHint" class="text-muted">Choose a project first to load its sites.</small>
                            </div>
                            <div class="text-center mt-20">
                                <button type="submit" class="btn btn-primary" id="generateBtn" disabled>
                                    <i class="fa fa-file-alt"></i> Generate Site Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#project, #site').select2({ width: '100%' });
            let currentRequest = null;

            function toggleGenerate() {
                $('#generateBtn').prop('disabled', !$('#site').val());
            }

            $('#project').on('change', function() {
                let projectId = $(this).val();
                let $site = $('#site');

                if (currentRequest) {
                    currentRequest.abort();
                }

                $site.prop('disabled', true).empty().append('<option value="">Loading sites...</option>').trigger('change.select2');
                toggleGenerate();

                if (!projectId) {
                    $site.empty().append('<option value="">Select a Site</option>').prop('disabled', true).trigger('change.select2');
                    $('#siteHint').text('Choose a project first to load its sites.');
                    return;
                }

                currentRequest = $.ajax({
                    url: "{{ route('project_reports.sitesList') }}",
                    type: "GET",
                    data: { project_id: projectId },
                    dataType: 'json',
                    success: function(data) {
                        $site.empty().append('<option value="">Select a Site</option>');
                        if (data.length === 0) {
                            $('#siteHint').text('No sites found for this project.');
                        } else {
                            data.forEach(function(element) {
                                let label = element.site_no;
                                if (element.status) {
                                    label += ' (' + element.status + ')';
                                }
                                $site.append(`<option value="${element.id}">${label}</option>`);
                            });
                            $('#siteHint').text(data.length + ' site(s) available.');
                        }
                        $site.prop('disabled', false).trigger('change.select2');
                    },
                    error: function() {
                        $site.empty().append('<option value="">Unable to load sites</option>').trigger('change.select2');
                        $('#siteHint').text('Failed to load sites. Please try again.');
                    }
                });
            });

            $('#site').on('change', toggleGenerate);
        });
    </script>
@endsection
