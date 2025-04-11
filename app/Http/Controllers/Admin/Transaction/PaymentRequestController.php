<?php
namespace App\Http\Controllers\web\Transaction;

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
use Helper;
use ValidUnique;
use logs;
use activeMenuNames;
use docTypes;
use cruds;

class PaymentRequestController extends Controller{
	private $general;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $Settings;
    private $Menus;
	private $generalDB;
	private $logDB;
    private $CurrFYDB;

    public function __construct(){
		$this->ActiveMenuName=activeMenuNames::PaymentRequest->value;
		$this->PageTitle="Payment Request";
        $this->middleware('auth');    
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			$this->generalDB=Helper::getGeneralDB();
			$this->logDB=Helper::getLogDB();
			$this->CurrFYDB=Helper::getCurrFYDB();
			return $next($request);
		});
    }
    public function view(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"view")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
            return view('app.transaction.payment-request.view',$FormData);
        }else{
            return view('errors.403');
        }
    }
	public function updateStatus(Request $req){
		$status=DB::Table($this->CurrFYDB."tbl_withdraw_request")->where('WithdrawID',$req->ReqID)->update(['status'=>$req->Status,"UpdatedOn"=>now(),"UpdatedBy"=>$this->UserID]);
		return ["status"=>$status];
	}
	public function TableView(Request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$columns = array(
				array( 'db' => 'PR.WithdrawID', 'dt' => '0' ),
				array( 'db' => 'PR.ReqOn', 'dt' => '1' ),
				array( 'db' => 'V.VendorName', 'dt' => '2' ),
				array( 'db' => 'V.MobileNumber1', 'dt' => '3' ),
				array( 'db' => 'PR.ReqAmount', 'dt' => '4' ),
				array( 'db' => 'PR.Status', 'dt' => '5' ),
				array( 'db' => 'CO.PhoneCode', 'dt' => '6' ),
			);
			$columns1 = array(
				array( 'db' => 'WithdrawID', 'dt' => '0' ),
				array( 'db' => 'ReqOn', 'dt' => '1' ,'formatter' => function( $d, $row ) { return date($this->Settings['date-format'],strtotime($d));} ),
				array( 'db' => 'VendorName', 'dt' => '2' ),
				array( 
					'db' => 'MobileNumber1', 
					'dt' => '3' ,
					'formatter' => function( $d, $row ) { 
						$phoneCode=$row['PhoneCode']!=""?"+".$row['PhoneCode']:"";
						return $phoneCode." ".$d;
					}
				),
				array( 'db' => 'ReqAmount', 'dt' => '4', 'formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);} ),
				array( 
					'db' => 'Status', 
					'dt' => '5' ,
					'formatter' => function( $d, $row ) {
						$tdata=array('Requested', 'Rejected', 'Sent');
						$html='<select class="form-control lstTStatus" data-id="'.$row['WithdrawID'].'">';
							foreach($tdata as $status){
								$selected=$d==$status?"selected":"";
								$html.='<option '.$selected.' value="'.$status.'">'.$status.'</option>';
							}
						$html.='</select>';
						return $html;
					}
				),
				array( 'db' => 'PhoneCode', 'dt' => '6' ),
			);
			$Where=" 1=1 ";
			if($request->status){
				$status=json_decode($request->status,true);
				if(count($status)>0){
					$Where.=" and PR.Status in('".implode("','",$status)."')";
				}
			}
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$this->CurrFYDB.'tbl_withdraw_request as PR LEFT JOIN tbl_vendors as V ON V.VendorID = PR.VendorID LEFT JOIN '.$this->generalDB.'tbl_countries as CO On CO.CountryID=V.CountryID';
			$data['PRIMARYKEY']='PR.WithdrawID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns1;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=$Where;
			return SSP::SSP( $data);
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function getSearchStatus(Request $req){
		$sql="Select DISTINCT(Status) as Status From ".$this->CurrFYDB."tbl_withdraw_request  Where 1=1 ";
		return DB::SELECT($sql);
	}
}
