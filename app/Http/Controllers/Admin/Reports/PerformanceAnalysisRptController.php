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
class PerformanceAnalysisRptController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::rptPerformanceAnalysis->value;
        $this->PageTitle="Performance Analysis Report";
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
            return view('app.reports.performance-analysis.index',$FormData);
        }else{
            return view('errors.403');
        }
    }
	private function generateTempTable($req){
		$limit=floatval($req->limit);
		$uuid=Helper::RandomString(5);
		$tableName1="tmp_performance_rpt1_".$uuid;
		$tableName="tmp_performance_rpt_".$uuid;
		$sql="CREATE TEMPORARY TABLE IF NOT EXISTS  ".$this->tmpDBName.$tableName1."(SLNo int(11) Primary Key AUTO_INCREMENT,Entity enum('Vendors','Customers','Category','Sub-Category','Product'),EntityID Varchar(50), EntityName Varchar(200),OrderCount Double default 0,OrdersValues Double Default 0)";
		DB::Statement($sql);
		$sql="CREATE TEMPORARY TABLE IF NOT EXISTS  ".$this->tmpDBName.$tableName."(SLNo int(11) Primary Key AUTO_INCREMENT,EntityID Varchar(50), EntityName Varchar(200),OrderCount Double default 0,OrdersValues Double Default 0)";
		DB::Statement($sql);

		if($req->Entity=="Vendors"){
			$sql=" Insert Into ".$this->tmpDBName.$tableName1."(EntityID,EntityName,Entity,OrderCount,OrdersValues)";
			$sql.="SELECT O.VendorID,V.VendorName,'Vendors' as Entity,1, (O.NetAmount+O.CommissionAmount) as orderValue FROM ".$this->FYDBName."tbl_vendor_orders as O LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID";
			$sql.=" Where O.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."' and O.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."' and O.Status<>'Cancelled' ";
			DB::Statement($sql);
		}
		if($req->Entity=="Customers"){
			$sql=" Insert Into ".$this->tmpDBName.$tableName1."(EntityID,EntityName,Entity,OrderCount,OrdersValues)";
			$sql.="SELECT O.CustomerID,C.CustomerName,'Customers' as Entity,1, O.NetAmount as orderValue FROM ".$this->FYDBName."tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID";
			$sql.=" Where O.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."' and O.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."' and O.Status<>'Cancelled' ";
			DB::Statement($sql);
		}
		if($req->Entity=="Category"){
			$sql=" Insert Into ".$this->tmpDBName.$tableName1."(EntityID,EntityName,Entity,OrderCount,OrdersValues)";
			$sql.="SELECT P.CID,C.PCName,'Category' as Entity,OD.Qty,OH.NetAmount FROM ".$this->FYDBName."tbl_order_details as  OD LEFT JOIN ".$this->FYDBName."tbl_order as OH ON OH.OrderID=OD.OrderID LEFT JOIN tbl_products as P ON P.ProductID=OD.ProductID LEFT JOIN tbl_product_category as C ON C.PCID=P.CID LEFT JOIN tbl_product_subcategory as SC ON SC.PSCID=P.SCID ";
			$sql.=" Where OH.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."' and OH.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."' and OD.Status<>'Cancelled' ";
			DB::Statement($sql);
		}
		if($req->Entity=="Sub-Category"){
			
			$sql=" Insert Into ".$this->tmpDBName.$tableName1."(EntityID,EntityName,Entity,OrderCount,OrdersValues)";
			$sql.="SELECT P.SCID,SC.PSCName,'Sub-Category' as Entity,OD.Qty,OH.NetAmount FROM ".$this->FYDBName."tbl_order_details as  OD LEFT JOIN ".$this->FYDBName."tbl_order as OH ON OH.OrderID=OD.OrderID LEFT JOIN tbl_products as P ON P.ProductID=OD.ProductID LEFT JOIN tbl_product_category as C ON C.PCID=P.CID LEFT JOIN tbl_product_subcategory as SC ON SC.PSCID=P.SCID ";
			$sql.=" Where OH.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."' and OH.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."' and OD.Status<>'Cancelled' ";
			DB::Statement($sql);

		}
		if($req->Entity=="Product"){
			$sql=" Insert Into ".$this->tmpDBName.$tableName1."(EntityID,EntityName,Entity,OrderCount,OrdersValues)";
			$sql.="SELECT P.ProductID,P.ProductName,'Product' as Entity,OD.Qty,OH.NetAmount FROM ".$this->FYDBName."tbl_order_details as  OD LEFT JOIN ".$this->FYDBName."tbl_order as OH ON OH.OrderID=OD.OrderID LEFT JOIN tbl_products as P ON P.ProductID=OD.ProductID LEFT JOIN tbl_product_category as C ON C.PCID=P.CID LEFT JOIN tbl_product_subcategory as SC ON SC.PSCID=P.SCID ";
			$sql.=" Where OH.OrderDate>='".date("Y-m-d",strtotime($req->FromDate))."' and OH.OrderDate<='".date("Y-m-d",strtotime($req->ToDate))."' and OD.Status<>'Cancelled' ";
			DB::Statement($sql);

		}

		



		$sql1="SELECT EntityID,EntityName,SUM(OrderCount) as OrderCount,SUM(OrdersValues) as OrdersValues From ".$this->tmpDBName.$tableName1." Where Entity='".$req->Entity."' Group By EntityID,EntityName,Entity";
		
		$sql =" Insert Into ".$this->tmpDBName.$tableName."(EntityID,EntityName,OrderCount,OrdersValues)";
		$sql.="SELECT EntityID,EntityName,OrderCount,OrdersValues From (".$sql1.") as T Order By OrdersValues desc  Limit 0,".$limit;
		DB::Statement($sql);

		return $this->tmpDBName.$tableName;
	}
    public function TableView(request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$TableName=$this->generateTempTable($request);

			$columns = array(
				array( 'db' => 'SLNo', 'dt' => '0'),
				array( 'db' => 'EntityName', 'dt' => '1'),
				array( 'db' => 'OrderCount', 'dt' => '2'),
				array( 'db' => 'OrdersValues', 'dt' => '3','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$TableName;
			$data['PRIMARYKEY']='SLNo';
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
