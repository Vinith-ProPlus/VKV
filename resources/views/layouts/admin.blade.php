<!DOCTYPE html>
<html lang="en">
	<head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
        <meta http-equiv="pragma" content="no-cache">
        <meta http-equiv="expires" content="0">

        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="keywords" content="">
        <meta name="author" content="ProPlus Logics">
        <meta name="_token" content="{{ csrf_token() }}"/>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{$PageTitle ?? ''}} {{-- - {{$Company['CompanyName']}} --}}</title>
		<link rel="icon" type="image/x-icon" href="{{-- {{url('/')}}/{{$Company['Logo']}} --}}">
		<link href="https://fonts.googleapis.com/css?family=Montserrat:300,300i,400,400i,500,500i,600,600i,700,700i&amp;display=swap" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700&amp;display=swap" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/fontawesome.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/icofont.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/themify.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/flag-icon.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/feather-icon.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/bootstrap.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/style.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/js/lightbox/css/lightgallery.css?r={{date('dmyHis')}}">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
		<link rel="stylesheet" type="text/css" href="{{url('/assets/plugins/pplDataTable/pplDataTable.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/assets/plugins/pplDataTable/pplDataTable.min.css')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/sweetalert2.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/select2.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/toastr.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/plugins/dropify/css/dropify.min.css?r={{date('YmdHis')}}">
    	<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/plugins/bootstrap-multiselect/bootstrap-multiselect.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/plugins/image-cropper/cropper.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/plugins/dynamic-form/v3/dynamicForm.min.css?r={{date('YmdHis')}}">

		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/color-1.css?r={{date('YmdHis')}}" media="screen" id="color">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/responsive.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/loader.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/custom.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/custom-n.css?r={{date('YmdHis')}}">
		<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/css/datatables.css?r={{date('YmdHis')}}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">

		<!-- sweetalert2 -->
		<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Dropzone CSS & JS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>

    </head>
    <style>
        .wizard-head {
            /* pointer-events: none !important; */
            margin-bottom: 30px !important;
            justify-content: space-evenly;
        }

        .wizard-head a {
            text-decoration: none;
        }

        .wizard-head a .nav-contents {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .wizard-head .nav-title {
            color: #c0c0c0;
        }

        .wizard-head .nav-link.active .nav-title {
            color: #545454;
        }

        .wizard-head .nav-link i {
            font-size: 32px;
            color: #d8d6ff;
        }

        .wizard-head .nav-link.active i {
            color: #7167f4 !important;
        }

        .nav-tabs {
            border-bottom: none !important;
        }

        .wizard-head .nav-link {
            border: none !important;
			padding: 0px;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }

        .preview-item {
            position: relative;
            width: 150px;
            background: #f8f9fa;
            border: 2px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin: 15px;

        }

        .preview-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }

        .preview-document {
            width: 100%;
            height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 4px;
        }

        .preview-document i {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .file-name {
            font-size: 12px;
            text-align: center;
            word-break: break-word;
            margin-top: 5px;
            color: #333;
        }

        .file-size {
            font-size: 11px;
            color: #666;
            text-align: center;
            margin-top: 3px;
        }

        .dropify-wrapper {
            /* margin-bottom: 20px; */
        }

        .remove-file, .remove-modal-file, .btnDeleteDocumentFile, .btnDeleteDocArray {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }
    </style>
	<body>
		<input type="hidden" style="display:none!important" id="txtRootUrl" value="{{url('/')}}/">
		<input type="hidden" name="txtActiveName" id="txtActiveName" value="{{$ActiveMenuName ?? ''}}">
		{{-- <div id="divsettings" style="display:none!important">{{json_encode($Settings)}}</div> --}}
		<div class="loader-wrapper">
			<div class="theme-loader"></div>
		</div>
		<div class="page-wrapper compact-wrapper" id="pageWrapper">
			<div class="page-main-header">
				<div class="main-header-right">
					<div class="main-header-left">
						<div class="logo-wrapper">
							<a href="{{url('/')}}/admin">
{{--								<img loading="lazy" src="--}}{{-- {{url('/')}}/{{$Company['Logo']}} --}}{{--" alt="" width="50" height="52">--}}
							</a>
						</div>
					</div>
					<div class="mobile-sidebar">
						<div class="flex-grow-1 text-end switch-sm">
							<label class="switch">
								<input id="sidebar-toggle" type="checkbox" data-bs-toggle=".container" checked="checked">
								<span class="switch-state"></span>
							</label>
						</div>
					</div>
					<div class="nav-right col pull-right right-menu">
						<ul class="nav-menus"><!--
							<li>
								<a class="text-dark" href="#!" onclick="javascript:toggleFullScreen()">
									<i data-feather="maximize"></i>
								</a>
							</li>-->
							<li>

							</li>
							<li class="theme-setting">
								<i data-feather="settings"></i>
							</li>
							<li class="onhover-dropdown px-0">
								<span class="d-flex user-header">
									<img loading="lazy" class="me-2 rounded-circle img-35" src="{{-- {{url('/')}}/{{$UInfo->ProfileImage}} --}}" alt="">
									<span class="flex-grow-1">
										<span class="f-12 f-w-600">{{-- {{$UInfo->Name}} --}}</span>
										<span class="d-block">{{-- {{$UInfo->RoleName}} --}}</span>
									</span>
								</span>
								<ul class="profile-dropdown onhover-show-div">
                                    <li><a href="{{url('/')}}/admin/users-and-permissions/change-password/"><i data-feather="user"> </i>Password Change</a></li>
                                    <li class="btnLogout"><i data-feather="log-in"></i>Logout </li>
								</ul>
							</li>
						</ul>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
					</div>
					<div class="d-lg-none mobile-toggle pull-right">
						<i data-feather="more-horizontal"></i>
					</div>
				</div>
			</div>
			<div class="page-body-wrapper sidebar-icon">
				<nav-menus></nav-menus>
				@include('layouts.sidebar')
				<div class="page-body">
                    @yield('content')
					<div class="modal  medium" tabindex="-1" role="dialog" id="ImgCrop">
						<div class="modal-dialog modal-dialog-centered" role="document">
							<div class="modal-content">
								<div class="modal-header pt-10 pb-10">
									<h5 class="modal-title">Image Crop</h5>
									<button type="button" class="close display-none" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<div class="row">
										<div class="col-sm-12">
											<img loading="lazy" style="width:100%" src="" id="ImageCrop" data-id="">
										</div>
									</div>
									<div class="row mt-10 d-flex justify-content-center">
										<div class="col-sm-12 docs-buttons d-flex justify-content-center">
											<div class="btn-group">
												<button class="btn btn-outline-primary" type="button" data-method="rotate" data-option="-45" title="Rotate Left"><span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false" title="$().cropper(&quot;rotate&quot;, -45)"><span class="fa fa-rotate-left"></span></span></button>
												<button class="btn btn-outline-primary" type="button" data-method="rotate" data-option="45" title="Rotate Right"><span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false" title="$().cropper(&quot;rotate&quot;, 45)"><span class="fa fa-rotate-right"></span></span></button>
												<button class="btn btn-outline-primary" type="button" data-method="scaleX" data-option="-1" title="Flip Horizontal"><span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false" title="$().cropper(&quot;scaleX&quot;, -1)"><span class="fa fa-arrows-h"></span></span></button>
												<button class="btn btn-outline-primary" type="button" data-method="scaleY" data-option="-1" title="Flip Vertical"><span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false" title="$().cropper(&quot;scaleY&quot;, -1)"><span class="fa fa-arrows-v"></span></span></button>
												<button class="btn btn-outline-primary" type="button" data-method="reset" title="Reset"><span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false" title="$().cropper(&quot;reset&quot;)"><span class="fa fa-refresh"></span></span></button>
												<button class="btn btn-outline-primary btn-upload" id="btnUploadImage" title="Upload image file"><span class="docs-tooltip" data-bs-toggle="tooltip" data-animation="false" title="Import image with Blob URLs"><span class="fa fa-upload"></span></span></button>
												<?php
													$Images=array("jpg","jpeg","png","gif","bmp","tiff");
													if(isset($FileTypes)){
														if(array_key_exists("category",$FileTypes)){
															if(array_key_exists("Images",$FileTypes['category'])){
																$Images=$FileTypes['category']['Images'];
															}
														}
													}
													$Images=".".implode(",.",$Images);
												?>
												<input class="sr-only display-none" id="inputImage" type="file" name="file" accept="{{$Images}}">
											</div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-outline-dark"  id="btnCropModelClose">Close</button>
									<button type="button" class="btn btn-outline-info" id="btnCropApply">Apply</button>
								</div>
							</div>
						</div>
					</div>
				</div>
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12 footer-copyright">
                                <p class="mb-0">Copyright  &copy; @if(date("Y")=="2023") {{date("Y")}} @else 2023-{{date("Y")}} @endif <a class="text-bold-800 grey darken-2" href="https://propluslogics.com/" target="_blank">Web Development Company </a>, All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                </footer>
			</div>
		</div>
		<?php
			$color="color-1";
			$mixLayout="light-only";
			$layout="ltr";
			$zoomLevel='100%';
			$FontSize="14px";
			$buttonSize="";
			$tableSize="";
			$switchSize="";
			$inputSize="";
		?>
		<div class="customizer-contain">
			<div class="customizer-links">
				<div class="nav nac-pills" id="c-pills-tab" role="tablist" aria-orientation="vertical">
					<a class="nav-link active show" id="c-pills-home-tab" data-bs-toggle="pill" href="#c-pills-home" role="tab"
						aria-controls="c-pills-home" aria-selected="true">
						<div class="settings"> <i class="icofont icofont-ui-settings"></i> General setting </div>
					</a>
					<a class="nav-link" id="c-pills-profile-tab" data-bs-toggle="pill" href="#c-pills-profile" role="tab"
						aria-controls="c-pills-profile" aria-selected="false">
						<div class="settings color-settings"> <i class="icofont icofont-color-bucket"></i> Colors </div>
					</a>
				</div>
			</div>
			<div class="tab-content" id="c-pills-tabContent">
				<div class="customizer-body custom-scrollbar">
					<div class="tab-pane fade show active" id="c-pills-home" role="tabpanel" aria-labelledby="c-pills-home-tab"><!--
						<h6>Layout Type</h6>
						<ul class="main-layout layout-grid">
							<li data-attr="ltr" class=" @if($layout=='ltr') active  @endif">
								<div class="body">
									<ul>
										<li class="body"> <span class="badge badge-light">LTR</span> </li>
									</ul>
								</div>
							</li>
							<li data-attr="rtl" class=" @if($layout=='rtl') active  @endif">
								<div class="body">
									<ul>
										<li class="body"> <span class="badge badge-light">RTL</span></li>
									</ul>
								</div>
							</li>
							<li data-attr="box-layout" class="box-layout  @if($layout=='box-layout') active  @endif">
								<div class="body">
									<ul>
										<li class="body"> <span class="badge badge-light">Box</span> </li>
									</ul>
								</div>
							</li>
						</ul>-->
						<h6 class="mb-1">Sidebar Type</h6>
						<ul class="sidebar-type layout-grid">
							<li data-attr="normal-sidebar">
								<div class="header bg-light">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-dark sidebar"></li>
										<li class="bg-light body w-100"> </li>
									</ul>
								</div>
							</li>
							<li data-attr="compact-sidebar">
								<div class="header bg-light">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-dark sidebar compact"></li>
										<li class="bg-light body"> </li>
									</ul>
								</div>
							</li>
						</ul>
						<h6 class="mb-1">Sidebar background setting</h6>
						<ul class="nav nac-pills nav-primary" id="pills-tab" role="tablist">
							<li class="nav-item"><a class="nav-link active show" id="pills-home-tab" data-bs-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true" data-original-title="" title="">Color</a></li>
							<li class="nav-item"><a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false" data-original-title="" title="">Pattern</a></li>
							<li class="nav-item"><a class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" href="#pills-contact" role="tab" aria-controls="pills-contact" aria-selected="false" data-original-title="" title="">image</a></li>
						</ul>
						<div class="tab-content sidebar-main-bg-setting" id="pills-tabContent">
							<div class="tab-pane fade active show" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
								<ul class="sidebar-bg-settings">
									<li class="bg-dark active" data-attr="dark-sidebar"> </li>
									<li class="bg-white" data-attr="light-sidebar"> </li>
									<li class="bg-color1" data-attr="color1-sidebar"> </li>
									<li class="bg-color2" data-attr="color2-sidebar"> </li>
									<li class="bg-color3" data-attr="color3-sidebar"> </li>
									<li class="bg-color4" data-attr="color4-sidebar"> </li>
									<li class="bg-color5" data-attr="color5-sidebar"> </li>
								</ul>
							</div>
							<div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
								<ul class="sidebar-bg-settings">
									<li class=" bg-pattern1" data-attr="sidebar-pattern1"> </li>
									<li class=" bg-pattern2" data-attr="sidebar-pattern2"> </li>
									<li class=" bg-pattern3" data-attr="sidebar-pattern3"> </li>
									<li class=" bg-pattern4" data-attr="sidebar-pattern4"> </li>
									<li class=" bg-pattern5" data-attr="sidebar-pattern5"> </li>
									<li class=" bg-pattern6" data-attr="sidebar-pattern6"> </li>
								</ul>
							</div>
							<div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
								<ul class="sidebar-bg-settings">
									<li class="bg-img1" data-attr="sidebar-img1"> </li>
									<li class="bg-img2" data-attr="sidebar-img2"> </li>
									<li class="bg-img3" data-attr="sidebar-img3"> </li>
									<li class="bg-img4" data-attr="sidebar-img4"> </li>
									<li class="bg-img5" data-attr="sidebar-img5"> </li>
									<li class="bg-img6" data-attr="sidebar-img6"> </li>
								</ul>
							</div>
						</div>
						<div class="row "  style="display:none">
							<div class="col-12 col-sm-6 mt-30 d-none" >
								<h6 class="mb-1">Zoom</h6>
								<select  class="form-control" id="lstZoom">
									<option value="25%" @if($zoomLevel=="25%") Selected  @endif>25 %</option>
									<option value="50%" @if($zoomLevel=="50%") Selected  @endif>50 %</option>
									<option value="75%" @if($zoomLevel=="75%") Selected  @endif>75 %</option>
									<option value="100%" @if($zoomLevel=="100%") Selected  @endif>100 %</option>
									<option value="125%" @if($zoomLevel=="125%") Selected  @endif>125 %</option>
									<option value="150%" @if($zoomLevel=="150%") Selected  @endif>150 %</option>
									<option value="175%" @if($zoomLevel=="175%") Selected  @endif>175 %</option>
									<option value="200%" @if($zoomLevel=="200%") Selected  @endif>200 %</option>
								</select>
							</div>
							<div class="col-12 col-sm-6 mt-30" style="display:none">
								<h6 class="mb-1">Font Size</h6>
								<select  class="form-control" id="lstFontSize" >
									@for($i=12;$i<=20;$i++)
										<option value="{{$i}}px" @if($FontSize==$i."px") Selected  @endif >{{$i}} px</option>
									@endfor
								</select>
							</div>
							<div class="col-12  col-sm-6 mt-30" style="display:none">
								<h6 class="mb-1">Input Size</h6>
								<select  class="form-control" id="lstInputSize">
									<option value="form-control-lg" @if($inputSize=="form-control-lg") Selected  @endif>Large</option>
									<option value="" @if($inputSize=="") Selected  @endif>Normal</option>
									<option value="form-control-sm" @if($inputSize=="form-control-sm") Selected  @endif>Small</option>
								</select>
							</div>
							<div class="col-12 col-sm-6 mt-30" style="display:none">
								<h6 class="mb-1">Button Size</h6>
								<select  class="form-control" id="lstButtonSize">
									<option value="btn-lg" @if($buttonSize=="btn-lg") Selected  @endif>Large</option>
									<option value="" @if($buttonSize=="") Selected  @endif>Normal</option>
									<option value="btn-sm" @if($buttonSize=="btn-sm") Selected  @endif>Small</option>
									<option value="btn-xs" @if($buttonSize=="btn-xs") Selected  @endif>Extra Small</option>
								</select>
							</div>
							<div class="col-12 col-sm-6 mt-30" style="display:none">
								<h6 class="mb-1">Table Size</h6>
								<select  class="form-control" id="lstTableSize">
									<option value="" @if($tableSize=="") Selected  @endif>Normal</option>
									<option value="table-sm" @if($tableSize=="table-sm") Selected  @endif>Small</option>
								</select>
							</div>
							<div class="col-12  col-sm-6 mt-30" style="display:none">
								<h6 class="mb-1">switch Size</h6>
								<select  class="form-control" id="lstSwitchSize">
									<option value="switch-lg" @if($switchSize=="switch-lg") Selected  @endif>Large</option>
									<option value="" @if($switchSize=="") Selected  @endif>Normal</option>
									<option value="switch-sm" @if($switchSize=="switch-sm") Selected  @endif>Small</option>
								</select>
							</div>
						</div>
					</div>
					<div class="tab-pane fade " id="c-pills-profile" role="tabpanel" aria-labelledby="c-pills-profile-tab">
						<h6 class="mb-1">Light layout</h6>
						<ul class="layout-grid customizer-color">
							<li class="color-layout @if($color=='color-1') active @endif" data-attr="color-1" data-primary="#158df7" data-secondary="#fb2e63"><div></div></li>
							<li class="color-layout @if($color=='color-2') active @endif" data-attr="color-2" data-primary="#0288d1" data-secondary="#26c6da"><div></div></li>
							<li class="color-layout @if($color=='color-3') active @endif" data-attr="color-3" data-primary="#d64dcf" data-secondary="#8e24aa"><div></div></li>
							<li class="color-layout @if($color=='color-4') active @endif" data-attr="color-4" data-primary="#4c2fbf" data-secondary="#2e9de4"><div></div></li>
							<li class="color-layout @if($color=='color-5') active @endif" data-attr="color-5" data-primary="#7c4dff" data-secondary="#7b1fa2"><div></div></li>
							<li class="color-layout @if($color=='color-6') active @endif" data-attr="color-6" data-primary="#3949ab" data-secondary="#4fc3f7"><div></div></li>
						</ul>
						<h6 class="mb-1">Dark Layout</h6>
						<ul class="layout-grid customizer-color dark">
							<li class="color-layout @if($color=='color-1') active @endif" data-attr="color-1" data-primary="#4466f2" data-secondary="#1ea6ec"><div></div></li>
							<li class="color-layout @if($color=='color-2') active @endif" data-attr="color-2" data-primary="#0288d1" data-secondary="#26c6da"><div></div></li>
							<li class="color-layout @if($color=='color-3') active @endif" data-attr="color-3" data-primary="#d64dcf" data-secondary="#8e24aa"><div></div></li>
							<li class="color-layout @if($color=='color-4') active @endif" data-attr="color-4" data-primary="#4c2fbf" data-secondary="#2e9de4"><div></div></li>
							<li class="color-layout @if($color=='color-5') active @endif" data-attr="color-5" data-primary="#7c4dff" data-secondary="#7b1fa2"><div></div></li>
							<li class="color-layout @if($color=='color-6') active @endif" data-attr="color-6" data-primary="#3949ab" data-secondary="#4fc3f7"><div></div></li>
						</ul>
						<h6 class="mb-1">Mix Layout</h6>
						<ul class="layout-grid customizer-mix">
							<li class="color-layout @if($mixLayout=='light-only') active @endif" data-attr="light-only">
								<div class="header bg-light">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-light sidebar"></li>
										<li class="bg-light body"> </li>
									</ul>
								</div>
							</li>
							<li class="color-layout @if($mixLayout=='dark-sidebar') active @endif" data-attr="dark-sidebar">
								<div class="header bg-light">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-dark sidebar"></li>
										<li class="bg-light body"> </li>
									</ul>
								</div>
							</li>
							<li class="color-layout @if($mixLayout=='dark-body-only') active @endif" data-attr="dark-body-only">
								<div class="header bg-light">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-light sidebar"></li>
										<li class="bg-dark body"> </li>
									</ul>
								</div>
							</li>
							<li class="color-layout @if($mixLayout=='dark-sidebar-body-mix') active @endif" data-attr="dark-sidebar-body-mix">
								<div class="header bg-light">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-dark sidebar"></li>
										<li class="bg-dark body"> </li>
									</ul>
								</div>
							</li>
							<li class="color-layout @if($mixLayout=='dark-header-sidebar-mix') active @endif" data-attr="dark-header-sidebar-mix">
								<div class="header bg-dark">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-dark sidebar"></li>
										<li class="bg-light body"> </li>
									</ul>
								</div>
							</li>
							<li class="color-layout @if($mixLayout=='dark-only') active @endif" data-attr="dark-only">
								<div class="header bg-dark">
									<ul>
										<li></li>
										<li></li>
										<li></li>
									</ul>
								</div>
								<div class="body">
									<ul>
										<li class="bg-dark sidebar"></li>
										<li class="bg-dark body"> </li>
									</ul>
								</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
        <!-- Cropper Modal -->
        @include('components.cropper-modal')
		<script src="{{url('/')}}/assets/js/jquery-3.7.1.min.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/bootstrap/bootstrap.bundle.min.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/icons/feather-icon/feather.min.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/icons/feather-icon/feather-icon.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/sidebar-menu.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/config.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/script.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/theme-customizer/customizer.js?r={{date('YmdHis')}}"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.5/jszip.min.js?r={{date('YmdHis')}}"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js?r={{date('YmdHis')}}"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.min.js?r={{date('YmdHis')}}"></script>

		<script src="{{url('/assets/plugins/pplDataTable/pplDataTable.js')}}"></script>
		<script src="{{url('/assets/plugins/pplDataTable/dataTable.min.js')}}"></script>


		<script src="{{url('/')}}/assets/plugins/bootstrap-multiselect/bootstrap-multiselect.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/select2/select2.full.min.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/sweet-alert/sweetalert.min.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/toastr.min.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/plugins/dropify/js/dropify.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/plugins/bootbox-js/bootbox.min.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/plugins/image-cropper/cropper.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/plugins/dynamic-form/v3/dynamicForm.min.js?r={{date('YmdHis')}}"></script>
    	<script src="{{url('/')}}/assets/js/prototypes.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/support.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/custom.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/address.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/app-init.js?r={{date('YmdHis')}}"></script>
		<script src="{{url('/')}}/assets/js/form-wizard/form-wizard-two.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
		<script src="{{url('/')}}/assets/js/lightbox/js/lightgallery.js?r={{date('dmyHis')}}"></script>
		<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-app.js"></script>
		<script src="https://www.gstatic.com/firebasejs/8.10.0/firebase-messaging.js"></script>
{{--		<script src="{{url('/assets/firebase.full.js')}}"></script>--}}


		<script src="{{url('/')}}/assets/plugins/ckeditor/ckeditor.js"></script>
    	<script src="{{url('/')}}/assets/plugins/ckeditor/custom.js"></script>
		<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

        <!-- Include Cropper.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

        <!-- Load jQuery UI -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css">

		@include('common_script.alert_script')
		@include('common_script.action_script')
		@include('common_script.cropper-script')
		<script>
			$.extend($.fn.dataTable.defaults, {
				processing: true,
				deferRender: true,
				responsive: true,
				dom: "<'row m-1'<'col-md-6 d-flex align-items-center'B><'col-md-6 text-right'f>>" + // Top row
						"<'row'<'col-md-12'tr>>" + // Table
						"<'row m-1'<'col-md-6'i><'col-md-6 text-right'p>>", // Bottom row (Info & Pagination)
				buttons: [
					{
						extend: 'pageLength',
						text: function (dt) {
							let length = dt.page.len();
							return `Show ${length} Records`; // Show current page length
						},
						className: 'btn btn-secondary dropdown-toggle',
						attr: { 'data-bs-toggle': 'dropdown', 'data-bs-display': 'static' } // Prevent clipping
					}
				]
			});
		</script>
		@yield('script')
	</body>
</html>
