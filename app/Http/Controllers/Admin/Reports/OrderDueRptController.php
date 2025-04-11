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
class OrderDueRptController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::rptOrdersDue->value;
        $this->PageTitle="Orders Due Report";
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
            return view('app.reports.orders-due.index',$FormData);
        }else{
            return view('errors.403');
        }
    }
	private function generateTemp($req){
		$minDays=intval($req->minDays)>=0?intval($req->minDays):0;
		$uuid=Helper::RandomString(6);
		$tableName="tmp_order_due_rpt_".$uuid;
		$sql="CREATE TABLE IF NOT EXISTS  ".$this->tmpDBName.$tableName."(OrderID VarChar(50),OrderNo Varchar(50),OrderDate Date,LedgerName VarChar(200),CreditDays int(11) Default 0,OrderValue Double Default 0,PaidAmount Double Default 0,BalanceAmount Double Default 0,DaysOutstanding int(11) Default 0)";
		DB::Statement($sql);

		if($req->LedgerType=="Customer"){
			$sql="INSERT INTO ".$this->tmpDBName.$tableName."(OrderID,OrderNo,OrderDate,LedgerName,CreditDays,OrderValue,PaidAmount,BalanceAmount,DaysOutstanding)";
			$sql.=" SELECT O.OrderID,O.OrderNo,O.OrderDate,C.CustomerName,C.CreditDays,O.NetAmount,O.TotalPaidAmount,O.NetAmount-O.TotalPaidAmount as BalanceAmount,DATEDIFF('".date('Y-m-d')."',DATE_ADD(O.OrderDate, INTERVAL C.CreditDays DAY)) as Days ";
			$sql.=" FROM ".$this->FYDBName."tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID ";
			$sql.=" Where DATEDIFF('".date('Y-m-d')."',DATE_ADD(O.OrderDate, INTERVAL C.CreditDays DAY))>='".$minDays."' and O.Status<>'Cancelled' ";
			DB::Statement($sql);
		}
		if($req->LedgerType=="Vendor"){
			$sql="INSERT INTO ".$this->tmpDBName.$tableName."(OrderID,OrderNo,OrderDate,LedgerName,CreditDays,OrderValue,PaidAmount,BalanceAmount,DaysOutstanding)";
			$sql.=" SELECT O.VOrderID,O.OrderNo,O.OrderDate,V.VendorName,V.CreditDays,O.NetAmount,O.TotalPaidAmount,O.NetAmount-O.TotalPaidAmount as BalanceAmount,DATEDIFF('".date('Y-m-d')."',DATE_ADD(O.OrderDate, INTERVAL V.CreditDays DAY)) as Days ";
			$sql.=" FROM ".$this->FYDBName."tbl_vendor_orders as O LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID ";
			$sql.=" Where DATEDIFF('".date('Y-m-d')."',DATE_ADD(O.OrderDate, INTERVAL V.CreditDays DAY))>='".$minDays."' and O.Status<>'Cancelled' ";
			DB::Statement($sql);
		}
		return $this->tmpDBName.$tableName;
	}
	
    public function TableView(request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$tableName=$this->generateTemp($request);
			$columns = array(
				array( 'db' => 'OrderNo', 'dt' => '0'),
				array( 'db' => 'OrderDate','dt' => '1','formatter' => function( $d, $row ) { return date($this->Settings['date-format'],strtotime($d));} ),
				array( 'db' => 'LedgerName', 'dt' => '2'),
				array( 'db' => 'OrderValue', 'dt' => '3','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'PaidAmount', 'dt' => '4','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'BalanceAmount', 'dt' => '5','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'DaysOutstanding', 'dt' => '6'),
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$tableName;
			$data['PRIMARYKEY']='OrderID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=null;
			$return= SSP::SSP( $data);
			//$return['total']=$totals;
			return $return;
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
    }
}
