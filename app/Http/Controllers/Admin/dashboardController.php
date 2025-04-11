<?php

namespace App\Http\Controllers\web;

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
use Carbon\Carbon;
class dashboardController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::Dashboard->value;
        $this->PageTitle="Dashboard";
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
    public function dashboard(Request $req){
		//return $this->getOutstandings();
        $FormData=$this->general->UserInfo;
        $FormData['ActiveMenuName']=$this->ActiveMenuName;
        $FormData['PageTitle']=$this->PageTitle;
        $FormData['menus']=$this->Menus;
        $FormData['crud']=$this->CRUD;
        $FormData['DashboardType']=auth()->user()->DashboardType;
        return view('app.dashboard',$FormData);
    }
	public function getDashboardStats(){
		$tmp=Ledgers::getLedger(["FYDBName"=>$this->FYDBName]);
		$return=json_decode(
					json_encode(
						[
							"customer"=>[
								"orderValues"=>0,
								"received"=>0,
								"outstanding"=>0,
								"stats"=>[
									"active"=>0,
									"inactive"=>0,
									"deleted"=>0,
									"total"=>0
								]
							],
							"vendor"=>[
								"orderValues"=>0,
								"paid"=>0,
								"outstanding"=>0,
								"stats"=>[
									"active"=>0,
									"inactive"=>0,
									"deleted"=>0,
									"total"=>0
								]
							],
							"employee"=>[
								"stats"=>[
									"active"=>0,
									"inactive"=>0,
									"deleted"=>0,
									"total"=>0
								]
							]
						]
					)
				);
		//Customer
		$sql="SELECT LedgerType,IFNULL(SUM(IFNULL(debit,0)),0) as ordersValue,IFNULL(SUM(IFNULL(Credit,0)),0) as Received,IFNULL(SUM(IFNULL(debit,0)),0)- IFNULL(SUM(IFNULL(Credit,0)),0) as Balance FROM  ".$tmp->DBName.$tmp->TableName." Where LedgerType='Customer' and PaymentType='Order' Group By LedgerType";
		$result=DB::SELECT($sql);
		if(count($result)>0){
			$return->customer->orderValues=Helper::shortenValue($result[0]->ordersValue);
			$return->customer->received=Helper::shortenValue($result[0]->Received);
			$return->customer->outstanding=Helper::shortenValue($result[0]->Balance);
		}
		//Vendor
		$sql="SELECT LedgerType,IFNULL(SUM(IFNULL(Credit,0)),0) as ordersValue,IFNULL(SUM(IFNULL(Debit,0)),0) as paid,IFNULL(SUM(IFNULL(Credit,0)),0)- IFNULL(SUM(IFNULL(Debit,0)),0) as Balance FROM  ".$tmp->DBName.$tmp->TableName." Where LedgerType='Vendor' and PaymentType='Order' Group By LedgerType";
		$result=DB::SELECT($sql);
		if(count($result)>0){
			$return->vendor->orderValues=Helper::shortenValue($result[0]->ordersValue);
			$return->vendor->paid=Helper::shortenValue($result[0]->paid);
			$return->vendor->outstanding=Helper::shortenValue($result[0]->Balance);
		}
		Ledgers::dropTable($tmp->TableName,$tmp->DBName);

		//vendor stats
		$return->vendor->stats->total=DB::Table('tbl_vendors')->count();
		$return->vendor->stats->deleted=DB::Table('tbl_vendors')->where('DFlag',1)->count();
		$return->vendor->stats->inactive=DB::Table('tbl_vendors')->where('DFlag',0)->where('ActiveStatus','Inactive')->count();
		$return->vendor->stats->active=DB::Table('tbl_vendors')->where('DFlag',0)->where('ActiveStatus','Active')->count();

		//customer stats
		$return->customer->stats->total=DB::Table('tbl_customer')->count();
		$return->customer->stats->deleted=DB::Table('tbl_customer')->where('DFlag',1)->count();
		$return->customer->stats->inactive=DB::Table('tbl_customer')->where('DFlag',0)->where('ActiveStatus','Inactive')->count();
		$return->customer->stats->active=DB::Table('tbl_customer')->where('DFlag',0)->where('ActiveStatus','Active')->count();
		
		//employees stats
		$return->employee->stats->total=DB::Table('users')->where('LoginType','Admin')->count();
		$return->employee->stats->deleted=DB::Table('users')->where('LoginType','Admin')->where('DFlag',1)->count();
		$return->employee->stats->inactive=DB::Table('users')->where('LoginType','Admin')->where('DFlag',0)->where('ActiveStatus','Inactive')->count();
		$return->employee->stats->active=DB::Table('users')->where('LoginType','Admin')->where('DFlag',0)->where('ActiveStatus','Active')->count();
		return $return;
	}
	public function getRecentQuoteEnquiry(Request $request){
		$columns = array(
			array( 'db' => 'EnqNo', 'dt' => '0' ),
			array( 'db' => 'EnqDate', 'dt' => '1','formatter' => function( $d, $row ) {return date($this->Settings['date-format'],strtotime($d));}),
			array( 'db' => 'ReceiverName', 'dt' => '2' ),
			array( 'db' => 'ReceiverMobNo', 'dt' => '3' ),
			array( 'db' => 'ExpectedDeliveryDate', 'dt' => '4','formatter' => function( $d, $row ) {return date($this->Settings['date-format'],strtotime($d));}),
			array( 'db' => 'CustomerID', 'dt' => '5',
				'formatter' => function( $d, $row ) {
					return DB::table('tbl_customer')->where('CustomerID',$d)->value('Email');
				}
			),
			array( 'db' => 'Status','dt' => '6',
				'formatter' => function( $d, $row ) {
					$html = "";
					if($d=="New"){
						$html="<span class='badge badge-info m-1'>".$d."</span>";
					}elseif($d=="Converted to Quotation"){
						$html="<span class='badge badge-secondary m-1'>".$d."</span>";
					}elseif($d=="Quote Requested"){
						$html="<span class='badge badge-primary m-1'>".$d."</span>";
					}elseif($d=="Accepted"){
						$html="<span class='badge badge-success m-1'>".$d."</span>";
					}
					return $html;
				}
			),
			array(
					'db' => 'EnqID',
					'dt' => '7',
					'formatter' => function( $d, $row ) {
						$html='<button type="button" data-id="'.$d.'" class="btn btn-outline-info '.$this->general->UserInfo['Theme']['button-size'].'  mr-10 btnView">View Enquiry</button>';
						return $html;
					}
			)
		);//$this->FYDBName.'tbl_enquiry as e LEFT JOIN tbl_customer as C ON C.CustomerID = e.CustomerID LEFT JOIN '.$this->generalDB.'tbl_countries as CO On CO.CountryID=C.CountryID';
		$data=array();
		$data['POSTDATA']=$request;
		$data['TABLE']=$this->FYDBName . 'tbl_enquiry';
		$data['PRIMARYKEY']='EnqID';
		$data['COLUMNS']=$columns;
		$data['COLUMNS1']=$columns;
		$data['GROUPBY']=null;
		$data['WHERERESULT']=null;
		$data['WHEREALL']="Status != 'Cancelled'";
		return SSP::SSP( $data);
	}
	public function getRecentOrders(Request $request){
		$columns = array(
			array( 'db' => 'O.OrderNo', 'dt' => '0' ),
			array( 'db' => 'O.OrderDate', 'dt' => '1' ),
			array( 'db' => 'C.CustomerName', 'dt' => '2' ),
			array( 'db' => 'C.MobileNo1', 'dt' => '3' ),
			array( 'db' => 'O.ExpectedDelivery', 'dt' => '4' ),
			array( 'db' => 'O.NetAmount', 'dt' => '5' ),
			array( 'db' => 'O.TotalPaidAmount', 'dt' => '6' ),
			array( 'db' => 'O.BalanceAmount', 'dt' => '7' ),
			array( 'db' => 'O.Status', 'dt' => '8' ),
			array( 'db' => 'O.PaymentStatus', 'dt' => '9' ),
			array( 'db' => 'O.OrderID', 'dt' => '10' ),
			array( 'db' => 'CO.PhoneCode', 'dt' => '11' ),
		);
		$columns1 = array(
			array( 'db' => 'OrderNo', 'dt' => '0' ),
			array( 'db' => 'OrderDate', 'dt' => '1','formatter' => function( $d, $row ) { return date($this->Settings['date-format'],strtotime($d));} ),
			array( 'db' => 'CustomerName', 'dt' => '2' ),
			array( 
				'db' => 'MobileNo1', 
				'dt' => '3' ,
				'formatter' => function( $d, $row ) { 
					$phoneCode=$row['PhoneCode']!=""?"+".$row['PhoneCode']:"";
					return $phoneCode." ".$d;
				}
			),
			array( 'db' => 'ExpectedDelivery', 'dt' => '4' ,'formatter' => function( $d, $row ) { return date($this->Settings['date-format'],strtotime($d));}  ),
			array( 'db' => 'NetAmount', 'dt' => '5', 'formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);} ),
			array( 'db' => 'TotalPaidAmount', 'dt' => '6' , 'formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);} ),
			array( 'db' => 'BalanceAmount', 'dt' => '7' , 'formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);} ),
			array( 
				'db' => 'Status', 
				'dt' => '8' ,
				'formatter' => function( $d, $row ) {
					if($d=="Cancelled"){
						return '<span class="badge badge-danger">'.$d.'</span>';
					}else if($d=="Partially Delivered"){
						return '<span class="badge badge-warning">'.$d.'</span>';
					}else if($d=="Delivered"){
						return '<span class="badge badge-success">'.$d.'</span>';
					}else{
						return '<span class="badge badge-primary">'.$d.'</span>';
					}
				}
			),
			array( 
				'db' => 'PaymentStatus', 
				'dt' => '9' ,
				'formatter' => function( $d, $row ) {
					if($d=="Partial Paid"){
						return '<span class="badge badge-warning">'.$d.'</span>';
					}else if($d=="Paid"){
						return '<span class="badge badge-success">'.$d.'</span>';
					}else{
						return '<span class="badge badge-danger">'.$d.'</span>';
					}
				}
			),
			array( 
				'db' => 'OrderID', 
				'dt' => '10',
				'formatter' => function( $d, $row ) {
					$phoneCode=$row['PhoneCode']!=""?"+".$row['PhoneCode']:"";
					$mobileNo= $phoneCode.$row['MobileNo1'];
					$html='';
						$html.='<a href="'.route('admin.transaction.orders.details',$d).'" data-id="'.$d.'"  class="btn btn-outline-info  m-5 '.$this->general->UserInfo['Theme']['button-size'].'  btnView"><i class="fa fa-eye"></i></a>';
					return $html;
				}
			),
			array( 'db' => 'PhoneCode', 'dt' => '11' ),
		);
		$data=array();
		$data['POSTDATA']=$request;
		$data['TABLE']=$this->FYDBName.'tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID = O.CustomerID LEFT JOIN '.$this->generalDB.'tbl_countries as CO On CO.CountryID=C.CountryID';
		$data['PRIMARYKEY']='O.OrderID';
		$data['COLUMNS']=$columns;
		$data['COLUMNS1']=$columns1;
		$data['GROUPBY']=null;
		$data['WHERERESULT']=null;
		$data['WHEREALL']=null;
		return SSP::SSP( $data);
	}
	public function getOrderStats(request $req){
		$FYFromDate=date("Y-m-d",strtotime($this->FY->FromDate));
		$FYToDate=date("Y-m-d",strtotime($this->FY->ToDate));
		$FromDate=date("Y-m-d",strtotime($this->FY->FromDate));
		$return=[];
		$return[]=["Month", "Quote Enquiry", "Quotes", "Orders"];
		for($i=0;$i<12;$i++){
			$tmp=[];
			$FromDate=date("Y-m-d",strtotime($i." month ",strtotime($FYFromDate)));
			$ToDate=date("Y-m-t",strtotime($FromDate));
			$tmp[]=date("M, Y",strtotime($FromDate));

			$tmp[]=DB::Table($this->FYDBName.'tbl_enquiry')->where('EnqDate','>=',$FromDate)->where('EnqDate','<=',$ToDate)->count();
			$tmp[]=DB::Table($this->FYDBName.'tbl_quotation')->where('QDate','>=',$FromDate)->where('QDate','<=',$ToDate)->count();
			$tmp[]=DB::Table($this->FYDBName.'tbl_order')->where('OrderDate','>=',$FromDate)->where('OrderDate','<=',$ToDate)->count();
			$return[]=$tmp;
		}
		return $return;
	}
	public function getPaymentStats(request $req){
		$FYFromDate=date("Y-m-d",strtotime($this->FY->FromDate));
		$FYToDate=date("Y-m-d",strtotime($this->FY->ToDate));
		$FromDate=date("Y-m-d",strtotime($this->FY->FromDate));
		$return=[];
		$tmp=[];
		$tmp[]="Month";
		if($this->Settings['enable-advance-receipts']){
			$tmp[]="Advance Receipts";
		}
		$tmp[]="Receipts";
		if($this->Settings['enable-advance-payments']){
			$tmp[]="Advance Payments";
		}
		$tmp[]="Payments";
		$return[]=$tmp;//["Month", "Receipts", "Payments"]
		for($i=0;$i<12;$i++){
			$tmp=[];
			$FromDate=date("Y-m-d",strtotime($i." month ",strtotime($FYFromDate)));
			$ToDate=date("Y-m-t",strtotime($FromDate));
			$tmp[]=date("M, Y",strtotime($FromDate));
			//Receipts
			$receipts=json_decode(json_encode(["AdvanceTotal"=>0,"OrderAmount"=>0,"ReceiptAmount"=>0]));
			$sql="SELECT IFNULL(SUM(CASE WHEN PaymentType='Advance' THEN TotalAmount ELSE 0 END),0) as AdvanceTotal,IFNULL(SUM(CASE WHEN PaymentType='Order' THEN TotalAmount ELSE 0 END),0) as OrderAmount,IFNULL(SUM(TotalAmount),0) as ReceiptAmount FROM ".$this->FYDBName."tbl_receipts Where TranDate>='".date("Y-m-d",strtotime($FromDate))."' and TranDate<='".date("Y-m-d",strtotime($ToDate))."'";
			$result=DB::SELECT($sql);
			if(count($result)>0){
				$receipts->AdvanceTotal=$result[0]->AdvanceTotal;
				$receipts->OrderAmount=$result[0]->OrderAmount;
				$receipts->ReceiptAmount=$result[0]->ReceiptAmount;
			}
			if($this->Settings['enable-advance-receipts']){
				$tmp[]=$receipts->AdvanceTotal;
			}
			$tmp[]=$receipts->OrderAmount;
			//payments
			$payments=json_decode(json_encode(["AdvanceTotal"=>0,"OrderAmount"=>0,"PaidAmount"=>0]));
			$sql="SELECT IFNULL(SUM(CASE WHEN PaymentType='Advance' THEN TotalAmount ELSE 0 END),0) as AdvanceTotal,IFNULL(SUM(CASE WHEN PaymentType='Order' THEN TotalAmount ELSE 0 END),0) as OrderAmount,IFNULL(SUM(TotalAmount),0) as PaidAmount FROM ".$this->FYDBName."tbl_payments Where TranDate>='".date("Y-m-d",strtotime($FromDate))."' and TranDate<='".date("Y-m-d",strtotime($ToDate))."'";
			$result=DB::SELECT($sql);
			if(count($result)>0){
				$payments->AdvanceTotal=$result[0]->AdvanceTotal;
				$payments->OrderAmount=$result[0]->OrderAmount;
				$payments->PaidAmount=$result[0]->PaidAmount;
			}
			if($this->Settings['enable-advance-payments']){
				$tmp[]=$payments->AdvanceTotal;
			}
			$tmp[]=$payments->PaidAmount;
			$return[]=$tmp;
		}
		return $return;
	}
	public function getUpcomingPayments(Request $req){
		$tmpDB=Helper::getTmpDB();
		$dateTime = Carbon::parse($req->start);
		$req->start = $dateTime->format('Y-m-d');
		$dateTime = Carbon::parse($req->end);
		$req->end = $dateTime->format('Y-m-d');
		$return=[];
		$StartDate=date("Y-m-d",strtotime($req->start));
		$EndDate=date("Y-m-d",strtotime($req->end));
		$TableName="tbl_upcoming_payments_".Helper::RandomString(7);
		$sql="CREATE TEMPORARY TABLE IF NOT EXISTS ".$tmpDB.$TableName." (TranDate DATE, LedgerType ENUM('Customer', 'Vendor'), LedgerID VARCHAR(50), LedgerName VARCHAR(200), MobileNumber VARCHAR(20), DueDate DATE, Amount DOUBLE DEFAULT 0);";
		DB::Statement($sql);
        
		$result=DB::Table('tbl_financial_year')->get();

		foreach($result as $data){
			if(Helper::checkDBExists($data->DBName)){
				if(Helper::checkTableExists($data->DBName,"tbl_order")){
					$sql="Insert Into ".$tmpDB.$TableName." (TranDate , LedgerType, LedgerID, LedgerName, MobileNumber, DueDate, Amount) ";
					$sql.=" SELECT O.OrderDate, 'Customer' as LedgerType, O.CustomerID, C.CustomerName,C.MobileNo1, DATE_ADD(O.OrderDate, INTERVAL C.CreditDays DAY) AS DueDate,SUM(O.NetAmount)-SUM(O.TotalPaidAmount) as Amount";
					$sql.=" FROM ".$data->DBName.".tbl_order AS O LEFT JOIN tbl_customer AS C ON C.CustomerID = O.CustomerID WHERE PaymentStatus <> 'Paid'  and C.isEnableCreditLimit='Enabled'";
					$sql.=" Group By O.OrderDate, O.CustomerID, C.CustomerName,C.MobileNo1,C.CreditDays,C.isEnableCreditLimit";
					DB::Statement($sql);
				}
				if(Helper::checkTableExists($data->DBName,"tbl_vendor_orders")){
					$sql="Insert Into ".$tmpDB.$TableName." (TranDate , LedgerType, LedgerID, LedgerName, MobileNumber, DueDate, Amount) ";
					$sql.="SELECT O.OrderDate,'Vendor' as LedgerType, O.VendorID, IFNULL(V.VendorName,'Vendor') as VendorName,V.MobileNumber1, DATE_ADD(O.OrderDate, INTERVAL IFNULL(V.CreditDays,30) DAY) AS DueDate,SUM(O.NetAmount)-SUM(O.TotalPaidAmount) as Amount ";
					$sql.=" FROM  ".$data->DBName.".tbl_vendor_orders as O LEFT JOIN tbl_vendors as V ON V.VendorID=O.VendorID WHERE O.PaymentStatus <> 'Paid'  and V.isEnableCreditLimit='Enabled'";
					$sql.=" Group By O.OrderDate, O.VendorID, V.VendorName,V.MobileNumber1,V.CreditDays,V.isEnableCreditLimit";
					DB::Statement($sql);
				}
			}
		}
		$sql="Select * From ".$tmpDB.$TableName." Where Amount>0 and  DueDate>='".date("Y-m-d",strtotime($StartDate))."' and DueDate<='".date("Y-m-d",strtotime($EndDate))."'";
		$result= DB::SELECT($sql);
		foreach($result as $data){
			$title=$data->LedgerType=="Vendor"?" To ":" From ";
			$color=$data->LedgerType=="Vendor"?"#dc3545":"#198754";
			$return[]=[
				"title"=> $title.$data->LedgerName." : Rs.".Helper::NumberFormat($data->Amount,$this->Settings['price-decimals']),
				"start"=>date("Y-m-d",strtotime($data->DueDate)),
				"end"=>date("Y-m-d",strtotime($data->DueDate)),
				"color"=>$color
			];
		}
		return $return;
	}
	public function getEnquiryCircleStats(Request $req){
		// Get the current date
		$currentDate = Carbon::now();

		// Get the start of the current week (Monday)
		$startDate = $currentDate->startOfWeek()->format('Y-m-d');

		// Get the end of the current week (Sunday)
		$endDate = $currentDate->endOfWeek()->format('Y-m-d');
		$return=json_decode(
			json_encode(
				[
					"lastMonth"=>0,
					"today"=>0,
					"thisWeek"=>0,
					"thisMonth"=>0
				]
			)
		);
		//get last Month enquiries
		$result=DB::Table('tbl_financial_year')->where('FromDate','<=',date("Y-m-01",strtotime('-1 Month')))->where('ToDate','>=',date("Y-m-01",strtotime('-1 Month')))->get();
		if(count($result)>0){
			if(Helper::checkDBExists($result[0]->DBName)){
				if(Helper::checkTableExists($result[0]->DBName,"tbl_enquiry")){
					$return->lastMonth=DB::Table($result[0]->DBName.".tbl_enquiry")->where('EnqDate','>=',date("Y-m-01",strtotime('-1 Month')))->where('EnqDate','<=',date("Y-m-t",strtotime('-1 Month')))->count();
				}
			}
		}
		$return->today=DB::Table($this->FYDBName."tbl_enquiry")->where('EnqDate','=',date("Y-m-d"))->count();
		$return->thisWeek=DB::Table($this->FYDBName."tbl_enquiry")->where('EnqDate','>=',date("Y-m-01",strtotime($startDate)))->where('EnqDate','<=',date("Y-m-t",strtotime($endDate)))->count();
		$return->thisMonth=DB::Table($this->FYDBName."tbl_enquiry")->where('EnqDate','>=',date("Y-m-01"))->where('EnqDate','<=',date("Y-m-t"))->count();

		$return->lastMonth=floatval($return->lastMonth)>1000?Helper::shortenValue($return->lastMonth):$return->lastMonth;
		$return->today=floatval($return->today)>1000?Helper::shortenValue($return->today):$return->today;
		$return->thisWeek=floatval($return->thisWeek)>1000?Helper::shortenValue($return->thisWeek):$return->thisWeek;
		$return->thisMonth=floatval($return->thisMonth)>1000?Helper::shortenValue($return->thisMonth):$return->thisMonth;
		return $return;
	}
	public function getOrdersCircleStats(Request $req){
		// Get the current date
		$currentDate = Carbon::now();

		// Get the start of the current week (Monday)
		$startDate = $currentDate->startOfWeek()->format('Y-m-d');

		// Get the end of the current week (Sunday)
		$endDate = $currentDate->endOfWeek()->format('Y-m-d');
		$return=json_decode(
			json_encode(
				[
					"lastMonth"=>0,
					"today"=>0,
					"thisWeek"=>0,
					"thisMonth"=>0
				]
			)
		);
		//get last Month Orders
		$result=DB::Table('tbl_financial_year')->where('FromDate','<=',date("Y-m-01",strtotime('-1 Month')))->where('ToDate','>=',date("Y-m-01",strtotime('-1 Month')))->get();
		if(count($result)>0){
			if(Helper::checkDBExists($result[0]->DBName)){
				if(Helper::checkTableExists($result[0]->DBName,"tbl_order")){
					$return->lastMonth=DB::Table($result[0]->DBName.".tbl_order")->where('OrderDate','>=',date("Y-m-01",strtotime('-1 Month')))->where('OrderDate','<=',date("Y-m-t",strtotime('-1 Month')))->count();
				}
			}
		}
		$return->today=DB::Table($this->FYDBName."tbl_order")->where('OrderDate','=',date("Y-m-d"))->count();
		$return->thisWeek=DB::Table($this->FYDBName."tbl_order")->where('OrderDate','>=',date("Y-m-01",strtotime($startDate)))->where('OrderDate','<=',date("Y-m-t",strtotime($endDate)))->count();
		$return->thisMonth=DB::Table($this->FYDBName."tbl_order")->where('OrderDate','>=',date("Y-m-01"))->where('OrderDate','<=',date("Y-m-t"))->count();

		$return->lastMonth=floatval($return->lastMonth)>1000?Helper::shortenValue($return->lastMonth):$return->lastMonth;
		$return->today=floatval($return->today)>1000?Helper::shortenValue($return->today):$return->today;
		$return->thisWeek=floatval($return->thisWeek)>1000?Helper::shortenValue($return->thisWeek):$return->thisWeek;
		$return->thisMonth=floatval($return->thisMonth)>1000?Helper::shortenValue($return->thisMonth):$return->thisMonth;
		return $return;
	}
	public function getDeliveryCircleStats(Request $req){
		// Get the current date
		$currentDate = Carbon::now();

		// Get the start of the current week (Monday)
		$startDate = $currentDate->startOfWeek()->format('Y-m-d');

		// Get the end of the current week (Sunday)
		$endDate = $currentDate->endOfWeek()->format('Y-m-d');
		$return=json_decode(
			json_encode(
				[
					"nextMonth"=>0,
					"today"=>0,
					"tomorrow"=>0,
					"thisWeek"=>0,
					"thisMonth"=>0
				]
			)
		);
		//get next month Deliveries
		$result=DB::Table('tbl_financial_year')->where('FromDate','<=',date("Y-m-01",strtotime('+1 Month')))->where('ToDate','>=',date("Y-m-01",strtotime('+1 Month')))->get();
		if(count($result)>0){
			if(Helper::checkDBExists($result[0]->DBName)){
				if(Helper::checkTableExists($result[0]->DBName,"tbl_order")){
					$return->nextMonth=DB::Table($result[0]->DBName.".tbl_order")->where('ExpectedDelivery','>=',date("Y-m-01",strtotime('+1 Month')))->where('ExpectedDelivery','<=',date("Y-m-t",strtotime('+1 Month')))->count();
				}
			}
		}
		$return->today=DB::Table($this->FYDBName."tbl_order")->where('ExpectedDelivery','=',date("Y-m-d"))->count();
		$return->tomorrow=DB::Table($this->FYDBName."tbl_order")->where('ExpectedDelivery','=',date("Y-m-d",strtotime('+1 days')))->count();
		$return->thisWeek=DB::Table($this->FYDBName."tbl_order")->where('ExpectedDelivery','>=',date("Y-m-01",strtotime($startDate)))->where('ExpectedDelivery','<=',date("Y-m-t",strtotime($endDate)))->count();
		$return->thisMonth=DB::Table($this->FYDBName."tbl_order")->where('ExpectedDelivery','>=',date("Y-m-01"))->where('ExpectedDelivery','<=',date("Y-m-t"))->count();

		$return->nextMonth=floatval($return->nextMonth)>1000?Helper::shortenValue($return->nextMonth):$return->nextMonth;
		$return->today=floatval($return->today)>1000?Helper::shortenValue($return->today):$return->today;
		$return->tomorrow=floatval($return->tomorrow)>1000?Helper::shortenValue($return->tomorrow):$return->tomorrow;
		$return->thisWeek=floatval($return->thisWeek)>1000?Helper::shortenValue($return->thisWeek):$return->thisWeek;
		$return->thisMonth=floatval($return->thisMonth)>1000?Helper::shortenValue($return->thisMonth):$return->thisMonth;
		return $return;
	}
}
