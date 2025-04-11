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
use Ledgers;
use ValidDB;
class PaymentController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::Payments->value;
		$this->PageTitle="Payments";
        $this->middleware('auth');    
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			$this->generalDB=Helper::getGeneralDB();
			$this->CurrFYDB=Helper::getCurrFYDB();
			$this->logDB=Helper::getLogDB();
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
			$FormData['Setting']=$this->Settings;

            return view('app.transaction.payments.view',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"add")==true){
			return Redirect::to('/admin/transaction/payments/create');
        }else{
            return view('errors.403');
        }
    }
    public function TrashView(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"restore")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
            return view('app.transaction.payments.trash',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('/admin/transaction/payments/');
        }else{
            return view('errors.403');
        }
    }
	
    public function advancePaymentView(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"add")==true && $this->Settings['enable-advance-payments']){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=false;
			$FormData['Settings']=$this->Settings;
			return view('app.transaction.payments.advance',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('/admin/transaction/payments');
        }else{
            return view('errors.403');
        }
    }
    public function AdvanceEdit(Request $req,$TranNo){
        if($this->general->isCrudAllow($this->CRUD,"edit")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=true;
			$FormData['TranNo']=$TranNo;
			$FormData['Settings']=$this->Settings;

			$FormData['EditData']=$this->getPayments(array("TranNo"=>$TranNo,"PaymentType"=>"Advance"));
			if(count($FormData['EditData'])>0){
				$FormData['EditData']=$FormData['EditData'][0];
				return view('app.transaction.payments.advance',$FormData);
			}else{
				return view('errors.404');	
			}
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('/admin/transaction/payments');
        }else{
            return view('errors.403');
        }
    }
    public function create(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"add")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=false;
			$FormData['Settings']=$this->Settings;

			return view('app.transaction.payments.create',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('/admin/transaction/payments');
        }else{
            return view('errors.403');
        }
    }
    public function Edit(Request $req,$TranNo){
        if($this->general->isCrudAllow($this->CRUD,"edit")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=true;
			$FormData['TranNo']=$TranNo;
			$FormData['EditData']=$this->getPayments(array("TranNo"=>$TranNo));
			if(count($FormData['EditData'])>0){
				$FormData['EditData']=$FormData['EditData'][0];
				return view('app.transaction.payments.create',$FormData);
			}else{
				return view('errors.403');
			}
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
            return Redirect::to('/admin/transaction/payments/');
        }else{
            return view('errors.403');
        }
    }
    public function getLedger(Request $req){

		$sql="SELECT V.VendorID as LedgerID,V.VendorName as LedgerName,CONCAT(IFNULL(CONCAT('+',CO.PhoneCode),''), V.MobileNumber1) as MobileNumber, 'Vendor' as LedgerType,0 as AdvanceAmount From tbl_vendors as V LEFT JOIN ".$this->generalDB."tbl_countries as CO ON CO.CountryID=V.CountryID Where V.ActiveStatus='Active' and V.DFlag=0  Order By V.VendorName";
		$result=DB::SELECT($sql);
		for($i=0;$i<count($result);$i++){
			
			//get Advance Amount
			$sql=" SELECT TranNo,TotalAmount as Debit,0 as Credit FROM ".$this->CurrFYDB."tbl_payments Where LedgerID='".$result[$i]->LedgerID."'  and PaymentType='Advance'";
			$sql.=" UNION";
			$sql.=" SELECT AdvID,0 as debit,Amount as Credit From ".$this->CurrFYDB."tbl_advance_amount_log Where LedgerID='".$result[$i]->LedgerID."' and TranType='Payments' ";
			if($req->TranNo!=""){
				$sql.=" and PaymentID<>'".$req->TranNo."'";
			}
			$sql=" SELECT IFNULL(SUM(Debit)-Sum(Credit),0) as Balance FROM( ".$sql." ) as T";
			$temp=DB::SELECT($sql);
			if(count($temp)>0){
				$result[$i]->AdvanceAmount=$temp[0]->Balance;
			}
		}
		return $result;
	}
	//rpcsoftware6811@gmail.com || Rpc6811@bs
    public function getOrders(Request $req){ // invoice Payment
        $sql="SELECT H.VOrderID as OrderID,H.OrderNo,H.VendorID as LedgerID,H.OrderDate,H.SubTotal AS Taxable,H.CGSTAmount,H.SGSTAmount,H.IGSTAmount,H.TotalAmount,H.AdditionalCost,H.NetAmount,H.LessFromAdvance,H.PaidAmount,H.TotalPaidAmount,0 as BalanceAmount,0 as AdvanceAmt,0 as PayLessFromAdvance,0 as PayPaidAmount,0 as PayTotalPaidAmount FROM ".$this->CurrFYDB."tbl_vendor_orders as H ";
		$sql.=" Where H.PaymentStatus<>'Paid' ";
		if($req->LedgerID!=""){
			$sql.=" and H.VendorID='".$req->LedgerID."'";
		}
		if($req->TranNo!=""){
			$sql.=" UNION SELECT H.VOrderID,H.OrderNo,H.VendorID as LedgerID,H.OrderDate,H.SubTotal AS Taxable,H.CGSTAmount,H.SGSTAmount,H.IGSTAmount,H.TotalAmount,H.AdditionalCost,H.NetAmount,H.LessFromAdvance,H.PaidAmount,H.TotalPaidAmount,0 as BalanceAmount,0 as AdvanceAmt,0 as PayLessFromAdvance,0 as PayPaidAmount,0 as PayTotalPaidAmount  FROM ".$this->CurrFYDB."tbl_vendor_orders as H ";
			$sql.=" Where H.PaymentStatus='Paid' and VOrderID in(SELECT DISTINCT(OrderID) as OrderID FROM ".$this->CurrFYDB."tbl_payment_details where TranNo='".$req->TranNo."')";
			if($req->LedgerID!=""){
				$sql.=" and H.VendorID='".$req->LedgerID."'";
			}
		}
		$result=DB::SELECT($sql); 
		for($i=0;$i<count($result);$i++){
			//get Balance Amount
			$sql="SELECT SUM(IFNULL(LessFromAdvance,0)) as LessFromAdvance,SUM(IFNULL(PaidAmount,0)) as PaidAmount,SUM(IFNULL(Amount,0)) as TotalPaidAmount FROM ".$this->CurrFYDB."tbl_payment_details where OrderID='".$result[$i]->OrderID."'";
			if($req->TranNo!=""){
				$sql.=" and TranNo<>'".$req->TranNo."'";
			}
			$temp=DB::SELECT($sql);
			if(count($temp)>0){
				$result[$i]->LessFromAdvance=$temp[0]->LessFromAdvance;
				$result[$i]->PaidAmount=$temp[0]->PaidAmount;
				$result[$i]->TotalPaidAmount=$temp[0]->TotalPaidAmount;
			}
			$result[$i]->BalanceAmount=floatval($result[$i]->NetAmount)-floatval($result[$i]->TotalPaidAmount);
			//get Payment paid on this invoice for edit 
			
			if($req->TranNo!=""){
				$sql="SELECT SUM(IFNULL(LessFromAdvance,0)) as LessFromAdvance,SUM(IFNULL(PaidAmount,0)) as PaidAmount,SUM(IFNULL(Amount,0)) as TotalPaidAmount FROM ".$this->CurrFYDB."tbl_payment_details where OrderID='".$result[$i]->OrderID."'";
				$sql.=" and TranNo='".$req->TranNo."'";
				$temp=DB::SELECT($sql);
				if(count($temp)>0){
					$result[$i]->PayLessFromAdvance=$temp[0]->LessFromAdvance;
					$result[$i]->PayPaidAmount=$temp[0]->PaidAmount;
					$result[$i]->PayTotalPaidAmount=$temp[0]->TotalPaidAmount;
				}
			}
		}
		return $result;
    }
    public function getPayments($data=array()){
        $return=array();
        $sql="SELECT H.TranNo,H.TranDate,H.PaymentType,H.LedgerID,V.VendorName as LedgerName,V.MobileNumber1,V.MobileNumber2,V.Email,V.GSTNo,H.MOP,H.MOPRefNo,H.ChequeDate,H.TotalAmount FROM ".$this->CurrFYDB."tbl_payments as H LEFT JOIN tbl_vendors as V ON V.VendorID=H.LedgerID ";
        $sql.=" Where 1=1 ";
        if(is_array($data)){
            if(array_key_exists("TranNo",$data)){$sql.=" and H.TranNo='".$data['TranNo']."'";}
            if(array_key_exists("TranDate",$data)){$sql.=" and H.TranDate='".$data['TranDate']."'";}
            if(array_key_exists("PaymentType",$data)){$sql.=" and H.PaymentType='".$data['PaymentType']."'";}
            if(array_key_exists("LedgerID",$data)){$sql.=" and H.LedgerID='".$data['LedgerID']."'";}
        }
		$sql.=" Order By TranNo,TranDate";
        $result=DB::SELECT($sql);
        for($i=0;$i<count($result);$i++){
			$sql="SELECT D.DetailID,D.TranNo,D.OrderID,O.OrderNo,O.OrderDate,O.NetAmount,D.LessFromAdvance,D.PaidAmount,D.Amount FROM ".$this->CurrFYDB."tbl_payment_details as D  LEFT JOIN ".$this->CurrFYDB."tbl_vendor_orders as O On O.VOrderID=D.OrderID ";
			$sql.=" Where D.TranNo='".$result[$i]->TranNo."' and O.VendorID='".$result[$i]->LedgerID."'";
			$result[$i]->Details=DB::SELECT($sql); 
        }
        return $result;
    }
	public function Save(Request $req){ 
		if($this->general->isCrudAllow($this->CRUD,"add")==true){
			$OldData=$NewData=[];
			$TranNo="";
			$rules=array(
                'TranDate' => 'required|date|before:'.date('Y-m-d',strtotime('+1 days')),
                'MOP' => 'required'
			);
			$message=array(
				'TranDate.required'=>"Payment Date is required",
				'TranDate.date'=>"Payment Date must be Date",
				'MOP.required'=>"Mode Of Payment is required"
			);
			$validator = Validator::make($req->all(), $rules,$message);
			
			if ($validator->fails()) {
				return array('status'=>false,'message'=>"payment save failed",'errors'=>$validator->errors());			
			}
			DB::beginTransaction();
			$status=false;
			try {
				$TranNo = DocNum::getDocNum(docTypes::Payments->value, $this->CurrFYDB,Helper::getCurrentFy());
				$data=array(
                    "TranNo"=>$TranNo,
                    "TranDate"=>$req->TranDate,
                    "LedgerID"=>$req->LedgerID,
                    "PaymentType"=>$req->PaymentType,
                    "MOP"=>$req->MOP,
                    "MOPRefNo"=>$req->MOPRefNo,
                    "ChequeDate"=>$req->ChequeDate,
                    "TotalAmount"=>floatval($req->TotalAmount),
                    "CreatedOn"=>date("Y-m-d H:i:s"),
                    "CreatedBy"=>$this->UserID
				);
				$status=DB::Table($this->CurrFYDB.'tbl_payments')->insert($data);
                if($status){
                    $Details=json_decode($req->Details,true);
                    for($i=0;$i<count($Details);$i++){
                        if($status){
							$DetailID = DocNum::getDocNum(docTypes::PaymentsDetails->value, $this->CurrFYDB,Helper::getCurrentFy());
                            $data=array(
                                "DetailID"=>$DetailID,
                                "TranNo"=>$TranNo,
                                "OrderID"=>$Details[$i]['OrderID'],
                                "LessFromAdvance"=>floatval($Details[$i]['LessFromAdvance']),
                                "PaidAmount"=>floatval($Details[$i]['PaidAmount']),
                                "Amount"=>floatval($Details[$i]['Amount']),
                                "CreatedOn"=>date("Y-m-d H:i:s")
                            );
                            $status=DB::Table($this->CurrFYDB.'tbl_payment_details')->insert($data);
                            if($status){
								DocNum::updateDocNum(docTypes::PaymentsDetails->value, $this->CurrFYDB);
                                $status=$this->general->PaymentUpdates($req->LedgerID,$Details[$i]['OrderID'],$this->CurrFYDB);
                            }
							if($status){
								$tdata=array("TranType"=>"Payments","LedgerID"=>$req->LedgerID,"PaymentID"=>$TranNo,"DetailID"=>$DetailID,"Amount"=>floatval($Details[$i]['LessFromAdvance']));
								$status=$this->general->AdvanceAmountUsedLog($tdata,$this->CurrFYDB);
							}
                        }
                    }
                }
			}catch(Exception $e) {
				$status=false;
			}

			if($status==true){
				DocNum::updateDocNum(docTypes::Payments->value, $this->CurrFYDB);
				$NewData=$this->getPayments(array("TranNo"=>$TranNo));
				$logData=array("Description"=>"New payment created successfully","ModuleName"=>$this->ActiveMenuName,"Action"=>"Add","ReferID"=>$TranNo,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				DB::commit();
				return array('status'=>true,'message'=>"Payment created successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Payment create Failed");
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}	
	}
    public function update(request $req,$TranNo){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$OldData=$this->getPayments(array("TranNo"=>$TranNo));$NewData=array();
			$ValidDB=array();
			$currentAdvanceAmount=0;
			if(count($OldData)>0){
				$currentAdvanceAmount=floatval($OldData['0']->TotalAmount);
			}
			$rules=array(
                'TranDate' => 'required|date|before:'.date('Y-m-d',strtotime('+1 days')),
                'MOP' => 'required'
			)				;
			$message=array(
				'Vendor.required'=>"Vendor is required",
				'TranDate.required'=>"Payment Date is required",
				'TranDate.date'=>"Payment Date must be Date",
				'MOP.required'=>"Mode Of Payment is required"
			);
			$validator = Validator::make($req->all(), $rules,$message);
			
			if ($validator->fails()) {
				return array('status'=>false,'message'=>"Payment update failed",'errors'=>$validator->errors());			
			}
			DB::beginTransaction();
			$status=false;
			try {
				$data=array(
                    "TranDate"=>date("Y-m-d",strtotime($req->TranDate)),
                    "LedgerID"=>$req->LedgerID,
                    "MOP"=>$req->MOP,
                    "MOPRefNo"=>$req->MOPRefNo,
                    "ChequeDate"=>date("Y-m-d",strtotime($req->ChequeDate)),
                    "TotalAmount"=>floatval($req->TotalAmount),
                    "UpdatedOn"=>date("Y-m-d H:i:s"),
                    "UpdatedBy"=>$this->UserID
				);
				$status=DB::Table($this->CurrFYDB.'tbl_payments')->where('TranNo',$TranNo)->update($data);
                $DetailIDs=array();
				DB::Table($this->CurrFYDB.'tbl_advance_amount_log')->where('PaymentID',$TranNo)->delete();

                if($status){
                    $Details=json_decode($req->Details,true);
                    for($i=0;$i<count($Details);$i++){
                        if($status){
							$DetailID="";
                            $t=DB::Table($this->CurrFYDB.'tbl_payment_details')->where('OrderID',$Details[$i]['OrderID'])->where('TranNo',$TranNo)->get();
                            if(count($t)>0){
								$DetailID=$t[0]->DetailID;
                                $DetailIDs[]=$t[0]->DetailID;
                                $data=array(
									"OrderID"=>$Details[$i]['OrderID'],
									"LessFromAdvance"=>floatval($Details[$i]['LessFromAdvance']),
									"PaidAmount"=>floatval($Details[$i]['PaidAmount']),
									"Amount"=>floatval($Details[$i]['Amount']),
                                    "UpdatedOn"=>date("Y-m-d H:i:s")
                                );
                                $status=DB::Table($this->CurrFYDB.'tbl_payment_details')->where('DetailID',$t[0]->DetailID)->update($data);
                                if($status){
                                    $status=$this->general->PaymentUpdates($req->Vendor,$Details[$i]['OrderID'], $this->CurrFYDB);
                                }
                            }else{
								$DetailID = DocNum::getDocNum(docTypes::PaymentDetails->value, $this->CurrFYDB, Helper::getCurrentFy());
                                $DetailIDs[]=$DetailID;
                                $data=array(
                                    "DetailID"=>$DetailID,
                                    "TranNo"=>$TranNo,
									"OrderID"=>$Details[$i]['OrderID'],
									"LessFromAdvance"=>floatval($Details[$i]['LessFromAdvance']),
									"PaidAmount"=>floatval($Details[$i]['PaidAmount']),
									"Amount"=>floatval($Details[$i]['Amount']),
                                    "CreatedOn"=>date("Y-m-d H:i:s")
                                );
                                $status=DB::Table($this->CurrFYDB.'tbl_payment_details')->insert($data);
                                if($status){
									DocNum::updateDocNum(docTypes::PaymentDetails->value, $this->CurrFYDB);
                                    $status=$this->general->PaymentUpdates($req->LedgerID,$Details[$i]['OrderID'], $this->CurrFYDB);
                                }
                            }
							if($status ){
								$tdata=array("TranType"=>"Payments","TranNo"=>$TranNo,"LedgerID"=>$req->LedgerID,"PaymentID"=>$TranNo,"DetailID"=>$DetailID,"Amount"=>floatval($Details[$i]['LessFromAdvance']));
								$status=$this->general->AdvanceAmountUsedLog($tdata, $this->CurrFYDB);
							}
                        }
                    }
                }
                if(($status)&&(count($DetailIDs)>0)){
                    $sql="Select * From ".$this->CurrFYDB."tbl_payment_details  Where TranNo='".$TranNo."'  and DetailID not in('".implode("','",$DetailIDs)."')";
                    $result=DB::SELECT($sql);
                    if(count($result)>0){
                        $sql="Delete From ".$this->CurrFYDB."tbl_payment_details  Where TranNo='".$TranNo."'  and DetailID not in('".implode("','",$DetailIDs)."')";
                        $status=DB::DELETE($sql);
                        for($i=0;$i<count($result);$i++){
                            if($status){
                                $status=$this->general->PaymentUpdates($req->LedgerID,$result[$i]->OrderID,$result[$i]->OrderID);
                            }
                        }
                    }
                }
				if($status==true && $this->Settings['APRARP']==true && $req->PaymentType=="Advance"){
					$this->general->UpdateAdvancePayment($TranNo,$req->LedgerID,$this->CurrFYDB);
				}
			}catch(Exception $e) {
				$status=false;
			}

			if($status==true){
				$NewData=$this->getPayments(array("TranNo"=>$TranNo));
				$logData=array("Description"=>"Payment modified ","ModuleName"=>$this->ActiveMenuName,"Action"=>"Update","ReferID"=>$TranNo,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				DB::commit();
				return array('status'=>true,'message'=>"Payment updated successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Payment update Failed");
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}
    }
	public function TableView(Request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$columns = array(
				array( 'db' => 'H.TranNo', 'dt' => '0' ),
				array( 'db' => 'H.Trandate', 'dt' => '1','formatter' => function( $d, $row ) {return date($this->Settings['date-format'],strtotime($d));}),
				array( 'db' => 'V.VendorName', 'dt' => '2' ),
				array( 'db' => 'H.MOP', 'dt' => '3' ),
				array( 'db' => 'H.MOPRefNo', 'dt' => '4' ),
				array( 'db' => 'H.PaymentType', 'dt' => '5' ),
				array( 'db' => 'H.TotalAmount', 'dt' => '6','formatter' => function( $d, $row ) {return Helper::NumberFormat($d,$this->Settings['price-decimals']);}),
				array( 'db' => 'H.TranNo',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$html='';
							if($row['PaymentType']=="Order"){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-info btn-sm -success mr-10 btnDetailView" data-original-title="Edit"><i class="fa fa-eye"></i></button>';
							}
                            if($this->general->isCrudAllow($this->CRUD,"edit")==true){
                                $html.='<button type="button" data-id="'.$d.'" data-payment-type="'.$row['PaymentType'].'" class="btn  btn-outline-success btn-sm -success mr-10 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
                            }
                            if($this->general->isCrudAllow($this->CRUD,"delete")==true){
                                $html.='<button type="button" data-id="'.$d.'" data-payment-type="'.$row['PaymentType'].'" class="btn  btn-outline-danger btn-sm -success btnDelete" data-original-title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                            }
							return $html;
						}
					),
				array( 'db' => 'H.PaymentType', 'dt' => '8' )
			);
			$columns1 = array(
				array( 'db' => 'TranNo', 'dt' => '0' ),
				array( 'db' => 'Trandate', 'dt' => '1','formatter' => function( $d, $row ) {return date($this->Settings['date-format'],strtotime($d));} ),
				array( 'db' => 'VendorName', 'dt' => '2' ),
				array( 'db' => 'MOP', 'dt' => '3' ),
				array( 'db' => 'MOPRefNo', 'dt' => '4' ),
				array( 
					'db' => 'PaymentType', 
					'dt' => '5',
					'formatter' => function( $d, $row ) {
						if($d=="Advance"){
							return "<span class='badge badge-info m-1'>".$d."</span>";
						}else{
							return "<span class='badge badge-primary m-1'>".$d."</span>";
						}
					}
				),
				array( 'db' => 'TotalAmount', 'dt' => '6','formatter' => function( $d, $row ) {return Helper::NumberFormat($d,$this->Settings['price-decimals']);} ),
				array(
						'db' => 'TranNo',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$html='';
							if($row['PaymentType']=="Order"){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-info btn-sm -success mr-10 btnDetailView" data-original-title="Edit"><i class="fa fa-eye"></i></button>';
							}
                            
                            if($this->general->isCrudAllow($this->CRUD,"edit")==true){
                                $html.='<button type="button" data-id="'.$d.'" data-payment-type="'.$row['PaymentType'].'" class="btn  btn-outline-success btn-sm -success mr-10 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
                            }
                            if($this->general->isCrudAllow($this->CRUD,"delete")==true){
                                $html.='<button type="button" data-id="'.$d.'" data-payment-type="'.$row['PaymentType'].'" class="btn  btn-outline-danger btn-sm -success btnDelete" data-original-title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                            }
							return $html;
						}
                ),
				array( 'db' => 'PaymentType', 'dt' => '8' )
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$this->CurrFYDB.'tbl_payments as H LEFT JOIN tbl_vendors as V ON V.VendorID=H.LedgerID';
			$data['PRIMARYKEY']='H.TranNo';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns1;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=" H.DFlag=0 ";
			return SSP::SSP( $data);
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	
	public function getVendorOrder($data=array()){
		$sql ="SELECT O.VOrderID,O.OrderID, O.OrderNo, O.OrderDate, O.QID, '' as EnqID, O.ExpectedDelivery, O.CustomerID, C.CustomerName, C.MobileNo1, C.MobileNo2, C.Email, C.Address as BAddress, C.CountryID as BCountryID, BC.CountryName as BCountryName, ";
		$sql.=" C.StateID as BStateID, BS.StateName as BStateName, C.DistrictID as BDistrictID, BD.DistrictName as BDistrictName, C.TalukID, BT.TalukName as BTalukName, C.CityID as BCityID, BCI.CityName as BCityName, C.PostalCodeID as BPostalCodeID, ";
		$sql.=" BPC.PostalCode as BPostalCode, BC.PhoneCode, O.ReceiverName, O.ReceiverMobNo, O.DAddress, O.DCountryID, CO.CountryName as DCountryName, O.DStateID, S.StateName as DStateName, O.DDistrictID, D.DistrictName as DDistrictName, O.DTalukID, ";
		$sql.=" T.TalukName as DTalukName, O.DCityID, CI.CityName as DCityName, O.DPostalCodeID, PC.PostalCode as DPostalCode, O.TaxAmount, O.SubTotal, O.DiscountType, O.DiscountPercentage, O.DiscountAmount, O.CGSTAmount, ";
		$sql.=" O.SGSTAmount, O.IGSTAmount, O.TotalAmount, O.AdditionalCost, O.NetAmount, O.PaidAmount,O.LessFromAdvance, O.TotalPaidAmount, O.BalanceAmount, O.PaymentStatus,  O.AdditionalCostData, O.Status,  O.RejectedOn,  O.RejectedBy, O.ReasonID, RR.RReason, O.RDescription ";
		$sql.=" FROM ".$this->CurrFYDB."tbl_vendor_orders as O  LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID LEFT JOIN ".$this->generalDB."tbl_countries as BC ON BC.CountryID=C.CountryID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_states as BS ON BS.StateID=C.StateID LEFT JOIN ".$this->generalDB."tbl_districts as BD ON BD.DistrictID=C.DistrictID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_taluks as BT ON BT.TalukID=C.TalukID LEFT JOIN ".$this->generalDB."tbl_cities as BCI ON BCI.CityID=C.CityID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_postalcodes as BPC ON BPC.PID=C.PostalCodeID LEFT JOIN ".$this->generalDB."tbl_countries as CO ON CO.CountryID=O.DCountryID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_states as S ON S.StateID=O.DStateID LEFT JOIN ".$this->generalDB."tbl_districts as D ON D.DistrictID=O.DDistrictID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_taluks as T ON T.TalukID=O.DTalukID LEFT JOIN ".$this->generalDB."tbl_cities as CI ON CI.CityID=O.DCityID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_postalcodes as PC ON PC.PID=O.DPostalCodeID LEFT JOIN tbl_reject_reason as RR ON RR.RReasonID=O.ReasonID "; 
		$sql.=" Where 1=1 ";
		if(is_array($data)){
			if(array_key_exists("VOrderID",$data)){$sql.=" AND O.VOrderID='".$data['VOrderID']."'";}
			if(array_key_exists("OrderID",$data)){$sql.=" AND O.OrderID='".$data['OrderID']."'";}
		}
		$result=DB::SELECT($sql);
		for($i=0;$i<count($result);$i++){
			$tmp=DB::Table($this->CurrFYDB."tbl_order")->where('OrderID',$result[0]->OrderID)->get();
			if(count($tmp)>0){
				$result[$i]->EnqID=$tmp[0]->EnqID;
			}
			$result[$i]->AdditionalCostData=unserialize($result[$i]->AdditionalCostData);
			$sql="SELECT OD.DetailID, OD.OrderID, OD.QID, OD.QDID, OD.VOrderID, OD.ProductID, P.ProductName, P.HSNSAC, P.UID, U.UCode, U.UName, OD.Qty, OD.Price, OD.TaxType, OD.TaxPer, OD.Taxable, OD.DiscountType, OD.DiscountPer, OD.DiscountAmt, OD.TaxAmt, OD.CGSTPer, OD.SGSTPer, OD.IGSTPer, OD.CGSTAmt, OD.SGSTAmt, OD.IGSTAmt, OD.TotalAmt, OD.VendorID, V.VendorName, OD.Status, OD.RejectedBy, OD.RejectedOn, OD.ReasonID, RR.RReason, OD.RDescription, OD.DeliveredOn, OD.DeliveredBy  ";
			$sql.=" FROM ".$this->CurrFYDB."tbl_order_details as OD LEFT JOIN tbl_products as P ON P.ProductID=OD.ProductID LEFT JOIN tbl_uom as U ON U.UID=P.UID LEFT JOIN tbl_reject_reason as RR ON RR.RReasonID=OD.ReasonID LEFT JOIN tbl_vendors as V ON V.VendorID=OD.VendorID ";
			$sql.=" Where OD.VOrderID='".$result[$i]->VOrderID."' Order By OD.DetailID ";
			$result[$i]->Details=DB::SELECT($sql);
			$addCharges=[];
			$result1=DB::Table($this->CurrFYDB.'tbl_vendor_quotation')->Where('EnqID',$result[$i]->EnqID)->get();
			foreach($result1 as $tmp){
				$addCharges[$tmp->VendorID]=Helper::NumberFormat($tmp->AdditionalCost,$this->Settings['price-decimals']);
			}
			$result[$i]->AdditionalCharges=$addCharges;

		}
		return $result;
	}
	public function getOrderDetails(Request $req,$OrderID){
		$formdata=$this->general->UserInfo;
		$formdata['OData']=$this->getVendorOrder(["VOrderID"=>$OrderID]);
		if(count($formdata['OData'])>0){
			$formdata['OData']=$formdata['OData'][0];
			return view('app.transaction.payments.order-details',$formdata);
		}else{
			return "";
		}
	}
	public function Delete(Request $req,$TranNo){
		$OldData=$this->getPayments(array("TranNo"=>$TranNo));$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$status=true;
			try{
				if($OldData[0]->PaymentType=="Advance"){
					$temp=DB::Table($this->CurrFYDB.'tbl_advance_amount_log')->where('AdvID',$TranNo)->get();
					for($i=0;$i<count($temp);$i++){
						$sql="Update ".$this->CurrFYDB."tbl_payment_details Set LessFromAdvance=LessFromAdvance-".$temp[$i]->Amount." Where TranNo='".$temp[$i]->PaymentID."' and DetailID='".$temp[$i]->DetailID."'";
						DB::Update($sql);

						$sql="Update ".$this->CurrFYDB."tbl_payment_details SET Amount=LessFromAdvance+PaidAmount Where TranNo='".$temp[$i]->PaymentID."' and DetailID='".$temp[$i]->DetailID."'";
						DB::Update($sql);
						
						$sql="Update ".$this->CurrFYDB."tbl_payments SET TotalAmount= (SELECT SUM(IFNULL(Amount,0)) as Amount FROM ".$this->CurrFYDB."tbl_payment_details where TranNo='".$temp[$i]->PaymentID."') Where TranNo='".$temp[$i]->PaymentID."'";
						DB::Update($sql);

						
						$sql="Update ".$this->CurrFYDB."tbl_advance_amount_log SET Amount= 0 Where TranNo='".$temp[$i]->TranNo."'";
						DB::Update($sql);
						
					}
					$this->general->UpdateAdvanceAmount("Payments",$OldData[0]->LedgerID,$this->CurrFYDB);

					DB::table($this->CurrFYDB.'tbl_advance_amount_log')->where('AdvID',$TranNo)->delete();
				}else{
					
					$temp=DB::Table($this->CurrFYDB.'tbl_advance_amount_log')->where('PaymentID',$TranNo)->get();
					for($i=0;$i<count($temp);$i++){
						$sql="Update ".$this->CurrFYDB."tbl_advance_amount_log SET Amount= 0 Where TranNo='".$temp[$i]->TranNo."'";
						DB::Update($sql);
					}
					$this->general->UpdateAdvanceAmount("Payments",$OldData[0]->LedgerID,$this->CurrFYDB);
					DB::Table($this->CurrFYDB.'tbl_advance_amount_log')->where('PaymentID',$TranNo)->delete();

					
				}

				$status=DB::table($this->CurrFYDB.'tbl_payments')->where('TranNo',$TranNo)->delete();
				if($status){
					$temp=DB::Table($this->CurrFYDB.'tbl_payment_details')->where('TranNo',$TranNo)->get();
					if(count($temp)>0){
						$status=DB::Table($this->CurrFYDB.'tbl_payment_details')->where('TranNo',$TranNo)->delete();
					}
				}
                if($status){
                    $Details=$OldData[0]->Details; 
                    for($i=0;$i<count($Details);$i++){
                        $status=$this->general->PaymentUpdates($OldData[0]->LedgerID,$Details[$i]->OrderID,$this->CurrFYDB);
                    }
					
                }
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
                $NewData=$this->getPayments(array("TranNo"=>$TranNo));
				$logData=array("Description"=>"Payment(".$TranNo.") has been Deleted ","ModuleName"=>$this->ActiveMenuName,"Action"=>"Delete","ReferID"=>$TranNo,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Payment deleted successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Payment delete failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
    public function getDetails(Request $req){
        $FormData=array();
		$FormData['Settings']=$this->Settings;
        $FormData['Data']=$this->getPayments(array('TranNo'=>$req->TranNo));
        return view('app.transaction.payments.details',$FormData);
    }
}