const rootUrl=$('#txtRootUrl').val();
if(localStorage.getItem("color"))
    $("#color" ).attr("href", rootUrl+"assets/css/"+localStorage.getItem("color")+".css" );
if(localStorage.getItem("dark"))
    $("body").attr("class",localStorage.getItem("dark"));

$(document).ready(function() {
    $('.customizer-mix li.color-layout.active').removeClass('active')
    $("[data-attr="+localStorage.getItem("body")+"]").addClass('active')

    $('.main-layout li.active').removeClass('active')
    $("[data-attr="+localStorage.getItem("class")+"]").addClass('active')

    
    $(".theme-setting").click(function(){
        $(".customizer-contain").toggleClass("open");
        $(".customizer-links").toggleClass("open");
    });

    $(".close-customizer-btn").on('click', function() {
        $(".floated-customizer-panel").removeClass("active");
    });

    $(".customizer-contain .icon-close").on('click', function() {
        $(".customizer-contain").removeClass("open");
        $(".customizer-links").removeClass("open");
    });

    $(".customizer-color li").on('click', function() {
        $(".customizer-color li").removeClass('active');
        $(this).addClass("active");
        var color = $(this).attr("data-attr");
        var primary = $(this).attr("data-primary"); 
        var secondary = $(this).attr("data-secondary");
        localStorage.setItem("color", color);
        localStorage.setItem("primary", primary);
        localStorage.setItem("secondary", secondary);
        localStorage.removeItem("dark");
        $("#color" ).attr("href", "../assets/css/"+color+".css" );
        $(".dark-only").removeClass('dark-only');
        location.reload(true);
    });

    $(".customizer-color.dark li").on('click', function() {
        $(".customizer-color.dark li").removeClass('active');
        $(this).addClass("active");
        $("body").attr("class", "dark-only");
        localStorage.setItem("dark", "dark-only");
    });


    $(".customizer-mix li").on('click', function() {
        $(".customizer-mix li").removeClass('active');
        $(this).addClass("active");
        var mixLayout = $(this).attr("data-attr");
        $("body").attr("class", mixLayout);
        localStorage.setItem("body", mixLayout);
    });


    $('.sidebar-setting li').on('click', function() {
        $(".sidebar-setting li").removeClass('active');
        $(this).addClass("active");
        var sidebar = $(this).attr("data-attr");
        $(".page-sidebar").attr("sidebar-layout",sidebar);
    });

    $('.sidebar-main-bg-setting li').on('click', function() {
        $(".sidebar-main-bg-setting li").removeClass('active')
        $(this).addClass("active")
        var bg = $(this).attr("data-attr");
        $("body").attr("class", "page-sidebar "+bg);
    });

    $('.sidebar-type li').on('click', function () {
        // $(".sidebar-type li").removeClass('active');
        var type = $(this).attr("data-attr");
        
        var boxed = "";
        if($(".page-wrapper").hasClass("box-layout")){
            boxed = "box-layout";
        }
        switch (type) {
            case 'compact-sidebar':
            {
                    $(".page-wrapper").attr("class", "page-wrapper compact-wrapper "+boxed);
                    $(".page-body-wrapper").attr("class", "page-body-wrapper sidebar-icon");
                    localStorage.setItem('page-wrapper', 'compact-wrapper');
                    localStorage.setItem('page-body-wrapper', 'sidebar-icon');
                    break;
            }
            case 'normal-sidebar':
            {
                
                $(".page-wrapper").attr("class", "page-wrapper horizontal-wrapper "+boxed);
                $(".page-body-wrapper").attr("class", "page-body-wrapper horizontal-menu");
                $(".logo-wrapper").find('img').attr('src', '../assets/images/logo/logo.png');
                localStorage.setItem('page-wrapper', 'horizontal-wrapper');
                localStorage.setItem('page-body-wrapper', 'horizontal-menu');
                console.log(localStorage.getItem('page-wrapper'))
                break;
            }
        }
        // $(this).addClass("active");
        location.reload(true);
    });

    $('.main-layout li').on('click', function() {
        $(".main-layout li").removeClass('active');
        $(this).addClass("active");
        var layout = $(this).attr("data-attr");
        $("body").attr("class", layout);
        $("html").attr("dir", layout);
        localStorage.setItem('class', layout);
        localStorage.setItem('html', layout);
    });

    $('.main-layout .box-layout').on('click', function() {
        $(".main-layout .box-layout").removeClass('active');
        $(this).addClass("active");
        var layout = $(this).attr("data-attr");
        $("body").attr("class", layout);
        $("html").attr("dir", layout);
        localStorage.setItem('class', layout);
        localStorage.setItem('html', layout);
    });

});
/*
const rootUrl=$('#txtRootUrl').val();
const appName=($('#txtAppName').val()!="" && $('#txtAppName').val()!=undefined)?$('#txtAppName').val():"app";
//$('').appendTo($('body'));
//live customizer js
$(document).ready(function() {
    
    const darkLayouts=["dark-only","dark-header-sidebar-mix","dark-sidebar-body-mix","dark-body-only"];
    var ThemeOptions={};
    const ThemeInit=()=>{
        var tmp={};
        var tmpDB={};
        try {
            tmp=JSON.parse(localStorage.getItem(appName));
        } catch (error) {
            tmp={};
        }
        try {
            tmpDB=JSON.parse($('#txtThemeOption').val());
        } catch (error) {
            
        }
        if(tmp==undefined || tmp==null){tmp={};}
        if(tmpDB==undefined || tmpDB==null){tmpDB={};}
        if(tmpDB.color!=undefined){ThemeOptions.color =tmpDB.color;}else if(tmp.color!=undefined){ThemeOptions.color = tmp.color;}
        if(tmpDB.primary!=undefined){ThemeOptions.primary =tmpDB.primary;}else if(tmp.primary!=undefined){ThemeOptions.primary = tmp.primary;}
        if(tmpDB.secondary!=undefined){ThemeOptions.secondary =tmpDB.secondary;}else if(tmp.secondary!=undefined){ThemeOptions.secondary = tmp.secondary;}
        if(tmpDB.bodyTheme!=undefined){ThemeOptions.bodyTheme =tmpDB.bodyTheme;}else if(tmp.bodyTheme!=undefined){ThemeOptions.bodyTheme = tmp.bodyTheme;}
        if(tmpDB.mixLayout!=undefined){ThemeOptions.mixLayout =tmpDB.mixLayout;}else if(tmp.mixLayout!=undefined){ThemeOptions.mixLayout = tmp.mixLayout;}
        if(tmpDB.sidebar!=undefined){ThemeOptions.sidebar =tmpDB.sidebar;}else if(tmp.sidebar!=undefined){ThemeOptions.sidebar = tmp.sidebar;}
        if(tmpDB.bg!=undefined){ThemeOptions.bg =tmpDB.bg;}else if(tmp.bg!=undefined){ThemeOptions.bg = tmp.bg;}
        if(tmpDB.pageWrapper!=undefined){ThemeOptions.pageWrapper =tmpDB.pageWrapper;}else if(tmp.pageWrapper!=undefined){ThemeOptions.pageWrapper = tmp.pageWrapper;}
        if(tmpDB.pageBodyWrapper!=undefined){ThemeOptions.pageBodyWrapper =tmpDB.pageBodyWrapper;}else if(tmp.pageBodyWrapper!=undefined){ThemeOptions.pageBodyWrapper = tmp.pageBodyWrapper;}
        if(tmpDB.layout!=undefined){ThemeOptions.layout =tmpDB.layout;}else if(tmp.layout!=undefined){ThemeOptions.layout = tmp.layout;}
        if(tmpDB.zoom!=undefined){ThemeOptions.zoom =tmpDB.zoom;}else if(tmp.zoom!=undefined){ThemeOptions.zoom = tmp.zoom;}
        if(tmpDB['button-size']!=undefined){ThemeOptions['button-size'] =tmpDB['button-size'];}else if(tmp['button-size']!=undefined){ThemeOptions['button-size'] = tmp['button-size'];}
        if(tmpDB['table-size']!=undefined){ThemeOptions['table-size'] =tmpDB['table-size'];}else if(tmp['table-size']!=undefined){ThemeOptions['table-size'] = tmp['table-size'];}
        if(tmpDB['switch-size']!=undefined){ThemeOptions['switch-size'] =tmpDB['switch-size'];}else if(tmp['switch-size']!=undefined){ThemeOptions['switch-size'] = tmp['switch-size'];}
        if(tmpDB['input-size']!=undefined){ThemeOptions['input-size'] =tmpDB['input-size'];}else if(tmp['input-size']!=undefined){ThemeOptions['input-size'] = tmp['input-size'];}

        
        if(ThemeOptions.color!=undefined){ $("#color" ).attr("href", rootUrl+"/assets/css/"+ThemeOptions.color+".css" );}
        if(ThemeOptions.bodyTheme!=undefined){$("body").addClass("class", ThemeOptions.bodyTheme);}
        if(ThemeOptions.mixLayout!=undefined){$("body").attr("class", ThemeOptions.mixLayout);}
        if(ThemeOptions.sidebar!=undefined){$(".page-sidebar").attr("sidebar-layout",ThemeOptions.sidebar);}
        if(ThemeOptions.bg!=undefined){ $("body").addClass( "page-sidebar "+ThemeOptions.bg);}
        if(ThemeOptions.pageWrapper!=undefined){ $(".page-wrapper").attr("class",ThemeOptions.pageWrapper);}
        if(ThemeOptions.pageBodyWrapper!=undefined){ $(".page-body-wrapper").attr("class", ThemeOptions.pageBodyWrapper);}
        if(ThemeOptions.layout!=undefined){ 
            $("body").addClass( ThemeOptions.layout);
            $("html").attr("dir",  ThemeOptions.layout);
        }
    }
    //ThemeInit();
    const themeUpdate=(isPageReload=false)=>{
        localStorage.setItem(appName, JSON.stringify(ThemeOptions));
        let url=rootUrl+"theme/update";
        $.ajax({
            type:'post',
            url:url,
            async:false,
            headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') },
            data:{Theme:JSON.stringify(ThemeOptions) },
            complete: function(e, x, settings, exception){
                if(isPageReload){
                    //location.reload(true);
                }
            }
        });
        
    }
    
    $('.customizer-mix li.color-layout.active').removeClass('active')
    $("[data-attr="+localStorage.getItem("body")+"]").addClass('active')

    $('.main-layout li.active').removeClass('active')
    $("[data-attr="+localStorage.getItem("class")+"]").addClass('active')

    
    $(".theme-setting").click(function(){
        $(".customizer-contain").toggleClass("open");
        $(".customizer-links").toggleClass("open");
    });

    $(".close-customizer-btn").on('click', function() {
        $(".floated-customizer-panel").removeClass("active");
    });

    $(".customizer-contain .icon-close").on('click', function() {
        $(".customizer-contain").removeClass("open");
        $(".customizer-links").removeClass("open");
    });
    $(".customizer-color li").on('click', function() {
        $(".customizer-color li").removeClass('active');
        $(this).addClass("active");
        ThemeOptions.color = $(this).attr("data-attr");
        ThemeOptions.primary = $(this).attr("data-primary"); 
        ThemeOptions.secondary = $(this).attr("data-secondary");
        ThemeOptions.bodyTheme ="light-only";


        localStorage.setItem("color", color);
        localStorage.setItem("primary", primary);
        localStorage.setItem("secondary", secondary);
        localStorage.removeItem("dark");
        $("#color" ).attr("href", rootUrl+"/assets/css/"+ThemeOptions.color+".css" );
        $(".dark-only").removeClass('dark-only');
        if(darkLayouts.indexOf(ThemeOptions.mixLayout)>=0){
            ThemeOptions.mixLayout="light-only"
        }
        themeUpdate(true);
        //location.reload(true);
    });

    $(".customizer-color.dark li").on('click', function() {
        $(".customizer-color.dark li").removeClass('active');
        $(this).addClass("active");
        $("body").attr("class", "dark-only");
        ThemeOptions.bodyTheme ="dark-only"; console.log(ThemeOptions.mixLayout);
        if(darkLayouts.indexOf(ThemeOptions.mixLayout)<0){
            ThemeOptions.mixLayout="dark-only"
        }
        console.log(ThemeOptions.mixLayout);
        themeUpdate(true);
    });


    $(".customizer-mix li").on('click', function() {
        $(".customizer-mix li").removeClass('active');
        $(this).addClass("active");
        ThemeOptions.mixLayout = $(this).attr("data-attr");
        if(darkLayouts.indexOf(ThemeOptions.mixLayout)>=0){
            ThemeOptions.bodyTheme ="dark-only";
        }else{
            ThemeOptions.bodyTheme ="light-only";
        }
        $("body").attr("class", mixLayout);
        localStorage.setItem("body", ThemeOptions.mixLayout);
        themeUpdate(true);
    });


    $('.sidebar-setting li').on('click', function() {
        $(".sidebar-setting li").removeClass('active');
        $(this).addClass("active");
        ThemeOptions.sidebar = $(this).attr("data-attr");
        $(".page-sidebar").attr("sidebar-layout",ThemeOptions.sidebar);
        themeUpdate(true);
    });

    $('.sidebar-main-bg-setting li').on('click', function() {
        $(".sidebar-main-bg-setting li").removeClass('active')
        $(this).addClass("active")
        ThemeOptions.bg = $(this).attr("data-attr");
        $("body").attr("class", "page-sidebar "+ThemeOptions.bg);
        themeUpdate(true);
    });

    $('.sidebar-type li').on('click', function () {
        var type = $(this).attr("data-attr");
        
        var boxed = "";
        if($(".page-wrapper").hasClass("box-layout")){
            boxed = "box-layout";
        }
        switch (type) {
            case 'compact-sidebar':{
                    $(".page-wrapper").attr("class", "page-wrapper compact-wrapper "+boxed);
                    $(".page-body-wrapper").attr("class", "page-body-wrapper sidebar-icon");
                    ThemeOptions.pageWrapper="page-wrapper compact-wrapper "+boxed;
                    ThemeOptions.pageBodyWrapper="page-body-wrapper sidebar-icon";
                    break;
            }
            case 'normal-sidebar':{
                
                $(".page-wrapper").attr("class", "page-wrapper horizontal-wrapper "+boxed);
                $(".page-body-wrapper").attr("class", "page-body-wrapper horizontal-menu");
                $(".logo-wrapper").find('img').attr('src', rootUrl+'/assets/images/logo/logo.png');
                
                ThemeOptions.pageWrapper="page-wrapper horizontal-wrapper "+boxed;
                ThemeOptions.pageBodyWrapper="page-body-wrapper horizontal-menu";
                break;
            }
        }
        themeUpdate(true);
    });

    $('.main-layout li').on('click', function() {
        $(".main-layout li").removeClass('active');
        $(this).addClass("active");
        ThemeOptions.layout = $(this).attr("data-attr");
        $("body").attr("class",  ThemeOptions.layout);
        $("html").attr("dir",  ThemeOptions.layout);
        themeUpdate(true);
    });

    $('.main-layout .box-layout').on('click', function() {
        $(".main-layout .box-layout").removeClass('active');
        $(this).addClass("active");
        ThemeOptions.layout = $(this).attr("data-attr");
        $("body").attr("class",  ThemeOptions.layout);
        $("html").attr("dir",  ThemeOptions.layout);
        themeUpdate(true);
    });
    $('#lstZoom').on('change',function(){
        ThemeOptions.zoom=$('#lstZoom').val();
        themeUpdate(true);
    });
    $('#lstFontSize').on('change',function(){
        ThemeOptions['font-size']=$('#lstFontSize').val();
        themeUpdate(true);
    });
    $('#lstTableSize').on('change',function(){
        ThemeOptions['table-size']=$('#lstTableSize').val();
        themeUpdate(true);
    });
    $('#lstButtonSize').on('change',function(){
        ThemeOptions['button-size']=$('#lstButtonSize').val();
        themeUpdate(true);
    });
    $('#lstSwitchSize').on('change',function(){
        ThemeOptions['switch-size']=$('#lstSwitchSize').val();
        themeUpdate(true);
    });
    $('#lstInputSize').on('change',function(){
        ThemeOptions['input-size']=$('#lstInputSize').val();
        themeUpdate(true);
    });
});
*/