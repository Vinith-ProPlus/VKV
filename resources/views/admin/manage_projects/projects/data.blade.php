@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Project";
        $ActiveMenuName = 'Projects';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Master</li>
                        <li class="breadcrumb-item">{{$PageTitle}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>{{ $project ? 'Edit' : 'Create' }} {{$PageTitle}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="nav nav-tabs wizard-head" id="projectTabs">
                            <a class="nav-link active" href="#project-details" data-tab="project-details"
                               data-name="tab-project-details" data-bs-toggle="tab">
                                <span class="nav-contents">
                                    <i class="bi bi-1-square-fill active"></i>
                                    <span class="nav-title">Details</span>
                                </span>
                            </a>
                            <a class="nav-link" href="#project-contracts" data-tab="project-contracts"
                            data-name="tab-project-contracts" data-bs-toggle="tab">
                            <span class="nav-contents">
                                <i class="bi bi-2-square-fill"></i>
                                <span class="nav-title">Contracts</span>
                            </span>
                            </a>
                            <a class="nav-link" href="#project-stages" data-tab="project-stages"
                            data-name="tab-project-stages" data-bs-toggle="tab">
                                <span class="nav-contents">
                                    <i class="bi bi-3-square-fill"></i>
                                    <span class="nav-title">Stages</span>
                                </span>
                            </a>
                            <a class="nav-link" href="#project-amenities" data-tab="project-amenities"
                            data-name="tab-project-amenities" data-bs-toggle="tab">
                                <span class="nav-contents">
                                    <i class="bi bi-4-square-fill"></i>
                                    <span class="nav-title">Amenities</span>
                                </span>
                            </a>
                            <a class="nav-link" href="#project-documents" data-tab="project-documents"
                               data-name="tab-project-documents" data-bs-toggle="tab">
                                <span class="nav-contents">
                                    <i class="bi bi-5-square-fill"></i>
                                    <span class="nav-title">Documents</span>
                                </span>
                            </a>
                        </div>

                        <form id="project-form"
                              action="{{ $project ? route('projects.update', $project->id) : route('projects.store') }}"
                              method="POST">
                            @csrf
                            @if($project)
                                @method('PUT')
                            @endif
                            <div class="tab-content mt-3">
                                <!-- Project Details Tab -->
                                <div class="tab-pane fade show active" id="project-details">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="site_id">Site Name <span
                                                        class="text-danger">*</span></label>
                                                <select name="site_id" id="site_id" class="form-control select2"
                                                        data-selected="{{ old('site_id', $project->site_id ?? '') }}"
                                                        required></select>
                                                @error('site_id')
                                                <span class="text-danger mt-1">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="engineer_id">Engineer Name <span
                                                        class="text-danger">*</span></label>
                                                <select name="engineer_id" id="engineer_id" class="form-control select2"
                                                        data-selected="{{ old('engineer_id', $project->engineer_id ?? '') }}"
                                                        required></select>
                                                @error('engineer_id')
                                                <span class="text-danger mt-1">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-10">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Project ID <span class="text-danger">*</span></label>
                                                <input type="text" name="project_id" class="form-control"
                                                       value="{{ old('project_id', $project->project_id ?? '') }}"
                                                       required>
                                                @error('project_id')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Project Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control"
                                                       value="{{ old('name', $project->name ?? '') }}" required>
                                                @error('name')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-10">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Location <span class="text-danger">*</span></label>
                                                <input type="text" name="location" class="form-control"
                                                       value="{{ old('location', $project->location ?? '') }}" required>
                                                @error('location')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Type <span class="text-danger">*</span></label>
                                                <input type="text" name="type" class="form-control"
                                                       value="{{ old('type', $project->type ?? '') }}" required>
                                                @error('type')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-10">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Units <span class="text-danger">*</span></label>
                                                <input type="number" name="units" class="form-control"
                                                       value="{{ old('units', $project->units ?? '') }}" required>
                                                @error('units')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Target Customers <span class="text-danger">*</span></label>
                                                <input type="text" name="target_customers" class="form-control"
                                                       value="{{ old('target_customers', $project->target_customers ?? '') }}"
                                                       required>
                                                @error('target_customers')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-10">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Range <span class="text-danger">*</span></label>
                                                <input type="text" name="range" class="form-control"
                                                       value="{{ old('range', $project->range ?? '') }}" required>
                                                @error('range')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Area in Sq.ft <span class="text-danger">*</span></label>
                                                <input type="number" name="area_sqft" class="form-control"
                                                       value="{{ old('area_sqft', $project->area_sqft ?? '') }}" required>
                                                @error('area_sqft')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-10">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label>Status <span class="text-danger">*</span></label>
                                                <select name="status" class="form-control" id="status" required>
                                                    <option value="">Select a Status</option>
                                                    @foreach(PROJECT_STATUSES as $status)
                                                        <option value="{{ $status }}" {{ $status == ($project ? old('status', $project->status) : old('status')) ? 'selected' : '' }}>{{ $status }}</option>
                                                    @endforeach
                                                </select>
                                                @error('status')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-6 d-none" id="sold_amount_div">
                                            <div class="form-group">
                                                <label>Sold Price <span class="text-danger">*</span></label>
                                                <input type="number" id="sold_amount" class="form-control" value="{{ old('sold_amount', $project->sold_amount ?? '') }}">
                                                @error('sold_amount')
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-40 text-end">
                                        <div class="col-6 text-start">
                                            <a class="btn btn-light">Back</a>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="btn btn-primary btn-next">Next</a>
                                        </div>
                                        {{-- <div>
                                            <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                            <button type="submit" class="btn btn-primary">{{ $project ? 'Update' : 'Save' }}</button>
                                        </div> --}}
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="project-contracts">
                                    <div class="card">
                                        <div class="row" style="background-color: #7167f430;padding: 20px;border-radius: 15px;box-shadow: 1px 10px 40px #e4e2fde3;">
                                            <div class="col-4">
                                                <label for="contract_type_id"><strong>Contracts</strong></label>
                                                <select  class="form-control select2" id="contract_type_id">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <label for="contractor_id"><strong>Contractors</strong></label>
                                                <select  class="form-control select2" id="contractor_id">
                                                    <option value="">Select a Contractor</option>
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <label for="textAmount"><strong>Amount</strong></label>
                                                <input class="form-control" id="txtAmount" type="number">
                                            </div>
                                            <div class="col-2 align-self-end">
                                                <a class="btn btn-sm" id="addContracts" style="background-color: #7167f4;color: #fff;">Add</a>
                                                <a class="btn btn-sm btn-warning d-none" id="updateContracts">Update</a>
                                                <a class="btn btn-sm mx-2 btn-danger" id="clearContracts">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-hover {{ (is_object($project) && count($project->contracts) > 0) ? '' : 'd-none' }} mt-20 form-group">
                                    <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Contract Types</th>
                                                <th>Contractors</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblContract">
                                            @php
                                                $serialNum = 1;
                                            @endphp
                                            @if ($project)
                                                @foreach ($project->contracts as $contract)
                                                <tr data-new="false">
                                                    <td>{{$serialNum++}}</td>
                                                    <td data-contract-type="{{ $contract->contract_type->name }}">
                                                            {{ $contract->contract_type->name }}
                                                            <input class="d-none" value="{{ $contract->contract_type_id }}" name="contracts[{{ $contract->id }}][contract_type_id]" data-id="{{ $contract->id }}">
                                                        </td>
                                                        <td data-contractor="{{ $contract->user->name }}">
                                                            {{ $contract->user->name }}
                                                            <input class="d-none" value="{{ $contract->user_id }}" name="contracts[{{ $contract->id }}][user_id]" data-id="{{ $contract->id }}">
                                                        </td>
                                                        <td data-amount="{{ $contract->amount }}">
                                                            {{ $contract->amount }}
                                                            <input class="d-none" value="{{ $contract->amount }}" name="contracts[{{ $contract->id }}][amount]" data-id="{{ $contract->id }}">
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-outline-primary editContracts"><i class="fa fa-pencil"></i></a>
                                                        </td>
                                                        <td data-tdata="{{ json_encode($contract) }}" class="d-none">{{ json_encode($contract) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="row mt-40 text-end">
                                        <div class="col-6 text-start">
                                            <a class="btn btn-light">Back</a>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="btn btn-outline-light btn-prev">Previous</a>
                                            <a class="btn btn-primary btn-next">Next</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="project-stages">
                                    <!-- Stages Management -->
                                    <div class="row col-12 mt-10 card form-group">
                                        <label>Project Stages</label>
                                        <div class="input-group">
                                            <input type="text" id="stage-name" class="form-control"
                                                   placeholder="Enter stage name">
                                            <button type="button" class="btn btn-primary" id="add-stage-btn">+</button>
                                        </div>
                                        @error('stages')
                                        <div class="err text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                        <ul id="stage-list" class="list-group mt-2 ml-10">
                                            @php
                                                $stages = old('stages', $project ? $project->stages()->withTrashed()->orderBy('order_no')->get()->toArray() : []);
                                            @endphp

                                            @foreach($stages as $index => $stage)
                                                @php
                                                    $isDeleted = $stage['deleted'] ?? (!empty($stage['deleted_at']) ? 1 : 0);
                                                    $stageId = $stage['id'] ?? '';
                                                    $stageName = $stage['name'] ?? '';
                                                    $stageNo = $stage['order_no'] ?? '';
                                                @endphp

                                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                                    data-id="{{ $stageId }}">
                                                    <span class="stage-name">{{ $stageName }}</span>
                                                    <div>
                                                        <button type="button" class="btn btn-sm btn-warning edit-stage">
                                                            Edit
                                                        </button>
                                                        <button type="button"
                                                                class="btn btn-sm {{ $isDeleted ? 'btn-success restore-stage' : 'btn-danger delete-stage' }}">
                                                            {{ $isDeleted ? 'Restore' : 'Delete' }}
                                                        </button>
                                                        <input type="hidden" name="stages[{{ $index }}][id]"
                                                               value="{{ $stageId }}">
                                                        <input type="hidden" name="stages[{{ $index }}][name]"
                                                               value="{{ $stageName }}">
                                                        <input type="hidden" name="stages[{{ $index }}][order_no]"
                                                               class="order-no" value="{{ $stageNo }}">
                                                        <input type="hidden" name="stages[{{ $index }}][deleted]"
                                                               value="{{ $isDeleted }}">
                                                    </div>
                                                </li>
                                                <!-- Individual Stage Error Display -->
                                                @error("stages.{$index}.name")
                                                <div class="err text-danger mt-1">{{ $message }}</div>
                                                @enderror
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="row mt-40 text-end">
                                        <div class="col-6 text-start">
                                            <a class="btn btn-light">Back</a>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="btn btn-outline-light btn-prev">Previous</a>
                                            <a class="btn btn-primary btn-next">Next</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="project-amenities">
                                    <div class="card" style="box-shadow: none;">
                                        <div class="row" style="background-color: #7167f430;padding: 20px;border-radius: 15px;box-shadow: 1px 10px 40px #e4e2fde3; width:90%; margin:0px auto;">
                                            <div class="col-4">
                                                <label for="amenity_id"><strong>Amenities</strong></label>
                                                <select  class="form-control select2" id="amenity_id">
                                                    <option value="">Select</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label for="amenty_description"><strong>Description</strong></label>
                                                <textarea class="form-control" id="amenty_description" cols="10" rows="1"></textarea>
                                            </div>
                                            <div class="col-2 align-self-end">
                                                <a class="btn" id="addAmenities" style="background-color: #7167f4;color: #fff;">Add</a>
                                                <a class="btn btn-warning d-none" id="updateAmenities">Update</a>
                                                <a class="btn mx-2 btn-danger" id="clearAmenities">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-hover {{ (is_object($project) && count($project->amenities) > 0) ? '' : 'd-none' }} mt-20 form-group">
                                        <thead>
                                            <tr>
                                                <th>Sno</th>
                                                <th>Amenities</th>
                                                <th>Description</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tblAmenity">
                                            @php
                                                $Sa_Num = 1;
                                            @endphp
                                            @if ($project)
                                                @foreach ($project->amenities as $amenity)
                                                <tr data-new="false">
                                                    <td>{{$Sa_Num++}}</td>
                                                    <td data-amenity="{{ $amenity->amenity->name }}">
                                                            {{ $amenity->amenity->name }}
                                                            <input class="d-none" value="{{ $amenity->amenity_id }}" name="amenities[{{ $amenity->id }}][amenity_id]" data-id="{{ $amenity->id }}">
                                                        </td>
                                                        <td data-description="{{ $amenity->description }}">
                                                            {{ $amenity->description }}
                                                            <input class="d-none" value="{{ $amenity->description }}" name="amenities[{{ $amenity->id }}][description]" data-id="{{ $amenity->id }}">
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-outline-primary editAmenities"><i class="fa fa-pencil"></i></a>
                                                            <a class="btn btn-danger deleteAmenities"><i class="fa fa-trash"></i></a>
                                                        </td>
                                                        <td data-tdata="{{ json_encode($amenity) }}" class="d-none">{{ json_encode($amenity) }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <input type="text" class="d-none" name="deletedAmenities" id="deletedAmenities">
                                    <div class="row mt-40 text-end">
                                        <div class="col-6 text-start">
                                            <a class="btn btn-light">Back</a>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="btn btn-outline-light btn-prev">Previous</a>
                                            <a class="btn btn-primary btn-next">Next</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Project Documents Tab -->
                                <div class="tab-pane fade" id="project-documents">
                                    <div class="text-center mb-10">
                                        <a class="btn btn-outline-primary btnAddDocs" type="button"
                                           data-bs-toggle="modal" data-bs-target=".mdlDocument">
                                            Add Documents
                                        </a>
                                    </div>
                                    <table class="table table-responsive table-hover mb-30 d-none">
                                        <thead class="bg-light">
                                        <tr>
                                            <th>S.No</th>
                                            <th>Title</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody id="tblProjectDocuments"></tbody>
                                    </table>

                                    <div class="modal fade mdlDocument" tabindex="-1"
                                         aria-labelledby="myExtraLargeModal" aria-hidden="true">
                                        <div class="modal-dialog modal-md">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title" id="myExtraLargeModal">Document Details</h4>
                                                    <button id="btnCloseDocumentModal" class="btn-close py-0"
                                                            type="button" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body dark-modal">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <label for="txtTitle">Title <span class="required">*</span></label>
                                                            <input type="text" id="txtTitle" class="form-control">
                                                            <span class="errors err-sm" id="txtTitle-err"></span>
                                                        </div>
                                                        <div class="col-12 mt-20">
                                                            <label for="txtDescription">Description</label>
                                                            <textarea id="txtDescription" cols="30"
                                                                      class="form-control"></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-12 mt-20 divDocument" id="divDocument">
                                                            <label for="multipleImageDocument">Documents <span
                                                                    class="required">*</span></label>
                                                            <input type="file" class="dropify multipleImageDocument"
                                                                   id="multipleImageDocument"
                                                                   data-allowed-file-extensions="png jpg jpeg gif pdf doc docx xls xlsx txt"
                                                                   data-height="100" readonly>
                                                            <div class="filterInfos d-none">
                                                                <div class="upload-status mb-2"
                                                                     id="divDocumentCount"></div>
                                                                <div class="row justify-content-between filters">
                                                                    <div class="col-6">
                                                                        <button class="filter-btn active"
                                                                                data-filter="all">All Files
                                                                        </button>
                                                                        <button class="filter-btn" data-filter="image">
                                                                            Images
                                                                        </button>
                                                                        <button class="filter-btn"
                                                                                data-filter="document">Documents
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span class="errors err-sm" id="txtDocumentFile-err"></span>
                                                            <div
                                                                class="preview-container document-preview-container"></div>
                                                        </div>
                                                    </div>

                                                    <div class="row justify-content-end mt-20">
                                                        <div class="col-auto text-end">
                                                            <button type="button" class="btn btn-warning d-none"
                                                                    id="btnUpdateDocument">Update
                                                            </button>
                                                            <button type="button" class="btn btn-primary"
                                                                    id="btnSaveDocument">Save
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-40 text-end">
                                        <div class="col-6 text-start">
                                            <a class="btn btn-light">Back</a>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="btn btn-outline-light btn-prev">Previous</a>
                                            <a class="btn btn-primary" onclick="$('#project-form').submit()">Submit</a>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- End Tab Content -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            let tableEditId = 1;
            let deletedDocuments = [];
            let selectedContractRow = null;
            let contractCellId = 0;
            let contractUpdateId = 0;
            let selectedAmenityRow = null;
            let amenityCellId = 0;
            let amenityUpdateId = 0;
            let amenityDeletedId = [];
            $('#status').select2();
            //-------------------start of wizard tab toggle function

            function validateRequiredFields($container) {
                var isValid = true;
                $container.find('input[required], select[required], textarea[required]').each(function() {
                    var $field = $(this);
                    var value = $.trim($field.val());
                    var $errorSpan = $field.closest('.form-group').find('.err');
                    if (!value) {
                        isValid = false;
                        if ($errorSpan.length) {
                            $errorSpan.text('This field is required.');
                        } else {
                            $field.closest('.form-group').append('<span class="err text-danger mt-1">This field is required.</span>');
                        }
                    } else {
                        $errorSpan.remove();
                    }
                });
                return isValid;
            }

            $('.btn-prev').on('click', function (e) {
                e.preventDefault();
                var $currentTab = $(this).closest('.tab-pane');
                if (validateRequiredFields($currentTab)) {
                    changePage(0, $currentTab.attr('id'));
                }
            });

            $('.btn-next').on('click', function (e) {
                e.preventDefault();
                var $currentTab = $(this).closest('.tab-pane');
                if (validateRequiredFields($currentTab)) {
                    changePage(1, $currentTab.attr('id'));
                }
            });

            const changePage = (flag, id) => {
                let tab = $(`a[data-name='tab-${id}']`);
                if (flag) {
                    let nextTab = tab.next();
                    if (nextTab.length) {
                        tab.removeClass('active');
                        nextTab.addClass('active');
                        $(`.tab-pane[id='${tab.attr("data-tab")}']`).removeClass('show active');
                        $(`.tab-pane[id='${nextTab.attr("data-tab")}']`).addClass('show active');
                    }
                } else {
                    let prevTab = tab.prev();
                    if (prevTab.length) {
                        tab.removeClass('active');
                        prevTab.addClass('active');
                        $(`.tab-pane[id='${tab.attr("data-tab")}']`).removeClass('show active');
                        $(`.tab-pane[id='${prevTab.attr("data-tab")}']`).addClass('show active');
                    }
                }
            }

            //-------------------------------end of wizard tab toggle function

            //-------------------------------Document image handler

            function clearDropify(inputSelector) {
                let dropifyInstance = $(inputSelector).dropify();
                dropifyInstance = dropifyInstance.data('dropify');
                if (dropifyInstance) {
                    dropifyInstance.clearElement();
                }
            }

            $('.multipleImageDocument').on('input', function (event) {
                let title = $('#txtTitle').val();
                let description = $('#txtDescription').val();
                if (title) {
                    let files = event.target.files;
                    if (files.length === 0) {
                        return;
                    }
                    let formData = new FormData();
                    $.each(files, function (i, file) {
                        formData.append('images[]', file);
                    });
                    formData.append('title', title);
                    formData.append('description', description);
                    formData.append('module_name', "{{ ($project && $project->id) ? 'Project' : 'User-Project' }}");
                    formData.append('module_id', "{{ ($project && $project->id) ? $project?->id : Auth::id() }}");
                    let csrfToken = $('meta[name="_token"]').attr('content');
                    formData.append('_token', csrfToken);
                    $.ajax({
                        url: "{{ route('documentHandler') }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        success: function (response) {
                            if (response.success) {
                                updateDocumentPreview(response);
                            } else {
                                handleUploadError(response);
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Upload failed';
                            try {
                                let errorResponse = JSON.parse(xhr.responseText);
                                errorMessage = errorResponse.message || errorMessage;
                            } catch (e) {
                                errorMessage = xhr.statusText || errorMessage;
                            }
                            console.error("Upload error:", errorMessage);
                        }
                    });
                    $('#txtTitle-err').text('');
                    clearDropify('#multipleImageDocument');
                } else {
                    $('#txtTitle-err').text('Title is required !');
                }
                $('#multipleImageDocument').closest('.dropify-wrapper').find('.dropify-preview').addClass('d-none');
            });

            function handleUploadError(response) {
                let errorMessage = 'Upload failed';
                if (response.errors) {
                    let errorList = Object.values(response.errors).flat();
                    errorMessage = errorList.join('\n');
                } else if (response.message) {
                    errorMessage = response.message;
                }
                console.log(errorMessage);
            }

            function updateDocumentPreview(response) {
                if (response.files && response.files.length > 0) {
                    let previewContainer = $('.preview-container');
                    let filePath;
                    const allowedImageExtensions = ["jpeg", "jpg", "png", "webp"];

                    response.files.forEach(function (file) {
                        filePath = file.path;
                        let extension = filePath.split('.').pop().toLowerCase();
                        let docImg = filePath;
                        if (!allowedImageExtensions.includes(extension)) {
                            docImg = "/storage/essentials/doc.png";
                        }
                        let previewHtml = `<div class="col-md-3 col-6 img-hover preview-item light-card hover-1" data-url="${file.path}" data-ext="jpeg" data-filename="${file.name}">
                                                <button data-id="${file.id}" class="doc-rem remove-file btnDeleteDocumentFile">x</button>
                                                <a href="${file.path}" data-lightbox="image-gallery" data-title="${file.name}" class="gallery-item">
                                                    <div class="gallery-img-wrap">
                                                        <img src="${docImg}" class="preview-image" data-title="${file.name}" alt="${file.name}">
                                                    </div>
                                                </a>
                                                <div class="file-name">${file.name}</div>
                                                <div class="text-center">
                                                    <a href="${file.path}" download="${file.name}" class="btn btn-sm btn-primary mt-2">Download</a>
                                                </div>
                                            </div>`;
                        previewContainer.append(previewHtml);
                    });
                }
            }

            $(document).on('click', '.remove-file', function (e) {
                e.preventDefault();
                let $button = $(this);
                let id = $button.attr('data-id');
                let csrfToken = $('meta[name="_token"]').attr('content');
                $.ajax({
                    url: "{{ route('deleteDocuments') }}",
                    type: "DELETE",
                    data: JSON.stringify({id: [id]}),
                    contentType: "application/json",
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        $button.closest('.preview-item').remove();
                    },
                    error: function (xhr) {
                        let errorMessage = 'Deletion failed';
                        try {
                            let errorResponse = JSON.parse(xhr.responseText);
                            errorMessage = errorResponse.message || errorMessage;
                        } catch (e) {
                            errorMessage = xhr.statusText || errorMessage;
                        }
                        console.error("Deletion error:", errorMessage);
                        alert(errorMessage);
                    }
                });
            });

            //-----------------------------end of Document image handlers

            //---------------------------Document image table handlers

            $('.btnAddDocs').on('click', function () {
                $('#btnSaveDocument').removeClass('d-none');
                $('#btnUpdateDocument').addClass('d-none');
                clearDocumentModal();
            });

            $('#btnSaveDocument').on('click', function () {
                let title = $('#txtTitle').val();
                let description = $('#txtDescription').val();
                let images = [];
                $('.preview-container .preview-item').each(function () {
                    let fileName = $(this).data('filename');
                    let fileUrl = $(this).data('url');
                    let fileId = $(this).find('.doc-rem').data('id');
                    if (fileName && fileUrl) {
                        images.push({
                            fileId: fileId,
                            filename: fileName,
                            url: fileUrl
                        });
                    }
                });
                const obj = {
                    'title': title,
                    'description': description,
                    'images': images
                }

                let html = `<tr data-edit="${tableEditId++}">
                                <td data="serial">*</td>
                                <td data="title">${title}</td>
                                <td data="description">${description ? description : '-'}</td>
                                <td>
                                    <a class="btn btn-warning editDocuments"><i class="fa fa-pencil"></i></a>
                                    <a class="btn btn-outline-danger deleteDocuments"><i class="fa fa-trash"></i></a>
                                </td>
                                <td data="tdata" class="d-none tdata">${JSON.stringify(obj)}</td>
                            </tr>`;
                $('#tblProjectDocuments').append(html).closest('table').removeClass('d-none');
                serialize('#tblProjectDocuments');
                $('.mdlDocument').modal('hide');
                clearDocumentModal();
            });

            let updatingRowId;

            $(document).on('click', '.editDocuments', function () {
                clearDocumentModal();
                updatingRowId = $(this).closest('tr').attr('data-edit');
                let tData = JSON.parse($(this).closest('tr').find('td').last().text());
                $('#txtTitle').val(tData.title);
                $('#txtDescription').val(tData.description);
                let previewContainer = $('.preview-container');
                let previewHtml = '';
                const allowedImageExtensions = ["jpeg", "jpg", "png", "webp"];
                let filePath;
                if (Array.isArray(tData.images)) {
                    tData.images.forEach(image => {
                        filePath = image.url;
                        let extension = filePath.split('.').pop().toLowerCase();
                        let docImg = filePath;
                        if (!allowedImageExtensions.includes(extension)) {
                            docImg = "/storage/essentials/doc.png";
                        }
                        previewHtml += `
                            <div class="col-md-3 col-6 img-hover preview-item light-card hover-1" data-url="${image.url}" data-ext="jpeg"  data-filename="${image.filename}">
                                <button data-id="${image.fileId}" class="doc-rem btnDeleteDocArray">x</button>
                                <a href="${image.url}" data-lightbox="image-gallery" data-title="${image.filename}" class="gallery-item">
                                        <div class="gallery-img-wrap">
                                            <img src="${docImg}" class="preview-image" data-title="${image.filename}" alt="${image.filename}">
                                        </div>
                                    </a>
                                <div class="file-name">${image.filename}</div>
                                <div class="text-center">
                                    <a href="${image.url}" download="${image.filename}" class="btn btn-sm btn-primary mt-2">Download</a>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    console.error("tData.images is not an array or is undefined:", tData.images);
                }
                previewContainer.append(previewHtml);
                $('#btnUpdateDocument').removeClass('d-none');
                $('#btnSaveDocument').addClass('d-none');
                $('.mdlDocument').modal('show');
                deletedDocuments = [];
            });

            $('#btnUpdateDocument').on('click', function () {
                let title = $('#txtTitle').val();
                let description = $('#txtDescription').val();
                let images = [];
                let fileIds = [];
                $('.preview-container .preview-item').each(function () {
                    let fileName = $(this).data('filename');
                    let fileUrl = $(this).data('url');
                    let fileId = $(this).find('.doc-rem').data('id');

                    if (fileName && fileUrl) {
                        images.push({
                            fileId: fileId,
                            filename: fileName,
                            url: fileUrl
                        });
                    }

                    if (fileId) {
                        fileIds.push(fileId);
                    }
                });

                const obj = {
                    'title': title,
                    'description': description,
                    'images': images
                }

                if (title) {
                    let formData = new FormData();
                    formData.append('documentIds', fileIds);
                    formData.append('title', title);
                    formData.append('description', description);
                    formData.append('deletedDocuments', deletedDocuments);

                    let csrfToken = $('meta[name="_token"]').attr('content');
                    formData.append('_token', csrfToken);

                    $.ajax({
                        url: "{{ route('updateDocuments') }}",
                        type: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        success: function (response) {
                            if (response.success) {
                                $('#tblProjectDocuments').find(`tr[data-edit="${updatingRowId}"]`).each(function () {
                                    let row = $(this);

                                    row.find('td[data="title"]').text(title);
                                    row.find('td[data="description"]').text(description ? description : '-');
                                    row.find('td[data="tdata"]').text(JSON.stringify(obj));
                                });

                                $('.mdlDocument').modal('hide');
                                $('#btnSaveDocument').removeClass('d-none');
                                $('#btnUpdateDocument').addClass('d-none');

                                clearDocumentModal();
                                Swal.fire({
                                    position: 'center',
                                    icon: 'success',
                                    title: 'Document updated successfully!',
                                    showConfirmButton: false,
                                    timer: 2000,
                                    color: 'black',
                                    background: 'white',
                                })
                            } else {
                                alert("Document update failed!");
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Document update failed';
                            try {
                                let errorResponse = JSON.parse(xhr.responseText);
                                errorMessage = errorResponse.message || errorMessage;
                            } catch (e) {
                                errorMessage = xhr.statusText || errorMessage;
                            }

                            console.error("Document update error:", errorMessage);
                        }
                    });
                    $('#txtTitle-err').text('');
                    clearDropify('#multipleImageDocument');
                } else {
                    $('#txtTitle-err').text('Title is required !');
                }
            });

            $(document).on('click', '.btnDeleteDocArray', function () {
                $(this).closest('.preview-item').remove();
                deletedDocuments.push($(this).attr('data-id'));
            });

            $(document).on('click', '.deleteDocuments', function () {

                let row = $(this).closest('tr');
                let tData = JSON.parse(row.find('td[data="tdata"]').text());
                let imageID = [];

                if (Array.isArray(tData.images)) {
                    tData.images.forEach(image => {
                        imageID.push(image.fileId);
                    });
                }

                deleteByImageIds(imageID);

                row.remove();

                let flag = $('#tblProjectDocuments tr').length;

                if (!flag) $('#tblProjectDocuments').closest('table').addClass('d-none');

                serialize('#tblProjectDocuments');

                $('#btnSaveDocument').removeClass('d-none');
                $('#btnUpdateDocument').addClass('d-none');

                clearDocumentModal();
            });

            const deleteByImageIds = (id) => {

                let csrfToken = $('meta[name="_token"]').attr('content');

                $.ajax({
                    url: "{{ route('deleteDocuments') }}",
                    type: "DELETE",
                    data: JSON.stringify({id: id}),
                    contentType: "application/json",
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                    },
                    error: function (xhr) {
                        let errorMessage = 'Upload failed';

                        try {
                            let errorResponse = JSON.parse(xhr.responseText);
                            errorMessage = errorResponse.message || errorMessage;
                        } catch (e) {
                            errorMessage = xhr.statusText || errorMessage;
                        }

                        console.error("Upload error:", errorMessage);
                    }
                });
            }

            //---------------------------End of Document image table handlers

            //--------------------------Document handling helpers

            const serialize = (selector) => {
                let serialNum = 1;
                $(`${selector} tr`).each(function () {
                    $(this).find('td[data="serial"]').text(serialNum++);
                });
            };

            const clearDocumentModal = () => {
                $('#txtTitle').val('');
                $('#txtDescription').val('');
                $('.preview-item').remove();
            }

            //----------------------------End of Document handling helpers


            // -----------------------------contract functionalities


            const getContractTypes = () => {
                let ContractID = $('#contract_type_id');
                let SelectedContractID = ContractID.attr('data-selected');
                ContractID.select2('destroy');
                $('#contract_type_id option').remove();
                ContractID.append('<option value="">--Select a Contract Type--</option>');

                $.ajax({
                    url: "{{route('getContractTypes')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == SelectedContractID)) {
                                ContractID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                ContractID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        ContractID.select2();
                    },
                    error: function (xhr) {
                    }
                });
            }

            const getContractors = () => {
                let ContractorID = $('#contractor_id');
                let SelectedContractorID = ContractorID.attr('data-selected');
                ContractorID.select2('destroy');
                $('#contractor_id option').remove();
                ContractorID.append('<option value="">--Select a Contractor--</option>');

                $.ajax({
                    url: "{{route('getContractors')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == SelectedContractorID)) {
                                ContractorID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                ContractorID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        ContractorID.select2();
                    },
                    error: function (xhr) {
                    }
                });
            }

            $('#addContracts').on('click',function(){
                let contract_type = $('#contract_type_id').find('option:selected');
                let contractor = $('#contractor_id').find('option:selected');
                let amount = parseFloat($('#txtAmount').val()).toFixed(2);

                if(contract_type.val() && contractor.val() && amount){
                    let status = true;
                    let table = $('#tblContract');

                    table.find('tr').each(function () {
                        let contractData = $(this).find('td[data-contract-type]').data('contract');
                        let contractorData = $(this).find('td[data-contractor]').data('contractor');

                        if (contractData === contract_type.text() && contractorData === contractor.text()) {
                            status = false;
                            return false;
                        }
                    });

                    if(status){

                        const obj = {
                            'contract_type_id':contract_type.val(),
                            'contract_name':contract_type.text(),
                            'user_id':contractor.val(),
                            'user_name':contractor.text(),
                            'amount':amount
                        };

                        let rowLength = $('#tblContract').find('tr').length;

                        if(rowLength){
                            contractCellId = $('#tblContract').find('tr:last td[data-contract-type] input').attr('data-id');
                            contractCellId++;
                        }

                        let html = `
                        <tr data-new="true">
                            <td>*</td>
                            <td data-contract="${contract_type.text()}">${contract_type.text()}<input class="d-none" value="${contract_type.val()}" name="contracts[${contractCellId}][contract_type_id]" data-id="${contractCellId}"></td>
                            <td data-contractor="${contractor.text()}">${contractor.text()}<input class="d-none" value="${contractor.val()}" name="contracts[${contractCellId}][user_id]" data-id="${contractCellId}"></td>
                            <td data-amount="${amount}">${amount}<input class="d-none" value="${amount}" name="contracts[${contractCellId}][amount]" data-id="${contractCellId}"></td>
                            <td>
                                <a class="btn btn-outline-primary editContracts"><i class="fa fa-pencil"></i></a>
                                <a class="btn btn-danger deleteContracts"><i class="fa fa-trash"></i></a>
                            </td>
                            <td data-tdata='${JSON.stringify(obj)}' class="d-none">${JSON.stringify(obj)}</td>
                        </tr>`;

                        table.append(html);
                        serializeTable('#tblContract');

                        $('#tblContract').closest('table').removeClass('d-none');
                        clearContractFields();
                    }
                }
            });

            $('#clearContracts').on('click',function(){
                $('#updateContracts').addClass('d-none');
                $('#addContracts').removeClass('d-none');
                clearContractFields();
            })

            $(document).on('click','.editContracts',function(){
                selectedContractRow = $(this).closest('tr');
                let isNew = selectedContractRow.find('.deleteContracts').length > 0;
                contractUpdateId = selectedContractRow.find('td[data-contract-type] input').attr('data-id');
                let tdata = JSON.parse(selectedContractRow.find('td[data-tdata]').attr('data-tdata'));

                $('#contract_type_id').val(tdata.contract_type_id).trigger('change');
                $('#contractor_id').val(tdata.user_id).trigger('change');
                $('#txtAmount').val(tdata.amount);

                if(!isNew){
                    $('#contract_type_id').attr('disabled',true);
                    $('#contractor_id').attr('disabled',true);
                }

                $('#addContracts').addClass('d-none');
                $('#updateContracts').removeClass('d-none');
            });

            $(document).on('click','.deleteContracts', function(){
                $(this).closest('tr').remove();
                let isRowEmpty = $('#tblContract').find('tr').length;
                if(!isRowEmpty){
                    $('#tblContract').closest('table').addClass('d-none');
                }
            })

            $('#updateContracts').on('click', function () {
                let contract_type = $('#contract_type_id').find('option:selected');
                let contractor = $('#contractor_id').find('option:selected');
                let amount = parseFloat($('#txtAmount').val()).toFixed(2);

                if (contract_type.val() && contractor.val() && amount) {
                    if (contract_type.val() && contractor.val() && amount) {
                    let status = true;
                    let table = $('#tblContract');

                    table.find(`tr`).not(selectedContractRow).each(function () {
                        let contractData = $(this).find('td[data-contract-type]').attr('data-contract-type');
                        let contractorData = $(this).find('td[data-contractor]').attr('data-contractor');

                        if (contractData === contract_type.text() && contractorData === contractor.text()) {
                            status = false;
                            return false;
                        }
                    });

                    if (status) {
                        const obj = {
                            'contract_type_id': contract_type.val(),
                            'contract_name': contract_type.text(),
                            'user_id': contractor.val(),
                            'user_name': contractor.text(),
                            'amount': amount
                        };

                        selectedContractRow.each(function () {
                            $(this).find('td[data-contract-type]').attr('data-contract-type', contract_type.text()).html(contract_type.text()+`<input class="d-none" value="${contract_type.val()}" name="contracts[${contractUpdateId}][contract_type_id]" data-id="${contractUpdateId}">`);
                            $(this).find('td[data-contractor]').attr('data-contractor', contractor.text()).html(contractor.text()+`<input class="d-none" value="${contractor.val()}" name="contracts[${contractUpdateId}][user_id]" data-id="${contractUpdateId}">`);
                            $(this).find('td[data-amount]').attr('data-amount', amount).html(amount+`<input class="d-none" value="${amount}" name="contracts[${contractUpdateId}][amount]" data-id="${contractUpdateId}">`);
                            $(this).find('td[data-tdata]').attr('data-tdata', JSON.stringify(obj)).text(JSON.stringify(obj));
                        });

                        $('#updateContracts').addClass('d-none');
                        $('#addContracts').removeClass('d-none');

                        selectedContractRow = null;
                        clearContractFields();
                    } else {
                        alert("")
                    }
                }
                    }
            });

            const clearContractFields = () => {
                $('#contract_type_id').attr('disabled',false);
                $('#contractor_id').attr('disabled',false);
                $('#contract_type_id, #contractor_id').val(null).trigger('change');
                $('#txtAmount').val('');
            };

            const serializeTable = (selector) => {
                let i = 1;
                $(`${selector} tr`).each(function () {
                    $(this).find('td:first').text(i++);
                });
            };

            // -----------------------------end contract functionalities

            // -----------------------------Amenities functionalities



            const getAmenities = () => {
                let AmenityID = $('#amenity_id');
                let SelectedAmenityID = AmenityID.attr('data-selected');
                AmenityID.select2('destroy');
                $('#amenity_id option').remove();
                AmenityID.append('<option value="">--Select an Amenity--</option>');

                $.ajax({
                    url: "{{route('getAmenities')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == SelectedAmenityID)) {
                                AmenityID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                AmenityID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        AmenityID.select2();
                    },
                    error: function (xhr) {
                    }
                });
            }


            $('#addAmenities').on('click',function(){
                let amenity = $('#amenity_id').find('option:selected');
                let description = $('#amenty_description').val();

                if(amenity.val() && description){
                    let status = true;
                    let table = $('#tblAmenity');

                    table.find('tr').each(function () {
                        let amenityData = $(this).find('td[data-amenity]').data('amenity');

                        if (amenityData === amenity.text()) {
                            status = false;
                            return false;
                        }
                    });

                    if(status){

                        const obj = {
                            'amenity_id':amenity.val(),
                            'amenity_name':amenity.text(),
                            'description':description,
                        };

                        let rowLength = $('#tblAmenity').find('tr').length;

                        if(rowLength){
                            amenityCellId = $('#tblAmenity').find('tr:last td[data-amenity] input').attr('data-id');
                            amenityCellId++;
                        }

                        let html = `
                        <tr data-new="true">
                            <td>*</td>
                            <td data-amenity="${amenity.text()}">${amenity.text()}<input class="d-none" value="${amenity.val()}" name="amenities[${amenityCellId}][amenity_id]" data-id="${amenityCellId}"></td>
                            <td data-description="${description}">${description}<input class="d-none" value="${description}" name="amenities[${amenityCellId}][description]" data-id="${amenityCellId}"></td>
                            <td>
                                <a class="btn btn-outline-primary editAmenities"><i class="fa fa-pencil"></i></a>
                                <a class="btn btn-danger deleteAmenities"><i class="fa fa-trash"></i></a>
                            </td>
                            <td data-tdata='${JSON.stringify(obj)}' class="d-none">${JSON.stringify(obj)}</td>
                        </tr>`;

                        table.append(html);
                        serializeTable('#tblAmenity');

                        $('#tblAmenity').closest('table').removeClass('d-none');
                        clearAmenityFields();
                    }
                }
            });

            $('#clearAmenities').on('click',function(){
                $('#updateAmenities').addClass('d-none');
                $('#addAmenities').removeClass('d-none');
                clearAmenityFields();
            })

            $(document).on('click','.editAmenities',function(){
                selectedAmenityRow = $(this).closest('tr');

                contractUpdateId = selectedAmenityRow.find('td[data-amenity] input').attr('data-id');

                let tdata = JSON.parse(selectedAmenityRow.find('td[data-tdata]').attr('data-tdata'));

                $('#amenity_id').val(tdata.amenity_id).trigger('change');
                $('#amenty_description').val(tdata.description);

                $('#addAmenities').addClass('d-none');
                $('#updateAmenities').removeClass('d-none');

            });


            $(document).on('click','.deleteAmenities', function(){
                let id = $(this).closest('tr[data-new="false"]').find('td[data-amenity] input').attr('data-id');

                if(id) amenityDeletedId.push(id);

                $('#deletedAmenities').val(JSON.stringify(amenityDeletedId));

                $(this).closest('tr').remove();
                let isRowEmpty = $('#tblAmenity').find('tr').length;

                if(!isRowEmpty){
                    $('#tblAmenity').closest('table').addClass('d-none');
                }
            })

            $('#updateAmenities').on('click', function () {

                let amenity = $('#amenity_id').find('option:selected');
                let description = $('#amenty_description').val();

                if (amenity.val() && description) {
                    let status = true;
                    let table = $('#tblAmenity');

                    table.find(`tr`).not(selectedAmenityRow).each(function () {
                        let amenityData = $(this).find('td[data-amenity]').attr('data-amenity');

                        if (amenityData === amenity.text() ) {
                            status = false;
                            return false;
                        }
                    });

                    if (status) {
                        const obj = {
                            'amenity_id': amenity.val(),
                            'amenity_name': amenity.text(),
                            'description': description
                        };

                        selectedAmenityRow.each(function () {
                            $(this).find('td[data-amenity]').attr('data-amenity', amenity.text()).html(amenity.text()+`<input class="d-none" value="${amenity.val()}" name="amenities[${amenityUpdateId}][amenity_id]" data-id="${amenityUpdateId}">`);
                            $(this).find('td[data-description]').attr('data-description', description).html(description+`<input class="d-none" value="${description}" name="amenities[${amenityUpdateId}][description]" data-id="${amenityUpdateId}">`);
                            $(this).find('td[data-tdata]').attr('data-tdata', JSON.stringify(obj)).text(JSON.stringify(obj));
                        });

                        $('#updateAmenities').addClass('d-none');
                        $('#addAmenities').removeClass('d-none');

                        selectedAmenityRow = null;
                        clearAmenityFields();
                    }
                }
            });

            const clearAmenityFields = () => {
                $('#amenity_id').val(null).trigger('change');
                $('#amenty_description').val('');
            }
            // -----------------------------end Amenities functionalities


            let stageList = $("#stage-list");
            let isEditMode = {{ $project ? 'true' : 'false' }};

            // Enable sorting and update order_no on change
            stageList.sortable({
                update: function () {
                    updateOrderNumbers();
                }
            });

            // Add Stage Function
            $("#add-stage-btn").on("click", function () {
                let stageName = $("#stage-name").val().trim();
                if (stageName === "") {
                    alert("Stage name cannot be empty!");
                    return;
                }

                if (stageExists(stageName)) {
                    alert("Stage already exists!");
                    return;
                }

                let index = stageList.children().length;
                let newStage = `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="stage-name">${stageName}</span>
                            <div>
                                <button type="button" class="btn btn-sm btn-warning edit-stage">Edit</button>
                                <button type="button" class="btn btn-sm btn-danger delete-stage">Delete</button>
                                <input type="hidden" name="stages[${index}][name]" value="${stageName}">
                                <input type="hidden" name="stages[${index}][deleted]" value="0">
                                <input type="hidden" name="stages[${index}][order_no]" value="${index + 1}">
                            </div>
                        </li>
                    `;
                stageList.append(newStage);
                $("#stage-name").val(""); // Clear input
                updateOrderNumbers();
            });

            // Edit Stage
            stageList.on("click", ".edit-stage", function () {
                let stageItem = $(this).closest("li");
                let name = stageItem.find(".stage-name").text();
                let newName = prompt("Edit Stage Name:", name);
                if (newName && !stageExists(newName)) {
                    stageItem.find(".stage-name").text(newName);
                    stageItem.find("input[name*='[name]']").val(newName);
                } else if (stageExists(newName)) {
                    alert("Stage already exists!");
                }
            });

            // Delete Stage (Soft Delete)
            stageList.on("click", ".delete-stage", function () {
                let stageItem = $(this).closest("li");
                if (isEditMode) {
                    stageItem.find("input[name*='[deleted]']").val(1);
                    $(this).replaceWith('<button type="button" class="btn btn-sm btn-success restore-stage">Restore</button>');
                } else {
                    stageItem.remove();
                }
            });

            // Restore Deleted Stage
            stageList.on("click", ".restore-stage", function () {
                let stageItem = $(this).closest("li");
                stageItem.find("input[name*='[deleted]']").val(0);
                $(this).replaceWith('<button type="button" class="btn btn-sm btn-danger delete-stage">Delete</button>');
            });

            // Function to check if stage already exists
            function stageExists(name) {
                let exists = false;
                $(".stage-name").each(function () {
                    if ($(this).text().toLowerCase() === name.toLowerCase()) {
                        exists = true;
                        return false;
                    }
                });
                return exists;
            }

            // Update Order Numbers after sorting
            function updateOrderNumbers() {
                stageList.children("li").each(function (index) {
                    $(this).find("input[name*='[order_no]']").val(index + 1);
                });
            }

            const getSites = () => {
                let SiteID = $('#site_id');
                let SelectedSiteID = SiteID.attr('data-selected');
                SiteID.select2('destroy');
                $('#site_id option').remove();
                SiteID.append('<option value="">--Select a Site--</option>');

                $.ajax({
                    url: "{{route('getSites')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == SelectedSiteID)) {
                                SiteID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                SiteID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        SiteID.select2();
                    },
                    error: function (xhr) {
                    }
                });
            }
            const getDocuments = () => {
                $.ajax({
                    url: "{{route('getDocuments')}}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}",
                        module_name: "{{ ($project && $project->id) ? 'Project' : 'User-Project' }}",
                        module_id: "{{ ($project && $project->id) ? $project->id : Auth::id() }}"
                    },
                    success: function (response) {
                        if (response.length > 0) {
                            response.forEach(function (item) {
                                let title = item.title || 'Untitled';
                                let description = item.description || '-';
                                let object = {
                                    title: item.title,
                                    description: item.description,
                                    images: item.images
                                };

                                let html = `<tr data-edit="${tableEditId++}"><td data="serial">*</td>
                                            <td data="title">${title}</td><td data="description">${description}</td>
                                            <td>
                                                <a class="btn btn-warning editDocuments"><i class="fa fa-pencil"></i></a>
                                                <a class="btn btn-outline-danger deleteDocuments"><i class="fa fa-trash"></i></a>
                                            </td>
                                            <td data="tdata" class="d-none tdata">${JSON.stringify(object)}</td>
                                            </tr>`;

                                $('#tblProjectDocuments').append(html).closest('table').removeClass('d-none');
                            });

                            serialize('#tblProjectDocuments');
                        }
                    },
                    error: function (xhr) {
                    }
                });
            }
            const getEngineers = () => {
                let EngineerID = $('#engineer_id');
                let SelectedEngineerID = EngineerID.attr('data-selected');
                EngineerID.select2('destroy');
                $('#engineer_id option').remove();
                EngineerID.append('<option value="">--Select a Engineer--</option>');

                $.ajax({
                    url: "{{route('getEngineers')}}",
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        response.forEach(function (item) {
                            if ((item.id == SelectedEngineerID)) {
                                EngineerID.append('<option selected value="' + item.id
                                    + '">' + item.name + '</option>');
                            } else {
                                EngineerID.append('<option value="' + item.id
                                    + '">' + item.name + '</option>');
                            }
                        });
                        EngineerID.select2();
                    },
                    error: function (xhr) {
                    }
                });
            }

            if ($('#status').val() === 'Completed') {
                $('#sold_amount_div').removeClass('d-none');
                $('#sold_amount').attr('required', true).attr('name','sold_amount');
            }
            
            $('#status').on('change', function () {
                if ($('#status').val() === 'Completed') {
                    $('#sold_amount_div').removeClass('d-none');
                    $('#sold_amount').attr('required', true).attr('name','sold_amount');
                } else {
                    $('#sold_amount_div').addClass('d-none');
                    $('#sold_amount').removeAttr('required').removeAttr('name');
                }
            });

            getSites();
            getEngineers();
            getDocuments();

            getContractTypes();
            getContractors();
            getAmenities();

        });
    </script>
@endsection
