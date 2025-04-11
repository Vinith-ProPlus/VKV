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
class DeliveryStatusRptController extends Controller{
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
	private $tmpDBName;
    private $Menus;
    public function __construct(){
		$this->ActiveMenuName=activeMenuNames::rptDeliveryStatus->value;
        $this->PageTitle="Delivery Status Report";
        $this->middleware('auth');
		$this->generalDB=Helper::getGeneralDB();
		$this->tmpDBName=Helper::getTmpDB();
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
            return view('app.reports.delivery-status.index',$FormData);
        }else{
            return view('errors.403');
        }
    }
	public function getStatus(Request $req){
		$sql="SELECT DISTINCT(Status) as Status,CASE WHEN Status='Delivered' Then Status Else 'Not Delivered' end as StatusText  FROM ".$this->FYDBName."tbl_vendor_orders Where  Status in('New','Delivered') ";
		if($req->FromDate!=""){
			$sql.=" AND OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."'";
		}
		if($req->ToDate!=""){
			$sql.=" AND OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."'";
		}
		return DB::SELECT($sql);
	}
	public function getVendors(Request $req){
		$sql="SELECT DISTINCT(O.VendorID) as VendorID,V.VendorName FROM ".$this->FYDBName."tbl_vendor_orders as O LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID Where  1=1 ";
		if($req->FromDate!=""){
			$sql.=" AND O.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."'";
		}
		if($req->ToDate!=""){
			$sql.=" AND O.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."'";
		}
		if($req->status!=""){
			$status=json_decode($req->status,true);
			if(count($status)>0){
				$sql.=" and O.Status in('".implode("','",$status)."')";
			}
		}
		return DB::SELECT($sql);
	}
	public function getCustomers(Request $req){
		$sql="SELECT DISTINCT(O.CustomerID) as VendorID,C.CustomerName FROM ".$this->FYDBName."tbl_vendor_orders as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID Where  1=1 ";
		if($req->FromDate!=""){
			$sql.=" AND O.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."'";
		}
		if($req->ToDate!=""){
			$sql.=" AND O.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."'";
		}
		if($req->status!=""){
			$status=json_decode($req->status,true);
			if(count($status)>0){
				$sql.=" and O.Status in('".implode("','",$status)."')";
			}
		}
		if($req->vendorIDs!=""){
			$vendorIDs=json_decode($req->vendorIDs,true);
			if(count($vendorIDs)>0){
				$sql.=" and O.VendorID in('".implode("','",$vendorIDs)."')";
			}
		}
		return DB::SELECT($sql);
	}
	private function generateTemp($req){
		
		$uuid=Helper::RandomString(5);
		$tableName="tmp_status_rpt_".$uuid;
		$sql="CREATE TEMPORARY TABLE IF NOT EXISTS  ".$this->tmpDBName.$tableName."(OrderID VarChar(50),OrderNo VarChar(50),OrderDate Date,VendorName Varchar(200),CustomerName VarChar(200),OrderValues Double Default 0,Status VarChar(50),DeliveredOn Timestamp null default null,DaysFromOrder Int(11) Default 0)";
		DB::Statement($sql);

		$sql="INSERT INTO ".$this->tmpDBName.$tableName."(OrderID,OrderNo,OrderDate,VendorName,CustomerName,OrderValues,Status,DeliveredOn,DaysFromOrder)";
		$sql.="SELECT O.OrderID, O.OrderNo, O.OrderDate, V.VendorName, C.CustomerName, O.CommissionAmount+O.NetAmount,O.Status,O.DeliveredOn,DATEDIFF('".date("Y-m-d")."',O.OrderDate) as Days FROM ".$this->FYDBName."tbl_vendor_orders as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID";
		$sql.=" Where O.Status in('New','Delivered') ";
		if($req->FromDate!=""){
			$sql.=" AND O.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."'";
		}
		if($req->ToDate!=""){
			$sql.=" AND O.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."'";
		}
		if($req->status!=""){
			$status=json_decode($req->status,true);
			if(count($status)>0){
				$sql.=" and O.Status in('".implode("','",$status)."')";
			}
		}
		if($req->vendorIDs!=""){
			$vendorIDs=json_decode($req->vendorIDs,true);
			if(count($vendorIDs)>0){
				$sql.=" and O.VendorID in('".implode("','",$vendorIDs)."')";
			}
		}
		if($req->customerIDs!=""){
			$customerIDs=json_decode($req->customerIDs,true);
			if(count($customerIDs)>0){
				$sql.=" and O.CustomerID in('".implode("','",$customerIDs)."')";
			}
		}
		DB::Statement($sql);
		return $this->tmpDBName.$tableName;
	}
    public function TableView(request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$TableName=$this->generateTemp($request);

			$columns = array(
				array( 'db' => 'OrderNo', 'dt' => '0'),
				array( 'db' => 'OrderDate', 'dt' => '1','formatter' => function( $d, $row ) { return date($this->Settings['date-format'],strtotime($d));}),
				array( 'db' => 'VendorName', 'dt' => '2'),
				array( 'db' => 'CustomerName', 'dt' => '3'),
				array( 'db' => 'OrderValues', 'dt' => '4','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 
					'db' => 'Status', 
					'dt' => '5',
					'formatter' => function( $d, $row ) {
						if($d=="Delivered"){
							return '<span class="badge badge-success">Delivered</span>';
						}else{
							return '<span class="badge badge-danger">Not Delivered</span>';
						}
					}
				),
				array( 'db' => 'DeliveredOn', 'dt' => '6','formatter' => function( $d, $row ) { return  $d!=""?date($this->Settings['date-format'],strtotime($d)):"--";}),
				array( 'db' => 'DaysFromOrder', 'dt' => '7'),
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$TableName;
			$data['PRIMARYKEY']='OrderID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=null;
			$return= SSP::SSP( $data);
			return $return;
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
    }
}
