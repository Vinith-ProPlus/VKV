$(document).ready(function(){
    let RootUrl=$('#txtRootUrl').val();

    const getADCity=async()=>{
        let PostalCode = $("#txtADPostalCode").val();
        $('#lstADCity').select2('destroy');
        $('#lstADCity option').remove();
        $('#lstADCity').append('<option value="" selected>Select a City</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/city",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            data:{PostalCode:PostalCode},
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                if (response.error) {
                    $('#txtADPostalCode-err').html(response.error);
                } else {
                    for (let Item of response) {
                        let selected = "";
                        if (Item.CityID == $('#lstADCity').attr('data-selected')) { selected = "selected"; }
                        $('#lstADCity').append('<option ' + selected + ' value="' + Item.CityID + '" data-country-id="'+Item.CountryID+'" data-state-id="'+Item.StateID+'" data-district-id="'+Item.DistrictID+'" data-taluk-id="'+Item.TalukID+'" data-postal-id="'+Item.PostalID+'">' + Item.CityName + '</option>');
                    }
                }
            }
        });
        $('#lstADCity').select2({
            dropdownParent: $('.AddressModal'),
        });
    }
    const getADCountry=()=>{
        $.ajax({
            type:"post",
            url:RootUrl+"get/country",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            async:true,
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                $('#lstADCountry').select2('destroy');
                $('#lstADCountry option').remove();
                $('#lstADCountry').append('<option value="" selected>Select a Country</option>');
                for(let Item of response){
                    let selected="";
                    if($('#lstADCountry').attr('data-selected')!=""){if(Item.CountryID==$('#lstADCountry').attr('data-selected')){selected="selected";}}
                    $('#lstADCountry').append('<option '+selected+' data-phone-code="'+Item.PhoneCode+'" data-country-name="'+Item.CountryName+'" data-phone-length="'+Item.PhoneLength+'" value="'+Item.CountryID+'">'+Item.CountryName+'('+Item.sortname+')'+' </option>');
                }
                $('#lstADCountry').select2({
                    dropdownParent: $('.AddressModal')
                });
                if($('#lstADCountry').val()!=""){
                    $('#lstADCountry').trigger('change');
                }
            }
        });
    }
    const getADStates=()=>{
        $.ajax({
            type:"post",
            url:RootUrl+"get/states",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            data:{CountryID:$('#lstADCountry').val()},
            async:true,
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                $('#lstADState').select2('destroy');
                $('#lstADState option').remove();
                $('#lstADState').append('<option value="" selected>Select a State</option>');
                for(let Item of response){
                    let selected="";
                    if($('#lstADState').attr('data-selected')!=""){if(Item.StateID==$('#lstADState').attr('data-selected')){selected="selected";}}
                    $('#lstADState').append('<option '+selected+'  value="'+Item.StateID+'">'+Item.StateName+' </option>');
                }
                $('#lstADState').select2({
                    dropdownParent: $('.AddressModal')
                });
                if($('#lstADState').val()!=""){
                    $('#lstADState').trigger('change');
                }
            }
        });
    }
    const getADDistricts=()=>{
        $.ajax({
            type:"post",
            url:RootUrl+"get/districts",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            data:{CountryID:$('#lstADCountry').val(),StateID:$('#lstADState').val()},
            async:true,
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                $('#lstADDistrict').select2('destroy');
                $('#lstADDistrict option').remove();
                $('#lstADDistrict').append('<option value="" selected>Select a District</option>');
                for(let Item of response){
                    let selected="";
                    if($('#lstADDistrict').attr('data-selected')!=""){if(Item.DistrictID==$('#lstADDistrict').attr('data-selected')){selected="selected";}}
                    $('#lstADDistrict').append('<option '+selected+'  value="'+Item.DistrictID+'">'+Item.DistrictName+' </option>');
                }
                $('#lstADDistrict').select2({
                    dropdownParent: $('.AddressModal')
                });
                if($('#lstADDistrict').val()!=""){
                    $('#lstADDistrict').trigger('change');
                }
            }
        });
    }
    const getADTaluks=()=>{
        $.ajax({
            type:"post",
            url:RootUrl+"get/taluks",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            data:{CountryID:$('#lstADCountry').val(),StateID:$('#lstADState').val(),DistrictID:$('#lstADDistrict').val()},
            async:true,
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                $('#lstADTaluk').select2('destroy');
                $('#lstADTaluk option').remove();
                $('#lstADTaluk').append('<option value="" >Select a Taluk</option>');
                for(let Item of response){
                    let selected="";
                    if($('#lstADTaluk').attr('data-selected')!=""){if(Item.TalukID==$('#lstADTaluk').attr('data-selected')){selected="selected";}}
                    $('#lstADTaluk').append('<option '+selected+'  value="'+Item.TalukID+'">'+Item.TalukName+' </option>');
                }
                $('#lstADTaluk').select2({
                    dropdownParent: $('.AddressModal')
                });
            }
        });
    }

    const getAddressModal=(data={})=>{
        $.ajax({
            type:"post",
            url:RootUrl+"shipping-address-form",
            data:{"data":JSON.stringify(data)},
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"html",
            async:true,
            error:function(e, x, settings, exception){},
            success:async(response)=>{
                if(response!=""){
                    bootbox.dialog({
                        title:"Shipping Address",
                        closeButton: true,
                        message: response,
                        className:"AddressModal",
                        buttons: {}
                    });
                }
            }
        });
    }
    const ValidateGetAddress = async () => {
        $(".errors.Address").html("");
        let status = true;
        let formData={};
        formData.uuid=$("#btnSaveAddress").attr('data-edit-id');
        formData.AID=$("#btnSaveAddress").attr('data-aid');
        formData.Address=$('#txtADAddress').val();
        formData.CountryID=$('#lstADCountry').val();
        formData.CountryName=$('#lstADCountry option:selected').text();
        formData.PhoneLength=$('#lstADCountry option:selected').attr('data-phone-length');
        formData.CallingCode=$('#lstADCountry option:selected').attr('data-phone-code');
        formData.StateID=$('#lstADState').val();
        formData.StateName=$('#lstADState option:selected').text();
        formData.DistrictID=$('#lstADDistrict').val();
        formData.DistrictName=$('#lstADDistrict option:selected').text();
        formData.TalukID=$('#lstADTaluk').val();
        formData.TalukName=$('#lstADTaluk option:selected').text();
        formData.CityID=$('#lstADCity').val();
        formData.CityName=$('#lstADCity option:selected').text();
        formData.PostalCode=$('#txtADPostalCode').val();
        formData.PostalCodeID=$('#lstADCity option:selected').attr('data-postal-id');
        // console.log(formData);
        let Address ="";
        if(formData.Address==""){
            $('#txtADAddress-err').html('Address is required');status=false;
        }else if(formData.Address.length<5){
            $('#txtADAddress-err').html('The Address must be greater than 5 characters.');status=false;
        }else{
            Address+=",<br>"+formData.Address;
        }
        if(formData.CityID==""){
            $('#lstADCity-err').html('City is required');status=false;
        }else{
            Address+=",<br>"+formData.CityName;
        }
        if(formData.TalukID==""){
            $('#lstADTaluk-err').html('Taluk is required');status=false;
        }else{
            Address+=",<br>"+formData.TalukName;
        }
        if(formData.DistrictID==""){
            $('#lstADDistrict-err').html('District is required');status=false;
        }else{
            Address+=",<br>"+formData.DistrictName;
        }
        if(formData.StateID==""){
            $('#lstADState-err').html('State is required');status=false;
        }else{
            Address+=",<br>"+formData.StateName;
        }
        if(formData.CountryID==""){
            $('#lstADCountry-err').html('Country is required');status=false;
        }else{
            Address+=","+formData.CountryName;
        }
        if(formData.PostalCode==""){
            $('#txtADPostalCode-err').html('Postal Code is required');status=false;
        }else{
            Address+=" - "+formData.PostalCode;
        }
        // status = true;
        return { status, formData, Address };
    };
    const SaveAddress = async (EditID,AID) => {
        let { status, formData, Address } = await ValidateGetAddress();
        console.log(formData);
        if (status) {
            let index = $('#tblAddress tbody tr').length;
            let html = `<tr data-aid="${AID}">`;
            html += `<td class="d-none">${EditID ? EditID : index + 1}</td>`;
            html += `<td class="text-center"><div class="radio radio-primary"><input id="DefaultAddress${EditID ? EditID : index + 1}" type="radio" name="Address" value="${EditID ? EditID : index + 1}"><label for="DefaultAddress${EditID ? EditID : index + 1}"></label></div></td>`;
            html += `<td class="pointer">${Address}</td>`;
            html += `<td class="text-center align-middle"><button type="button" class="btn btn-outline-success btnEditAddress"><i class="fa fa-pencil"></i></button> <button type="button" class="btn btn-outline-danger btnDeleteAddress"><i class="fa fa-trash"></i></button></td>`;
            html += `<td class="d-none">${JSON.stringify(formData)}</td>`;
            html += "</tr>";
            if(EditID){
                $("#tblAddress tbody tr").each(function () {
                    let SNo = $(this).find("td:eq(0)").text();
                    if (SNo === EditID) {
                        $(this).replaceWith(html);
                        return false;
                    }
                });
            }else{
                $('#tblAddress tbody').append(html);
            }
            bootbox.hideAll();
        }
    };
    
    /* $(document).on('click', '#btnSaveAddress', function () {
        SaveAddress();
    }); */
    $(document).on('click', '.btnAddAddress', function () {
        getAddressModal();
    });
    $(document).on('keydown', '#txtADPostalCode', function () {
        $('.errors.Address').html("");
        if (event.keyCode === 13) {
            $("#btnADPostalCode").click();
        }
    });
    $(document).on('click', '#btnADPostalCode', function () {
        $('.errors.Address').html("");
        $('#btnADPostalCode').html('<i class="fa fa-spinner fa-pulse"></i>');
        let PostalCode = $("#txtADPostalCode").val();
        if(!PostalCode){
            $("#txtADPostalCode-err").html("Enter a Postal Code");
        }else{
            getADCity();            
        }
        setTimeout(() => {
            $('#btnADPostalCode').html('<i class="fa fa-search"></i>');
        }, 500);
    });
    $(document).on('click', '.btnEditAddress', function () {
        let Row=$(this).closest('tr');
        let EditData=JSON.parse($(this).closest('tr').find("td:eq(4)").html());
        EditData.EditID=Row.find("td:first").html();
        EditData.AID=Row.attr('data-aid');
        getAddressModal(EditData);
    });

    $(document).on('click','#btnModalInit',function(){
        $('.adselect2').select2({
            dropdownParent: $('.AddressModal')
        });
    
        if($("#txtADPostalCode").val()){
            $("#btnADPostalCode").click();
        }
        setTimeout(function(){
            $('#lstADCity').trigger('change');
        },1000);
    });

    $(document).on('change', '#lstADCity', function () {
        if($(this).val()){
            let SelectedElem=$(this).find("option:selected");
            $("#lstADCountry").attr('data-selected', SelectedElem.attr('data-country-id'));
            $("#lstADState").attr('data-selected', SelectedElem.attr('data-state-id'));
            $("#lstADDistrict").attr('data-selected', SelectedElem.attr('data-district-id'));
            $("#lstADTaluk").attr('data-selected', SelectedElem.attr('data-taluk-id'));
            $("#txtADPostalCode").attr('data-id', SelectedElem.attr('data-postal-id'));
            $("#lstADCountry").trigger('change');
            getADCountry();
        }else{
            $("#lstADCountry").attr('data-selected', "");
            $("#lstADState").attr('data-selected', "");
            $("#lstADDistrict").attr('data-selected', "");
            $("#lstADTaluk").attr('data-selected', "");
            $("#txtADPostalCode").attr('data-id', "");
            $("#lstADCountry").trigger('change');
            $("#lstADState").trigger('change');
            $("#lstADDistrict").trigger('change');
            $("#lstADTaluk").trigger('change');
        }
    });
    $(document).on('change','#lstADCountry',function(){
        getADStates();
    });
    $(document).on('change','#lstADState',function(){
        getADDistricts();
    });
    $(document).on('change','#lstADDistrict',function(){
        getADTaluks();
    });
});