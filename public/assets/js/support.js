$(document).ready(function(){
    let RootUrl=$('#txtRootUrl').val();
    /*** Country  **********/
    const validateCountry=async()=>{
        let status=true;
        $('.New-Country-err').html('');
        let ShortName=$('#txtMShortName').val();
        let CountryName=$('#txtMCountryName').val();
        let CallingCode=$('#txtMCallingCode').val();
        let PhoneLength=$('#txtMPhoneLength').val();

        if(ShortName==""){
            $('#txtMShortName-err').html('Short Name is required');status=false;
        }else if(ShortName.length<2){
            $('#txtMShortName-err').html('Short Name must be atleast 2 characters');status=false;
        }else if(ShortName.length>3){
            $('#txtMShortName-err').html('Short Name may not be greater than 3 characters');status=false;
        }
        if(CountryName==""){
            $('#txtMCountryName-err').html('Country Name is required');status=false;
        }else if(CountryName.length<3){
            $('#txtMCountryName-err').html('Country Name must be atleast 3 characters');status=false;
        }else if(CountryName.length>100){
            $('#txtMCountryName-err').html('Country Name may not be greater than 100 characters');status=false;
        }
        if(CallingCode==""){
            $('#txtMCallingCode-err').html('Calling Code is required');status=false;
        }else if($.isNumeric(CallingCode)==false){
            $('#txtMCallingCode-err').html('Calling Code must be a number');status=false;
        }else if(CallingCode.length<1){
            $('#txtMCallingCode-err').html('Calling Code must be atleast 1 digits');status=false;
        }else if(CallingCode.length>10){
            $('#txtMCallingCode-err').html('Calling Code may not be greater than 10 digits');status=false;
        }
        if(PhoneLength==""){
            $('#txtMPhoneLength-err').html('Phone Length is required');status=false;
        }else if($.isNumeric(PhoneLength)==false){
            $('#txtMPhoneLength-err').html('Phone Length must be a number');status=false;
        }else if(PhoneLength<0){
            $('#txtMPhoneLength-err').html('Phone Length must be greater then equal to 0');status=false;
        }else if(PhoneLength.length>3){
            $('#txtMPhoneLength-err').html('Phone Length may not be greater than 3 digits');status=false;
        }
        return status;
    }
    const createCountryForm=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"country/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Country',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    const getCountries=async(elem)=>{
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Country</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/country",
            beforeSend:async()=>{
                $('#btnReloadCountry i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadCountry i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.CountryID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' data-phone-code="'+Item.PhoneCode+'" data-phone-length="'+Item.PhoneLength+'" value="'+Item.CountryID+'">'+Item.CountryName+' ( '+Item.sortname+' ) '+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });

    }
    $('#btnReloadCountry').click(function(){
        let id=$(this).parent().attr('for');
        getCountries(id);
    });
    $(document).on('click','#btnAddCountry',async function(){
        let id=$(this).parent().attr('for');
        createCountryForm(id);
    });
    $(document).on('click','#btnCreateCountry',async function(){
        let status=await validateCountry();
        if(status==true){
            let formData={};
            formData.ShortName=$('#txtMShortName').val();
            formData.CountryName=$('#txtMCountryName').val();
            formData.CallingCode=$('#txtMCallingCode').val();
            formData.PhoneLength=$('#txtMPhoneLength').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Country",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateCountry'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"country/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateCountry'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadCountry').length>0){
                                $('#btnReloadCountry').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Country-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="ShortName"){$('#txtMShortName-err').html(KeyValue);}
                                    if(key=="CountryName"){$('#txtMCountryName-err').html(KeyValue);}
                                    if(key=="CallingCode"){$('#txtMCallingCode-err').html(KeyValue);}
                                    if(key=="PhoneLength"){$('#txtMPhoneLength-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** States  **********/
    const validateState=async()=>{
        let status=true;
        $('.New-State-err').html('');
        let CountryName=$('#lstMCountry').val();
        let StateName=$('#txtMStateName').val();
        let stateCode=$('#txtMStateCode').val();

        if(CountryName==""){
            $('#lstMCountry-err').html('Country Name is required');status=false;
        }
        if(StateName==""){
            $('#txtMStateName-err').html('State Name is required');status=false;
        }else if(StateName.length<3){
            $('#txtMStateName-err').html('State Name must be atleast 3 characters');status=false;
        }else if(StateName.length>100){
            $('#txtMStateName-err').html('State Name may not be greater than 100 characters');status=false;
        }
        if(stateCode==""){
            $('#txtMStateCode-err').html('State Code is required');status=false;
        }
        return status;
    }
    const getStates=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let countryID=$('#'+Country).val();
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a State</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/states",
            beforeSend:async()=>{
                $('#btnReloadState i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:countryID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadState i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.StateID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.StateID+'">'+Item.StateName+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });
    }
    const CreateStateForm=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let countryID=$('#'+Country).val();
        $.ajax({
            type:"post",
            url:RootUrl+"states/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:countryID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New State',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnAddState').click(function(){
        let id=$(this).parent().attr('for');
        CreateStateForm(id);
    });
    $('#btnReloadState').click(function(){
        let id=$(this).parent().attr('for');
        getStates(id);
    });
    $(document).on('click','#btnCreateState',async function(){
        let status=await validateState();
        if(status==true){
            let formData={};
            formData.CountryID=$('#lstMCountry').val();
            formData.StateName=$('#txtMStateName').val();
            formData.StateCode=$('#txtMStateCode').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this State",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateState'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"states/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateState'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadState').length>0){
                                $('#btnReloadState').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-State-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="StateName"){$('#txtMStateName-err').html(KeyValue);}
                                    if(key=="StateCode"){$('#txtMStateCode-err').html(KeyValue);}
                                    if(key=="CountryID"){$('#txtMCountryName-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Districts  **********/
    const validateDistrict=async()=>{
        let status=true;
        $('.New-District-err').html('');
        let CountryName=$('#lstMCountry').val();
        let StateName=$('#lstMState').val();
        let District=$('#txtMDistrictName').val();

        if(CountryName==""){
            $('#lstMCountry-err').html('Country Name is required');status=false;
        }
        if(StateName==""){
            $('#lstMState-err').html('State Name is required');status=false;
        }
        if(District==""){
            $('#txtMDistrictName-err').html('District Name is required');status=false;
        }else if(District.length<3){
            $('#txtMDistrictName-err').html('District Name must be atleast 3 characters');status=false;
        }else if(District.length>20){
            $('#txtMDistrictName-err').html('District Name may not be greater than 20 characters');status=false;
        }
        return status;
    }
    const getDistricts=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a District</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/districts",
            beforeSend:async()=>{
                $('#btnReloadDistrict i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadDistrict i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.DistrictID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.DistrictID+'">'+Item.DistrictName+' </option>');
                }
            }
        });
        $('#'+elem).select2({
            tags:$('#'+elem).attr('data-tag')!=undefined?$('#'+elem).attr('data-tag'):false,
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });
        if($('#'+elem).val()!=""){
            $('#'+elem).trigger('change');
        }
    }
    const createDistrictForm=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        $.ajax({
            type:"post",
            url:RootUrl+"districts/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New District',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadDistrict').click(function(){
        let id=$(this).parent().attr('for');
        getDistricts(id);
    });
    $('#btnAddDistrict').click(function(){
        let id=$(this).parent().attr('for');
        createDistrictForm(id);
    });
    $(document).on('click','#btnCreateDistrict',async function(){
        let status=await validateDistrict();
        if(status==true){
            let formData={};
            formData.CountryID=$('#lstMCountry').val();
            formData.StateID=$('#lstMState').val();
            formData.DistrictName=$('#txtMDistrictName').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this District",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateDistrict'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"districts/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateDistrict'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadDistrict').length>0){
                                $('#btnReloadDistrict').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-District-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="DistrictName"){$('#txtMDistrictName-err').html(KeyValue);}
                                    if(key=="StateID"){$('#lstMState-err').html(KeyValue);}
                                    if(key=="CountryID"){$('#txtMCountryName-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Taluks  **********/
    const validateTaluk=async()=>{
        let status=true;
        $('.New-Taluk-err').html('');
        let CountryName=$('#lstMCountry').val();
        let StateName=$('#lstMState').val();
        let DistrictName=$('#lstMDistrict').val();
        let Taluk=$('#txtMTalukName').val();

        if(CountryName==""){
            $('#lstMCountry-err').html('Country Name is required');status=false;
        }
        if(StateName==""){
            $('#lstMState-err').html('State Name is required');status=false;
        }
        if(DistrictName==""){
            $('#lstMDistrict-err').html('District Name is required');status=false;
        }
        if(Taluk==""){
            $('#txtMTalukName-err').html('Taluk Name is required');status=false;
        }else if(Taluk.length<3){
            $('#txtMTalukName-err').html('Taluk Name must be atleast 3 characters');status=false;
        }else if(Taluk.length>20){
            $('#txtMTalukName-err').html('Taluk Name may not be greater than 20 characters');status=false;
        }
        return status;
    }
    const getTaluks=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        let District=$('#'+elem).attr('data-district-id');
        let DistrictID=$('#'+District).val();
        let isModal=$('#'+elem).attr('data-parent');

        let selected=$('#'+elem).attr('data-selected');
        if(selected==undefined){selected="";}
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Taluk</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/taluks",
            beforeSend:async()=>{
                $('#btnReloadTaluk i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID,DistrictID:DistrictID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadTaluk i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.TalukID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.TalukID+'">'+Item.TalukName+' </option>');
                }
            }
        });
        $('#'+elem).select2({
            tags:$('#'+elem).attr('data-tag')!=undefined?$('#'+elem).attr('data-tag'):false,
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });
    }
    const createTalukForm=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        let District=$('#'+elem).attr('data-district-id');
        let DistrictID=$('#'+District).val();
        $.ajax({
            type:"post",
            url:RootUrl+"taluks/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID,DistrictID:DistrictID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Taluk',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadTaluk').click(function(){
        let id=$(this).parent().attr('for');
        getTaluks(id);
    });
    $('#btnAddTaluk').click(function(){
        let id=$(this).parent().attr('for');
        createTalukForm(id);
    });
    $(document).on('click','#btnCreateTaluk',async function(){
        let status=await validateTaluk();
        if(status==true){
            let formData={};
            formData.CountryID=$('#lstMCountry').val();
            formData.StateID=$('#lstMState').val();
            formData.DistrictID=$('#lstMDistrict').val();
            formData.TalukName=$('#txtMTalukName').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Taluk",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateTaluk'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"taluks/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateTaluk'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadTaluk').length>0){
                                $('#btnReloadTaluk').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Taluk-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="TalukName"){$('#txtMTalukName-err').html(KeyValue);}
                                    if(key=="DistrictID"){$('#lstMDistrict-err').html(KeyValue);}
                                    if(key=="StateID"){$('#lstMState-err').html(KeyValue);}
                                    if(key=="CountryID"){$('#txtMCountryName-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** City  **********/
    const validateCity=async()=>{
        let status=true;
        $('.New-City-err').html('');
        let CountryName=$('#lstMCountry').val();
        let StateName=$('#lstMState').val();
        let DistrictName=$('#lstMDistrict').val();
        let TalukName=$('#lstMTaluk').val();
        let PostalCode=$('#lstMPostalCode').val();
        let City=$('#txtMCityName').val();

        if(CountryName==""){
            $('#lstMCountry-err').html('Country Name is required');status=false;
        }
        if(StateName==""){
            $('#lstMState-err').html('State Name is required');status=false;
        }
        if(DistrictName==""){
            $('#lstMDistrict-err').html('District Name is required');status=false;
        }
        if(TalukName==""){
            $('#lstMTaluk-err').html('Taluk Name is required');status=false;
        }
        if(PostalCode==""){
            $('#lstMPostalCode-err').html('PostalCode is required');status=false;
        }
        if(City==""){
            $('#txtMCityName-err').html('City Name is required');status=false;
        }else if(City.length<3){
            $('#txtMCityName-err').html('City Name must be atleast 3 characters');status=false;
        }else if(City.length>20){
            $('#txtMCityName-err').html('City Name may not be greater than 20 characters');status=false;
        }
        return status;
    }
    const getCity=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        let District=$('#'+elem).attr('data-district-id');
        let DistrictID=$('#'+District).val();
        let Taluk=$('#'+elem).attr('data-taluk-id');
        let TalukID=$('#'+Taluk).val();
        let PostalCode=$('#'+elem).attr('data-postalcode-id');
        let PID=$('#'+PostalCode).val();
        let isModal=$('#'+elem).attr('data-parent');

        let selected=$('#'+elem).attr('data-selected');
        if(selected==undefined){selected="";}
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a City</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/city",
            beforeSend:async()=>{
                $('#btnReloadCity i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID,DistrictID:DistrictID,TalukID:TalukID,PID:PID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadCity i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.CityID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.CityID+'">'+Item.CityName+' </option>');
                }
            }
        });
        $('#'+elem).select2({
            tags:$('#'+elem).attr('data-tag')!=undefined?$('#'+elem).attr('data-tag'):false,
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });
    }
    const createCityForm=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        let District=$('#'+elem).attr('data-district-id');
        let DistrictID=$('#'+District).val();
        let Taluk=$('#'+elem).attr('data-taluk-id');
        let TalukID=$('#'+Taluk).val();
        let PostalCode=$('#'+elem).attr('data-postalcode-id');
        let PID=$('#'+PostalCode).val();
        $.ajax({
            type:"post",
            url:RootUrl+"city/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID,DistrictID:DistrictID,TalukID:TalukID,PID:PID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New City',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadCity').click(function(){
        let id=$(this).parent().attr('for');
        getCity(id);
    });
    $('#btnAddCity').click(function(){
        let id=$(this).parent().attr('for');
        createCityForm(id);
    });
    $(document).on('click','#btnCreateCity',async function(){
        let status=await validateCity();
        if(status==true){
            let formData={};
            formData.CountryID=$('#lstMCountry').val();
            formData.StateID=$('#lstMState').val();
            formData.DistrictID=$('#lstMDistrict').val();
            formData.TalukID=$('#lstMTaluk').val();
            formData.PID=$('#lstMPostalCode').val();
            formData.CityName=$('#txtMCityName').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this City",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateCity'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"city/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateCity'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadCity').length>0){
                                $('#btnReloadCity').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-City-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="CityName"){$('#txtMCityName-err').html(KeyValue);}
                                    if(key=="DistrictID"){$('#lstMDistrict-err').html(KeyValue);}
                                    if(key=="TalukID"){$('#lstMTaluk-err').html(KeyValue);}
                                    if(key=="PID"){$('#lstMPostalCode-err').html(KeyValue);}
                                    if(key=="StateID"){$('#lstMState-err').html(KeyValue);}
                                    if(key=="CountryID"){$('#txtMCountryName-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Postal Code  **********/
    const validatePostalCode=async()=>{
        let status=true;
        $('.New-PostalCode-err').html('');
        let CountryName=$('#lstMCountry').val();
        let StateName=$('#lstMState').val();
        let DistrictName=$('#lstMDistrict').val();
        let PostalCode=$('#txtMPostalCode').val();

        if(CountryName==""){
            $('#lstMCountry-err').html('Country Name is required');status=false;
        }
        if(StateName==""){
            $('#lstMState-err').html('State Name is required');status=false;
        }
        if(DistrictName==""){
            $('#lstMDistrict-err').html('District Name is required');status=false;
        }
        if(PostalCode==""){
            $('#txtMPostalCode-err').html('Postal Code is required');status=false;
        }else if(PostalCode.length !== 6){
            $('#txtMPostalCode-err').html('Postal Code must be 6 characters');status=false;
        }
        return status;
    }
    const getPostalCode=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        let District=$('#'+elem).attr('data-district-id');
        let DistrictID=$('#'+District).val();
        let isModal=$('#'+elem).attr('data-parent');

        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Postal Code</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/postal-code",
            beforeSend:async()=>{
                $('#btnReloadPostalCode i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID,DistrictID:DistrictID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadPostalCode i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.PID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.PID+'">'+Item.PostalCode+' </option>');
                }
            }
        });
        $('#'+elem).select2({
            tags:$('#'+elem).attr('data-tag')!=undefined?$('#'+elem).attr('data-tag'):false,
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });
    }
    const createPostalCodeForm=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let CountryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();
        let District=$('#'+elem).attr('data-district-id');
        let DistrictID=$('#'+District).val();
        $.ajax({
            type:"post",
            url:RootUrl+"postal-code/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:CountryID,StateID:StateID,DistrictID:DistrictID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Postal Code',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadPostalCode').click(function(){
        let id=$(this).parent().attr('for');
        getPostalCode(id);
    });
    $('#btnAddPostalCode').click(function(){
        let id=$(this).parent().attr('for');
        createPostalCodeForm(id);
    });
    $(document).on('click','#btnCreatePostalCode',async function(){
        let status=await validatePostalCode();
        if(status==true){
            let formData={};
            formData.CountryID=$('#lstMCountry').val();
            formData.StateID=$('#lstMState').val();
            formData.DistrictID=$('#lstMDistrict').val();
            formData.PostalCode=$('#txtMPostalCode').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Postal Code",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreatePostalCode'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"postal-code/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreatePostalCode'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadPostalCode').length>0){
                                $('#btnReloadPostalCode').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-PostalCode-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="PostalCode"){$('#txtMPostalCode-err').html(KeyValue);}
                                    if(key=="DistrictID"){$('#lstMDistrict-err').html(KeyValue);}
                                    if(key=="StateID"){$('#lstMState-err').html(KeyValue);}
                                    if(key=="CountryID"){$('#txtMCountryName-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Gender  **********/
    const validateGender=async()=>{
        let status=true;
        $('.New-Gender-err').html('');
        let Gender=$('#txtMGender').val();

        if(Gender==""){
            $('#txtMGender-err').html('Gender is required');status=false;
        }else if(Gender.length<3){
            $('#txtMGender-err').html('Gender must be atleast 3 characters');status=false;
        }else if(Gender.length>20){
            $('#txtMGender-err').html('Gender may not be greater than 20 characters');status=false;
        }
        return status;
    }
    const getGender=async(elem)=>{
        let Country=$('#'+elem).attr('data-country-id');
        let countryID=$('#'+Country).val();
        let State=$('#'+elem).attr('data-state-id');
        let StateID=$('#'+State).val();

        let selected=$('#'+elem).attr('data-selected');
        if(selected==undefined){selected="";}
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a gender</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/gender",
            beforeSend:async()=>{
                $('#btnReloadPostalCode i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{CountryID:countryID,StateID:StateID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajax_errors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadPostalCode i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.GID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.GID+'">'+Item.Gender+' </option>');
                }
            }
        });
        $('#'+elem).select2();
    }
    const createGenderForm=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"gender/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Gender',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadGender').click(function(){
        let id=$(this).parent().attr('for');
        getGender(id);
    });
    $('#btnAddGender').click(function(){
        let id=$(this).parent().attr('for');
        createGenderForm(id);
    });
    $(document).on('click','#btnCreateGender',async function(){
        let status=await validateGender();
        if(status==true){
            let formData={};
            formData.Gender=$('#txtMGender').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Gender",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateGender'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"gender/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateGender'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadGender').length>0){
                                $('#btnReloadGender').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Gender-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="Gender"){$('#txtMGender-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });
    //AddressOnChangeEvents

    $(document).on('change','#lstMCountry',function(){
        getStates('lstMState');
    });
    $(document).on('change','#lstMState',function(){
        getDistricts('lstMDistrict');
    });
    $(document).on('change','#lstMDistrict',function(){
        getTaluks('lstMTaluk');
    });
    $(document).on('change','#lstCountry',function(){
        getStates('lstState');
    });
    $(document).on('change','#lstState',function(){
        getDistricts('lstDistrict');
    });
    // $(document).on('change','#lstDistrict',function(){
    //     getTaluks('lstTaluk');
    // });
    /* $(document).on('change','#lstMTaluk',function(){
        getCity('lstMCity');
    }); */
    $(document).on('click','#btnCloseModal',function(){
        bootbox.hideAll();
    });
    $(document).on('change','#lstMVehicleType',function(){
        getVehicleBrand('lstMVehicleBrand');
    });

    //Vendor Category
    const validateVCategory=async()=>{
        $('.errors.new-category').html('');
        let status=true;
        let VCName=$('#txtMVCName').val();
        if(VCName==""){
            $('#txtMVCName-err').html('The Vendor Category Name is required.');status=false;
        }else if(VCName.length<2){
            $('#txtMVCName-err').html('Vendor Category Name must be greater than 2 characters');status=false;
        }else if(VCName.length>100){
            $('#txtMVCName-err').html('Vendor Category Name may not be greater than 100 characters');status=false;
        }
        if(status==false){$("html, body").animate({ scrollTop: 0 }, "slow");}
        return status;
    }
    const getVCategory=async(elem)=>{
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="" selected>Select a Vendor Category</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"master/vendor/category/get/VCategory",
            beforeSend:async()=>{
                $('#btnReloadVCategory i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadVCategory i').removeClass('fa-spin');
                }, 500);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.VCID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.VCID+'">'+Item.VCName+' </option>');
                }
            }
        });
        $('#'+elem).select2();
        if($('#'+elem).val()!=""){$('#'+elem).trigger('change');}
    }
    const createVCategory=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"master/vendor/category/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{},
            async:true,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Vendor Category',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadVCategory').click(function(){
        let id=$(this).parent().attr('for');
        getVCategory(id);
    });
    $('#btnAddVCategory').click(function(){
        let id=$(this).parent().attr('for');
        createVCategory(id);
    });
    $(document).on('click','#btnCreateVCategory',async function(){
        let status=await validateVCategory();
        if(status==true){
            let formData=new FormData();
            formData.append('VCName',$('#txtMVCName').val());
            formData.append('ActiveStatus',$('#lstMActiveStatus').val());
            if($('#txtMVCImage').val()!=""){
                formData.append('VCImage', $('#txtMVCImage')[0].files[0]);
            }
            swal({
                title: "Are you sure?",
                text: "Do you want add this Vendor Category",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateVCategory'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"master/vendor/category/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:true,
                    dataType:"json",
                    cache: false,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                percentComplete=parseFloat(percentComplete).toFixed(2);
                                $('#divProcessText').html(percentComplete+'% Completed.<br> Please wait for until upload process complete.');
                                //Do something with upload progress here
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function() {
                        ajaxIndicatorStart("Please wait Upload Process on going.");

                        var percentVal = '0%';
                        setTimeout(() => {
                        $('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
                        }, 100);
                    },
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateVCategory'));ajaxIndicatorStop();},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadVCategory').length>0){
                                $('#btnReloadVCategory').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Gender-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="VCName"){$('#txtMVCName-err').html(KeyValue);}
                                    if(key=="VCImage"){$('#txtMVCImage-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    //Vendor Sub Category
    const validateVSubCategory=async()=>{
        $('.errors.new-sub-category').html('');
        let status=true;
        let VCID=$('#lstMVCategory').val();
        let VSCName=$('#txtMVSCName').val();
        if(VSCName==""){
            $('#txtMVSCName-err').html('The Vendor Sub Category Name is required.');status=false;
        }else if(VSCName.length<2){
            $('#txtMVSCName-err').html('Vendor Sub Category Name must be greater than 2 characters');status=false;
        }else if(VSCName.length>100){
            $('#txtMVSCName-err').html('Vendor Sub Category Name may not be greater than 100 characters');status=false;
        }
        if(VCID==""){
            $('#lstMVCategory-err').html('The Vendor Category is required.');status=false;
        }
        if(status==false){$("html, body").animate({ scrollTop: 0 }, "slow");}
        return status;
    }
    const getVSubCategory=async(elem)=>{
        let VCategory = $('#'+elem).attr("data-vcategory-id");
        let VCID=$('#'+VCategory).val();
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="" selected>Select a Vendor Sub Category</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"master/vendor/sub-category/get/VSubCategory",
            beforeSend:async()=>{
                $('#btnReloadVSubCategory i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            data:{VCID:VCID},
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadVSubCategory i').removeClass('fa-spin');
                }, 500);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.VSCID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.VSCID+'">'+Item.VSCName+' </option>');
                }
            }
        });
        $('#'+elem).select2();
        if($('#'+elem).val()!=""){$('#'+elem).trigger('change');}
    }
    const createVSubCategory=async(elem)=>{
        let VCategory=$('#'+elem).attr('data-vcategory-id');
        let VCID=$('#'+VCategory).val();
        $.ajax({
            type:"post",
            url:RootUrl+"master/vendor/sub-category/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{VCID:VCID},
            async:true,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Vendor Sub Category',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadVSubCategory').click(function(){
        let id=$(this).parent().attr('for');
        getVSubCategory(id);
    });
    $('#btnAddVSubCategory').click(function(){
        let id=$(this).parent().attr('for');
        createVSubCategory(id);
    });
    $(document).on('click','#btnCreateVSubCategory',async function(){
        let status=await validateVSubCategory();
        if(status==true){
            let formData=new FormData();
            formData.append('VCategory',$('#lstMVCategory').val());
            formData.append('VSCName',$('#txtMVSCName').val());
            formData.append('ActiveStatus',$('#lstMActiveStatus').val());
            if($('#txtMVSCImage').val()!=""){
                formData.append('VSCImage', $('#txtMVSCImage')[0].files[0]);
            }
            swal({
                title: "Are you sure?",
                text: "Do you want add this Vendor Sub Category",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateVSubCategory'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"master/vendor/sub-category/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:true,
                    dataType:"json",
                    cache: false,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                percentComplete=parseFloat(percentComplete).toFixed(2);
                                $('#divProcessText').html(percentComplete+'% Completed.<br> Please wait for until upload process complete.');
                                //Do something with upload progress here
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function() {
                        ajaxIndicatorStart("Please wait Upload Process on going.");

                        var percentVal = '0%';
                        setTimeout(() => {
                        $('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
                        }, 100);
                    },
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateVSubCategory'));ajaxIndicatorStop();},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadVSubCategory').length>0){
                                $('#btnReloadVSubCategory').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Gender-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="VSCName"){$('#txtMVSCName-err').html(KeyValue);}
                                    if(key=="VSCImage"){$('#txtMVSCImage-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    //Product Category
    const validatePCategory=async()=>{
        $('.errors.new-category').html('');
        let status=true;
        let PCName=$('#txtMPCName').val();
        if(PCName==""){
            $('#txtMPCName-err').html('The Product Category Name is required.');status=false;
        }else if(PCName.length<2){
            $('#txtMPCName-err').html('Product Category Name must be greater than 2 characters');status=false;
        }else if(PCName.length>100){
            $('#txtMPCName-err').html('Product Category Name may not be greater than 100 characters');status=false;
        }
        if(status==false){$("html, body").animate({ scrollTop: 0 }, "slow");}
        return status;
    }
    const getPCategory=async(elem)=>{
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="" selected>Select a Product Category</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"admin/master/product/category/get/PCategory",
            beforeSend:async()=>{
                $('#btnReloadPCategory i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadPCategory i').removeClass('fa-spin');
                }, 500);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.PCID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.PCID+'">'+Item.PCName+' </option>');
                }
            }
        });
        $('#'+elem).select2();
        if($('#'+elem).val()!=""){$('#'+elem).trigger('change');}
    }
    const createPCategory=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"admin/master/product/category/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{},
            async:true,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Product Category',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadPCategory').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        getPCategory(id);
    });
    $('#btnAddPCategory').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        createPCategory(id);
    });
    $(document).on('click','#btnCreatePCategory',async function(){
        let status=await validatePCategory();
        if(status==true){
            let formData=new FormData();
            formData.append('PCName',$('#txtMPCName').val());
            formData.append('ActiveStatus',$('#lstMActiveStatus').val());
            if($('#txtMPCImage').val()!=""){
                formData.append('PCImage', $('#txtMPCImage')[0].files[0]);
            }
            swal({
                title: "Are you sure?",
                text: "Do you want add this Product Category",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreatePCategory'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"admin/master/product/category/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:true,
                    dataType:"json",
                    cache: false,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                percentComplete=parseFloat(percentComplete).toFixed(2);
                                $('#divProcessText').html(percentComplete+'% Completed.<br> Please wait for until upload process complete.');
                                //Do something with upload progress here
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function() {
                        ajaxIndicatorStart("Please wait Upload Process on going.");

                        var percentVal = '0%';
                        setTimeout(() => {
                        $('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
                        }, 100);
                    },
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreatePCategory'));ajaxIndicatorStop();},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadPCategory').length>0){
                                $('#btnReloadPCategory').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Gender-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="PCName"){$('#txtMPCName-err').html(KeyValue);}
                                    if(key=="PCImage"){$('#txtMPCImage-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    //Product Sub Category
    const validatePSubCategory=async()=>{
        $('.errors.new-sub-category').html('');
        let status=true;
        let PCID=$('#lstMPCategory').val();
        let PSCName=$('#txtMPSCName').val();
        if(PSCName==""){
            $('#txtMPSCName-err').html('The Product Sub Category Name is required.');status=false;
        }else if(PSCName.length<2){
            $('#txtMPSCName-err').html('Product Sub Category Name must be greater than 2 characters');status=false;
        }else if(PSCName.length>100){
            $('#txtMPSCName-err').html('Product Sub Category Name may not be greater than 100 characters');status=false;
        }
        if(PCID==""){
            $('#lstMPCategory-err').html('The Product Category is required.');status=false;
        }
        if(status==false){$("html, body").animate({ scrollTop: 0 }, "slow");}
        return status;
    }
    const getPSubCategory=async(elem)=>{
        let PCategory = $('#'+elem).attr("data-category-id");
        let PCID=$('#'+PCategory).val();
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="" selected>Select a Product Sub Category</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"admin/master/product/sub-category/get/PSubCategory",
            beforeSend:async()=>{
                $('#btnReloadPSubCategory i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            data:{PCID:PCID},
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadPSubCategory i').removeClass('fa-spin');
                }, 500);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.PSCID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.PSCID+'">'+Item.PSCName+' </option>');
                }
            }
        });
        $('#'+elem).select2();
        if($('#'+elem).val()!=""){$('#'+elem).trigger('change');}
    }
    const createPSubCategory=async(elem)=>{
        let PCategory=$('#'+elem).attr('data-category-id');
        let PCID=$('#'+PCategory).val();
        $.ajax({
            type:"post",
            url:RootUrl+"admin/master/product/sub-category/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{PCID:PCID},
            async:true,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Product Sub Category',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadPSubCategory').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        getPSubCategory(id);
    });
    $('#btnAddPSubCategory').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        createPSubCategory(id);
    });
    $(document).on('click','#btnCreatePSubCategory',async function(){
        let status=await validatePSubCategory();
        if(status==true){
            let formData=new FormData();
            formData.append('PCategory',$('#lstMPCategory').val());
            formData.append('PSCName',$('#txtMPSCName').val());
            formData.append('ActiveStatus',$('#lstMActiveStatus').val());
            if($('#txtMPSCImage').val()!=""){
                formData.append('PSCImage', $('#txtMPSCImage')[0].files[0]);
            }
            swal({
                title: "Are you sure?",
                text: "Do you want add this Product Sub Category",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreatePSubCategory'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"admin/master/product/sub-category/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:true,
                    dataType:"json",
                    cache: false,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                percentComplete=parseFloat(percentComplete).toFixed(2);
                                $('#divProcessText').html(percentComplete+'% Completed.<br> Please wait for until upload process complete.');
                                //Do something with upload progress here
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function() {
                        ajaxIndicatorStart("Please wait Upload Process on going.");
                        var percentVal = '0%';
                        setTimeout(() => {
                        $('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
                        }, 100);
                    },
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreatePSubCategory'));ajaxIndicatorStop();},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadPSubCategory').length>0){
                                $('#btnReloadPSubCategory').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Gender-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="PSCName"){$('#txtMPSCName-err').html(KeyValue);}
                                    if(key=="PSCImage"){$('#txtMPSCImage-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    //Tax
    const validateTax=async()=>{
        $('.errors.new-tax').html('');
        let status=true;
        let TaxName=$('#txtMTaxName').val();
        let Percentage=$('#txtMPercentage').val();
        if(TaxName==""){
            $('#txtMTaxName-err').html('The Tax Name is required.');status=false;
        } else if(TaxName.length<2){
            $('#txtMTaxName-err').html('Tax Name must be greater than 2 characters');status=false;
        }else if(TaxName.length>100){
            $('#txtMTaxName-err').html('Tax Name may not be greater than 100 characters');status=false;
        }

        if(Percentage==""){
            $('#txtMPercentage-err').html('The Percentage is required.');status=false;
        }else if($.isNumeric(Percentage)==false){
            $('#txtMPercentage-err').html('The Percentage is must be numeric.');status=false;
        }else if(Percentage.length>100){
            $('#txtMPercentage-err').html('Percentage may not be greater than 100 characters');status=false;
        }
        return status;
    }
    const getTax=async(elem)=>{
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="" selected>Select a Tax</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/tax",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.TaxID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.TaxID+'">'+Item.TaxName+' ('+NumberFormat(Item.TaxPercentage,'percentage')+') </option>');
                    if($('#'+elem).val()!=""){$('#'+elem).trigger('change');}
                }
            }
        });
        $('#'+elem).select2();
    }
    const createTax=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"tax/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{},
            async:true,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Tax',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadTax').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        getTax(id);
    });
    $('#btnAddTax').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        createTax(id);
    });
    $(document).on('click','#btnCreateTax',async function(){
        let status=await validateTax();
        if(status==true){
            let formData=new FormData();
            formData.append('TaxName',$('#txtMTaxName').val());
            formData.append('Percentage',$('#txtMPercentage').val());
            formData.append('ActiveStatus',$('#lstMActiveStatus').val());
            swal({
                title: "Are you sure?",
                text: "Do you want add this Tax",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateTax'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"admin/master/product/tax/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:true,
                    dataType:"json",
                    cache: false,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                percentComplete=parseFloat(percentComplete).toFixed(2);
                                $('#divProcessText').html(percentComplete+'% Completed.<br> Please wait for until upload process complete.');
                                //Do something with upload progress here
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function() {
                        ajaxIndicatorStart("Please wait Upload Process on going.");

                        var percentVal = '0%';
                        setTimeout(() => {
                        $('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
                        }, 100);
                    },
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateTax'));ajaxIndicatorStop();},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadTax').length>0){
                                $('#btnReloadTax').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.new-tax').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="TaxName"){$('#txtMTaxName-err').html(KeyValue);}
                                    if(key=="Percentage"){$('#txtMPercentage-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    //UOM
    const validateUOM=async()=>{
        $('.errors.new-uom').html('');
        let status=true;
        let UCode=$('#txtMUCode').val();
        let UName =$('#txtMUName').val();
        if(UCode==""){
            $('#txtMUCode-err').html('Unit Code  is required.');status=false;
        }else if(UCode.length>100){
            $('#txtMUCode-err').html('Unit Code  may not be greater than 100 characters');status=false;
        }
        if(UName==''){
            $('#txtMUName-err').html('The Unit Name name is required.');status=false;
        }
        return status;
    }
    const getUOM=async(elem)=>{
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="" selected>Select a UOM</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/uom",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            dataType:"json",
            async:true,
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete: function(e, x, settings, exception){},
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.UID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.UID+'">'+Item.UName+' ('+Item.UCode+') </option>');
                    if($('#'+elem).val()!=""){$('#'+elem).trigger('change');}
                }
            }
        });
        $('#'+elem).select2();
    }
    const createUOM=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"uom/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{},
            async:true,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Unit of Measurement',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $('#btnReloadUOM').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        getUOM(id);
    });
    $('#btnAddUOM').click(function(){
        let id=$(this).closest('.row').find('select').attr('id');
        createUOM(id);
    });
    $(document).on('click','#btnCreateUOM',async function(){
        let status=await validateUOM();
        if(status==true){
            let formData=new FormData();
            formData.append('UCode',$('#txtMUCode').val());
            formData.append('UName',$('#txtMUName').val());
            formData.append('ActiveStatus',$('#lstMActiveStatus').val());
            swal({
                title: "Are you sure?",
                text: "Do you want add this UOM",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateUOM'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"admin/master/product/unit-of-measurement/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:true,
                    dataType:"json",
                    cache: false,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = (evt.loaded / evt.total) * 100;
                                percentComplete=parseFloat(percentComplete).toFixed(2);
                                $('#divProcessText').html(percentComplete+'% Completed.<br> Please wait for until upload process complete.');
                                //Do something with upload progress here
                            }
                        }, false);
                        return xhr;
                    },
                    beforeSend: function() {
                        ajaxIndicatorStart("Please wait Upload Process on going.");

                        var percentVal = '0%';
                        setTimeout(() => {
                        $('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
                        }, 100);
                    },
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateUOM'));ajaxIndicatorStop();},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadUOM').length>0){
                                $('#btnReloadUOM').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('errors.new-uom').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="UCode"){$('#txtMUCode-err').html(KeyValue);}
                                    if(key=="UName"){$('#txtMUName-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Vehicle Type  **********/
    const validateVehicleType=async()=>{
        let status=true;
        $('.New-VehicleType-err').html('');
        let VehicleType=$('#txtMVehicleType').val();

        if(VehicleType==""){
            $('#txtMVehicleType-err').html('Vehicle Type is required');status=false;
        }else if(VehicleType.length<3){
            $('#txtMVehicleType-err').html('Vehicle Type must be atleast 3 characters');status=false;
        }else if(VehicleType.length>100){
            $('#txtMVehicleType-err').html('Vehicle Type may not be greater than 100 characters');status=false;
        }
        return status;
    }
    const createVehicleTypeForm=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"vehicle-type/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Vehicle Type',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    const getVehicleType=async(elem)=>{
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Vehicle Type</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/vehicle-type",
            beforeSend:async()=>{
                $('#btnReloadVehicleType i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadVehicleType i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.VehicleTypeID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.VehicleTypeID+'">'+Item.VehicleType+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });

    }
    $(document).on('click','.btnReloadVehicleType',async function(){
        let id=$(this).parent().attr('for');
        getVehicleType(id);
    });
    $(document).on('click','.btnAddVehicleType',async function(){
        let id=$(this).parent().attr('for');
        createVehicleTypeForm(id);
    });
    $(document).on('click','#btnCreateVehicleType',async function(){
        let status=await validateVehicleType();
        if(status==true){
            let formData={};
            formData.VehicleType=$('#txtMVehicleType').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Vehicle Type",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateVehicleType'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"vehicle-type/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateVehicleType'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('.btnReloadVehicleType').length>0){
                                $('.btnReloadVehicleType').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-VehicleType-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="VehicleType"){$('#txtMVehicleType-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Vehicle Brand  **********/
    const validateVehicleBrand=async()=>{
        let status=true;
        $('.New-VehicleBrand-err').html('');
        let VehicleType=$('#lstMVehicleType').val();
        let VehicleBrandName=$('#txtMVehicleBrandName').val();

        if(VehicleType==""){
            $('#lstMVehicleType-err').html('Vehicle Type is required');status=false;
        }
        if(VehicleBrandName==""){
            $('#txtMVehicleBrandName-err').html('Vehicle Brand Name is required');status=false;
        }else if(VehicleBrandName.length<3){
            $('#txtMVehicleBrandName-err').html('Vehicle Brand Name must be atleast 3 characters');status=false;
        }else if(VehicleBrandName.length>100){
            $('#txtMVehicleBrandName-err').html('Vehicle Brand Name may not be greater than 100 characters');status=false;
        }
        return status;
    }
    const getVehicleBrand=async(elem)=>{
        let VehicleType=$('#'+elem).attr('data-vehicle-type-id');
        let vehicleTypeID=$('#'+VehicleType).val();
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Vehicle Brand</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/vehicle-brand",
            beforeSend:async()=>{
                $('#btnReloadVehicleBrand i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{VehicleTypeID:vehicleTypeID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadVehicleBrand i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.VehicleBrandID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.VehicleBrandID+'">'+Item.VehicleBrandName+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });
    }
    const CreateVehicleBrandForm=async(elem)=>{
        let VehicleType=$('#'+elem).attr('data-vehicle-type-id');
        let vehicleTypeID=$('#'+VehicleType).val();
        $.ajax({
            type:"post",
            url:RootUrl+"vehicle-brand/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{VehicleTypeID:vehicleTypeID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Vehicle Brand',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $(document).on('click','.btnAddVehicleBrand',async function(){
        let id=$(this).parent().attr('for');
        CreateVehicleBrandForm(id);
    });
    $(document).on('click','.btnReloadVehicleBrand',async function(){
        let id=$(this).parent().attr('for');
        getVehicleBrand(id);
    });
    $(document).on('click','#btnCreateVehicleBrand',async function(){
        let status=await validateVehicleBrand();
        if(status==true){
            let formData={};
            formData.VehicleTypeID=$('#lstMVehicleType').val();
            formData.VehicleBrandName=$('#txtMVehicleBrandName').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Vehicle Brand",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateVehicleBrand'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"vehicle-brand/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateVehicleBrand'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('.btnReloadVehicleBrand').length>0){
                                $('.btnReloadVehicleBrand').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-VehicleBrand-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="VehicleBrandName"){$('#txtMVehicleBrandName-err').html(KeyValue);}
                                    if(key=="VehicleTypeID"){$('#txtMVehicleType-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Vehicle Model  **********/
    const validateVehicleModel=async()=>{
        let status=true;
        $('.New-VehicleModel-err').html('');
        let VehicleType=$('#lstMVehicleType').val();
        let VehicleBrand=$('#lstMVehicleBrand').val();
        let VehicleModel=$('#txtMVehicleModel').val();

        if(VehicleType==""){
            $('#lstMVehicleType-err').html('Vehicle Type is required');status=false;
        }
        if(VehicleBrand==""){
            $('#lstMVehicleBrand-err').html('Vehicle Brand is required');status=false;
        }
        if(VehicleModel==""){
            $('#txtMVehicleModel-err').html('Vehicle Model is required');status=false;
        }else if(VehicleModel.length<3){
            $('#txtMVehicleModel-err').html('Vehicle Model must be atleast 3 characters');status=false;
        }else if(VehicleModel.length>100){
            $('#txtMVehicleModel-err').html('Vehicle Model may not be greater than 100 characters');status=false;
        }
        return status;
    }
    const getVehicleModel=async(elem)=>{
        let VehicleType=$('#'+elem).attr('data-vehicle-type-id');
        let vehicleTypeID=$('#'+VehicleType).val();
        let VehicleBrand=$('#'+elem).attr('data-vehicle-brand-id');
        let vehicleBrandID=$('#'+VehicleBrand).val();
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Vehicle Model</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/vehicle-model",
            beforeSend:async()=>{
                $('#btnReloadVehicleModel i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{VehicleTypeID:vehicleTypeID,VehicleBrandID:vehicleBrandID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadVehicleModel i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.VehicleModelID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.VehicleModelID+'">'+Item.VehicleModel+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.bootbox-body') : $('.page-body')
        });
    }
    const CreateVehicleModelForm=async(elem)=>{
        let VehicleType=$('#'+elem).attr('data-vehicle-type-id');
        let vehicleTypeID=$('#'+VehicleType).val();
        let VehicleBrand=$('#'+elem).attr('data-vehicle-brand-id');
        let vehicleBrandID=$('#'+VehicleBrand).val();
        $.ajax({
            type:"post",
            url:RootUrl+"vehicle-model/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{VehicleTypeID:vehicleTypeID,VehicleBrandID:vehicleBrandID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Vehicle Model',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $(document).on('click','.btnAddVehicleModel',async function(){
        let id=$(this).parent().attr('for');
        CreateVehicleModelForm(id);
    });
    $(document).on('click','.btnReloadVehicleModel',async function(){
        let id=$(this).parent().attr('for');
        getVehicleModel(id);
    });
    $(document).on('click','#btnCreateVehicleModel',async function(){
        let status=await validateVehicleModel();
        if(status==true){
            let formData={};
            formData.VehicleTypeID=$('#lstMVehicleType').val();
            formData.VehicleBrandID=$('#lstMVehicleBrand').val();
            formData.VehicleModel=$('#txtMVehicleModel').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Vehicle Model",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateVehicleModel'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"vehicle-model/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateVehicleModel'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('.btnReloadVehicleModel').length>0){
                                $('.btnReloadVehicleModel').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-VehicleModel-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="VehicleModel"){$('#txtMVehicleModel-err').html(KeyValue);}
                                    if(key=="VehicleTypeID"){$('#txtMVehicleType-err').html(KeyValue);}
                                    if(key=="VehicleBrandID"){$('#txtMVehicleBrand-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Bank Type  **********/
    const validateBankType=async()=>{
        let status=true;
        $('.New-BankType-err').html('');
        let BankType=$('#txtMBankType').val();

        if(BankType==""){
            $('#txtMBankType-err').html('Bank Type is required');status=false;
        }else if(BankType.length<3){
            $('#txtMBankType-err').html('Bank Type must be atleast 3 characters');status=false;
        }else if(BankType.length>100){
            $('#txtMBankType-err').html('Bank Type may not be greater than 100 characters');status=false;
        }
        return status;
    }
    const createBankTypeForm=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"bank-type/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Bank Type',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    const getBankType=async(elem)=>{
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Bank Type</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/bank-type",
            beforeSend:async()=>{
                $('#btnReloadBankType i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadBankType i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.SLNO==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.SLNO+'">'+Item.TypeOfBank+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.dynamicValueModal') : $('.page-body')
        });

    }
    $(document).on('click','.btnReloadBankType',async function(){
        let id=$(this).parent().attr('for');
        getBankType(id);
    });
    $(document).on('click','.btnAddBankType',async function(){
        let id=$(this).parent().attr('for');
        createBankTypeForm(id);
    });
    $(document).on('click','#btnCreateBankType',async function(){
        let status=await validateBankType();
        if(status==true){
            let formData={};
            formData.BankType=$('#txtMBankType').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Bank Type",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateBankType'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"bank-type/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateBankType'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadBankType').length>0){
                                $('#btnReloadBankType').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-BankType-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="BankType"){$('#txtMBankType-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Bank  **********/
    const validateBank=async()=>{
        let status=true;
        $('.New-Bank-err').html('');
        let BankType=$('#lstMBankType').val();
        let BankName=$('#txtMBankName').val();

        if(BankType==""){
            $('#lstMBankType-err').html('Bank Type is required');status=false;
        }
        if(BankName==""){
            $('#txtMBankName-err').html('Bank Name is required');status=false;
        }else if(BankName.length<3){
            $('#txtMBankName-err').html('Bank Name must be atleast 3 characters');status=false;
        }else if(BankName.length>100){
            $('#txtMBankName-err').html('Bank Name may not be greater than 100 characters');status=false;
        }
        return status;
    }
    const getBank=async(elem)=>{
        let BankType = $('#'+elem).attr('data-bank-type-id');
        let bankTypeID=$('#'+BankType).val();
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Bank</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/bank",
            beforeSend:async()=>{
                $('#btnReloadBank i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{TypeOfBankID:bankTypeID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadBank i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.BankID==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.BankID+'">'+Item.NameOfBanks+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.dynamicValueModal') : $('.page-body')
        });
    }
    const CreateBankForm=async(elem)=>{
        let BankType=$('#'+elem).attr('data-bank-type-id');
        let bankTypeID=$('#'+BankType).val();
        $.ajax({
            type:"post",
            url:RootUrl+"bank/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{BankTypeID:bankTypeID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Bank',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $(document).on('click','.btnAddBank',async function(){
        let id=$(this).parent().attr('for');
        CreateBankForm(id);
    });
    $(document).on('click','.btnReloadBank',async function(){
        let id=$(this).parent().attr('for');
        getBank(id);
    });
    $(document).on('click','#btnCreateBank',async function(){
        let status=await validateBank();
        if(status==true){
            let formData={};
            formData.BankTypeID=$('#lstMBankType').val();
            formData.BankName=$('#txtMBankName').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Bank",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateBank'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"bank/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateBank'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadBank').length>0){
                                $('#btnReloadBank').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-Bank-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="BankName"){$('#txtMBankName-err').html(KeyValue);}
                                    if(key=="BankTypeID"){$('#txtMBankType-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Bank Branch  **********/
    const validateBankBranch=async()=>{
        let status=true;
        $('.New-BankBranch-err').html('');
        let Bank=$('#lstMBank').val();
        let BankBranch=$('#txtMBranchName').val();
        let IFSCCode=$('#txtMIFSCCode').val();
        let MICR=$('#txtMMICR').val();
        let BranchEmail=$('#txtMBranchEmail').val();

        if(Bank==""){
            $('#lstMBank-err').html('Bank is required');status=false;
        }
        if(BankBranch==""){
            $('#txtMBranchName-err').html('Bank Branch is required');status=false;
        }else if(BankBranch.length<3){
            $('#txtMBranchName-err').html('Bank Branch must be atleast 3 characters');status=false;
        }else if(BankBranch.length>100){
            $('#txtMBranchName-err').html('Bank Branch may not be greater than 100 characters');status=false;
        }
        if(IFSCCode==""){
            $('#txtMIFSCCode-err').html('IFSC Code is required');status=false;
        }
        return status;
    }
    const getBankBranch=async(elem)=>{
        let BankType=$('#'+elem).attr('data-bank-type-id');
        let bankTypeID=$('#'+BankType).val();
        let Bank=$('#'+elem).attr('data-bank-id');
        let bankID=$('#'+Bank).val();
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Bank Branch</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/bank-branch",
            beforeSend:async()=>{
                $('#btnReloadBankBranch i').addClass('fa-spin');
            },
            headers: {'X-CSRF-Token' : $('meta[name=_token]').attr('content')},
            data:{BankTypeID:bankTypeID,BankID:bankID},
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadBankBranch i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.SLNO==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+'  value="'+Item.SLNO +'">'+Item.BranchName+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.dynamicValueModal') : $('.page-body')
        });
    }
    const CreateBankBranchForm=async(elem)=>{
        let BankType=$('#'+elem).attr('data-bank-type-id');
        let bankTypeID=$('#'+BankType).val();
        let Bank=$('#'+elem).attr('data-bank-id');
        let bankID=$('#'+Bank).val();
        $.ajax({
            type:"post",
            url:RootUrl+"bank-branch/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content')},
            data:{BankTypeID:bankTypeID,BankID:bankID},
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Bank Branch',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    $(document).on('click','.btnAddBankBranch',async function(){
        let id=$(this).parent().attr('for');
        CreateBankBranchForm(id);
    });
    $(document).on('click','.btnReloadBankBranch',async function(){
        let id=$(this).parent().attr('for');
        getBankBranch(id);
    });
    $(document).on('click','#btnCreateBankBranch',async function(){
        let status=await validateBankBranch();
        if(status==true){
            let formData={};
            formData.BankID=$('#lstMBank').val();
            formData.BankBranch=$('#txtMBranchName').val();
            formData.IFSCCode=$('#txtMIFSCCode').val();
            formData.MICR=$('#txtMMICR').val();
            formData.BranchEmail=$('#txtMBranchEmail').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Bank Branch",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateBankBranch'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"bank-branch/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateBankBranch'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadBankBranch').length>0){
                                $('#btnReloadBankBranch').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-BankBranch-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="BankBranch"){$('#txtMBranchName-err').html(KeyValue);}
                                    if(key=="BankTypeID"){$('#txtMBankType-err').html(KeyValue);}
                                    if(key=="BankID"){$('#txtMBank-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

    /*** Bank Account Type  **********/
    const validateBankAccType=async()=>{
        let status=true;
        $('.New-BankAccType-err').html('');
        let BankAccType=$('#txtMBankAccType').val();

        if(BankAccType==""){
            $('#txtMBankAccType-err').html('Bank Account Type is required');status=false;
        }else if(BankAccType.length<3){
            $('#txtMBankAccType-err').html('Bank Account Type must be atleast 3 characters');status=false;
        }else if(BankAccType.length>100){
            $('#txtMBankAccType-err').html('Bank Account Type may not be greater than 100 characters');status=false;
        }
        return status;
    }
    const createBankAccTypeForm=async(elem)=>{
        $.ajax({
            type:"post",
            url:RootUrl+"bank-account-type/create-form",
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"html",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            success:function(response){
                bootbox.dialog({
                    title: 'Create New Bank Account Type',
                    closeButton: true,
                    message: response,
                    className:"dynamicValueModal",
                    buttons: {
                    }
                });
            }
        })
    }
    const getBankAccType=async(elem)=>{
        let isModal=$('#'+elem).attr('data-parent');
        $('#'+elem).select2('destroy');
        $('#'+elem+' option').remove();
        $('#'+elem).append('<option value="">Select a Bank Account Type</option>');
        $.ajax({
            type:"post",
            url:RootUrl+"get/bank-account-type",
            beforeSend:async()=>{
                $('#btnReloadBankAccType i').addClass('fa-spin');
            },
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            async:false,
            dataType:"json",
            error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
            complete:async()=>{
                setTimeout(() => {
                    $('#btnReloadBankAccType i').removeClass('fa-spin');
                }, 1000);
            },
            success:function(response){
                for(let Item of response){
                    let selected="";
                    if(Item.SLNO==$('#'+elem).attr('data-selected')){selected="selected";}
                    $('#'+elem).append('<option '+selected+' value="'+Item.SLNO+'">'+Item.AccountType+' </option>');
                }
                if($('#'+elem).val()!=""){
                    $('#'+elem).trigger('change');
                }
            }
        })
        $('#'+elem).select2({
            dropdownParent: isModal == 1 ? $('.dynamicValueModal') : $('.page-body')
        });

    }
    $(document).on('click','.btnReloadBankAccType',async function(){
        let id=$(this).parent().attr('for');
        getBankAccType(id);
    });
    $(document).on('click','.btnAddBankAccType',async function(){
        let id=$(this).parent().attr('for');
        createBankAccTypeForm(id);
    });
    $(document).on('click','#btnCreateBankAccType',async function(){
        let status=await validateBankAccType();
        if(status==true){
            let formData={};
            formData.BankAccType=$('#txtMBankAccType').val();
            swal({
                title: "Are you sure?",
                text: "Do you want add this Bank Account Type",
                type: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn-success",
                confirmButtonText: "Yes, Add it!",
                closeOnConfirm: false
            },
            async function(){
                swal.close();
                btnLoading($('#btnCreateBankAccType'));
                $.ajax({
                    type:"post",
                    url:RootUrl+"bank-account-type/create",
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
                    data:formData,
                    async:false,
                    dataType:"json",
                    error:function(e, x, settings, exception){ajaxErrors(e, x, settings, exception);},
                    complete: function(e, x, settings, exception){btnReset($('#btnCreateBankAccType'));},
                    success:function(response){
                        if(response.status==true){
                            toastr.success(response.message, "Success", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            });
                            if( $('#btnCloseModal').length>0){
                                $('#btnCloseModal').trigger('click');
                            }
                            if( $('#btnReloadBankAccType').length>0){
                                $('#btnReloadBankAccType').trigger('click');
                            }
                        }else{
                            toastr.error(response.message, "Failed", {
                                positionClass: "toast-top-right",
                                containerId: "toast-top-right",
                                showMethod: "slideDown",
                                hideMethod: "slideUp",
                                progressBar: !0
                            })
                            if(response['errors']!=undefined){
                                $('.New-BankAccType-err').html('');
                                $.each( response['errors'], function( KeyName, KeyValue ) {
                                    var key=KeyName;
                                    if(key=="BankAccType"){$('#txtMBankAccType-err').html(KeyValue);}
                                });
                            }
                        }
                    }
                })
            });
        }
    });

});
