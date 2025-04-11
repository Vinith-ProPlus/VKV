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
use Dompdf\Dompdf;
use Dompdf\Options;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class QuotationController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::Quotation->value;
		$this->PageTitle="Quotation";
		$this->generalDB=Helper::getGeneralDB();
		$this->CurrFYDB=Helper::getCurrFYDB();
        $this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
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
            return view('app.transaction.quotation.view',$FormData);
        }else{
            return view('errors.403');
        }
    }
    public function QuoteView(Request $req,$QID){
        if($this->general->isCrudAllow($this->CRUD,"edit")==true){

			$OtherCRUD=[
				"order"=>$this->general->getCrudOperations(activeMenuNames::Order->value)
			];
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=false;
			$FormData['Settings']=$this->Settings;
			$FormData['QID']=$QID;
			$FormData['OtherCRUD']=$OtherCRUD;
			$FormData['QData']=$this->getQuotes(["QID"=>$QID]);
			if(count($FormData['QData'])>0){
				$FormData['QData']=$FormData['QData'][0];
				return view('app.transaction.quotation.quote-view', $FormData);
			}else{
				return view('errors.403');
			}
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
            return Redirect::to('admin/transaction/quotation/');
        }else{
            return view('errors.403');
        }
    }

	public function QuotePDF1(Request $req,$QID){
		$FormData=$this->general->UserInfo;
		$FormData['PageTitle']=$this->PageTitle;
		$FormData['Settings']=$this->Settings;
		$FormData['QID']=$QID;
		$FormData['QData']=$this->getQuotes(["QID"=>$QID]);
		if(count($FormData['QData'])>0){
			$FormData['QData']=$FormData['QData'][0];
			return view('app.transaction.quotation.pdf-view', $FormData);
		}else{
			return view('errors.403');
		}
	}
	public function QuotePDF(Request $req, $QID){
		$FormData = $this->general->UserInfo;
		$FormData['PageTitle'] = $this->PageTitle;
		$FormData['Settings'] = $this->Settings;
		$FormData['QID'] = $QID;
		$FormData['ChatID'] = $req->chatID;
		$FormData['isPDF'] = $req->isPDF;
		$FormData['QData'] = $this->getQuotes(["QID" => $QID]);

		if (count($FormData['QData']) > 0) {
			logger('QUOTE');
			logger(json_encode($FormData['QData'][0]));
			$FormData['QData'] = $FormData['QData'][0]; //dd($FormData['QData']);
			
			return view('app.transaction.quotation.pdf-view', $FormData);
		} else {
			return response()->json(['status' => 'error', 'message' => 'Quote not found'], 404);
		}
	}
	public function QuoteCancel(request $req,$QID){
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$tdata=array(
				"Status"=>"Rejected",
				"RReasonID"=>$req->ReasonID,
				"RRDescription"=>$req->Description,
				"RejectedOn"=>now(),
				"RejectedBy"=>$this->UserID
			);
			$status=DB::Table($this->CurrFYDB."tbl_quotation")->where('QID',$QID)->update($tdata);
			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"Quote Successfully Canceled");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Failed to Cancel Quote");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function itemUpdate(Request $req,$DetailID){ 
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			
			DB::beginTransaction();
			$status=false;
			$tdata=array(
				"Qty"=>$req->Qty,
				"Price"=>$req->Price,
				"Taxable"=>$req->Taxable,
				"TaxAmt"=>$req->TaxAmt,
				"CGSTAmt"=>$req->CGSTAmt,
				"SGSTAmt"=>$req->SGSTAmt,
				"IGSTAmt"=>$req->IGSTAmt,
				"TotalAmt"=>$req->TotalAmt,
				"UpdatedOn"=>now(),
				"updatedBy"=>$this->UserID
			);
			//Update Quotation table amount totals
			$status=DB::Table($this->CurrFYDB."tbl_quotation_details")->where('QID',$req->QID)->where('DetailID',$DetailID)->update($tdata);
			if($status){
				$tdata=["TaxAmount"=>0,"CGSTAmount"=>0,"IGSTAmount"=>0,"SGSTAmount"=>0,"TotalAmount"=>0,"SubTotal"=>0,"DiscountAmount"=>0,"AdditionalCost"=>0,"OverAllAmount"=>0];
				$sql="SELECT IFNULL(SUM(TaxAmt),0) as TaxAmount, IFNULL(SUM(CGSTAmt),0) as CGSTAmount, IFNULL(SUM(IGSTAmt),0) as IGSTAmount, IFNULL(SUM(SGSTAmt),0) as SGSTAmount, SUM(TotalAmt) as TotalAmount, IFNULL(SUM(Taxable),0) as SubTotal FROM ".$this->CurrFYDB."tbl_quotation_details where QID='".$req->QID."' and isCancelled=0";
				$result=DB::SELECT($sql);
				foreach($result as $tmp){
					$tdata['TaxAmount']+=floatval($tmp->TaxAmount);
					$tdata['CGSTAmount']+=floatval($tmp->CGSTAmount);
					$tdata['IGSTAmount']+=floatval($tmp->IGSTAmount);
					$tdata['SGSTAmount']+=floatval($tmp->SGSTAmount);
					$tdata['TotalAmount']+=floatval($tmp->TotalAmount);
					$tdata['SubTotal']+=floatval($tmp->SubTotal);
				}
				$result=DB::Table($this->CurrFYDB."tbl_quotation")->where('QID',$req->QID)->get();
				foreach($result as $tmp){
					$tdata['DiscountAmount']+=floatval($tmp->DiscountAmount);
					$tdata['AdditionalCost']+=floatval($tmp->AdditionalCost);
				}
				$tdata['TotalAmount']=floatval($tdata['SubTotal'])+floatval($tdata['CGSTAmount'])+floatval($tdata['IGSTAmount'])+floatval($tdata['SGSTAmount']);
				$tdata['TotalAmount']-=floatval($tdata['DiscountAmount']);

				$tdata['OverAllAmount']=floatval($tdata['TotalAmount'])+floatval($tdata['AdditionalCost']);
				$tdata['UpdatedOn']=date("Y-m-d H:i:s",strtotime("1 minutes"));
				$tdata['UpdatedBy']=$this->UserID;
				$status=DB::Table($this->CurrFYDB."tbl_quotation")->where('QID',$req->QID)->update($tdata);
			}

			//Update Vendor Quote Details
			if($status){
				$tdata=array(
					"Qty"=>$req->Qty,
					"Price"=>$req->Price,
					"Taxable"=>$req->Taxable,
					"TaxAmt"=>$req->TaxAmt,
					"CGSTAmt"=>$req->CGSTAmt,
					"SGSTAmt"=>$req->SGSTAmt,
					"IGSTAmt"=>$req->IGSTAmt,
					"TotalAmt"=>$req->TotalAmt,
					"UpdatedOn"=>now(),
					"updatedBy"=>$this->UserID
				);
				//Update Quotation table amount totals
				$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation_details")->where('VQuoteID',$req->VQuoteID)->where('DetailID',$req->VQDetailID)->update($tdata);
				if($status){
					$tdata=["TaxAmount"=>0,"TotalAmount"=>0,"SubTotal"=>0];
					$sql="SELECT IFNULL(SUM(TaxAmt),0) as TaxAmount, IFNULL(SUM(CGSTAmt),0) as CGSTAmount, IFNULL(SUM(IGSTAmt),0) as IGSTAmount, IFNULL(SUM(SGSTAmt),0) as SGSTAmount, SUM(TotalAmt) as TotalAmount, IFNULL(SUM(Taxable),0) as SubTotal FROM ".$this->CurrFYDB."tbl_vendor_quotation_details where VQuoteID='".$req->VQuoteID."' and isCancelled=0";
					$result=DB::SELECT($sql);
					foreach($result as $tmp){
						$tdata['TaxAmount']+=floatval($tmp->TaxAmount);
						//$tdata['CGSTAmount']+=floatval($tmp->CGSTAmount);
						//$tdata['IGSTAmount']+=floatval($tmp->IGSTAmount);
						//$tdata['SGSTAmount']+=floatval($tmp->SGSTAmount);
						$tdata['TotalAmount']+=floatval($tmp->TotalAmount);
						$tdata['SubTotal']+=floatval($tmp->SubTotal);
					}
					$tdata['TotalAmount']=floatval($tdata['SubTotal'])+floatval($tdata['TaxAmount']);
	
					$tdata['UpdatedOn']=date("Y-m-d H:i:s",strtotime("1 minutes"));
					$tdata['UpdatedBy']=$this->UserID;
					$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation")->where('VQuoteID',$req->VQuoteID)->update($tdata);
				}
			}
			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"Quote updated Successfully ");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Quote updated failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function QuoteItemCancel(request $req,$DetailID){ 
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$status=false;
			$tdata=array(
				"isCancelled"=>"1",
				"ReasonID"=>$req->ReasonID,
				"RDescription"=>$req->Description,
				"CancelledOn"=>now(),
				"CancelledBy"=>$this->UserID
			);
			//item cancel
			$status=DB::Table($this->CurrFYDB."tbl_quotation_details")->where('QID',$req->QID)->where('DetailID',$DetailID)->update($tdata);

			//Update Vendor's Additional Cost in Quotation Table
			if($status){
				$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation")->where('EnqID',$req->EnqID)->where('VendorID',$req->VendorID)->update(["AdditionalCost"=>$req->VACharges,"UpdatedOn"=>now(),"UpdatedBy"=>$this->UserID]);
			}
			//Update Customer's Additional Cost in Quotation Table
			if($status){
				$sql="Update ".$this->CurrFYDB."tbl_quotation set AdditionalCost='".$req->CACharges."',OverAllAmount=(TotalAmount+'".floatval($req->CACharges)."'), UpdatedOn='".date("Y-m-d H:i:s")."',UpdatedBy='".$this->UserID."' Where QID='".$req->QID."'";
				$status=DB::Update($sql);
			}
			// Verify if all items have been cancelled. If all items are cancelled, update the status in the main quotation table.
			if($status){
				$t=DB::Table($this->CurrFYDB."tbl_quotation_details")->where('QID',$req->QID)->where('isCancelled',0)->count();
				if(intval($t)<=0){
					$tdata=array(
						"Status"=>"Rejected",
						"RReasonID"=>$req->ReasonID,
						"RRDescription"=>$req->Description,
						"RejectedOn"=>now(),
						"RejectedBy"=>$this->UserID
					);
					$status=DB::Table($this->CurrFYDB."tbl_quotation")->where('QID',$req->QID)->update($tdata);
				}
			}
			// Update Tax Amount, Total Amount, Subtotal, and Net Amount for non-cancelled items in the quotation table.
			if($status){
				$tdata=["TaxAmount"=>0,"CGSTAmount"=>0,"IGSTAmount"=>0,"SGSTAmount"=>0,"TotalAmount"=>0,"SubTotal"=>0,"DiscountAmount"=>0,"AdditionalCost"=>0,"OverAllAmount"=>0];
				$sql="SELECT IFNULL(SUM(TaxAmt),0) as TaxAmount, IFNULL(SUM(CGSTAmt),0) as CGSTAmount, IFNULL(SUM(IGSTAmt),0) as IGSTAmount, IFNULL(SUM(SGSTAmt),0) as SGSTAmount, SUM(TotalAmt) as TotalAmount, IFNULL(SUM(Taxable),0) as SubTotal FROM ".$this->CurrFYDB."tbl_quotation_details where QID='".$req->QID."' and isCancelled=0";
				$result=DB::SELECT($sql);
				foreach($result as $tmp){
					$tdata['TaxAmount']+=floatval($tmp->TaxAmount);
					$tdata['CGSTAmount']+=floatval($tmp->CGSTAmount);
					$tdata['IGSTAmount']+=floatval($tmp->IGSTAmount);
					$tdata['SGSTAmount']+=floatval($tmp->SGSTAmount);
					$tdata['TotalAmount']+=floatval($tmp->TotalAmount);
					$tdata['SubTotal']+=floatval($tmp->SubTotal);
				}
				$result=DB::Table($this->CurrFYDB."tbl_quotation")->where('QID',$req->QID)->get();
				foreach($result as $tmp){
					$tdata['DiscountAmount']+=floatval($tmp->DiscountAmount);
					$tdata['AdditionalCost']+=floatval($tmp->AdditionalCost);
				}
				$tdata['TotalAmount']=floatval($tdata['SubTotal'])+floatval($tdata['CGSTAmount'])+floatval($tdata['IGSTAmount'])+floatval($tdata['SGSTAmount']);
				$tdata['TotalAmount']-=floatval($tdata['DiscountAmount']);

				$tdata['OverAllAmount']=floatval($tdata['TotalAmount'])+floatval($tdata['AdditionalCost']);
				$tdata['UpdatedOn']=date("Y-m-d",strtotime("1 minutes"));
				$tdata['UpdatedBy']=$this->UserID;
				$status=DB::Table($this->CurrFYDB."tbl_quotation")->where('QID',$req->QID)->update($tdata);
			}
			
			/********************************************************************************************************************************* */
			//Update Vendor Quote Item Cancell
			/********************************************************************************************************************************* */
			if($status){
				$tdata=array(
					"isCancelled"=>"1",
					"ReasonID"=>$req->ReasonID,
					"RDescription"=>$req->Description,
					"CancelledOn"=>now(),
					"CancelledBy"=>$this->UserID
				);
				//Vendor item cancel
				$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation_details")->where('VQuoteID',$req->VQID)->where('DetailID',$req->VQDID)->update($tdata);
			}
			// Verify if all items have been cancelled. If all items are cancelled, update the status in the main vendor quotation table.
			if($status){
				$t=DB::Table($this->CurrFYDB."tbl_vendor_quotation_details")->where('VQuoteID',$req->VQID)->where('isCancelled',0)->count();
				if(intval($t)<=0){
					$tdata=array(
						"Status"=>"Rejected",
						"RReasonID"=>$req->ReasonID,
						"RRDescription"=>$req->Description,
						"RejectedOn"=>now(),
						"RejectedBy"=>$this->UserID
					);
					$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation")->where('VQuoteID',$req->VQID)->update($tdata);
				}
			}
			// Update Tax Amount, Total Amount, Subtotal, and Net Amount for non-cancelled items in the quotation table.
			if($status){
				$tdata=["TaxAmount"=>0,"TotalAmount"=>0,"SubTotal"=>0];
				$sql="SELECT IFNULL(SUM(TaxAmt),0) as TaxAmount, IFNULL(SUM(CGSTAmt),0) as CGSTAmount, IFNULL(SUM(IGSTAmt),0) as IGSTAmount, IFNULL(SUM(SGSTAmt),0) as SGSTAmount, SUM(TotalAmt) as TotalAmount, IFNULL(SUM(Taxable),0) as SubTotal FROM ".$this->CurrFYDB."tbl_vendor_quotation_details where VQuoteID='".$req->VQID."' and isCancelled=0";
				$result=DB::SELECT($sql);
				foreach($result as $tmp){
					$tdata['TaxAmount']+=floatval($tmp->TaxAmount);
					$tdata['TotalAmount']+=floatval($tmp->TotalAmount);
					$tdata['SubTotal']+=floatval($tmp->SubTotal);
				}
				$tdata['TotalAmount']=floatval($tdata['SubTotal'])+floatval($tdata['TaxAmount']);

				$tdata['UpdatedOn']=date("Y-m-d",strtotime("1 minutes"));
				$tdata['UpdatedBy']=$this->UserID;
				$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation")->where('VQuoteID',$req->VQID)->update($tdata);
			}
			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"Quote Successfully Canceled");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Failed to Cancel Quote");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	private function SaveVendorOrders($QData,$OrderID,$QID,$ExpectedDelivery){
		$status=true;
		$QDetails=DB::Select("Select DISTINCT(VendorID) as VendorID From ".$this->CurrFYDB."tbl_order_details Where OrderID='".$OrderID."' and Status<>'Cancelled'");
		//SELECT OD.DetailID as ODetailID, OD.QID, OD.QDID, OD.OrderID, OD.ProductID, VQD.Qty, VQD.Price FROM tbl_order_details as OD LEFT JOIN tbl_quotation_details as QD ON QD.DetailID=OD.QDID AND QD.QID=OD.QID LEFT JOIN tbl_vendor_quotation_details as VQD ON VQD.DetailID=QD.VQDetailID AND VQD.ProductID=QD.ProductID;
		foreach($QDetails as $QItem){
			$VendorID=$QItem->VendorID;
			$CommissionPercentage=0;
			$t=DB::Table('tbl_vendors')->where('VendorID',$VendorID)->get();
			if(count($t)>0){
				$CommissionPercentage=$t[0]->CommissionPercentage;
			}
			$VOrderID=DocNum::getDocNum(docTypes::VendorOrders->value, $this->CurrFYDB,Helper::getCurrentFy());
			$VOrderNo=DocNum::getInvNo(docTypes::VendorOrders->value);
			if($status){
				$sql =" SELECT OD.DetailID as ODetailID, OD.QID, OD.QDID, OD.OrderID, OD.ProductID, VQD.Qty, VQD.Price, VQD.TaxType, VQD.TaxID, VQD.TaxPer, VQD.Taxable, VQD.DiscountType, VQD.DiscountPer, VQD.DiscountAmt, VQD.TaxAmt, VQD.CGSTPer, VQD.SGSTPer, VQD.IGSTPer, VQD.CGSTAmt, VQD.SGSTAmt, VQD.IGSTAmt, VQD.TotalAmt ";
				$sql.=" FROM ".$this->CurrFYDB."tbl_order_details as OD LEFT JOIN ".$this->CurrFYDB."tbl_quotation_details as QD ON QD.DetailID=OD.QDID AND QD.QID=OD.QID LEFT JOIN ".$this->CurrFYDB."tbl_vendor_quotation_details as VQD ON VQD.DetailID=QD.VQDetailID AND VQD.ProductID=QD.ProductID ";
				$sql.=" Where QD.isCancelled = 0 and OD.OrderID='".$OrderID."' and OD.VendorID='".$VendorID."'";
				$result=DB::SELECT($sql);
				$totals=json_decode(json_encode(["TaxAmount"=>0,"SubTotal"=>0,"CGSTAmount"=>0,"SGSTAmount"=>0,"IGSTAmount"=>0,"additionalCharges"=>0,"TotalAmount"=>0]));
				foreach($result as $tdata){
					if($status){
						$DetailID=DocNum::getDocNum(docTypes::VendorOrderDetails->value, $this->CurrFYDB,Helper::getCurrentFy());
						$data=[
							"DetailID"=>$DetailID,
							"QID"=>$tdata->QID,
							"QDID"=>$tdata->QDID,
							"OrderID"=>$tdata->OrderID,
							"ODetailID"=>$tdata->ODetailID,
							"VOrderID"=>$VOrderID,
							"ProductID"=>$tdata->ProductID,
							"Qty"=>$tdata->Qty,
							"Price"=>$tdata->Price,
							"TaxType"=>$tdata->TaxType,
							"TaxPer"=>$tdata->TaxPer,
							"Taxable"=>$tdata->Taxable,
							"DiscountType"=>$tdata->DiscountType,
							"DiscountPer"=>$tdata->DiscountPer,
							"DiscountAmt"=>$tdata->DiscountAmt,
							"TaxAmt"=>$tdata->TaxAmt,
							"CGSTPer"=>$tdata->CGSTPer,
							"SGSTPer"=>$tdata->SGSTPer,
							"IGSTPer"=>$tdata->IGSTPer,
							"CGSTAmt"=>$tdata->CGSTAmt,
							"SGSTAmt"=>$tdata->SGSTAmt,
							"IGSTAmt"=>$tdata->IGSTAmt,
							"TotalAmt"=>$tdata->TotalAmt,
							"CreatedOn"=>now(),
							"CreatedBy"=>$this->UserID
						];

						$totals->TaxAmount+=$tdata->TaxAmt;
						$totals->SubTotal+=$tdata->Taxable;
						$totals->CGSTAmount+=$tdata->CGSTAmt;
						$totals->SGSTAmount+=$tdata->SGSTAmt;
						$totals->IGSTAmount+=$tdata->IGSTAmt;
						$status=DB::Table($this->CurrFYDB.'tbl_vendor_order_details')->insert($data);
						if($status){
							DocNum::updateDocNum(docTypes::VendorOrderDetails->value, $this->CurrFYDB);
							$status=DB::table($this->CurrFYDB.'tbl_order_details')->where('DetailID',$tdata->ODetailID)->update(["VOrderID"=>$VOrderID,"VOrderDetailID"=>$DetailID,"UpdatedOn"=>now(),"updatedBy"=>$this->UserID]);
						}
					}
				}
				if($status){
					$sql="SELECT AdditionalCost FROM ".$this->CurrFYDB."tbl_vendor_quotation Where VendorID='".$VendorID."' and EnqID in(Select EnqID From ".$this->CurrFYDB."tbl_quotation Where QID='".$QID."')";
					$tmp=DB::SELECT($sql);
					foreach($tmp as $t){
						$totals->additionalCharges+=floatval($t->AdditionalCost);
					}
					$totals->TotalAmount=floatval($totals->SubTotal)+floatval($totals->CGSTAmount)+floatval($totals->SGSTAmount)+floatval($totals->IGSTAmount);
					$CommissionAmount=(($totals->TotalAmount*$CommissionPercentage)/100);
					$NetAmount=(($totals->TotalAmount-$CommissionAmount)+$totals->additionalCharges);
					$tdata=[
						"VOrderID"=>$VOrderID,
						"OrderID"=>$OrderID,
						"OrderNo"=>$VOrderNo,
						"OrderDate"=>date("Y-m-d"),
						"ExpectedDelivery"=>date("Y-m-d",strtotime($ExpectedDelivery)),
						"QID"=>$QID,
						"CustomerID"=>$QData->CustomerID,
						"AID"=>$QData->AID,
						"VendorID"=>$VendorID,
						"ReceiverName"=>$QData->ReceiverName,
						"ReceiverMobNo"=>$QData->ReceiverMobNo,
						"DAddress"=>$QData->DAddress,
						"DCountryID"=>$QData->DCountryID,
						"DStateID"=>$QData->DStateID,
						"DDistrictID"=>$QData->DDistrictID,
						"DTalukID"=>$QData->DTalukID,
						"DCityID"=>$QData->DCityID,
						"DPostalCodeID"=>$QData->DPostalCodeID,
						"Status"=>"New",
						"TaxAmount"=>$totals->TaxAmount,
						"SubTotal"=>$totals->SubTotal,
						"DiscountType"=>"",
						"DiscountPercentage"=>0,
						"DiscountAmount"=>0,
						"CGSTAmount"=>$totals->CGSTAmount,
						"SGSTAmount"=>$totals->SGSTAmount,
						"IGSTAmount"=>$totals->IGSTAmount,
						"TotalAmount"=>$totals->TotalAmount,
						"CommissionAmount"=>$CommissionAmount,
						"CommissionPercentage"=>$CommissionPercentage,
						"AdditionalCost"=>$totals->additionalCharges,
						"NetAmount"=>$NetAmount,
						"PaidAmount"=>0,
						"BalanceAmount"=>$NetAmount,
						"PaymentStatus"=>"Unpaid",
						"AdditionalCostData"=> serialize([]),
						"CreatedOn"=>now(),
						"CreatedBy"=>$this->UserID
					];
					$status=DB::table($this->CurrFYDB.'tbl_vendor_orders')->insert($tdata);
					if($status){
						DocNum::updateDocNum(docTypes::VendorOrders->value, $this->CurrFYDB);
						DocNum::updateInvNo(docTypes::VendorOrders->value);
						$Title = "New Order Arrived. Order No " . $VOrderNo . ".";
						$Message = "You have a new order! Check now for details and fulfill it promptly.";
						Helper::saveNotification($VendorID,$Title,$Message,'Orders',$VOrderID);
						
					}
				}
			}
		}
		return $status;
	}
	public function QuoteApprove(Request $req,$QID){
		$OrderCRUD=$this->general->getCrudOperations(activeMenuNames::Order->value);
		if($this->general->isCrudAllow($this->CRUD,"add")==true){
			$data=$this->getQuotes(["QID"=>$QID]);
			$status=true;
			DB::beginTransaction();
			try {
				if(count($data)>0){
					$data=$data[0];
					$OrderID=DocNum::getDocNum(docTypes::Order->value, $this->CurrFYDB,Helper::getCurrentFy());
					$OrderNo=DocNum::getInvNo(docTypes::Order->value);
					$tdata=[
						"OrderID"=>$OrderID,
						"OrderNo"=>$OrderNo,
						"OrderDate"=>date("Y-m-d"),
						"ExpectedDelivery"=>date("Y-m-d",strtotime($req->ExpectedDelivery)),
						"QID"=>$QID,
						"EnqID"=>$data->EnqID,
						"CustomerID"=>$data->CustomerID,
						"AID"=>$data->AID,
						"ReceiverName"=>$data->ReceiverName,
						"ReceiverMobNo"=>$data->ReceiverMobNo,
						"DAddress"=>$data->DAddress,
						"DCountryID"=>$data->DCountryID,
						"DStateID"=>$data->DStateID,
						"DDistrictID"=>$data->DDistrictID,
						"DTalukID"=>$data->DTalukID,
						"DCityID"=>$data->DCityID,
						"DPostalCodeID"=>$data->DPostalCodeID,
						"Status"=>"New",
						"TaxAmount"=>$data->TaxAmount,
						"SubTotal"=>$data->SubTotal,
						"DiscountType"=>$data->DiscountType,
						"DiscountPercentage"=>$data->DiscountPercentage,
						"DiscountAmount"=>$data->DiscountAmount,
						"CGSTAmount"=>$data->CGSTAmount,
						"SGSTAmount"=>$data->SGSTAmount,
						"IGSTAmount"=>$data->IGSTAmount,
						"TotalAmount"=>$data->TotalAmount,
						"AdditionalCost"=>$data->AdditionalCost,
						"NetAmount"=>$data->NetAmount,
						"PaidAmount"=>0,
						"BalanceAmount"=>$data->NetAmount,
						"PaymentStatus"=>"Unpaid",
						"AdditionalCostData"=> serialize($data->AdditionalCostData),
						"CreatedOn"=>now(),
						"CreatedBy"=>$this->UserID
					];
					$status=DB::table($this->CurrFYDB.'tbl_order')->insert($tdata);
					if($status){
						DocNum::updateDocNum(docTypes::Order->value, $this->CurrFYDB);
						DocNum::updateInvNo(docTypes::Order->value);
						$details=$data->Details;
						foreach($details as $item){
							if($status){
								$DetailID=DocNum::getDocNum(docTypes::OrderDetails->value, $this->CurrFYDB,Helper::getCurrentFy());
								$tdata=array(
									"DetailID"=>$DetailID,
									"OrderID"=>$OrderID,
									"QID"=>$QID,
									"QDID"=>$item->DetailID,
									"ProductID"=>$item->ProductID,
									"Qty"=>$item->Qty,
									"Price"=>$item->Price,
									"TaxType"=>$item->TaxType,
									"TaxPer"=>$item->TaxPer,
									"Taxable"=>$item->Taxable,
									"DiscountType"=>$item->DiscountType,
									"DiscountPer"=>$item->DiscountPer,
									"DiscountAmt"=>$item->DiscountAmt,
									"TaxAmt"=>$item->TaxAmt,
									"CGSTPer"=>$item->CGSTPer,
									"SGSTPer"=>$item->SGSTPer,
									"IGSTPer"=>$item->IGSTPer,
									"CGSTAmt"=>$item->CGSTAmt,
									"SGSTAmt"=>$item->SGSTAmt,
									"IGSTAmt"=>$item->IGSTAmt,
									"TotalAmt"=>$item->TotalAmt,
									"VendorID"=>$item->VendorID,
									"CreatedOn"=>now(),
									"CreatedBy"=>$this->UserID
								);
								$status=DB::table($this->CurrFYDB.'tbl_order_details')->insert($tdata);
								if($status){
									DocNum::updateDocNum(docTypes::OrderDetails->value, $this->CurrFYDB);
								}
							}
						}
					}
					//save orders to vendors;
					if($status){
						$status=$this->SaveVendorOrders($data,$OrderID,$QID,$req->ExpectedDelivery);
					}
					
					/*
					$sql="SELECT OrderID,QID,VendorID,Sum(Taxable) as SubTotal,Sum(TaxAmt) as TaxAmount, Sum(CGSTAmt) as CGSTAmount, Sum(SGSTAmt) as SGSTAmount, Sum(IGSTAmt) as IGSTAmount, Sum(TotalAmt) as TotalAmount  FROM ".$this->CurrFYDB."tbl_order_details Where OrderID='".$OrderID."' Group By OrderID,QID,VendorID";
					$result=DB::SELECT($sql);
					foreach($result as $item){
						if($status){
							$sql="SELECT AdditionalCost FROM ".$this->CurrFYDB."tbl_vendor_quotation Where VendorID='".$item->VendorID."' and EnqID in(Select EnqID From ".$this->CurrFYDB."tbl_quotation Where QID='".$item->QID."')";
							$tmp=DB::SELECT($sql);
							$additionalCharges=0;
							foreach($tmp as $t){
								$additionalCharges+=floatval($t->AdditionalCost);
							}
							$VOrderID=DocNum::getDocNum(docTypes::VendorOrders->value, $this->CurrFYDB,Helper::getCurrentFy());
							$VOrderNo=DocNum::getInvNo(docTypes::VendorOrders->value);
							$tdata=[
								"VOrderID"=>$VOrderID,
								"OrderID"=>$OrderID,
								"OrderNo"=>$VOrderNo,
								"OrderDate"=>date("Y-m-d"),
								"ExpectedDelivery"=>date("Y-m-d",strtotime($req->ExpectedDelivery)),
								"QID"=>$QID,
								"CustomerID"=>$data->CustomerID,
								"AID"=>$data->AID,
								"VendorID"=>$item->VendorID,
								"ReceiverName"=>$data->ReceiverName,
								"ReceiverMobNo"=>$data->ReceiverMobNo,
								"DAddress"=>$data->DAddress,
								"DCountryID"=>$data->DCountryID,
								"DStateID"=>$data->DStateID,
								"DDistrictID"=>$data->DDistrictID,
								"DTalukID"=>$data->DTalukID,
								"DCityID"=>$data->DCityID,
								"DPostalCodeID"=>$data->DPostalCodeID,
								"Status"=>"New",
								"TaxAmount"=>$item->TaxAmount,
								"SubTotal"=>$item->SubTotal,
								"DiscountType"=>"",
								"DiscountPercentage"=>0,
								"DiscountAmount"=>0,
								"CGSTAmount"=>$item->CGSTAmount,
								"SGSTAmount"=>$item->SGSTAmount,
								"IGSTAmount"=>$item->IGSTAmount,
								"TotalAmount"=>$item->TotalAmount,
								"AdditionalCost"=>$additionalCharges,
								"NetAmount"=>($item->TotalAmount+$additionalCharges),
								"PaidAmount"=>0,
								"BalanceAmount"=>($item->TotalAmount+$additionalCharges),
								"PaymentStatus"=>"Unpaid",
								"AdditionalCostData"=> serialize([]),
								"CreatedOn"=>now(),
								"CreatedBy"=>$this->UserID
							];
							$status=DB::table($this->CurrFYDB.'tbl_vendor_orders')->insert($tdata);
							if($status){
								DocNum::updateDocNum(docTypes::VendorOrders->value, $this->CurrFYDB);
								DocNum::updateInvNo(docTypes::VendorOrders->value);
								$Title = "New Order Arrived. Order No " . $VOrderNo . ".";
								$Message = "You have a new order! Check now for details and fulfill it promptly.";
								Helper::saveNotification($item->VendorID,$Title,$Message,'Orders',$VOrderID);
								$status=DB::table($this->CurrFYDB.'tbl_order_details')->where('VendorID',$item->VendorID)->where('QID',$item->QID)->update(["VOrderID"=>$VOrderID,"UpdatedOn"=>now(),"updatedBy"=>$this->UserID]);
							}
						}

					}*/
					
					if($status){
						$status=DB::Table($this->CurrFYDB."tbl_quotation")->where('QID',$QID)->update(["Status"=>"Accepted","UpdatedOn"=>now(),"UpdatedBy"=>$this->UserID]);
					}
				}else{
					$status=false;
				}
			} catch (Exception $e) {
				$status=false;
				DB::rollback();
				return response(array('status'=>false,'message'=>$e->getMessage()), 500);
			}
			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"The quote has been successfully moved to orders.");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"The attempt to move the quote to orders has failed.");
			}
		}
	}
	public function updateVendorAdditionalCost(Request $req,$QID){

		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			$status=true;
			$details=json_decode($req->details);
			foreach($details as $VendorID=>$Cost){
				if($status){
					$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation")->where('EnqID',$req->EnqID)->where('VendorID',$VendorID)->update(["AdditionalCost"=>$Cost,"UpdatedOn"=>now(),"UpdatedBy"=>$this->UserID]);
				}
			}
			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"Vendor additional cost updated successfully.");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Failed to update vendor additional cost.");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function updateCustomerAdditionalCost(Request $req,$QID){

		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			$sql="Update ".$this->CurrFYDB."tbl_quotation Set AdditionalCost='".$req->AdditionalCharges."',OverAllAmount=(TotalAmount+'".$req->AdditionalCharges."'),UpdatedOn='".date("Y-m-d H:i:s")."',UpdatedBy='".$this->UserID."'  where QID='".$QID."'";
			$status=DB::Update($sql);

			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"Customer additional cost updated successfully.");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Failed to update customer additional cost.");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function getCancelReasons(Request $req){
		$sql="Select * From tbl_reject_reason Where ActiveStatus='Active' and DFlag=0 and (RReasonFor='All' OR RReasonFor='Admin')";
		return DB::Select($sql);
	}
	public function getQuotes($data=array()){
		$sql ="SELECT Q.QID, Q.EnqID, Q.QNo, Q.QDate, Q.QExpiryDate, Q.CustomerID, Q.AID, C.CustomerName, C.MobileNo1, C.MobileNo2, C.Email, C.Address as BAddress, C.CountryID as BCountryID, BC.CountryName as BCountryName, ";
		$sql.=" C.StateID as BStateID, BS.StateName as BStateName, C.DistrictID as BDistrictID, BD.DistrictName as BDistrictName, C.TalukID, BT.TalukName as BTalukName, C.CityID as BCityID, BCI.CityName as BCityName, C.PostalCodeID as BPostalCodeID, ";
		$sql.=" BPC.PostalCode as BPostalCode, BC.PhoneCode, Q.ReceiverName, Q.ReceiverMobNo, Q.DAddress, Q.DCountryID, CO.CountryName as DCountryName, Q.DStateID, S.StateName as DStateName, Q.DDistrictID, D.DistrictName as DDistrictName, Q.DTalukID, ";
		$sql.=" T.TalukName as DTalukName, Q.DCityID, CI.CityName as DCityName, Q.DPostalCodeID, PC.PostalCode as DPostalCode, Q.TaxAmount, Q.SubTotal, Q.DiscountType, Q.DiscountPercent as DiscountPercentage, Q.DiscountAmount, Q.CGSTAmount, ";
		$sql.=" Q.SGSTAmount, Q.IGSTAmount, Q.TotalAmount, Q.AdditionalCost, Q.OverAllAmount as NetAmount, Q.AdditionalCostData, Q.Status, Q.AcceptedOn, Q.RejectedOn, Q.ApprovedBy, Q.RejectedBy, Q.RReasonID, RR.RReason, Q.RRDescription ";
		$sql.=" FROM ".$this->CurrFYDB."tbl_quotation as Q LEFT JOIN tbl_customer as C ON C.CustomerID=Q.CustomerID LEFT JOIN ".$this->generalDB."tbl_countries as BC ON BC.CountryID=C.CountryID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_states as BS ON BS.StateID=C.StateID LEFT JOIN ".$this->generalDB."tbl_districts as BD ON BD.DistrictID=C.DistrictID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_taluks as BT ON BT.TalukID=C.TalukID LEFT JOIN ".$this->generalDB."tbl_cities as BCI ON BCI.CityID=C.CityID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_postalcodes as BPC ON BPC.PID=C.PostalCodeID LEFT JOIN ".$this->generalDB."tbl_countries as CO ON CO.CountryID=Q.DCountryID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_states as S ON S.StateID=Q.DStateID LEFT JOIN ".$this->generalDB."tbl_districts as D ON D.DistrictID=Q.DDistrictID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_taluks as T ON T.TalukID=Q.DTalukID LEFT JOIN ".$this->generalDB."tbl_cities as CI ON CI.CityID=Q.DCityID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_postalcodes as PC ON PC.PID=Q.DPostalCodeID LEFT JOIN tbl_reject_reason as RR ON RR.RReasonID=Q.RReasonID ";
		$sql.=" Where 1=1 ";
		if(is_array($data)){
			if(array_key_exists("QID",$data)){$sql.=" AND Q.QID='".$data['QID']."'";}
		}
		$result=DB::SELECT($sql);
		for($i=0;$i<count($result);$i++){
			$result[$i]->AdditionalCostData=unserialize($result[$i]->AdditionalCostData);
			$sql="SELECT QD.DetailID, QD.QID, QD.VQDetailID, QD.ProductID, P.ProductName, P.ProductImage, P.Decimals, P.HSNSAC, P.UID, U.UCode, U.UName, QD.Qty, QD.Price, QD.TaxType, QD.TaxPer, QD.Taxable, QD.DiscountType, QD.DiscountPer, QD.DiscountAmt, QD.TaxAmt, QD.CGSTPer, QD.SGSTPer, QD.IGSTPer, QD.CGSTAmt, QD.SGSTAmt, QD.IGSTAmt, QD.TotalAmt, QD.VendorID, V.VendorName, QD.isCancelled, QD.CancelledBy, QD.CancelledOn, QD.ReasonID, RR.RReason, QD.RDescription  ";
			$sql.=" FROM ".$this->CurrFYDB."tbl_quotation_details as QD LEFT JOIN tbl_products as P ON P.ProductID=QD.ProductID LEFT JOIN tbl_uom as U ON U.UID=P.UID LEFT JOIN tbl_reject_reason as RR ON RR.RReasonID=QD.ReasonID LEFT JOIN tbl_vendors as V ON V.VendorID=QD.VendorID ";
			$sql.=" Where QD.QID='".$result[$i]->QID."' and QD.isCancelled=0 ";
			$result[$i]->Details=DB::SELECT($sql);
			for($j=0;$j<count($result[$i]->Details);$j++){
				
				$result[$i]->Details[$j]->ProductImage=(file_exists($result[$i]->Details[$j]->ProductImage))?url('/')."/".$result[$i]->Details[$j]->ProductImage:url('/')."/assets/images/no-image-b.png";

				$result[$i]->Details[$j]->VQuoteID="";
				$result1=DB::Table($this->CurrFYDB.'tbl_vendor_quotation')->Where('EnqID',$result[$i]->EnqID)->where('VendorID',$result[$i]->Details[$j]->VendorID)->get();
				if(count($result1)>0){
					$result[$i]->Details[$j]->VQuoteID=$result1[0]->VQuoteID;
				}
			}
			$addCharges=[];
			$result1=DB::Table($this->CurrFYDB.'tbl_vendor_quotation')->Where('EnqID',$result[$i]->EnqID)->get();
			foreach($result1 as $tmp){
				$addCharges[$tmp->VendorID]=Helper::NumberFormat($tmp->AdditionalCost,$this->Settings['price-decimals']);
			}
			$result[$i]->AdditionalCharges=$addCharges;
		}
		return $result;
	}
	public function TableView(Request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){

			$columns = array(
				array( 'db' => 'Q.QNo', 'dt' => '0' ),
				array( 'db' => 'Q.QDate', 'dt' => '1' ),
				array( 'db' => 'C.CustomerName', 'dt' => '2' ),
				array( 'db' => 'C.MobileNo1', 'dt' => '3' ),
				array( 'db' => 'C.Email', 'dt' => '4' ),
				array( 'db' => 'Q.QExpiryDate', 'dt' => '5' ),
				array( 'db' => 'Q.Status', 'dt' => '6' ),
				array( 'db' => 'Q.QID', 'dt' => '7' ),
				array( 'db' => 'CO.PhoneCode', 'dt' => '8' ),
			);
			$columns1 = array(
				array( 'db' => 'QNo', 'dt' => '0' ),
				array( 'db' => 'QDate', 'dt' => '1','formatter' => function( $d, $row ) { return date($this->Settings['date-format'],strtotime($d));} ),
				array( 'db' => 'CustomerName', 'dt' => '2' ),
				array( 
					'db' => 'MobileNo1', 
					'dt' => '3' ,
					'formatter' => function( $d, $row ) { 
						$phoneCode=$row['PhoneCode']!=""?"+".$row['PhoneCode']:"";
						return $phoneCode." ".$d;
					}
				),
				array( 'db' => 'Email', 'dt' => '4' ),
				array( 'db' => 'QExpiryDate', 'dt' => '5' ,'formatter' => function( $d, $row ) {return date($this->Settings['date-format'],strtotime($d));}),
				array( 'db' => 'Status','dt' => '6',
					'formatter' => function( $d, $row ) {
						$html = "";
						if($d=="New"){
							$html="<span class='badge badge-info m-1'>".$d."</span>";
						}elseif($d=="Rejected"){
							$html="<span class='badge badge-danger m-1'>".$d."</span>";
						}elseif($d=="Accepted"){
							$html="<span class='badge badge-success m-1'>".$d."</span>";
						}
						return $html;
					}
				),
				array(
						'db' => 'QID',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$OrderCRUD=$this->general->getCrudOperations(activeMenuNames::Order->value);
							$html='';
							if($this->general->isCrudAllow($this->CRUD,"view")==true){
								$html.='<a href="'.route('admin.transaction.quotes.details',$d).'" data-id="'.$d.'"  class="btn btn-outline-info  m-5 '.$this->general->UserInfo['Theme']['button-size'].'  btnView">View</a>';
								// $html.='<a href="'.route('admin.transaction.quotes.pdf',$d).'" data-id="'.$d.'"  class="btn btn-outline-info  m-5 '.$this->general->UserInfo['Theme']['button-size'].'  btnView">View</a>';
							}
							/*
							if($this->general->isCrudAllow($OrderCRUD,"add")==true && $row['Status']=="New" ){
								$html.='<button type="button" data-id="'.$d.'"  class="btn btn-outline-success btnConfirm  m-5 '.$this->general->UserInfo['Theme']['button-size'].'" title="Confirm Order">Confirm</button>';
							}*/
							if($this->general->isCrudAllow($this->CRUD,"delete")==true && $row['Status']=="New" ){
								$html.='<button type="button" data-id="'.$d.'"  data-qno="'.$row['QNo'].'" class="btn btn-outline-danger btnCancelQuote  m-5 '.$this->general->UserInfo['Theme']['button-size'].'" title="Cancel this Quote">Cancel</button>';
							}
							return $html;
						}
					),
				array( 'db' => 'PhoneCode', 'dt' => '8' ),
			);
			$Where=" 1=1 ";
			if($request->status){
				$status=json_decode($request->status,true);
				if(count($status)>0){
					$Where.=" and Status in('".implode("','",$status)."')";
				}
			}
			if($request->customers){
				$customers=json_decode($request->customers,true);
				if(count($customers)>0){
					$Where.=" and Q.CustomerID in('".implode("','",$customers)."')";
				}
			}
			if($request->quoteDates){
				$quoteDates=json_decode($request->quoteDates,true);
				if(count($quoteDates)>0){
					$Where.=" and QDate in('".implode("','",$quoteDates)."')";
				}
			}
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$this->CurrFYDB . 'tbl_quotation as Q  LEFT JOIN tbl_customer as C ON C.CustomerID = Q.CustomerID LEFT JOIN '.$this->generalDB.'tbl_countries as CO On CO.CountryID=C.CountryID';
			$data['PRIMARYKEY']='Q.QID';
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
		$sql="Select DISTINCT(Status) as Status From ".$this->CurrFYDB."tbl_quotation Where 1=1 ";
		return DB::SELECT($sql);
	}
	public function getSearchCustomers(Request $req){
		$sql="Select DISTINCT(Q.CustomerID) as CustomerID,C.CustomerName From ".$this->CurrFYDB."tbl_quotation as Q LEFT JOIN tbl_customer as C ON C.CustomerID=Q.CustomerID Where 1=1 ";
		if($req->status){
			$status=json_decode($req->status,true);
			if(count($status)>0){
				$sql.=" and Q.Status in('".implode("','",$status)."')";
			}
		}
		return DB::SELECT($sql);
	}
	public function getSearchQuoteDates(Request $req){
		$sql="Select DISTINCT(Q.QDate) as QuoteDate From ".$this->CurrFYDB."tbl_quotation as Q LEFT JOIN tbl_customer as C ON C.CustomerID=Q.CustomerID Where 1=1 ";
		if($req->status){
			$status=json_decode($req->status,true);
			if(count($status)>0){
				$sql.=" and Q.Status in('".implode("','",$status)."')";
			}
		}
		if($req->customers){
			$customers=json_decode($req->customers,true);
			if(count($customers)>0){
				$sql.=" and Q.CustomerID in('".implode("','",$customers)."')";
			}
		}
		return DB::SELECT($sql);
	}
}
