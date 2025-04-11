<?php

namespace App\Http\Controllers\web\Settings;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Models\DocNum;
use App\Models\general;
use App\Models\support;
use Illuminate\Support\Facades\DB;
use App\enums\cruds;
use logs;
use Mockery\Undefined;

class StepperController extends Controller
{
    private $general;
    private $support;
    private $DocNum;
    private $UserID;
    private $ActiveMenuName;
    private $PageTitle;
    private $CRUD;
    private $Settings;
    private $Menus;

    public function __construct()
    {
        $this->ActiveMenuName = "Steppers";
        $this->PageTitle = "Steppers";
        $this->middleware('auth');
        $this->DocNum = new DocNum();
        $this->support = new support();
        $this->middleware(function ($request, $next) {
            $this->UserID = auth()->user()->UserID;
            $this->general = new general($this->UserID, $this->ActiveMenuName);
            $this->Menus = $this->general->loadMenu();
            $this->CRUD = $this->general->getCrudOperations($this->ActiveMenuName);
            $this->Settings = $this->general->getSettings();
            return $next($request);
        });
    }

    public function index(Request $req)
    {
        if ($this->general->isCrudAllow($this->CRUD, "view") == true) {
            $FormData = $this->general->UserInfo;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            $FormData['StepperImages'] = DB::Table('tbl_stepper_images')->where('StepperType', 'Web')->where('DFlag', 0)->orderBy('StepperTitle')->get();
            $FormData['MStepperImages'] = DB::Table('tbl_stepper_images')->where('StepperType', 'Customer App')->where('DFlag', 0)->orderBy('TranNo')->get();
            $FormData['OGStepperImages'] = DB::Table('tbl_stepper_images')->where('StepperType', 'Customer OG')->where('DFlag', 0)->orderBy('TranNo')->get();
            $FormData['VStepperImages'] = DB::Table('tbl_stepper_images')->where('StepperType', 'Vendor App')->where('DFlag', 0)->orderBy('TranNo')->get();
            return view('app.settings.steppers.view', $FormData);
        } elseif ($this->general->isCrudAllow($this->CRUD, "Add") == true) {
            return Redirect::to('/admin/settings/steppers/create');
        } else {
            return view('errors.403');
        }
    }

    public function Edit(Request $req, $TranNo = null)
    {
        if ($this->general->isCrudAllow($this->CRUD, "edit") == true) {
            $FormData = $this->general->UserInfo;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            $FormData['isEdit'] = true;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            $FormData['TranNo'] = $TranNo;
            $FormData['EditData'] = DB::Table('tbl_stepper_images')->where('TranNo', $TranNo)->where('DFlag', 0)->get();
            if (count($FormData['EditData']) > 0) {
                return view('app.settings.steppers.upload', $FormData);
            } else {
                return view('errors.400');
            }
        } else {
            return view('errors.403');
        }
    }

    private function getImageData($base64)
    {
        $base64_str = substr($base64, strpos($base64, ",") + 1);
        $image = base64_decode($base64_str);
        return $image;
    }

    public function Update(Request $req, $TranNo)
    {
        if ($this->general->isCrudAllow($this->CRUD, "edit") == true) {
            $OldData = DB::Table('tbl_stepper_images')->where('TranNo', $TranNo)->get();
            DB::beginTransaction();
            $StepperImage = "";
            try {
                $dir = "uploads/settings/stepper/";
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                if ($req->hasFile('StepperImage')) {
                    $file = $req->file('StepperImage');
                    $fileName = md5($file->getClientOriginalName() . time());
                    $fileName1 = $fileName . "." . $file->getClientOriginalExtension();
                    $file->move($dir, $fileName1);
                    $StepperImage = $dir . $fileName1;
                } else if ($req->StepperImage != "undefined") {
                    $rnd = $this->support->RandomString(10) . "_" . date("YmdHis");
                    $fileName = $rnd . ".png";
                    $imgData = $this->getImageData($req->StepperImage);
                    file_put_contents($dir . $fileName, $imgData);
                    $StepperImage = $dir . $fileName;
                }
                $data = array(
                    "StepperTitle" => $req->StepperTitle,
                    "Description" => $req->Description ?? '',
                    "StepperType" => $req->StepperType,
                    "updatedOn" => date("Y-m-d H:i:s"),
                    "updatedBy" => $this->UserID
                );
                if($StepperImage){
                    $data['StepperImage'] = $StepperImage;
                }
                $status = DB::Table('tbl_stepper_images')->where('TranNo', $TranNo)->update($data);
            } catch (Exception $e) {
                $status = false;
            }
            if ($status == true) {
                DB::commit();
                $NewData = DB::Table('tbl_stepper_images')->where('TranNo', $TranNo)->get();
                $logData = array("Description" => "Stepper updated ", "ModuleName" => $this->ActiveMenuName, "Action" => cruds::UPDATE, "ReferID" => $TranNo, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $this->UserID, "IP" => $req->ip());
                logs::Store($logData);
                return array('status' => true, 'message' => "Stepper image updated successfully");
            } else {
                if ($StepperImage != "") {
                    if (file_exists($StepperImage)) {
                        // unlink($StepperImage);
                    }
                }
                DB::rollback();
                return array('status' => false, 'message' => "Stepper image update failed");
            }
        } else {
            return response(array('status' => false, 'message' => "Access Denied"), 403);
        }
    }
}
