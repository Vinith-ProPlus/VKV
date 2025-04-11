<?php

namespace App\Http\Controllers\web\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use App\Models\DocNum;
use App\Models\general;
use App\Models\ServerSideProcess;
use DB;
use Auth;
use Hash;
use cruds;
use App\Rules\ValidUnique;
use App\Rules\ValidDB;
use logs;
class CMSController extends Controller{
	private $general;
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
		$this->ActiveMenuName="Content-Management";
		$this->PageTitle="Content Management";
        $this->middleware('auth');
		$this->DocNum=new DocNum();
    
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
	public function index(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			return view('app.settings.cms.view',$FormData);
		}else{
			return view('errors.403');
		}
	}
    
	public function edit(Request $req,$CID=null){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$FormData=$this->general->UserInfo;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=true;
			$FormData['menus']=$this->Menus;
			$FormData['crud']=$this->CRUD;
			$FormData['CID']=$CID;
			$FormData['EditData']=DB::Table('tbl_page_content')->where('DFlag',0)->Where('CID',$CID)->get();
			if(count($FormData['EditData'])>0){
				return view('app.settings.cms.create',$FormData);
			}else{
				return view('errors.400');
			}
		}else{
			return view('errors.403');
		}
	}
	public function Update(Request $req,$CID){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){

			$OldData=DB::Table('tbl_page_content')->where('CID',$CID)->get();$NewData=array();
			DB::beginTransaction();
			$status=true;
			try{


				$data=array(
                    "PageContent"=>$req->pageContent,
					"UpdatedOn"=>date("Y-m-d H:i:s"),
					"UpdatedBy"=>$this->UserID
				);
				$status=DB::Table('tbl_page_content')->where('CID',$CID)->update($data);
			}catch(Exception $e) {
				$status=false;
			}
			if($status==true){
				DB::commit();
				$NewData=DB::Table('tbl_page_content')->where('CID',$CID)->get();
				$logData=array("Description"=>"CMS Page Content updated","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::UPDATE,"ReferID"=>$CID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"CMS Page Content updated successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"CMS Page Content update failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function TableView(Request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$ServerSideProcess=new ServerSideProcess();
			$columns = array(
                array( 'db' => 'CID', 'dt' => '0' ),
                array( 'db' => 'PageName', 'dt' => '1' ),
				array( 
						'db' => 'ActiveStatus', 
						'dt' => '2',
						'formatter' => function( $d, $row ) {
							if($d=="1"){
								return "<span class='badge badge-success m-1'>Active</span>";
							}else{
								return "<span class='badge badge-danger m-1'>Inactive</span>";
							}
						} 
                    ),
				array(
						'db' => 'CID', 
						'dt' => '3',
						'formatter' => function( $d, $row ) {
							$html='';
							if($this->general->isCrudAllow($this->CRUD,"edit")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-success m-5 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
							}
							if($this->general->isCrudAllow($this->CRUD,"delete")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-danger m-5 btnDelete" data-original-title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>';
							}
							return $html;
						} 
				)
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=' tbl_page_content';
			$data['PRIMARYKEY']='CID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=" DFlag=0 ";
			return $ServerSideProcess->SSP( $data);
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
}
