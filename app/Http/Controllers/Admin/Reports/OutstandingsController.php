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
class OutstandingsController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::rptOutstandings->value;
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
            return view('app.reports.outstandings.index',$FormData);
        }else{
            return view('errors.403');
        }
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
				array( 'db' => 'Balance', 'dt' => '1','formatter' => function( $d, $row ) { return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'LedgerID', 'dt' => '2'),
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
	
}
