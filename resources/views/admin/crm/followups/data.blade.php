@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = 'Followup';
        $ActiveMenuName = 'Followups';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">CRM</li>
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
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2">
                                <h5>{{ $followup ? 'Edit' : 'Create' }} {{ $PageTitle }}</h5>
                            </div>
                            <div class="col-sm-4 my-2 text-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form class="row"
                                    action="{{ $followup ? route('followups.update', $followup->id) : route('followups.store') }}"
                                    method="POST">
                                    @csrf
                                    @if ($followup)
                                        @method('PUT')
                                    @endif

                                    <!-- Lead Dropdown -->
                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Lead <span class="text-danger">*</span></label>
                                        <select name="customer_id" id="customer_id"
                                            class="form-control select2 @error('customer_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select a Lead</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    {{ $followup && old('customer_id', $followup->customer_id) == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} - {{ $customer->mobile_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Project Dropdown -->
                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Project <span class="text-danger">*</span></label>
                                        <select name="project_id" id="project_id"
                                            class="form-control select2 @error('project_id') is-invalid @enderror"
                                            data-selected='{{ $followup ? old('project_id', $followup->project_id) : old('project_id') }}'
                                            required>
                                            <option value="">Select a Project</option>
                                        </select>
                                        @error('project_id')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Site Dropdown (Optional) -->
                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Site</label>
                                        <select name="site_id" id="site_id"
                                            class="form-control select2 @error('site_id') is-invalid @enderror"
                                            data-selected='{{ $followup ? old('site_id', $followup->site_id) : old('site_id') }}'>
                                            <option value="">Select a Site (Optional)</option>
                                        </select>
                                        @error('site_id')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status Dropdown -->
                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status"
                                            class="form-control select2 @error('status') is-invalid @enderror" required>
                                            <option value="">Select a Status</option>
                                            <option value="new"
                                                {{ $followup && old('status', $followup->status) == 'new' ? 'selected' : '' }}>
                                                New</option>
                                            <option value="under followup"
                                                {{ $followup && old('status', $followup->status) == 'under followup' ? 'selected' : '' }}>
                                                Under Followup</option>
                                            <option value="visited"
                                                {{ $followup && old('status', $followup->status) == 'visited' ? 'selected' : '' }}>
                                                Visited</option>
                                            <option value="booked"
                                                {{ $followup && old('status', $followup->status) == 'booked' ? 'selected' : '' }}>
                                                Booked</option>
                                            <option value="sold"
                                                {{ $followup && old('status', $followup->status) == 'sold' ? 'selected' : '' }}>
                                                Sold</option>
                                            <option value="closed"
                                                {{ $followup && old('status', $followup->status) == 'closed' ? 'selected' : '' }}>
                                                Closed</option>
                                        </select>
                                        @error('status')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Remarks Text Area -->
                                    <div class="form-group col-sm-12 col-lg-12 mt-15">
                                        <label>Remarks</label>
                                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4"
                                            placeholder="Enter remarks here...">{{ $followup ? old('remarks', $followup->remarks) : old('remarks') }}</textarea>
                                        <div class="last-remarks-div d-none">
                                            <span>Last:</span>
                                            <span class="text-muted" id="last-remark">asxsa</span>
                                        </div>
                                        @error('remarks')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()"
                                                class="btn btn-warning">Back</a>
                                            @if (!$followup)
                                                @can('Create Followups')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Followups')
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

@section('script')
    <script>
        $(document).ready(function() {
            $('#select2').select2();

            // Get projects
            const getProjects = () => {
                let ProjectID = $('#project_id');
                let SelectedProject = ProjectID.attr('data-selected');
                ProjectID.select2('destroy');
                $('#project_id option').remove();
                ProjectID.append('<option value="">Select a Project</option>');

                $.ajax({
                    url: "{{ route('getProjects') }}",
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        response.forEach(function(item) {
                            if ((item.id == SelectedProject)) {
                                ProjectID.append('<option selected value="' + item.id +
                                    '">' + item.name + '</option>');
                            } else {
                                ProjectID.append('<option value="' + item.id +
                                    '">' + item.name + '</option>');
                            }
                        });
                    },
                    error: function(e, x, settings, exception) {
                        // ajaxErrors(e, x, settings, exception);
                    },
                });
                ProjectID.select2();
            }

            // Get sites based on selected project
            const getSites = () => {
                let SiteID = $('#site_id');
                let SelectedSite = SiteID.attr('data-selected');
                let ProjectID = $('#project_id').val();
                SiteID.select2('destroy');
                $('#site_id option').remove();
                SiteID.append('<option value="">Select a Site (Optional)</option>');

                if (ProjectID) {
                    $.ajax({
                        url: "{{ route('getSites') }}",
                        type: 'GET',
                        data: {
                            project_id: ProjectID
                        },
                        dataType: 'json',
                        success: function(response) {
                            response.forEach(function(item) {
                                if ((item.id == SelectedSite)) {
                                    SiteID.append('<option selected value="' + item.id +
                                        '">' + item.site_no + '</option>');
                                } else {
                                    SiteID.append('<option value="' + item.id +
                                        '">' + item.site_no + '</option>');
                                }
                            });
                        },
                        error: function(e, x, settings, exception) {
                            // ajaxErrors(e, x, settings, exception);
                        },
                    });
                }
                SiteID.select2();
            }

            // Event listeners
            $('#project_id').on('change', function() {
                getSites();
            });

            const getLastFollowup = () => {
                let id = $('#customer_id option:selected').val();

                if (!id) {
                    console.warn('No customer selected');
                    return;
                }

                $.ajax({
                    url: "{{ route('followups.getLastFollowup') }}",
                    type: "GET",
                    data: {
                        customer_id: id
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.data) {
                            $('#project_id').val(response.data.project_id).trigger('change');
                            setTimeout(function() {
                                $('#site_id').val(response.data.site_id).trigger('change');
                            }, 300);
                            $('#status').val(response.data.status).trigger('change');
                            $('#last-remark').html(!response.data.remarks ? 'No remarks listed' : response.data.remarks);
                            $('.last-remarks-div').removeClass('d-none');
                        } else {
                            $('.last-remarks-div').addClass('d-none');
                        }
                    },
                    error: function(err) {
                        console.error(err);
                    }
                });
            };

            @if (!$followup)
                $('#customer_id').on('change', function() {
                    getLastFollowup();
                });
            @endif


            // Initialize on page load
            $('#customer_id').select2();
            getProjects();
            getSites();
        });
    </script>
@endsection
