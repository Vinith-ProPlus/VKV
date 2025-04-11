<?php

namespace App\Http\Controllers\web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use App\Models\DocNum;
use App\Models\support;
use App\Models\general;
use App\Models\ServerSideProcess;
use DB;
use Auth;
use Hash;
use App\Rules\ValidUnique;
use App\Rules\ValidDB;
use logs;
class GeneralSettingsController extends Controller{
	private $general;
	private $support;
	private $DocNum;
	private $UserID;
	private $LoginType;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $logs;
	private $Settings;
    private $Menus;
    public function __construct(){
		$this->ActiveMenuName="General-Settings";
		$this->PageTitle="General Settings";
        $this->middleware('auth');
		$this->DocNum=new DocNum();
		$this->support=new support();
    
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->LoginType=auth()->user()->LoginType;
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
			$FormData['DateFormats']=DB::Table("tbl_formats")->where('FType','date')->get();
			$FormData['TimeFormats']=DB::Table("tbl_formats")->where('FType','time')->get();
			return view('app.settings.general.index',$FormData);
		}else{
			return view('errors.403');
		}
	}
	
	public function Update(Request $req){
		$sType=$req->sType;
		
		$data=(array)$req->all();
		unset($data['sType']);
		unset($data['PostalCode']);
		DB::beginTransaction();
		$status=true;
		try{
			foreach($data as $KeyName=>$KeyValue){
				if($status){
					$Rnd=$this->support->OTPGenerator(1);
					$Rnd1=$this->support->OTPGenerator(2);
					$UKey=$this->support->RandomString($Rnd1);
					if($sType=="social-media-links"){
						$sql="Update tbl_company_settings Set KeyValue='".$KeyValue."',UKey='".$UKey."',UpdatedBy='".$this->UserID."',UpdatedOn='".date('Y-m-d H:i:s',strtotime($Rnd." min "))."' Where KeyName='".$KeyName."'";
						$status=DB::Update($sql);
					}elseif($sType=="map"){
						$sql="Update tbl_company_settings Set KeyValue='".$KeyValue."',UKey='".$UKey."',UpdatedBy='".$this->UserID."',UpdatedOn='".date('Y-m-d H:i:s',strtotime($Rnd." min "))."' Where KeyName='".$KeyName."'";
						$status=DB::Update($sql);
					}else{
						$sql="Update tbl_settings Set KeyValue='".$KeyValue."',UKey='".$UKey."',UpdatedBy='".$this->UserID."',UpdatedOn='".date('Y-m-d H:i:s',strtotime($Rnd." min "))."' Where KeyName='".$KeyName."'";
						$status=DB::Update($sql);
					}
				}
			}
		}catch(Exception $e) {
			$status=false;
		}
		if($status==true){
			DB::commit();
			if($sType=="company"){
				return array('status'=>true,'message'=>"Social media links updated successfully");
			}elseif($sType=="map"){
				return array('status'=>true,'message'=>"Map updated successfully");
			}else{
				return array('status'=>true,'message'=>"General settings updated successfully");
			}
		}else{
			DB::rollback();
			if($sType=="company"){
				return array('status'=>false,'message'=>"Company Info update failed");
			}elseif($sType=="map"){
				return array('status'=>false,'message'=>"Map update failed");
			}else{
				return array('status'=>false,'message'=>"General settings update failed");
			}
		}
		return $data;
	}
}
