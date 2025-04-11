@extends('layouts.admin')

@section('content')
    @php
        $PageTitle="Project Specifications";
        $ActiveMenuName='Project-Specifications';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" data-original-title="" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">Manage Projects</li>
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
                            <div class="col-sm-4 my-2"><h5>{{ $projectSpecification  ? 'Edit' : 'Create' }} {{$PageTitle}}</h5></div>
                            <div class="col-sm-4 my-2 text-right text-md-right"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 mt-20">
                                <div class="form-group">
                                    <label class="txtSpecName">Project Specification Name <span class="required"> * </span></label>
                                    <input type="text" class="form-control" id="txtSpecName" value="{{ $projectSpecification->spec_name ?? '' }}">
                                    <div class="errors err-sm" id="txtSpecName-err"></div>
                                </div>
                            </div>
                            <div class="col-sm-12 mt-20">
                                <div class="form-group">
                                    <label class="lstActiveStatus">Active Status</label>
                                    <select class="form-control" id="lstActiveStatus">
                                        <option value="1" {{ $projectSpecification && $projectSpecification->is_active ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ $projectSpecification && !$projectSpecification->is_active ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-12 mt-20">
                                <div class="form-group">
                                    <label class="txtValues">Project Specification Values <span class="required"> * </span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="txtValues">
                                        <button class="input-group-text btn-outline-primary px-4 position-relative" id="btnAddSpecValue"><i class="fa fa-plus"></i></button>
                                    </div>
                                    <div class="errors err-sm" id="txtValues-err"></div>
                                </div>
                            </div>
                            <div class="col-12 my-2">

                            </div>
                            <div class="col-sm-12 mt-3">
                                <table class="table tblSpecValues" id="tblSpecValues">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Specification Value Name</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($projectSpecification)
                                        @php
                                            $spec_values = json_decode($projectSpecification->spec_values);
                                        @endphp
                                            @foreach($spec_values as $Key=>$row)
                                                <tr>
                                                    <td>{{$Key + 1}}</td>
                                                    <td>{{$row->value_name}}</td>
                                                    <td><button type="button" class="btn btn-sm btn-outline-danger btnDeleteSpecValue"><i class="fa fa-trash"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>                                                                        
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row mt-15 justify-content-end">
                            <div class="col-md-4 text-end">
                                <a href="javascript:void(0)" onclick="window.history.back()" type="button" class="btn btn-warning">Back</a>
                                @if(!$projectSpecification)
                                    @can('Create Project Specifications')
                                        <button type="button" class="btn btn-primary" id="btnSave">Save</button>
                                    @endcan
                                @else
                                    @can('Edit Project Specifications')
                                        <button type="button" class="btn btn-primary" id="btnSave">Update</button>
                                    @endcan
                                @endif
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
        

        const ValuesExistingValidation=async(data)=>{
            $('.errors').html('');
            let status=true;
            let lowerCaseData = data.toLowerCase();
            $('#tblSpecValues tbody tr').each(function(){
                let ExistingValue = $(this).find('td:eq(1)').text().toLowerCase().trim();
                if(ExistingValue == lowerCaseData){
                    $("#txtValues-err").html('This Value exists');status=false;
                    return false;
                }
            });
            if(status==false){$("html, body").animate({ scrollTop: 0 }, "slow");}
            return status;
        }
        const ValidateGetData=async()=>{

            let formData={
                spec_name : $('#txtSpecName').val(),
                is_active : $('#lstActiveStatus').val(),
            };
            let Values = [];

            $('#tblSpecValues tbody tr').each(function(){
                let value_name = $(this).find('td:eq(1)').text().trim();
                let reference = $(this).find('td:eq(2)').text().trim();
                let sub_heading = $(this).find('td:eq(3)').text().trim();
                Values.push({value_name});
            });

            formData.spec_values = JSON.stringify(Values);
            let status = true;

            if (!formData.spec_name) {
                $('#txtSpecName-err').html('Project Specification Name is required');status = false;
            }else if(formData.spec_name<3){
                $('#txtSpecName-err').html('Project Specification Name must be greater than 2 characters');status=false;
            }else if(formData.spec_name>100){
                $('#txtSpecName-err').html('Project Specification Name may not be greater than 100 characters');status=false;
            }

            if (formData.spec_name && formData.spec_values.length < 1) {
                toastr.error("Add a Project Specification Value", "Failed", { positionClass: "toast-top-right", containerId: "toast-top-right", showMethod: "slideDown", hideMethod: "slideUp", progressBar: !0 })
            }
            
            return { status, formData };
        }
        $("#btnAddSpecValue").on("click", async function () {

            let SpecName = $('#txtSpecName').val();
            let Value = $('#txtValues').val();
            let status = await ValuesExistingValidation(Value);
            if (SpecName=="") {
                $('#txtSpecName-err').html('Project Specification Name is required');status = false;
            }else if(SpecName.length<3){
                $('#txtSpecName-err').html('Project Specification Name must be greater than 2 characters');status=false;
            }else if(SpecName.length>100){
                $('#txtSpecName-err').html('Project Specification Name may not be greater than 100 characters');status=false;
            }
            if (Value=="") {
                $('#txtValues-err').html('Value is required');status = false;
            }
            if (status) {
                let index = $('#tblSpecValues tbody tr').length;
                let html='<tr>';
					html+='<td>'+ (index + 1) +'</td>';
					html+='<td>'+ Value +'</td>';
					html+='<td><button type="button" class="btn btn-sm btn-outline-danger btnDeleteSpecValue"><i class="fa fa-trash"></i></button></td>';
    				html+='</tr>';
				$('#tblSpecValues tbody').append(html);
				$('#txtValues').val('');
            }
        });
        
        $(document).on('click', '.btnDeleteSpecValue', function () {
            $(this).closest("tr").remove();
            $('#tblSpecValues tbody tr').each(function(index){
                $(this).find('td:eq(0)').text(index+1);
            });
		});
        $("#txtValues").keydown(function (event) {
            if (event.keyCode === 13) {
                $("#btnAddSpecValue").click();
            }
        });
        $('#btnSave').click(async function(e){
            e.preventDefault();
            let { status, formData }=await ValidateGetData();

            if(status){
                swal({
                    title: "Are you sure?",
                    text: "You want @if(!$projectSpecification) Save @else Update @endif this Project Specification!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonClass: "btn-outline-success",
                    confirmButtonText: "Yes, @if(!$projectSpecification) Save @else Update @endif it!",
                    closeOnConfirm: false
                }).then(function () {  
                    swal.close();
                    btnLoading($('#btnSave'));
                    let postUrl="{{ $projectSpecification ? route('project_specifications.update', $projectSpecification->id) : route('project_specifications.store') }}";
                    let Type= "{{ $projectSpecification ? 'PUT' : 'POST' }}";
                    $.ajax({
                        type:Type,
                        url:postUrl,
                        headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                        data:formData,
                        success:function(response){
                            document.documentElement.scrollTop = 0;
                            if(response.status==true){
                                
                                @if($projectSpecification)
                                    window.location.replace("{{route('project_specifications.index')}}");
                                @else
                                    window.location.reload();
                                @endif
                                
                            }else{
                                toastr.error(response.message, "Failed", { positionClass: "toast-top-right", containerId: "toast-top-right", showMethod: "slideDown", hideMethod: "slideUp", progressBar: !0 })
                                if(response['errors']!=undefined){
                                    $('.errors').html('');
                                    $.each( response['errors'], function( KeyName, KeyValue ) {
                                        var key=KeyName;
                                        if(key=="spec_name"){$('#txtSpecName-err').html(KeyValue);}
                                    });
                                }
                            }
                        }
                    });
                });
            }
        });
    });
</script>
    
@endsection
