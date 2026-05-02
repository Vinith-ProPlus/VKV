@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = 'Visitor';
        $ActiveMenuName = 'Visitor';
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
                                <h5>{{ $visitor ? 'Edit' : 'Create' }} {{ $PageTitle }}</h5>
                            </div>
                            <div class="col-sm-4 my-2 text-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form class="row"
                                    action="{{ $visitor ? route('visitors.update', $visitor->id) : route('visitors.store') }}"
                                    method="POST">
                                    @csrf
                                    @if ($visitor)
                                        @method('PUT')
                                    @endif

                                    <!-- Lead Dropdown -->
                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Lead <span class="text-danger">*</span></label>
                                        <select name="customer_id" id="customer_id"
                                            class="form-control select2 @error('customer_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select or type mobile number</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    {{ $visitor && old('customer_id', $visitor->customer_id) == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }} - {{ $customer->mobile_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15 d-none" id="newCustomerNameWrapper">
                                        <label>Visitor Name <span class="text-danger">*</span></label>
                                        <input type="text" name="new_customer_name" id="new_customer_name"
                                            class="form-control">
                                        <input type="hidden" name="new_customer_flag" id="new_customer_flag" value="false">
                                    </div>


                                    <!-- Project Dropdown -->
                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Project <span class="text-danger">*</span></label>
                                        <select name="project_id" id="project_id"
                                            class="form-control select2 @error('project_id') is-invalid @enderror"
                                            data-selected='{{ $visitor ? $visitor?->project_id : old('project_id') }}'
                                            required>
                                            <option value="">Select a Project</option>
                                            @foreach ($projects as $data)
                                                <option value="{{ $data->id }}"
                                                    {{ $visitor && $visitor?->project_id == $data->id ? 'selected' : '' }}>
                                                    {{ $data->name }}
                                                </option>
                                            @endforeach

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
                                            data-selected='{{ $visitor ? $visitor?->site_id : old('site_id') }}'>
                                            <option value="">Select a Site (Optional)</option>
                                            @foreach ($sites as $data)
                                                <option value="{{ $data->id }}"
                                                    {{ $visitor && $visitor?->site_id == $data->id ? 'selected' : '' }}>
                                                    {{ $data->site_no }}</option>
                                            @endforeach
                                        </select>
                                        @error('site_id')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status Dropdown -->
                                    <div class="form-group col-sm-6 col-lg-6 mt-15" style="display: none;">
                                        <label>Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status"
                                            class="form-control select2 @error('status') is-invalid @enderror" required>
                                            <option value="">Select a Status</option>
                                            <option value="visited" selected>Visited</option>
                                        </select>
                                        @error('status')
                                            <span class="error invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Remarks Text Area -->
                                    <div class="form-group col-sm-12 col-lg-12 mt-15">
                                        <label>Remarks</label>
                                        <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" rows="4"
                                            placeholder="Enter remarks here...">{{ $visitor ? old('remarks', $visitor->remarks) : old('remarks') }}</textarea>
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
                                            @if (!$visitor)
                                                @can('Create Visitors')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Visitors')
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
            $('.select2').select2();

            // Get projects
            // const getProjects = () => {
            //     let ProjectID = $('#project_id');
            //     let SelectedProject = ProjectID.attr('data-selected');
            //     ProjectID.select2('destroy');
            //     $('#project_id option').remove();
            //     ProjectID.append('<option value="">Select a Project</option>');

            //     $.ajax({
            //         url: "{{ route('getProjects') }}",
            //         type: 'GET',
            //         dataType: 'json',
            //         success: function(response) {
            //             response.forEach(function(item) {
            //                 if ((item.id == SelectedProject)) {
            //                     ProjectID.append('<option selected value="' + item.id +
            //                         '">' + item.name + '</option>');
            //                 } else {
            //                     ProjectID.append('<option value="' + item.id +
            //                         '">' + item.name + '</option>');
            //                 }
            //             });
            //         },
            //         error: function(e, x, settings, exception) {
            //             // ajaxErrors(e, x, settings, exception);
            //         },
            //     });
            //     ProjectID.select2();
            // }


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

            $('#site_id').on('change', function() {
                $('#project_id').select2();
            });

            // Event listeners
            $('#project_id').on('change', function() {
                getSites();
            });

            // Initialize on page load

            @if (!$visitor)
                // Create mode - allow adding new customers
                $('#customer_id').select2({
                    tags: true,
                    placeholder: "Select or type mobile number",
                    createTag: function(params) {
                        let term = $.trim(params.term);

                        
                        // Only allow numbers (mobile vibes 📱)
                        if (!/^\d+$/.test(term)) {
                            return null;
                        }

                        return {
                            id: term,
                            text: term + ' (New)',
                            newOption: true
                        };
                    },
                    templateSelection: function(data) {
                        return data.text;
                    }
                });

                $('#customer_id').on('select2:select', function(e) {
                    let data = e.params.data;

                    if (data.newOption) {
                        // New mobile number → show name field
                        $('#newCustomerNameWrapper').removeClass('d-none');
                        $('#new_customer_name').prop('required', true);
                        $('#new_customer_flag').val('true');
                    } else {
                        // Existing customer → hide name field
                        $('#newCustomerNameWrapper').addClass('d-none');
                        $('#new_customer_name').val('').prop('required', false);
                        $('#new_customer_flag').val('false');
                    }
                });
            @else
                // Edit mode - only existing customers
                $('#customer_id').select2({
                    placeholder: "Select a Lead",
                    tags: false
                });
            @endif

            $('#project_id').select2();
            $('#site_id').select2();
            getSites();
        });
    </script>
@endsection
