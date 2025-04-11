<?php

namespace App\Http\Controllers\web\users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Helper;
use DocNum;
use general;
use DB;
use Auth;
use Hash;
use Session;
use ValidUnique;
use ValidDB;
use logs;
use cruds;
use activeMenuNames;

class PasswordChangeController extends Controller{
	private $general;
	private $support;
	private $UserID;
	private $LoginType;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $logs;
	private $Settings;
    private $Menus;
	public function __construct(){
		$this->ActiveMenuName=activeMenuNames::PasswordChange->value;
		$this->PageTitle="Password Change";
        $this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->LoginType=auth()->user()->LoginType;
			$this->general=new general($this->UserID,$this->ActiveMenuName,auth()->user()->LoginType);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			return $next($request);
		});
    }
	
	
	public function PasswordChange(Request $req){
		$FormData=$this->general->UserInfo;
		$FormData['ActiveMenuName']=$this->ActiveMenuName;
		$FormData['PageTitle']=$this->PageTitle;
		$FormData['menus']=$this->Menus;
		$FormData['crud']=$this->CRUD;	
		return view('app.users.password-change.password-change',$FormData);
	}
	public function PasswordUpdate(Request $req){
		$user = DB::Table('users')->where('UserID',$this->UserID)->get();
		$OldData=$NewData=array();
		$rules=array(			
				'Password' =>'required|min:3|max:20',
				'ConfirmPassword' =>'required|min:3|max:20|same:Password',
				'CurrentPassword' =>['required',function($attribute, $value, $fail){
					$hasher = app('hash');
					if (!$hasher->check($value, Auth::user()->password)) {
						return $fail(__('The current password is incorrect.'));
					}
				}],
				);
			$message=array(
				'Password.required'=>'Password  is required',
				'Password.min'=>'Password  must be at least 3 characters',
				'Password.max'=>'Password  may not be greater than 20 characters',
				'CurrentPassword.required'=>'Current Password  is required',
				'ConfirmPassword.required'=>'Confirm Password  is required',
				'ConfirmPassword.min'=>'Confirm Password  must be at least 3 characters',
				'ConfirmPassword.max'=>'Confirm Password  may not be greater than 20 characters',
			);
			$validator = Validator::make($req->all(), $rules,$message);
			if ($validator->fails()) {
				return array('success'=>false,'message'=>"Password Change failed",'errors'=>$validator->errors());			
			}
			DB::beginTransaction();
			$status=false;$csrf="";
			try{
				$OldData=DB::Table('users')->where('UserID',$this->UserID)->get();
				$Password=Hash::make($req->Password);
				$Password1=Helper::EncryptDecrypt("ENCRYPT",$req->Password);
				$sql="Update users set Password='".$Password."',Password1='".$Password1."',UpdatedOn='".date("Y-m-d H:i:s")."',UpdatedBy='".$this->UserID."' where UserID='".$this->UserID."'";
				$status=DB::update($sql);
			}catch(Exception $e) {
				
			}
			if($status==true){
				DB::commit();
				//Auth::attempt(['email'=>auth()->user()->email,'password'=>$req->password,'ActiveStatus' => 1,'DFlag' => 0,'isLogin' => 1,'email_verify'=>1],true);
				$NewData=DB::Table('users')->where('UserID',$this->UserID)->get();
				$logData=array("Description"=>"Password Changed successfully ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::UPDATE->value,"ReferID"=>$this->UserID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Password Changed successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Password Change Failed");
			}
	}
}
