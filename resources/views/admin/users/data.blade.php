@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Users";
        $ActiveMenuName='Users';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item">{{$PageTitle}}</li>
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
                            <div class="col-sm-4 my-2"><h5>{{ $user  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-sm-12 col-lg-12">
                                <form class="row" action="{{ $user ? route('users.update', $user->id) : route('users.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @if($user) @method('PUT') @endif
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="text-center">
                                            <label class="d-block">Profile Image</label>
                                            <div id="image-dropzone" class="image-box border rounded d-flex align-items-center justify-content-center flex-column text-center"
                                                 style="width: 200px; height: 200px; cursor: pointer; background: #f8f9fa; border: 2px dashed #ccc;">
                                                <i class="fa fa-upload fa-2x text-secondary"></i>
                                                <p class="text-muted m-0">Drag &amp; drop a file here or click</p>
                                                <img id="image-preview" src="" class="img-fluid d-none" style="max-width: 100%; max-height: 100%;" alt="">
                                            </div>
                                            <input type="file" id="image-input" name="image" class="d-none" accept="image/*">
                                        </div>
                                        @error('image')
                                            <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-sm-6 col-lg-6 mt-10">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                               value="{{ $user ? old('name', $user->name) : old('name') }}" required>
                                        @error('name')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-10">
                                        <label>Email Address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                               value="{{ $user ? old('email', $user->email) : old('email') }}" required>
                                        @error('email')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror"
                                               value="{{ $user ? old('dob', $user->dob) : old('dob') }}" max="{{ \Carbon\Carbon::today()->toDateString() }}" required>
                                        @error('dob')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Mobile <span class="text-danger">*</span></label>
                                        <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                               value="{{ $user ? old('mobile', $user->mobile) : old('mobile') }}" required>
                                        @error('mobile')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-12 col-lg-12 mt-15">
                                        <label>Address <span class="text-danger">*</span></label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" required>{{ $user ? old('address', $user->address) : old('address') }}</textarea>
                                        @error('address')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>State <span class="text-danger">*</span></label>
                                        <select name="state_id" id="state" class="form-control select2 @error('state_id') is-invalid @enderror"
                                                data-selected='{{ $user ? old('state_id', $user->state_id) : old('state_id') }}' required>
                                            <option value="">Select a State</option>
                                        </select>
                                        @error('state_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>District <span class="text-danger">*</span></label>
                                        <select name="district_id" id="district" class="form-control select2 @error('district_id') is-invalid @enderror"
                                                data-selected='{{ $user ? old('district_id', $user->district_id) : old('district_id') }}' required>
                                            <option value="">Select a District</option>
                                        </select>
                                        @error('district_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Area <span class="text-danger">*</span></label>
                                        <select name="area_id" id="area" class="form-control select2 @error('area_id') is-invalid @enderror"
                                                data-selected='{{ $user ? old('area_id', $user->area_id) : old('area_id') }}' required>
                                            <option value="">Select a Area</option>
                                        </select>
                                        @error('area_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Pincode <span class="text-danger">*</span></label>
                                        <select name="pincode_id" id="pincode" class="form-control select2 @error('pincode_id') is-invalid @enderror"
                                                data-selected='{{ $user ? old('pincode_id', $user->pincode_id) : old('pincode_id') }}' required>
                                            <option value="">Select a Pincode</option>
                                        </select>
                                        @error('pincode_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Role <span class="text-danger">*</span></label>
                                        <select name="role_id" id="role_id" class="form-control select2 @error('role_id') is-invalid @enderror"
                                                data-selected='{{ $user ? old('role_id', $user->role_id) : old('role_id') }}' required>
                                            <option value="">Select a Role</option>
                                        </select>
                                        @error('role_id')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Password @if(!$user)<span class="text-danger">*</span>@endif</label>
                                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $user ? '' : 'required' }}>
                                        @error('password')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-sm-6 col-lg-6 mt-15">
                                        <label>Active Status <span class="text-danger">*</span></label>
                                        <select name="active_status" class="form-control @error('active_status') is-invalid @enderror" required>
                                            <option value="1" {{ $user && $user->active_status ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $user && !$user->active_status ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('active_status')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>

                                    <div class="row mt-15 text-end">
                                        <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                                            @if(!$user)
                                                @can('Create Users')
                                                    <button type="submit" class="btn btn-primary">Save</button>
                                                @endcan
                                            @else
                                                @can('Edit Users')
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
        $(document).ready(function () {
            @if($user && $user->image)
            $("#image-preview").removeClass("d-none").attr("src", "{{ Storage::url($user->image) }}");
            $("#image-dropzone i, #image-dropzone p").hide();
            @endif

            // ===================== Config =====================

            const ROUTES = {
                states:          "{{ route('getStates') }}",
                districts:       "{{ route('getDistricts') }}",
                areas:           "{{ route('getAreas') }}",
                pincodes:        "{{ route('getPinCodes') }}",
                areaDetails:     "{{ route('getAreaDetails') }}",
                districtDetails: "{{ route('getDistrictDetails') }}",
                roles:           "{{ route('getRoles') }}",
            };

            const LOCATION_CHAIN = ['#state', '#district', '#area', '#pincode'];

            // ===================== Core Helpers =====================

            /**
             * Rebuild a Select2 dropdown with new options.
             */
            const updateSelect2 = (selector, options, selectedValue = null) => {
                const $el = $(selector);
                if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');

                const label  = selector === '#pincode' ? 'pincode' : 'name';
                const blanks = { '#state': 'State', '#district': 'District', '#area': 'Area', '#pincode': 'Pincode' };

                $el.empty().append(`<option value="">Select a ${blanks[selector] ?? 'option'}</option>`);

                options.forEach(item => {
                    const opt = new Option(item[label] ?? item.name, item.id, false, String(item.id) === String(selectedValue));
                    $(opt).data('raw', item);       // stash full record for reverse-cascade
                    $el.append(opt);
                });

                $el.val(selectedValue ?? '').select2();
            };

            /**
             * Clear one or more downstream dropdowns.
             */
            const clearFields = (...selectors) =>
                selectors.forEach(sel => $(sel).removeAttr('data-selected').val('').select2());

            /**
             * Simple $.get wrapped in a Promise.
             */
            const fetchJson = (url, params = {}) => $.get(url, params);

            // ===================== Cascade Loaders =====================

            /**
             * Each loader reads its own `data-selected`, fetches fresh options,
             * then automatically walks down the chain if a pre-selection exists.
             */
            const loaders = {
                states: async () => {
                    const selected = $('#state').data('selected');
                    const states   = await fetchJson(ROUTES.states);
                    updateSelect2('#state', states, selected);
                    if (selected) await loaders.districts();
                },

                districts: async () => {
                    const stateId  = $('#state').val();
                    const selected = $('#district').data('selected');

                    if (!stateId) { updateSelect2('#district', []); return; }

                    const districts = await fetchJson(ROUTES.districts, { state_id: stateId });
                    updateSelect2('#district', districts, selected);
                    if (selected) await loaders.areas();
                },

                areas: async () => {
                    const districtId = $('#district').val();
                    const selected   = $('#area').data('selected');

                    if (!districtId) { updateSelect2('#area', []); return; }

                    const areas = await fetchJson(ROUTES.areas, { district_id: districtId });
                    updateSelect2('#area', areas, selected);
                    if (selected) {
                        $('#pincode').removeAttr('data-selected');
                        await loaders.pincodes();
                    }
                },

                pincodes: async () => {
                    const areaId   = $('#area').val();
                    const selected = $('#pincode').data('selected');

                    if (!areaId) { updateSelect2('#pincode', []); return; }

                    const pincodes = await fetchJson(ROUTES.pincodes, { area_id: areaId });
                    updateSelect2('#pincode', pincodes, selected);

                    // Auto-select first pincode when none is pre-selected
                    if (!selected) {
                        const first = $(`#pincode option:eq(1)`);
                        if (first.length) $('#pincode').val(first.val()).select2();
                    }
                },
            };

            // ===================== Forward Cascade (State → Pincode) =====================

            // When user picks a new value, clear everything downstream then re-load
            const forwardHandlers = {
                '#state':    () => { clearFields('#district', '#area', '#pincode'); loaders.districts(); },
                '#district': () => { clearFields('#area', '#pincode');              loaders.areas();     },
                '#area':     () => { clearFields('#pincode');                       loaders.pincodes();  },
            };

            Object.entries(forwardHandlers).forEach(([sel, handler]) =>
                $(sel).on('change', handler)
            );

            // ===================== Reverse Cascade (Pincode → State) =====================

            // When the user picks a pincode directly, walk UP the chain to resolve
            // state/district/area, then populate all four dropdowns at once.
            $('#pincode').on('change', async function () {
                const pincodeId = $(this).val();
                if (!pincodeId) return;

                try {
                    // Step 1 – resolve area from pincode
                    const pincodes = await fetchJson(ROUTES.pincodes, { pincode_id: pincodeId });
                    const areaId   = pincodes[0]?.area_id;
                    if (!areaId) return;

                    // Step 2 – resolve district from area
                    const area       = await fetchJson(ROUTES.areaDetails,     { area_id: areaId });
                    const districtId = area?.district_id;
                    if (!districtId) return;

                    // Step 3 – resolve state from district
                    const district = await fetchJson(ROUTES.districtDetails, { district_id: districtId });
                    const stateId  = district?.state_id;
                    if (!stateId) return;

                    // Step 4 – fetch all four levels in parallel
                    const [states, districts, areas, allPincodes] = await Promise.all([
                        fetchJson(ROUTES.states),
                        fetchJson(ROUTES.districts, { state_id:    stateId    }),
                        fetchJson(ROUTES.areas,     { district_id: districtId }),
                        fetchJson(ROUTES.pincodes,  { area_id:     areaId     }),
                    ]);

                    // Temporarily suppress forward-cascade change events while we update
                    Object.keys(forwardHandlers).forEach(sel => $(sel).off('change'));

                    updateSelect2('#state',    states,       stateId);
                    updateSelect2('#district', districts,    districtId);
                    updateSelect2('#area',     areas,        areaId);
                    updateSelect2('#pincode',  allPincodes,  pincodeId);

                    // Re-attach forward-cascade handlers
                    Object.entries(forwardHandlers).forEach(([sel, handler]) =>
                        $(sel).on('change', handler)
                    );

                } catch (e) {
                    console.error('Reverse cascade failed:', e);
                }
            });

            // ===================== Pincode Live Search =====================

            let pincodeSearchActive = false;

            $('#pincode')
                .on('select2:open', function () {
                    if (pincodeSearchActive) return;
                    pincodeSearchActive = true;

                    const $search = $('.select2-container--open .select2-search__field');
                    $search.off('input.pincodeSearch').on('input.pincodeSearch', async function () {
                        const query = $(this).val().trim();
                        if (!query) return;

                        const results = await fetchJson(ROUTES.pincodes, { pincode: query });
                        updateSelect2('#pincode', results.data ?? results);
                        $('#pincode').select2('open');
                    });
                })
                .on('select2:close', () => { pincodeSearchActive = false; });

            // ===================== Roles =====================

            const getRoles = async () => {
                const selected = $('#role_id').data('selected');
                const roles    = await fetchJson(ROUTES.roles);
                updateSelect2('#role_id', roles, selected);
            };

            // ===================== Initialise =====================

            loaders.states();
            getRoles();
        });
    </script>
@endsection
