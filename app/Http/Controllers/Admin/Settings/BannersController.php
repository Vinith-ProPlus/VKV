<?php

namespace App\Http\Controllers\web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use App\Models\DocNum;
use App\Models\general;
use App\Models\support;
use App\Models\ServerSideProcess;
use DB;
use Auth;
use Hash;
use cruds;
use App\Rules\ValidUnique;
use App\Rules\ValidDB;
use logs;

class BannersController extends Controller{
	private $general;
	private $support;
	private $DocNum;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $logs;
	private $Settings;
    private $Menus;
    public function __construct(){
		$this->ActiveMenuName="Banners";
		$this->PageTitle="Banners";
        $this->middleware('auth');
        $this->DocNum=new DocNum();
        $this->support=new support();

		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			return $next($request);
		});
    }
	public function index(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			$FormData['bannerImages']=DB::Table('tbl_banner_images')->where('BannerType','Web')->where('DFlag',0)->get();
			$FormData['MbannerImages']=DB::Table('tbl_banner_images')->where('BannerType','Mobile')->where('DFlag',0)->get();
			return view('app.settings.banners.view',$FormData);
		}elseif($this->general->isCrudAllow($this->CRUD,"Add")==true){
			return Redirect::to('/admin/settings/banner/create');
		}else{
			return view('errors.403');
		}
	}
	public function Create(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"Add")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			$FormData['isEdit']=false;
			return view('app.settings.banners.upload',$FormData);
		}elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('/admin/settings/banner');
		}else{
			return view('errors.403');
		}
	}

	public function Edit(Request $req,$TranNo=null){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=true;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			$FormData['TranNo']=$TranNo;
			$FormData['EditData']=DB::Table('tbl_banner_images')->where('TranNo',$TranNo)->where('DFlag',0)->get();
            logger($FormData['EditData']);
			if(count($FormData['EditData'])>0){
				return view('app.settings.banners.upload',$FormData);
			}else{
				return view('errors.400');
			}
		}else{
			return view('errors.403');
		}
	}

	private function getImageData($base64){
		$base64_str = substr($base64, strpos($base64, ",")+1);
		$image = base64_decode($base64_str);
		return $image;
	}
	public function Save(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"add")==true){
            $OldData=$NewData=array();$TranNo="";
			DB::beginTransaction();
			$status=false;
			$bannerImage="";
			try{

				$TranNo=$this->DocNum->getDocNum("Home-Banner");
				$dir="uploads/settings/banner/";
				if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
				if($req->hasFile('bannerImage')){
					$file = $req->file('bannerImage');
					$fileName=md5($file->getClientOriginalName() . time());
					$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
					$file->move($dir, $fileName1);
					$bannerImage=$dir.$fileName1;
				}else if($req->bannerImage!=""){
					$rnd=$this->support->RandomString(10)."_".date("YmdHis");
					$fileName = $rnd.".png";
					$imgData = $this->getImageData($req->bannerImage);
					file_put_contents($dir.$fileName, $imgData);
					$bannerImage=$dir.$fileName;
				}
				$data=array(
					"TranNo"=>$TranNo,
					"BannerTitle"=>$req->BannerTitle,
					"BannerType"=>$req->BannerType,
					"BannerImage"=>$bannerImage,
					"createdOn"=>date("Y-m-d H:i:s"),
					"createdBy"=>$this->UserID
				);
				$status=DB::Table('tbl_banner_images')->insert($data);
			}catch(Exception $e) {
				$status=false;
			}
			if($status==true){
				DB::commit();
				$this->DocNum->updateDocNum("Home-Banner");
				$NewData=DB::Table('tbl_banner_images')->where('TranNo',$TranNo)->get();
				$logData=array("Description"=>"New Banner added ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::ADD,"ReferID"=>$TranNo,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Banner image added successfully");
			}else{
				if($bannerImage!=""){
					if(file_exists($bannerImage)){
						// unlink($bannerImage);
					}
				}
				DB::rollback();
				return array('status'=>false,'message'=>"Banner image add failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function Update(Request $req,$TranNo){
        logger($req);
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
            $OldData=DB::Table('tbl_banner_images')->where('TranNo',$TranNo)->get();
			$NewData=array();
			DB::beginTransaction();
			$status=false;
			$bannerImage="";
			try{
				$dir="uploads/settings/banner/";
				if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
				if($req->hasFile('bannerImage')){
					$file = $req->file('bannerImage');
					$fileName=md5($file->getClientOriginalName() . time());
					$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
					$file->move($dir, $fileName1);
					$bannerImage=$dir.$fileName1;
				}else if($req->bannerImage!=""){
					$rnd=$this->support->RandomString(10)."_".date("YmdHis");
					$fileName = $rnd.".png";
					$imgData = $this->getImageData($req->bannerImage);
					file_put_contents($dir.$fileName, $imgData);
					$bannerImage=$dir.$fileName;
				}
				$data=array(
					"BannerImage"=>$bannerImage,
                    "BannerTitle"=>$req->BannerTitle,
					"BannerType"=>$req->BannerType,
					"updatedOn"=>date("Y-m-d H:i:s"),
					"updatedBy"=>$this->UserID
				);
				$status=DB::Table('tbl_banner_images')->where('TranNo',$TranNo)->update($data);
			}catch(Exception $e) {
				$status=false;
			}
			if($status==true){
				DB::commit();
				$NewData=DB::Table('tbl_banner_images')->where('TranNo',$TranNo)->get();
				$logData=array("Description"=>"Banner updated ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::UPDATE,"ReferID"=>$TranNo,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Banner image updated successfully");
			}else{
				if($bannerImage!=""){
					if(file_exists($bannerImage)){
						// unlink($bannerImage);
					}
				}
				DB::rollback();
				return array('status'=>false,'message'=>"Banner image update failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function Delete(Request $req,$TranNo){
		$OldData=$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$status=false;
			try{
				$OldData=DB::table('tbl_banner_images')->where('TranNo',$TranNo)->get();
				$status=DB::table('tbl_banner_images')->where('TranNo',$TranNo)->update(array("DFlag"=>1,"DeletedBy"=>$this->UserID,"DeletedOn"=>date("Y-m-d H:i:s")));
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
				$logData=array("Description"=>"Banner image has been Deleted ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::DELETE,"ReferID"=>$TranNo,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Banner image deleted successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Banner image delete failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function Restore(Request $req,$TranNo){
		$OldData=$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"restore")==true){
			DB::beginTransaction();
			$status=false;
			try{
				$OldData=DB::table('tbl_banner_images')->where('TranNo',$TranNo)->get();
				$status=DB::table('tbl_banner_images')->where('TranNo',$TranNo)->update(array("DFlag"=>0,"UpdatedBy"=>$this->UserID,"UpdatedOn"=>date("Y-m-d H:i:s")));
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
				$NewData=DB::table('tbl_banner_images')->where('TranNo',$TranNo)->get();
				$logData=array("Description"=>"Banner image has been Restored ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::RESTORE,"ReferID"=>$TranNo,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Banner image restored successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Banner image restore failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
}
