const NumberFormat=(value,type)=>{
    try {
        if((value=="")||(value==undefined)||(isNaN(parseFloat(value)))){
            value=0;
        }
        let Decimal="auto";
        let settings=$('#divsettings').html();
        if(settings!=""){
            settings=JSON.parse(settings);
        }
        type=type.toString().toLowerCase();
        if(type=="weight"){
            if(settings['weight-decimals']!=undefined){
                Decimal=settings['weight-decimals'];
            }
		}else if(type=="price"){
            if(settings['price-decimals']!=undefined){
                Decimal=settings['price-decimals'];
            }
		}else if(type=="qty"){
            if(settings['qty-decimals']!=undefined){
                Decimal=settings['QTY-decimals'];
            }
		}else if(type=="percentage"){
            if(settings['percentage-decimals']!=undefined){
                Decimal=settings['percentage-decimals'];
            }
		}else{
			Decimal=0;
        }
		if(Decimal!="auto"){
			return parseFloat(value).toFixed(Decimal);
		}else{
			return value;
		}
    } catch (error) {
        return value;
    }

}

const numberSteps=(Decimal)=> {
    var Value = "1";
    if (Decimal !== "auto") {
        if (Decimal == 0) {
            return 1;
        } else {
            return parseFloat("0." + Value.padStart(Decimal, "0"));
        }
    } else {
        return Value;
    }
}
const QtyFormat=(value,Decimal)=>{
    try {
        if((value=="")||(value==undefined)||(isNaN(parseFloat(value)))){
            value=0;
        }
		if(Decimal!="auto"){
			return parseFloat(value).toFixed(Decimal);
		}else{
			return value;
		}
    } catch (error) {
        return value;
    }

}

