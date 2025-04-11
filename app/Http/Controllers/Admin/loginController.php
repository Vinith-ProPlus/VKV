<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Helper;
use logs;
use ValidUnique;
use ValidDB;
use DocNum;
use docTypes;
use Illuminate\Support\Facades\Hash;

class loginController extends Controller
{

    public function login(Request $req){
        $remember_me = $req->has('remember') ? true : false;
        $return=array('status'=>false);
        $result=DB::Table('users')->where('UserName',$req->email)->get();
        if(count($result)>0){
            if(($result[0]->DFlag==0)&&($result[0]->ActiveStatus=='Active')&&($result[0]->isLogin==1)){
                if(Auth::attempt(['UserName'=>$req->email,'password'=>$req->password,'ActiveStatus' => 1,'DFlag' => 0,'isLogin' => 1],$remember_me)){
                    return array("status"=>true,"message"=>"Login Successfully");
                }else{
                    $return['message']='login failed';
                    $return['password']='The user name and password not match.';
                }
            }elseif($result[0]->DFlag==1){
                $return['message']='Your account has been deleted.';
            }elseif($result[0]->ActiveStatus==0){
                $return['message']='Your account has been disabled.';
            }elseif($result[0]->isLogin==0){
                $return['message']='You dont have login rights.';
            }
        }else{
            $return['message']='login failed';
            $return['email']='User name does not exists. please verify user name.';
        }
        return $return;
    }

    public function CustomerMobileLogin(Request $req)
    {
        $remember_me = $req->has('remember') ? true : false;
        $return = array('status' => false);

        $result = DB::table('users')->where('LoginType', $req->LoginType)->where('MobileNumber', $req->MobileNumber)->first();

        if ($result) {
            if (($result->DFlag == 0) && ($result->ActiveStatus == 'Active') && ($result->isLogin == 1)) {
                $credentials = [
                    'UserName' => $result->UserName,
                    'password' => $result->UserName,
                    'LoginType' => $req->LoginType,
                    'ActiveStatus' => 1,
                    'DFlag' => 0,
                    'isLogin' => 1
                ];
                if (Auth::attempt($credentials, $remember_me)) {
                    return array("status" => true, "message" => "Login Successfully");
                } else {
                    $return['message'] = 'Login failed';
                }
            } elseif ($result->DFlag == 1) {
                $return['message'] = 'Your account has been deleted.';
            } elseif ($result->ActiveStatus == 0) {
                $return['message'] = 'Your account has been disabled.';
            } elseif ($result->isLogin == 0) {
                $return['message'] = 'You do not have login rights.';
            }
        } else {
            $return['message'] = 'Login failed';
            $return['mobile number'] = 'Account deactivated.';
        }
        return $return;
    }

    public function MobileNoRegister(Request $req)
    {
        if (!$req->OTP) {
            $status = false;
            $result = DB::Table('users')->where('MobileNumber', $req->MobileNumber)->where('LoginType', $req->LoginType)->first();
            if (is_null($result) || ($result && ($result->DFlag == 0) && ($result->ActiveStatus == 'Active') && ($result->isLogin == 1))) {
                $OTP = Helper::getOTP(6);
                $Message = "Your RPC OTP for login is $OTP. Please enter this code to proceed.";
                Helper::saveSmsOtp($req->MobileNumber, $OTP, $Message, $req->LoginType);
                $message = 'OTP sent!';
                $status = true;
            } elseif ($result->DFlag == 1) {
                $message = 'Your account has been deleted.';
            } elseif ($result->ActiveStatus == 'Inactive') {
                $message = 'Your account has been disabled.';
            } elseif ($result->isLogin == 0) {
                $message = 'You do not have login rights.';
            }
            return response()->json(['status' => $status, 'message' => $message]);
        } else {
            $OTP = DB::table(Helper::getCurrFYDB() . 'tbl_sms_otps')->where('MobileNumber', $req->MobileNumber)->where('isOtpExpired', 0)->value('OTP');
            if ($OTP == $req->OTP) {
                $UserData = DB::Table('users')->where('MobileNumber', $req->MobileNumber)->where('LoginType', $req->LoginType)->first();
                if ($UserData) {
                    $request = new Request([
                        'MobileNumber' => $UserData->MobileNumber,
                        'LoginType' => $req->LoginType,
                        'LoginMethod' => "MobileNumber"
                    ]);
                    return $this->CustomerMobileLogin($request);
                } else {
                    DB::beginTransaction();
                    $UserID = DocNum::getDocNum(docTypes::Users->value, '', Helper::getCurrentFY());
                    $pwd1 = Hash::make($req->MobileNumber);
                    $data = array(
                        "UserID" => $UserID,
                        "UserName" => $req->MobileNumber,
                        "MobileNumber" => $req->MobileNumber,
                        "password" => $pwd1,
                        "LoginType" => $req->LoginType,
                        "CreatedOn" => date("Y-m-d H:i:s"),
                        "CreatedBy" => $UserID
                    );
                    $status = DB::Table('users')->insert($data);
                    if ($status) {
                        DB::commit();
                        DocNum::updateDocNum(docTypes::Users->value);
                        $request = new Request([
                            'MobileNumber' => $req->MobileNumber,
                            'LoginType' => $req->LoginType,
                            'LoginMethod' => "MobileNumber",
                        ]);
                        return $this->CustomerMobileLogin($request);
                    } else {
                        DB::rollback();
                        return response()->json(['status' => false, 'message' => "Mobile Number Registration Failed!"]);
                    }
                    return response()->json(['status' => true, 'message' => "OTP Verified Successfully!"]);
                }
            } else {
                return response()->json(['status' => false, 'message' => "The OTP verification failed. Please enter the correct OTP."]);
            }
        }
    }
}
