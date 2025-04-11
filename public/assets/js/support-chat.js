const interval=1000;
let instance=null;
let defaultSettings = {
    isAdmin:false
};
let baseurl="";
class Chat{
    static ChatInstance(opts){
        $.extend(defaultSettings , opts);
        if(opts.isAdmin==true){
            baseurl=defaultSettings.basePath+"/admin/support";
        }else{
            baseurl=defaultSettings.basePath+"/customer/account-settings/support";
        }
        return instance ? instance : new Chat();
    }
    async load_chats(formData){
        try {
            const response=await new Promise((resolve,reject)=>{
                $.ajax({
                    type:"post",
                    url:baseurl+"/get/details",
                    headers: defaultSettings.headers,
                    data:formData,
                    error:function(e, x, settings, exception){reject(Error(exception));},
                    success:function(response){
                        resolve(response);
                    }
                });
            });
            return response;
        } catch (error) {
            console.log(error.message);
        }
    }
    async send_chats(formData){
        try {
            const response=await new Promise((resolve,reject)=>{
                $.ajax({
                    type:"post",
                    url:baseurl+"/details/save",
                    headers: defaultSettings.headers,
                    data:formData,
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
						btnLoading($('#btnSubmit'));
						ajaxIndicatorStart("Please wait Upload Process on going.");

						var percentVal = '0%';
						setTimeout(() => {
						$('#divProcessText').html(percentVal+' Completed.<br> Please wait for until upload process complete.');
						}, 100);
                    },
                    complete: function(e, x, settings, exception){ajaxIndicatorStop();},
                    error:function(e, x, settings, exception){reject(Error(exception));},
                    success:function(response){
                        resolve(response);
                    }
                });
            });
            return response;
        } catch (error) {
            console.log(error.message);
        }
    }
}