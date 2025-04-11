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
class LedgerController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::rptLedger->value;
        $this->PageTitle="Ledger";
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
            return view('app.reports.ledger.index',$FormData);
        }else{
            return view('errors.403');
        }
    }
	public function details(Request $req,$LedgerID){
		$FormData=$this->general->UserInfo;
		$FormData['ActiveMenuName']=$this->ActiveMenuName;
		$FormData['PageTitle']=$this->PageTitle;
		$FormData['menus']=$this->Menus;
		$FormData['crud']=$this->CRUD;
		$FormData['LedgerID']=$LedgerID;
		$FormData['LedgerType']=$req->type;
		$FormData['fromDate']=$req->from;
		$FormData['toDate']=$req->to;
		$FormData['LedgerType']=$req->ltype;
		$FormData['back']=$req->b==""?"ledger":$req->b;
		return view('app.reports.ledger.details',$FormData);
	}
	public function getLedgerAccounts(Request $req){
		$sql ="Select V.VendorID as LedgerID, V.VendorName as LedgerName,'Vendor' as LedgerType,CONCAT( CASE WHEN IFNULL(CO.PhoneCode,'')<>'' Then CONCAT('+', CO.PhoneCode) ELSE '' END ,' ', V.MobileNumber1) as MobileNumber From tbl_vendors as V  LEFT JOIN ".$this->generalDB."tbl_countries as CO ON CO.CountryID=V.CountryID Where V.ActiveStatus=1 and V.DFlag=0 ";
		$sql.=" UNION ";
		$sql.=" Select C.CustomerID as LedgerID, C.CustomerName as LedgerName,'Customer' as LedgerType,CONCAT( CASE WHEN IFNULL(CO.PhoneCode,'')<>'' Then CONCAT('+', CO.PhoneCode) ELSE '' END ,' ', C.MobileNo1) as MobileNumber From tbl_customer as C  LEFT JOIN  ".$this->generalDB."tbl_countries as CO ON CO.CountryID=C.CountryID Where C.ActiveStatus=1 and C.DFlag=0";

		$sql="SELECT * FROM (".$sql.") as T Where LedgerType='".$req->LedgerType."' Order BY LedgerName ";
		return DB::SELECT($sql);
	}
	public function generateTemp($req){
		$tmp=Ledgers::getLedger(["FYDBName"=>$this->FYDBName]);
		$tableName="tmp_ledger_".Helper::RandomString(8);
		$tableName1="tmp_ledger1_".Helper::RandomString(8);
		$sql="CREATE TEMPORARY TABLE IF NOT EXISTS ".$tmp->DBName.$tableName1."(LedgerID VarChar(50),Opening Double Default 0,Credit Double Default 0,Debit Double Default 0)";
		DB::Statement($sql);
		$sql="CREATE TEMPORARY TABLE IF NOT EXISTS ".$tmp->DBName.$tableName."(LedgerID VarChar(50),LedgerName VarChar(200),Opening Double Default 0,Credit Double Default 0,Debit Double Default 0,Balance Double default 0)";
		DB::Statement($sql);
		
		if($req->LedgerType=="Customer"){
			$sql=" Insert Into ".$tmp->DBName.$tableName1."(LedgerID,Opening,Credit,Debit)";
			$sql.="Select CustomerID,0,0,0 From tbl_customer Where ActiveStatus='Active' and DFlag=0 ";
			DB::INSERT($sql);
			
			$sql=" Insert Into ".$tmp->DBName.$tableName1."(LedgerID,Opening)";
			$sql.=" SELECT T.LedgerID, (IFNULL(SUM(IFNULL(T.Debit,0)),0) -  IFNULL(SUM(IFNULL(T.Credit,0)),0)) as Opening  FROM  ".$tmp->DBName.$tmp->TableName." as T  ";
			$sql.=" Where  T.LedgerType='Customer' and T.TranDate <'".date('Y-m-d',strtotime($req->FromDate))."' ";
			$sql.=" Group By T.LedgerID,T.LedgerType ";
			DB::Statement($sql);

			$sql=" Insert Into ".$tmp->DBName.$tableName1."(LedgerID,Credit,Debit)";
			$sql.=" SELECT T.LedgerID, IFNULL(SUM(IFNULL(T.Credit,0)),0) as Credit, IFNULL(SUM(IFNULL(T.Debit,0)),0)  as Debit  FROM  ".$tmp->DBName.$tmp->TableName." as T  ";
			$sql.=" Where  T.LedgerType='Customer' and T.TranDate >='".date('Y-m-d',strtotime($req->FromDate))."' AND T.TranDate <='".date('Y-m-d',strtotime($req->ToDate))."' ";
			$sql.=" Group By T.LedgerID,T.LedgerType ";
			DB::Statement($sql); 

			$sql=" Insert Into ".$tmp->DBName.$tableName."(LedgerID,LedgerName,Opening,Credit,Debit,Balance)";
			$sql.="SELECT T.LedgerID,C.CustomerName,SUM(T.Opening) as Opening,Sum(T.Credit) as Credit, SUM(T.debit) as Debit,(SUM(T.Opening)+(SUM(T.Debit)-SUM(T.Credit))) as Balance ";
			$sql.=" FROM ".$tmp->DBName.$tableName1." as T LEFT JOIN tbl_customer as C ON C.CustomerID=T.LedgerID ";
			$sql.=" Where IFNULL(C.CustomerName,'')<>'' ";
			$sql.=" Group By  T.LedgerID,C.CustomerName ";
			DB::Statement($sql); 
		}else{
			$sql=" Insert Into ".$tmp->DBName.$tableName1."(LedgerID,Opening,Credit,Debit)";
			$sql.="Select VendorID,0,0,0 From tbl_vendors Where ActiveStatus='Active' and DFlag=0 ";
			DB::INSERT($sql);
			
			$sql=" Insert Into ".$tmp->DBName.$tableName1."(LedgerID,Opening)";
			$sql.=" SELECT T.LedgerID, (IFNULL(SUM(IFNULL(T.Debit,0)),0) -  IFNULL(SUM(IFNULL(T.Credit,0)),0)) as Opening  FROM  ".$tmp->DBName.$tmp->TableName." as T  ";
			$sql.=" Where  T.LedgerType='Vendor' and T.TranDate <'".date('Y-m-d',strtotime($req->FromDate))."' ";
			$sql.=" Group By T.LedgerID,T.LedgerType ";
			DB::Statement($sql);

			$sql=" Insert Into ".$tmp->DBName.$tableName1."(LedgerID,Credit,Debit)";
			$sql.=" SELECT T.LedgerID, IFNULL(SUM(IFNULL(T.Credit,0)),0) as Credit, IFNULL(SUM(IFNULL(T.Debit,0)),0)  as Debit  FROM  ".$tmp->DBName.$tmp->TableName." as T  ";
			$sql.=" Where  T.LedgerType='Vendor' and T.TranDate >='".date('Y-m-d',strtotime($req->FromDate))."' AND T.TranDate <='".date('Y-m-d',strtotime($req->ToDate))."' ";
			$sql.=" Group By T.LedgerID,T.LedgerType ";
			DB::Statement($sql); 

			$sql=" Insert Into ".$tmp->DBName.$tableName."(LedgerID,LedgerName,Opening,Credit,Debit,Balance)";
			$sql.="SELECT T.LedgerID,V.VendorName,SUM(T.Opening) as Opening,Sum(T.Credit) as Credit, SUM(T.debit) as Debit,(SUM(T.Opening)+(SUM(T.Credit)-SUM(T.Debit))) as Balance ";
			$sql.=" FROM ".$tmp->DBName.$tableName1." as T LEFT JOIN tbl_vendors as V ON V.VendorID=T.LedgerID ";
			$sql.=" Where IFNULL(V.VendorName,'')<>'' ";
			$sql.=" Group By  T.LedgerID,V.VendorName ";
			
			DB::Statement($sql); 
		}
		$totOpening=$totDebit=$totCredit=$totBal=0;
		$sql="SELECT SUM(Opening) as Opening,Sum(Credit) as Credit, SUM(debit) as Debit,SUM(Balance) as Balance FROM ".$tmp->DBName.$tableName;
		$result=DB::SELECT($sql);
		foreach($result as $item){
			$totOpening+=floatval($item->Opening);
			$totCredit+=floatval($item->Credit);
			$totDebit+=floatval($item->Debit);
			$totBal+=floatval($item->Balance);
		}
		$totOpening=Helper::NumberFormat($totOpening,$this->Settings['price-decimals']);
		$totCredit=Helper::NumberFormat($totCredit,$this->Settings['price-decimals']);
		$totDebit=Helper::NumberFormat($totDebit,$this->Settings['price-decimals']);
		$totBal=Helper::NumberFormat($totBal,$this->Settings['price-decimals']);
		return ["TableName"=>$tableName,"DBName"=>$tmp->DBName,"total"=>["opening"=>$totOpening,"credit"=>$totCredit,"debit"=>$totDebit,"Balance"=>$totBal]];
	}
    public function TableView(request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$tData=$this->generateTemp($request);
			$columns = array(
				array( 
					'db' => 'LedgerName',
					'dt' => '0',
					'formatter' => function( $d, $row ) { 
						$url=route('admin.reports.ledger.details',$row['LedgerID']);
						return '<a class="btnLedgerDetails" href="'.$url.'" >'.$d.'</a>';
					}
				),
				array( 'db' => 'Opening', 'dt' => '1','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'Debit', 'dt' => '2','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'Credit', 'dt' => '3','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'Balance', 'dt' => '4','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'LedgerID', 'dt' => '5'),
			);
			$where=" 1=1 ";
			if($request->Filter=="non-zero"){
				$where.=" and Balance<>0 ";
			}
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$tData['DBName'].$tData['TableName'];
			$data['PRIMARYKEY']='LedgerID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=$where;
			$return= SSP::SSP( $data);
			$return['total']=$tData['total'];
			return $return;
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
    }
	
	public function generateLedgerTemp($req){
		$tmp=Ledgers::getLedger(["FYDBName"=>$this->FYDBName]);
		$tableName="tmp_ledger_".Helper::RandomString(8);
		$sql="CREATE  TABLE IF NOT EXISTS ".$tmp->DBName.$tableName."(TranNo VarChar(50),TranDate Date,LedgerType VarChar(30),LedgerID VarChar(50), LedgerName VarChar(200),Description text,Debit double default 0,Credit  double default 0,CreatedOn timestamp)";
		DB::Statement($sql);

		$sql ="Insert Into ".$tmp->DBName.$tableName."(TranNo,TranDate,LedgerType,LedgerID, LedgerName,Description,Debit,Credit,CreatedOn)";
		$sql.=" SELECT T.TranNo,T.TranDate,T.LedgerType,T.LedgerID, IFNULL(C.CustomerName,V.VendorName) as LedgerName,T.Description,T.Debit,T.Credit,T.CreatedOn ";
		$sql.=" FROM ".$tmp->DBName.$tmp->TableName."  as T LEFT JOIN tbl_vendors as V ON V.VendorID=T.LedgerID LEFT JOIN tbl_customer as C ON C.CustomerID=T.LedgerID  ";
		$sql.=" where LedgerID='".$req->LedgerID."' and T.TranDate>='".date("Y-m-d",strtotime($req->FromDate))."' and T.TranDate<='".date("Y-m-d",strtotime($req->ToDate))."' order By CreatedOn asc";
		DB::Statement($sql);

		$OpenDebit=$OpenCredit=$TotalDebit=$TotalCredit=$BalDebit=$BalCredit=0;

		//opening balance
		$sql="SELECT LedgerID,SUM(Debit) as Debit,SUM(Credit) as Credit FROM ".$tmp->DBName.$tmp->TableName."   where LedgerID='".$req->LedgerID."' and TranDate<'".date("Y-m-d",strtotime($req->FromDate))."' Group By LedgerID";
		$result=DB::SELECT($sql);
		foreach($result as $item){
			$OpenCredit+=floatval($item->Credit);
			$OpenDebit+=floatval($item->Debit);
		}
		if(floatval($OpenCredit)>=floatval($OpenDebit)){
			$OpenCredit=floatval($OpenCredit)-floatval($OpenDebit);
			$OpenDebit=0;
		}else{
			$OpenDebit=floatval($OpenDebit)-floatval($OpenCredit);
			$OpenCredit=0;
		}
		//totals
		$TotalCredit+=floatval($OpenCredit);
		$TotalDebit+=floatval($OpenDebit);
		$sql="SELECT LedgerID,SUM(Debit) as Debit,SUM(Credit) as Credit FROM ".$tmp->DBName.$tmp->TableName."   where  LedgerID='".$req->LedgerID."' and TranDate>='".date("Y-m-d",strtotime($req->FromDate))."' and TranDate<='".date("Y-m-d",strtotime($req->ToDate))."' Group By LedgerID";
		$result=DB::SELECT($sql);
		foreach($result as $item){
			$TotalCredit+=floatval($item->Credit);
			$TotalDebit+=floatval($item->Debit);
		}
		//balance
		if(floatval($TotalCredit)>=floatval($TotalDebit)){
			$BalCredit=floatval($TotalCredit)-floatval($TotalDebit);
			$BalDebit=0;
		}else{
			$BalDebit=floatval($TotalDebit)-floatval($TotalCredit);
			$BalCredit=0;
		}
		return [
			"TableName"=>$tableName,
			"DBName"=>$tmp->DBName,
			"Totals"=>[
				"OpenDebit"=>floatval($OpenDebit)==0?"":Helper::NumberFormat($OpenDebit,$this->Settings['price-decimals']),
				"OpenCredit"=>floatval($OpenCredit)==0?"":Helper::NumberFormat($OpenCredit,$this->Settings['price-decimals']),
				"TotalDebit"=>Helper::NumberFormat($TotalDebit,$this->Settings['price-decimals']),
				"TotalCredit"=>Helper::NumberFormat($TotalCredit,$this->Settings['price-decimals']),
				"TotalCreditBalance"=>floatval($BalCredit)==0?"":Helper::NumberFormat($BalCredit,$this->Settings['price-decimals']),
				"TotalDebitBalance"=>floatval($BalDebit)==0?"":Helper::NumberFormat($BalDebit,$this->Settings['price-decimals']),
			]
		];
	}
    public function LedgerTableView(request $request){
		$tData=$this->generateLedgerTemp($request);
		$columns = array(
			array( 
				'db' => 'CreatedOn',
				'dt' => '0',
				'formatter' => function( $d, $row ) { 
					return date($this->Settings['date-format'],strtotime($d));
				}
			),
			array( 
				'db' => 'CreatedOn',
				'dt' => '1',
				'formatter' => function( $d, $row ) { 
					return date($this->Settings['time-format'],strtotime($d));
				}
			),
			array( 'db' => 'Description', 'dt' => '2'),
			array( 'db' =>'Debit', 'dt' => '3','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
			array( 'db' => 'Credit', 'dt' => '4','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
		);
		$data=array();
		$data['POSTDATA']=$request;
		$data['TABLE']=$tData['DBName'].$tData['TableName'];
		$data['PRIMARYKEY']='LedgerID';
		$data['COLUMNS']=$columns;
		$data['COLUMNS1']=$columns;
		$data['GROUPBY']=null;
		$data['WHERERESULT']=null;
		$data['WHEREALL']=null;
		$return= SSP::SSP( $data);
		$return['totals']=$tData['Totals'];
		return $return;
    }
}
