@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Roles and Permissions";
        $ActiveMenuName='Roles-and-Permissions';
    @endphp
<style>
    .page-content {
        width: calc(100% - 17rem);
        margin-left: 17rem;
        transition: all 0.4s;
        padding: 12px!important;
    }
</style>

    <div class="side-app">

        <!-- CONTAINER -->
        <div class="container-fluid">

            <div class="row mt-5">

                <div class="col-md-12">

                    <div class="card">
                        <div class="card-header"><h5 class="text-center">Roles and Permission</h5></div>
                        <form class="form-validate" action="{{ $role ? route('role.update', $role->id) : route('role.store') }}" method="post">
                            @csrf
                            @if($role) @method('PUT') @endif
                            <div class="card-body">
                                <div class="row">
                                    <label for="inputName" class="col-md-2 form-label">Role Name</label>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="val-username" name="name"
                                               value="{{ $role ? old('name', $role->name) : old('name') }}" placeholder="Enter a role name.." required @if($role && in_array($role->name, SYSTEM_ROLES, true)) readonly @endif>
                                        @error('name')
                                        <span class="error invalid-feedback">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <h4 class="mt-25">Assign Permissions</h4>
                                <hr>
                                <div class="row text-center">
                                    <label class="custom-control custom-checkbox" for="check_all">
                                        <input type="checkbox" class="custom-control-input" id="check_all">
                                        <span class="custom-control-label">Check all</span>
                                    </label>
                                </div>
                                <div>
                                    @foreach($permissions as $guardName => $guards)
                                        <div class="mt-10">
                                            <h6><b>{{ snakeCaseToTitleCase($guardName) }}</b></h6>
                                        </div>
                                        <div class="row mt-3">
                                            @foreach($guards as $permission)
                                                <div class="col-md-2">
                                                    <label class="custom-control custom-checkbox" for="{{ $permission->id}}">
                                                        <input type="checkbox" class="custom-control-input" name="permissions[]" id="{{ $permission->id}}"
                                                               value="{{ $permission->id}}" {{ ($role && $role->hasPermissionTo($permission->name)) ? "checked" : '' }}>
                                                        <span class="custom-control-label">{{ snakeCaseToTitleCase(trim(str_replace($permission->model, '', $permission->name))) }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>

                                <div class="row mt-5 justify-content-center">
                                    <div class="col-md-4 d-flex justify-content-center align-items-center">
                                        <a href="{{ route('role.index') }}">
                                            <button type="button" class="btn btn-sm px-5 mb-2 btn-outline-warning mr-4">Cancel</button>
                                        </a>
                                        @if(!$role)
                                            @can('Create Roles and Permissions')
                                                <button type="submit" class="btn btn-sm px-5 mb-2 btn-outline-success">Save</button>
                                            @endcan
                                        @else
                                            @can('Edit Roles and Permissions')
                                                <button type="submit" class="btn btn-sm px-5 mb-2 btn-outline-success">Update</button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <!-- CONTAINER END -->
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            let check_boxes = $('.custom-control-input[name="permissions[]"]');
            let check_all_input = $('#check_all');
            function toggleCheckAll() {
                let allChecked = check_boxes.length === $('.custom-control-input[name="permissions[]"]:checked').length;
                check_all_input.prop('checked', allChecked);
            }

            check_all_input.on('change', function () {
                check_boxes.prop('checked', $(this).prop('checked'));
            });

            check_boxes.on('change', function () {
                toggleCheckAll();
            });

            toggleCheckAll();
        });
    </script>
@endsection
