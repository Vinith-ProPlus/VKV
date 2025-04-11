<?php

namespace App\Http\Controllers\web\reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use Helper;
use activeMenuNames;
use docTypes;
use general;
use Ledgers;
use SSP;
class CommissionRptController extends Controller{
	private $general;
	private $support;
	private $DocNum;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $logs;
	private $Settings;
    private $FY;
	private $FYDBName;
	private $generalDB;
    private $Menus;
    public function __construct(){
		$this->ActiveMenuName=activeMenuNames::rptDeliveryStatus->value;
        $this->PageTitle="Commision Report";
        $this->middleware('auth');
		$this->generalDB=Helper::getGeneralDB();
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
            $this->FY=$this->general->UserInfo['FY'];
			$this->FYDBName=$this->FY->DBName!=""?$this->FY->DBName.".":"";
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
            return view('app.reports.commission.index',$FormData);
        }else{
            return view('errors.403');
        }
    }
	public function details(Request $req,$OrderID){
		$FormData=$this->general->UserInfo;
		$FormData['ActiveMenuName']=$this->ActiveMenuName;
		$FormData['PageTitle']=$this->PageTitle;
		$FormData['menus']=$this->Menus;
		$FormData['crud']=$this->CRUD;
		$FormData['OrderID']=$OrderID;
		$FormData['fromDate']=$req->from;
		$FormData['toDate']=$req->to;
		return view('app.reports.commission.details',$FormData);
	}
	public function getVendors(Request $req){
		$sql="Select DISTINCT(O.VendorID) as VendorID,V.VendorName,CONCAT( CASE WHEN IFNULL(CO.PhoneCode,'')<>'' Then CONCAT('+', CO.PhoneCode) ELSE '' END ,' ', V.MobileNumber1) as MobileNumber From ".$this->FYDBName."tbl_vendor_orders as O LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID ";
		$sql.="  LEFT JOIN ".$this->generalDB."tbl_countries as CO ON CO.CountryID=V.CountryID  ";
		$sql.=" Where V.ActiveStatus='Active' and V.DFlag=0 and  O.Status<>'Cancelled' and O.OrderDate>='".date("Y-m-d",strtotime($req->fromData))."' and O.OrderDate<='".date("Y-m-d",strtotime($req->toDate))."'";
		return DB::SELECT($sql);
	}
	
    public function TableView(request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$totals=["orderValues"=>0,"CommissionAmount"=>0];
			$sql="Select SUM(TotalAmount) as orderValues,SUM(CommissionAmount) as CommissionAmount  From ".$this->FYDBName."tbl_vendor_orders Where Status<>'Cancelled' and OrderDate>='".date("Y-m-d",strtotime($request->FromDate))."' and OrderDate<='".date("Y-m-d",strtotime($request->ToDate))."'";
			$result=DB::SELECT($sql);
			foreach($result as $item){
				$totals['orderValues']+=floatval($item->orderValues);
				$totals['CommissionAmount']+=floatval($item->CommissionAmount);
			}
			$totals['orderValues']=Helper::NumberFormat($totals['orderValues'],$this->Settings['price-decimals']);
			$totals['CommissionAmount']=Helper::NumberFormat($totals['CommissionAmount'],$this->Settings['price-decimals']);
			
			$columns = array(
				array( 'db' => 'O.OrderDate','dt' => '0'),
				array( 'db' => 'O.OrderNo', 'dt' => '1'),
				array( 'db' => 'V.VendorName', 'dt' => '2'),
				array( 'db' => 'O.TotalAmount', 'dt' => '3'),
				array( 'db' => 'O.CommissionPercentage', 'dt' => '4'),
				array( 'db' => 'O.CommissionAmount', 'dt' => '5'),
				array( 'db' => 'O.VOrderID', 'dt' => '6'),
			);
			$columns1 = array(
				array( 'db' => 'OrderDate','dt' => '0','formatter' => function( $d, $row ) { return date($this->Settings['date-format'],strtotime($d));} ),
				array( 'db' => 'OrderNo', 'dt' => '1'),
				array( 'db' => 'VendorName', 'dt' => '2'),
				array( 'db' => 'TotalAmount', 'dt' => '3','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'CommissionPercentage', 'dt' => '4','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['percentage-decimals']);}),
				array( 'db' => 'CommissionAmount', 'dt' => '5','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'VOrderID', 'dt' => '6'),
			);
			$where=" O.Status<>'Cancelled' and O.OrderDate>='".date("Y-m-d",strtotime($request->FromDate))."' and O.OrderDate<='".date("Y-m-d",strtotime($request->ToDate))."'";
			if($request->VendorID!=""){
				$where.=" and O.VendorID='".$request->VendorID."'";
			}
			if($request->includeZero==0){
				$where.=" and O.CommissionAmount>0";
			}
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$this->FYDBName."tbl_vendor_orders as O LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID ";
			$data['PRIMARYKEY']='O.VOrderID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns1;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=$where;
			$return= SSP::SSP( $data);
			$return['total']=$totals;
			return $return;
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
    }
}