const UploadImages = async () => {
    let RootUrl=$('#txtRootUrl').val();
    let uploadImages=await new Promise((resolve,reject)=>{
        ajaxIndicatorStart("% Completed. Please wait for until upload process complete.");
        setTimeout(() => {
            let count = $("input.imageScrop").length;
            let completed = 0;
            let rowIndex=0;
            let images={profileImage:{uploadPath:"",fileName:""},coverImage:{uploadPath:"",fileName:""},gallery:[]};
            const uploadComplete=async(e, x, settings, exception)=>{
                completed++;
                let percentage=(100*completed)/count;
                $('#divProcessText').html(percentage + '% Completed. Please wait for until upload process complete.');
                checkUploadCompleted();
            }
            const checkUploadCompleted=async()=>{
                if(count<=completed){
                    ajaxIndicatorStop();
                    resolve(images);
                }
            }
            const upload=async(formData)=>{
                console.log(formData);
                $.ajax({
                    type: "post",
                    url: RootUrl+"tmp/upload-image",
                    headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                    data: formData,
                    dataType:"json",
                    error: function(e, x, settings, exception) {ajaxErrors(e, x, settings, exception);},
                    complete: uploadComplete,
                    success:function(response){
                        if(response.referData.isProfileImage==1){
                            images.profileImage={uploadPath:response.uploadPath,fileName:response.fileName};
                        }else if(response.referData.isCoverImage==1){
                            images.coverImage={uploadPath:response.uploadPath,fileName:response.fileName};
                        }else{
                            images.gallery.push({uploadPath:response.uploadPath,fileName:response.fileName,slno:response.referData.slno});
                        }
                    }
                });
            }
            $("input.imageScrop").each(function (index){
                let id = $(this).attr('id');
                if ($('#' + id).val() != "" ) {
                    let isProfileImage=$('#'+id).attr('data-is-profile-image');
                    let isCoverImage=$('#'+id).attr('data-is-cover-image');
                    isProfileImage=isNaN(parseInt(isProfileImage))==false?isProfileImage:0;
                    isCoverImage=isNaN(parseInt(isCoverImage))==false?isCoverImage:0;
                    rowIndex++;
                    let formData = {};
                        formData.image = $('#'+id).attr('src');
                        formData.referData = {index:rowIndex,id:id,slno:$('#'+id).attr('data-slno'),isProfileImage:isProfileImage,isCoverImage:isCoverImage};
                        upload(formData);
                }else{
                    completed++;
                    let percentage=(100*completed)/count;
                    $('#divProcessText').html(percentage + '% Completed. Please wait for until upload process complete.');
                    checkUploadCompleted();
                }
            });
        }, 200);
        
        
    });
    return uploadImages;
}
const ProductUploadImages = async () => {
    let RootUrl=$('#txtRootUrl').val();
    let uploadImages=await new Promise((resolve,reject)=>{
        ajaxIndicatorStart("% Completed. Please wait for until upload process complete.");
        setTimeout(() => {
            let count = $("input.imageScrop").length;
            let completed = 0;
            let rowIndex=0;
            let images={profileImage:{uploadPath:"",fileName:""},coverImage:{uploadPath:"",fileName:""},gallery:[]};
            const uploadComplete=async(e, x, settings, exception)=>{
                completed++;
                let percentage=(100*completed)/count;
                $('#divProcessText').html(percentage + '% Completed. Please wait for until upload process complete.');
                checkUploadCompleted();
            }
            const checkUploadCompleted=async()=>{
                if(count<=completed){
                    ajaxIndicatorStop();
                    resolve(images);
                }
            }
            const upload=async(formData)=>{
                console.log(formData);
                $.ajax({
                    type: "post",
                    url: RootUrl+"tmp/upload-image",
                    headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                    data: formData,
                    dataType:"json",
                    error: function(e, x, settings, exception) {ajaxErrors(e, x, settings, exception);},
                    complete: uploadComplete,
                    success:function(response){
                        if(response.referData.isProfileImage==1){
                            images.profileImage={uploadPath:response.uploadPath,fileName:response.fileName};
                        }else if(response.referData.isCoverImage==1){
                            images.coverImage={uploadPath:response.uploadPath,fileName:response.fileName,referID:response.referData.id};
                        }else{
                            images.gallery.push({uploadPath:response.uploadPath,fileName:response.fileName,referID:response.referData.id});
                        }
                    }
                });
            }
            $("input.imageScrop").each(function (index){
                let id = $(this).attr('id');
                if ($('#' + id).val() != "" ) {
                    let isProfileImage=$('#'+id).attr('data-is-profile-image');
                    let isCoverImage=$('#'+id).attr('data-is-cover-image');
                    isProfileImage=isNaN(parseInt(isProfileImage))==false?isProfileImage:0;
                    isCoverImage=isNaN(parseInt(isCoverImage))==false?isCoverImage:0;
                    rowIndex++;
                    let formData = {};
                        formData.image = $('#'+id).attr('src');
                        formData.referData = {index:rowIndex,id:id,slno:$('#'+id).attr('data-slno'),isProfileImage:isProfileImage,isCoverImage:isCoverImage};
                        upload(formData);
                }else{
                    completed++;
                    let percentage=(100*completed)/count;
                    $('#divProcessText').html(percentage + '% Completed. Please wait for until upload process complete.');
                    checkUploadCompleted();
                }
            });
        }, 200);
        
        
    });
    return uploadImages;
}
const randomString=(length)=> {
    let result           = '';
    let characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let charactersLength = characters.length;
    for ( let i = 0; i < length; i++ ) {
      result += characters.charAt(Math.floor(Math.random() *  charactersLength));
    }
   return result;
}
const btnLoading=async($this) =>{
    let loadingText = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Processing';
    if ($($this).html() !== loadingText) {
        $this.data('original-text', $($this).html());
        $this.html(loadingText);
    }
}
const btnReset=async($this)=> {
    $('.waves-ripple').remove();
    $this.html($this.data('original-text'));
    $this.removeAttr('disabled');
}
const ajaxErrors=async(e, x, settings, exception)=> {
    let isSwal=false;let isToastr=false;
    try {
        if(window.swal != undefined) {isSwal=true;}
    }
    catch(err) {
        console.log("toastr is missing");
    }
    try {
        if(window.toastr != undefined) {isToastr=true;}
    }
    catch(err) {
        console.log("toastr is missing");
    }
    if ((e.status != 200) && (e.status != undefined)) {
        var message="";
        var statusErrorMap = {
            '400': "Server understood the request, but request content was invalid.",
            '401': "Unauthorized access.",
            '403': "Forbidden resource can't be accessed.",
            '404': "Sorry! Page Not Found",
            '405': "Sorry! Method not Allowed",
            '419': "Sorry! Page session has been expired",
            '500': "Internal server error.",
            '503': "Service unavailable."
        };
        if (e.status) {
            message = statusErrorMap[e.status];
        } else if (x == 'timeout') {
            message = "Request Time out.";
        } else if (x == 'abort') {
            //message = "Request was aborted by the server";
        }
        console.log(isToastr)
        if ((message != "")&&(message!=undefined)) {
            if(isToastr==true){
                toastr.error(message, "Failed", {
                    positionClass: "toast-top-right",
                    containerId: "toast-top-right",
                    showMethod: "slideDown",
                    hideMethod: "slideUp",
                    progressBar: !0
                })
            }else if(isSwal==true){
                swal("Error", message, "error");
            }
            if(e.status==419){
                setTimeout(async()=>{
                    window.location.reload();
                },100)
            }
        }
    } else if (x == 'parsererror') {
        if(isToastr==true){
            toastr.error("Parsing JSON Request failed.", "Failed", {
                positionClass: "toast-top-right",
                containerId: "toast-top-right",
                showMethod: "slideDown",
                hideMethod: "slideUp",
                progressBar: !0
            })
        }else if(isSwal==true){
            swal("Error", "Parsing JSON Request failed.", "error");
        }
    } else if (x == 'timeout') {
        if(isToastr==true){
            toastr.error("Request Time out.", "Failed", {
                positionClass: "toast-top-right",
                containerId: "toast-top-right",
                showMethod: "slideDown",
                hideMethod: "slideUp",
                progressBar: !0
            })
        }else if(isSwal==true){
            swal("Error", "Request Time out.", "error");
        }
    } else if (x == 'abort') {
        if(isToastr==true){
            toastr.error("Request was aborted by the server", "Failed", {
                positionClass: "toast-top-right",
                containerId: "toast-top-right",
                showMethod: "slideDown",
                hideMethod: "slideUp",
                progressBar: !0
            })
        }else if(isSwal==true){
            swal("Error", "Request was aborted by the server", "error");
        }
    }
}
const ajaxIndicatorStart =async(text="") =>{
    var basepath=$('#txtRootUrl').val();
    if ($('body').find('#resultLoading').attr('id') != 'resultLoading') {
        if(text==""){text="Processing";}
        $('body').append('<div id="resultLoading" style="display:none"><div style="font-weight: 700;"><img src="' + basepath + '/assets/images/ajax-loader.gif"><div id="divProcessText">'+text+'</div></div><div class="bg"></div></div>');
    }
    $('#resultLoading').css({
        'width': '100%',
        'height': '100%',
        'position': 'fixed',
        'z-index': '10000000',
        'top': '0',
        'left': '0',
        'right': '0',
        'bottom': '0',
        'margin': 'auto'
    });
    $('#resultLoading .bg').css({
        'background': '#000000',
        'opacity': '0.7',
        'width': '100%',
        'height': '100%',
        'position': 'absolute',
        'top': '0'
    });
    $('#resultLoading>div:first').css({
        'width': '50%',
        'height': '75px',
        'text-align': 'center',
        'position': 'fixed',
        'top': '0',
        'left': '0',
        'right': '0',
        'bottom': '0',
        'margin': 'auto',
        'font-size': '16px',
        'z-index': '10',
        'color': '#ffffff'
    });
    $('#resultLoading .bg').height('100%');
    $('#resultLoading').fadeIn(300);
    $('body').css('cursor', 'wait');
}
const ajaxIndicatorStop=async()=> {
    $('#resultLoading .bg').height('100%');
    $('#resultLoading').fadeOut(300);
    $('body').css('cursor', 'default');
}
const isMobile = ()=> {
    let check = false;
    (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
    return check;
};
$(document).on('click','.btnLogout',async(e)=>{
    e.preventDefault();
    $('#logout-form').submit();
})
if(isMobile()){
    $('#sidebar-toggle').trigger('click')
}
/*
//Inspect element Disable Start
document.onkeydown = function (e) { 
            if (event.keyCode == 123) { 
                return false; 
            } 
            if (e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) { 
                return false; 
            } 
            if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) { 
                return false; 
            } 
            if (e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) { 
                return false; 
            } 
            if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) { 
                return false; 
            } 
        }

        
if((typeof devtoolsDetector!==undefined)&&(typeof devtoolsDetector!=='undefined')){
    devtoolsDetector.addListener(function(isOpen) {
        if(isOpen==true){
          $('body').html('');
          $('head').html('');
          $('body').html('DEVTOOLS detected.')
          setTimeout(async()=>{alert('DEVTOOLS detected. all operations will be terminated.');},100);
          
        }
    });
    devtoolsDetector.launch();
}
document.addEventListener('contextmenu', event => event.preventDefault());*/
