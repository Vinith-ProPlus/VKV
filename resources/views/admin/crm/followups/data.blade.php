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
                                                    {{ ($followup && old('customer_id', $followup->customer_id) == $customer->id) || (isset($selectedLeadId) && $selectedLeadId == $customer->id) ? 'selected' : '' }}>
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
                                        @error('remarks')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Followup History Grid -->
                                    <div class="col-sm-12 col-lg-12 mt-15">
                                        <div class="followup-history-div d-none">
                                            <div class="text-center">
                                                <h4>Recent Followup</h4>
                                            </div>
                                            <div class="followup-grid-container" id="followup-grid">
                                                <!-- Followup cards will be inserted here -->
                                            </div>
                                        </div>
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

@section('style')
    <style>
        .followup-grid-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 10px 0;
        }

        .followup-card {
            background: white;
            border-left: 4px solid #007bff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
            padding: 0;
        }

        .followup-card::before {
            display: none;
        }

        .followup-card.status-info {
            border-left-color: #17a2b8;
        }

        .followup-card.status-success {
            border-left-color: #28a745;
        }

        .followup-card.status-dark {
            border-left-color: #343a40;
        }

        .followup-card.status-secondary {
            border-left-color: #6c757d;
        }

        .followup-card.latest {
            border-left: 5px solid #28a745;
            background: #f8fff9;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.15);
        }

        .followup-card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transform: translateX(4px);
        }

        .followup-counter {
            display: none;
        }

        .followup-card-header {
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .followup-card-title {
            font-size: 15px;
            font-weight: 600;
            color: #343a40;
            margin: 0;
            flex: 1;
        }

        .followup-card-date {
            font-size: 12px;
            color: #6c757d;
            margin: 4px 0 0 0;
        }

        .followup-card-body {
            padding: 14px 16px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .followup-info-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f0f0f0;
        }

        .followup-info-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .followup-info-label {
            font-size: 11px;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }

        .followup-info-value {
            font-size: 14px;
            color: #343a40;
            word-break: break-word;
        }

        .followup-status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            color: white;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-dark {
            background-color: #343a40;
        }

        .remarks-text {
            color: #495057;
            line-height: 1.5;
            font-style: italic;
            max-height: 100px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .followup-card-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 12px;
            }

            .followup-card-body {
                padding: 12px;
            }
        }
    </style>
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
                            
                            // Wait for sites to load, then update display
                            setTimeout(function() {
                                $('#site_id').val(response.data.site_id).trigger('change');
                                
                                // Wait a bit more for sites to populate before reading values
                                setTimeout(function() {
                                    $('#status').val(response.data.status).trigger('change');
                                    
                                    // Now fetch all followups for timeline
                                    getAllFollowups(id);
                                }, 500);
                            }, 300);
                        } else {
                            $('.followup-history-div').addClass('d-none');
                        }
                    },
                    error: function(err) {
                        console.error(err);
                    }
                });
            };

            const getAllFollowups = (customerId) => {
                $.ajax({
                    url: "{{ route('followups.getAllFollowups') }}",
                    type: "GET",
                    data: {
                        customer_id: customerId
                    },
                    success: function(response) {
                        console.log('Most recent followup:', response);
                        if (response.data) {
                            let followup = response.data;
                            let date = new Date(followup.created_at);
                            let formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
                            let statusClass = getStatusClass(followup.status);
                            let statusText = followup.status.charAt(0).toUpperCase() + followup.status.slice(1);
                            
                            let cardsHTML = `
                                <div class="followup-card status-${statusClass} latest">
                                    <div class="followup-card-header">
                                        <div>
                                            <h6 class="followup-card-title">${followup.project_name || 'N/A'}</h6>
                                            <p class="followup-card-date">${formattedDate}</p>
                                        </div>
                                        <span class="followup-status-badge badge-${statusClass}">${statusText}</span>
                                    </div>
                                    <div class="followup-card-body">
                                        <div class="followup-info-item">
                                            <span class="followup-info-label">Site</span>
                                            <div class="followup-info-value">${followup.site_no || 'Not Specified'}</div>
                                        </div>
                                        <div class="followup-info-item">
                                            <span class="followup-info-label">Remarks</span>
                                            <div class="followup-info-value remarks-text">${followup.remarks || 'No remarks'}</div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            $('#followup-grid').html(cardsHTML);
                            $('.followup-history-div').removeClass('d-none');
                        } else {
                            $('.followup-history-div').addClass('d-none');
                        }
                    },
                    error: function(err) {
                        console.error('Error fetching followups:', err);
                    }
                });
            };

            const getStatusClass = (status) => {
                const statusMap = {
                    'new': 'primary',
                    'under followup': 'info',
                    'visited': 'secondary',
                    'booked': 'success',
                    'sold': 'success',
                    'closed': 'dark'
                };
                return statusMap[status] || 'secondary';
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

            // Auto-select lead if selectedLeadId is provided
            @if(isset($selectedLeadId) && $selectedLeadId)
                const selectedLeadId = {{ $selectedLeadId }};
                if (selectedLeadId) {
                    $('#customer_id').val(selectedLeadId).trigger('change');
                    setTimeout(function() {
                        getLastFollowup();
                    }, 100);
                }
            @endif
        });
    </script>
@endsection
