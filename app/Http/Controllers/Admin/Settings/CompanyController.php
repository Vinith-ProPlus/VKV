<?php
namespace App\Http\Controllers\web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use DocNum;
use general;
use SSP;
use DB;
use Auth;
use ValidUnique;
use logs;
use Helper;
use activeMenuNames;
use docTypes;
use cruds;
class CompanyController extends Controller{
	private $general;
	private $DocNum;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $Settings;
	private $FileTypes;
    private $Menus;
	private $generalDB;
    public function __construct(){
		$this->ActiveMenuName=activeMenuNames::Company->value;
		$this->PageTitle="Company";
        $this->middleware('auth');
		$this->FileTypes=Helper::getFileTypes(array("category"=>array("Images")));
		$this->middleware(function ($request, $next) {
			$this->generalDB=Helper::getGeneralDB();
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			return $next($request);
		});
    }
    public function edit(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"edit")==true){
            $OtherCruds=array(
				"Country"=>$this->general->getCrudOperations(activeMenuNames::Country->value),
				"States"=>$this->general->getCrudOperations(activeMenuNames::States->value),
				"Districts"=>$this->general->getCrudOperations(activeMenuNames::Districts->value),
				"Taluks"=>$this->general->getCrudOperations(activeMenuNames::Taluks->value),
				"PostalCodes"=>$this->general->getCrudOperations(activeMenuNames::PostalCodes->value),
				"City"=>$this->general->getCrudOperations(activeMenuNames::City->value),
				"VendorCategory"=>$this->general->getCrudOperations(activeMenuNames::VendorCategory->value),
			);
            $FormData=$this->general->UserInfo;
			$FormData['OtherCruds']=$OtherCruds;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=true;
			$FormData['FileTypes']=$this->FileTypes;
			$FormData['EditData']=DB::Table('tbl_company_settings')->get();
			if(count($FormData['EditData'])>0){
				$FormData['PostalCode'] = DB::table($this->generalDB.'tbl_postalcodes')->where('PID', $FormData['EditData'][6]->KeyValue)->value('PostalCode');
				return view('app.settings.company.company',$FormData);
			}else{
				return view('errors.403');
			}
        }else{
            return view('errors.403');
        }
    }
    public function Update1(Request $req){
		// return $req;
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$OldData=array();$NewData=array();
			$rules=array();
			$message=array(
				'CompanyLogo.mimes'=>"The Company Logo field must be a file of type: ".implode(", ",$this->FileTypes['category']['Images'])."."
			);
			if($req->hasFile('CompanyLogo')){
				$rules['CompanyLogo']='mimes:'.implode(",",$this->FileTypes['category']['Images']);
			}
			$validator = Validator::make($req->all(), $rules,$message);
			if ($validator->fails()) {
				return array('status'=>false,'message'=>"Brand Update Failed",'errors'=>$validator->errors());			
			}
			DB::beginTransaction();
			$status=false;
			$currBLogo=array();
			$images=array();
			try {
				$OldData=DB::table('tbl_company_settings')->get();
				return $OldData;
				$CompanyLogo="";
				$dir="assets/images/logo/";
				if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
				if($req->hasFile('CompanyLogo')){
					$file = $req->file('CompanyLogo');
					$fileName=md5($file->getClientOriginalName() . time());
					$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
					$file->move($dir, $fileName1);
					$CompanyLogo=$dir.$fileName1;
				}else if(Helper::isJSON($req->CompanyLogo)==true){
					$Img=json_decode($req->CompanyLogo);
					if(file_exists($Img->uploadPath)){
						$fileName1="logo.png";
						copy($Img->uploadPath,$dir.$fileName1);
						$CompanyLogo=$dir.$fileName1;
						unlink($Img->uploadPath);
					}
				}
				if(file_exists($CompanyLogo)){
					$images=helper::ImageResize($CompanyLogo,$dir);
				}
				if(($CompanyLogo!="" || intval($req->removeCompanyLogo)==1) && Count($OldData)>0){ 
					$currBLogo=$OldData[0]->Images!=""?unserialize($OldData[0]->Images):array();
				}
				$data=array(
					"BrandName"=>$req->BrandName,
					"ActiveStatus"=>$req->ActiveStatus,
					"UpdatedBy"=>$this->UserID,
					"UpdatedOn"=>date("Y-m-d H:i:s")
				);
				if($CompanyLogo!=""){
					$data['CompanyLogo']=$CompanyLogo;
					$data['Images']=serialize($images);
				}else if(intval($req->removeCompanyLogo)==1){
					$data['CompanyLogo']="";
					$data['Images']=serialize(array());
				}
				$status=DB::Table('tbl_company_settings')->where('BrandID',$BrandID)->update($data);
			}catch(Exception $e) {
				$status=false;
			}

			if($status==true){
				DB::commit();
				$NewData=DB::table('tbl_company_settings')->where('BrandID',$BrandID)->get();
				$logData=array("Description"=>"Brand Updated ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::UPDATE->value,"ReferID"=>$BrandID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				//Helper::removeFile($currBLogo);
				
				foreach($currBLogo as $KeyName=>$Img){
					Helper::removeFile($Img['url']);
				}
				return array('status'=>true,'message'=>"Brand Updated Successfully");
			}else{
				DB::rollback();
				foreach($images as $KeyName=>$Img){
					Helper::removeFile($Img['url']);
				}
				return array('status'=>false,'message'=>"Brand Update Failed");
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}
	}
	public function Update(Request $req){
		$OldData=$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$rules=array();
			$message=array(
				'CompanyLogo.mimes'=>"The Company Logo field must be a file of type: ".implode(", ",$this->FileTypes['category']['Images'])."."
			);
			if($req->hasFile('CompanyLogo')){
				$rules['CompanyLogo']='mimes:'.implode(",",$this->FileTypes['category']['Images']);
			}
			$validator = Validator::make($req->all(), $rules,$message);
			if ($validator->fails()) {
				return array('status'=>false,'message'=>"Brand Update Failed",'errors'=>$validator->errors());			
			}
			DB::beginTransaction();
			$status=false;
			try{
				$OldData=DB::table('tbl_company_settings')->get();
				$Settings = DB::table('tbl_company_settings')->select('KeyName', 'KeyValue')->get();
				$CompanyLogo="";
				$dir="assets/images/logo/";
				if (!file_exists( $dir)) {mkdir( $dir, 0777, true);}
				if($req->hasFile('CompanyLogo')){
					$file = $req->file('CompanyLogo');
					$fileName=md5($file->getClientOriginalName() . time());
					$fileName1 =  $fileName. "." . $file->getClientOriginalExtension();
					$file->move($dir, $fileName1);
					$CompanyLogo=$dir.$fileName1;
				}else if(Helper::isJSON($req->CompanyLogo)==true){
					$Img=json_decode($req->CompanyLogo);
					if(file_exists($Img->uploadPath)){
						$fileName1="logo.png";
						copy($Img->uploadPath,$dir.$fileName1);
						$CompanyLogo=$dir.$fileName1;
						unlink($Img->uploadPath);
					}
				}
				if(file_exists($CompanyLogo)){
					$images=helper::ImageResize($CompanyLogo,$dir);
				}
				if(intval($req->removeCompanyLogo)==1){ 
					$CompanyLogo="";
				}
				$data = [
					'CompanyName' => $req->CompanyName,
					'Address' => $req->Address,
					'CountryID' => $req->Country,
					'StateID' => $req->State,
					'CityID' => $req->City,
					'PostalCodeID' => $req->PostalCode,
					'GSTNo' => $req->GSTNumber,
					'Phone-Number' => $req->MobileNumber,
					'Mobile-Number' => $req->AMobileNumber,
					'E-Mail' => $req->Email,
					'PANNo' => $req->PanNumber,
					'BankName' => $req->Bank,
					'BankBranchName' => $req->BankBranch,
					'BankAccountNo' => $req->BankAccNo,
					'BankAccountType' => $req->BankAccType,
					'facebook' => $req->Facebook,
					'instagram' => $req->Instagram,
					'twitter' => $req->Twitter,
					'youtube' => $req->YouTube,
					'linkedin' => $req->LinkedIn,
					'pinterest' => $req->Pinterest,
					// 'TalukID' => $req->Taluk,
					'DistrictID' => $req->District,
					'BankType' => $req->BankType,
				];
				if($CompanyLogo!=""){
					$data['Logo']=$CompanyLogo;
					$data['Images']=serialize($images);
				}else if(intval($req->removeCompanyLogo)==1){
					$data['Logo']="";
					$data['Images']=serialize(array());
				}
				$status = true;
				foreach ($data as $key => $value) {
					$setting = $Settings->where('KeyName', $key)->first();
					
					if ($setting && $setting->KeyValue !== $value) {
						$status = DB::table('tbl_company_settings')->where('KeyName', $key)->update(['KeyValue' => $value]);
					}
				}
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
				$NewData=DB::table('tbl_company_settings')->get();
				$logData=array("Description"=>"Company Settings Updated ","ModuleName"=>"Settings","Action"=>"Update","ReferID"=>"Settings","OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Company Settings Updated Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Company Settings Update Failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	
}
