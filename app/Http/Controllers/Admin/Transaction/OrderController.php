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

class OrderController extends Controller{
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
		$this->ActiveMenuName=activeMenuNames::Order->value;
		$this->PageTitle="Order";
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
            return view('app.transaction.orders.view',$FormData);
        }else{
            return view('errors.403');
        }
    }
	public function OrderView(Request $req,$OrderID){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=false;
			$FormData['Settings']=$this->Settings;
			$FormData['OrderID']=$OrderID;
			$FormData['OData']=$this->getOrder(["OrderID"=>$OrderID]);
			if(count($FormData['OData'])>0){
				$FormData['OData']=$FormData['OData'][0];
				return view('app.transaction.orders.details', $FormData);
			}else{
				return view('errors.403');
			}
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
            return Redirect::to('admin/transaction/order/');
        }else{
            return view('errors.403');
        }
	}
	public function adminRatingSave(Request $req){
		
		DB::beginTransaction();
		$status=false;
		$tdata=array(
			"isRated"=>1,
			"Ratings"=>floatval($req->Rating),
			"Review"=>$req->Review,
			"RatedOn"=>now(),
			"RatedBy"=>$this->UserID
		);
		//Update Quotation table amount totals
		$status=DB::Table($this->CurrFYDB."tbl_vendor_orders")->where('OrderID',$req->OrderID)->where('VendorID',$req->VendorID)->update($tdata);

		if($status==true){
			DB::commit();
			return array('status'=>true,'message'=>"Rating saved successfully");
		}else{
			DB::rollback();
			return array('status'=>false,'message'=>"Rating save failed");
		}
	}
	
	public function sendOTP(Request $req){
		$OTP=Helper::getOTP(6);
		if($req->detailID!="" && $req->OrderID!=""){
			$result=DB::Table($this->CurrFYDB."tbl_order_details as OD")->leftJoin($this->CurrFYDB."tbl_order as O","O.OrderID","OD.OrderID")->leftJoin("tbl_products as P","OD.ProductID","P.ProductID")->where('OD.OrderID',$req->OrderID)->where('OD.DetailID',$req->detailID)->Where('OD.Status','New')->first();
			if($result){
				$status = DB::Table($this->CurrFYDB."tbl_order_details")->where('OrderID',$req->OrderID)->where('DetailID',$req->detailID)->update(["otp"=>$OTP,"UpdatedOn"=>now(),"UpdatedBy"=>$this->UserID]);
				if($status){
					$Title = "OTP for Order Delivery for Order No " . $result->OrderNo;
					$Message = "Your OTP for order delivery is ".$OTP.". Please use this code to confirm your delivery. Delivered Products: ".$result->ProductName.".";
					Helper::saveNotification($result->CustomerID,$Title,$Message,'Order',$result->VOrderID);
				}
			}
		}elseif($req->OrderID!=""){
			$result=DB::Table($this->CurrFYDB."tbl_order_details as OD")->leftJoin($this->CurrFYDB."tbl_order as O","O.OrderID","OD.OrderID")->leftJoin("tbl_products as P","OD.ProductID","P.ProductID")->where('OD.OrderID',$req->OrderID)->Where('OD.Status','New')->get();
			if(count($result)>0){
				$status = DB::Table($this->CurrFYDB."tbl_order")->where('OrderID',$req->OrderID)->update(["otp"=>$OTP,"UpdatedOn"=>now(),"UpdatedBy"=>$this->UserID]);
				if($status){
					$Title = "OTP for Order Delivery for Order No " . $result[0]->OrderNo;
					$Message = "Your OTP for order delivery is ".$OTP.". Please use this code to confirm your delivery. Delivered Products: ";
					foreach($result as $index => $item) {
						$Message .= $item->ProductName;
						if ($index < count($result) - 1) {
							$Message .= ", ";
						}
					}
					$Message .= ".";
					Helper::saveNotification($result[0]->CustomerID,$Title,$Message,'Order',$result[0]->OrderID);
				}
			}
		}
		return ["status"=>true];
	}

	public function ItemMarkAsDelivered(request $req){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			$return = json_decode(json_encode(["status"=>"","message"=>""]));
			$return->status = false;
			$return->message = "The attempt to mark the item as delivered has failed.";

			$result=DB::Table($this->CurrFYDB."tbl_order_details")->where('OrderID',$req->OrderID)->where('DetailID',$req->detailID)->get();
			if(count($result)>0){
				if($result[0]->OTP==$req->OTP){
					$status=DB::Table($this->CurrFYDB."tbl_order_details")->where('OrderID',$req->OrderID)->where('DetailID',$req->detailID)->update(["Status"=>'Delivered',"DeliveredOn"=>now(),"DeliveredBy"=>$this->UserID]);
					if($status){
						$status = DB::table($this->CurrFYDB.'tbl_order_details')->selectRaw(" CASE WHEN COUNT(*) = SUM(status = 'Delivered') THEN 'Delivered' WHEN SUM(status = 'Delivered' OR status = 'New' ) > 0 THEN 'Partially Delivered' ELSE 'New' END AS status")->where('OrderID', $req->OrderID)->where('status', '<>', 'Cancelled')->first();
						$status = $status->status ?? 'New';
						$sql = "UPDATE ".$this->CurrFYDB."tbl_order SET UpdatedOn='".date("Y-m-d H:i:s")."',UpdatedBy='".$this->UserID."',status ='".$status."' WHERE OrderID = '".$req->OrderID."'";
						$status=DB::statement($sql);
					}
					if($status){
						$return->status=true;
						$return->message="Marked as delivered successfully";
					}
				}else{
					$return->message = "The OTP verification has failed. Please enter the correct OTP.";
				}
			}
			$return->status?DB::commit():DB::rollback();
			return $return;

		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	
	public function MarkAsDelivered(request $req){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			$return = json_decode(json_encode(["status"=>"","message"=>""]));
			$return->status = false;
			$return->message = "Failed to mark the order as delivered.";

			$result=DB::Table($this->CurrFYDB."tbl_order")->where('OrderID',$req->OrderID)->get();
			if(count($result)>0){
				if($result[0]->OTP==$req->OTP){
					$status=DB::Table($this->CurrFYDB."tbl_order_details")->where('OrderID',$req->OrderID)->update(["Status"=>'Delivered',"DeliveredOn"=>now(),"DeliveredBy"=>$this->UserID]);
					if($status){
						$status = DB::table($this->CurrFYDB.'tbl_order_details')->selectRaw(" CASE WHEN COUNT(*) = SUM(status = 'Delivered') THEN 'Delivered' WHEN SUM(status = 'Delivered' OR status = 'New' ) > 0 THEN 'Partially Delivered' ELSE 'New' END AS status")->where('OrderID', $req->OrderID)->where('status', '<>', 'Cancelled')->first();
						$status = $status->status ?? 'New';
						$sql = "UPDATE ".$this->CurrFYDB."tbl_order SET UpdatedOn='".date("Y-m-d H:i:s")."',UpdatedBy='".$this->UserID."',status ='".$status."' WHERE OrderID = '".$req->OrderID."'";
						$status=DB::statement($sql);
					}
					if($status){
						$return->status=true;
						$return->message="Marked as delivered successfully";
					}
				}else{
					$return->message = "The OTP verification has failed. Please enter the correct OTP.";
				}
			}
			$return->status?DB::commit():DB::rollback();
			return $return;

		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function updateVendorAdditionalCost(Request $req,$OrderID){
		
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
	public function updateCustomerAdditionalCost(Request $req,$OrderID){
		
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			$sql="Update ".$this->CurrFYDB."tbl_order Set AdditionalCost='".$req->AdditionalCharges."',NetAmount=(TotalAmount+'".$req->AdditionalCharges."'),UpdatedOn='".date("Y-m-d H:i:s")."',UpdatedBy='".$this->UserID."'  where OrderID='".$OrderID."'";
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
	public function OrderItemCancel(request $req,$DetailID){
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$status=false;
			$tdata=array(
				"status"=>"Cancelled",
				"ReasonID"=>$req->ReasonID,
				"RDescription"=>$req->Description,
				"RejectedOn"=>now(),
				"RejectedBy"=>$this->UserID
			);
			//item cancel
			$status=DB::Table($this->CurrFYDB."tbl_order_details")->where('OrderID',$req->OrderID)->where('DetailID',$DetailID)->update($tdata);
			
			//Update Vendor's Additional Cost in Quotation Table
			if($status){
				$status=DB::Table($this->CurrFYDB."tbl_vendor_quotation")->where('EnqID',$req->EnqID)->where('VendorID',$req->VendorID)->update(["AdditionalCost"=>$req->VACharges,"UpdatedOn"=>now(),"UpdatedBy"=>$this->UserID]);
			}
			//Update Customer's Additional Cost in Quotation Table
			if($status){
				$sql="Update ".$this->CurrFYDB."tbl_order set   AdditionalCost='".$req->CACharges."',NetAmount=(TotalAmount+'".floatval($req->CACharges)."'), UpdatedOn='".date("Y-m-d H:i:s")."',UpdatedBy='".$this->UserID."' Where OrderID='".$req->OrderID."'";
				$status=DB::Update($sql);
			}
			// Verify if all items have been cancelled. If all items are cancelled, update the status in the main quotation table.
			if($status){ 
				$t=DB::Table($this->CurrFYDB."tbl_order_details")->where('OrderID',$req->OrderID)->where('status','<>','Cancelled')->count();
				if(intval($t)<=0){
					$tdata=array(
						"Status"=>"Cancelled",
						"ReasonID"=>$req->ReasonID,
						"RDescription"=>$req->Description,
						"RejectedOn"=>now(),
						"RejectedBy"=>$this->UserID
					);
					$status=DB::Table($this->CurrFYDB."tbl_order")->where('OrderID',$req->OrderID)->update($tdata);
				}
			}
			
			// Update Tax Amount, Total Amount, Subtotal, and Net Amount for non-cancelled items in the quotation table.
			if($status){
				$tdata=["TaxAmount"=>0,"CGSTAmount"=>0,"IGSTAmount"=>0,"SGSTAmount"=>0,"TotalAmount"=>0,"SubTotal"=>0,"DiscountAmount"=>0,"AdditionalCost"=>0,"NetAmount"=>0,"TotalPaidAmount"=>0,"BalanceAmount"=>0];
				$sql="SELECT IFNULL(SUM(TaxAmt),0) as TaxAmount, IFNULL(SUM(CGSTAmt),0) as CGSTAmount, IFNULL(SUM(IGSTAmt),0) as IGSTAmount, IFNULL(SUM(SGSTAmt),0) as SGSTAmount, SUM(TotalAmt) as TotalAmount, IFNULL(SUM(Taxable),0) as SubTotal FROM ".$this->CurrFYDB."tbl_order_details where OrderID='".$req->OrderID."' and Status<>'Cancelled'";
				$result=DB::SELECT($sql);
				/*
				$tdata=["TaxAmount"=>0,"CGSTAmount"=>0,"IGSTAmount"=>0,"SGSTAmount"=>0,"TotalAmount"=>0,"SubTotal"=>0,"DiscountAmount"=>0,"AdditionalCost"=>0,"NetAmount"=>0];
				$sql="SELECT IFNULL(SUM(TaxAmt),0) as TaxAmount, IFNULL(SUM(CGSTAmt),0) as CGSTAmount, IFNULL(SUM(IGSTAmt),0) as IGSTAmount, IFNULL(SUM(SGSTAmt),0) as SGSTAmount, SUM(TotalAmt) as TotalAmount, IFNULL(SUM(Taxable),0) as SubTotal FROM ".$this->CurrFYDB."tbl_order_details where OrderID='".$req->OrderID."' and Status<>'Cancelled'";
				
				$result=DB::SELECT($sql);*/
				foreach($result as $tmp){
					$tdata['TaxAmount']+=floatval($tmp->TaxAmount);
					$tdata['CGSTAmount']+=floatval($tmp->CGSTAmount);
					$tdata['IGSTAmount']+=floatval($tmp->IGSTAmount);
					$tdata['SGSTAmount']+=floatval($tmp->SGSTAmount);
					$tdata['TotalAmount']+=floatval($tmp->TotalAmount);
					$tdata['SubTotal']+=floatval($tmp->SubTotal);
				}
				$result=DB::Table($this->CurrFYDB."tbl_order")->where('OrderID',$req->OrderID)->get();
				foreach($result as $tmp){
					$tdata['DiscountAmount']+=floatval($tmp->DiscountAmount);
					$tdata['AdditionalCost']+=floatval($tmp->AdditionalCost);
					$tdata['TotalPaidAmount']+=floatval($tmp->TotalPaidAmount);
				}
				$tdata['TotalAmount']=floatval($tdata['SubTotal'])+floatval($tdata['CGSTAmount'])+floatval($tdata['IGSTAmount'])+floatval($tdata['SGSTAmount']);
				$tdata['TotalAmount']-=floatval($tdata['DiscountAmount']);

				$tdata['NetAmount']=floatval($tdata['TotalAmount'])+floatval($tdata['AdditionalCost']);
				$tdata['BalanceAmount']=floatval($tdata['NetAmount'])-floatval($tdata['TotalPaidAmount']);
				
				$tdata['UpdatedOn']=date("Y-m-d",strtotime("1 minutes"));
				$tdata['UpdatedBy']=$this->UserID;
				$status=DB::Table($this->CurrFYDB."tbl_order")->where('OrderID',$req->OrderID)->update($tdata);
			}
			/************************************************************************************************** */
			//Vendor Order Item Cancel
			/************************************************************************************************** */
			$VOrderDetailID="";
			$VOrderID="";
			$t=DB::Table($this->CurrFYDB."tbl_order_details")->where('OrderID',$req->OrderID)->where('DetailID',$DetailID)->get();
			if(count($t)>0){
				$VOrderID=$t[0]->VOrderID;
				$VOrderDetailID=$t[0]->VOrderDetailID;
			}
			//item cancel
			if($status){
				$tdata=array(
					"status"=>"Cancelled",
					"ReasonID"=>$req->ReasonID,
					"RDescription"=>$req->Description,
					"RejectedOn"=>now(),
					"RejectedBy"=>$this->UserID
				);
				$status=DB::Table($this->CurrFYDB."tbl_vendor_order_details")->where('VOrderID',$VOrderID)->where('DetailID',$VOrderDetailID)->update($tdata);
			}
			
			// Verify if all items have been cancelled. If all items are cancelled, update the status in the main quotation table.
			if($status){ 
				$t=DB::Table($this->CurrFYDB."tbl_vendor_order_details")->where('VOrderID',$VOrderID)->where('status','<>','Cancelled')->count();
				if(intval($t)<=0){
					$tdata=array(
						"Status"=>"Cancelled",
						"ReasonID"=>$req->ReasonID,
						"RDescription"=>$req->Description,
						"RejectedOn"=>now(),
						"RejectedBy"=>$this->UserID
					);
					$status=DB::Table($this->CurrFYDB."tbl_vendor_orders")->where('VOrderID',$VOrderID)->update($tdata);
				}
			}			
			
			// Update Tax Amount, Total Amount, Subtotal, and Net Amount for non-cancelled items in the quotation table.
			if($status){
				$tdata=["TaxAmount"=>0,"CGSTAmount"=>0,"IGSTAmount"=>0,"SGSTAmount"=>0,"TotalAmount"=>0,"SubTotal"=>0,"DiscountAmount"=>0,"AdditionalCost"=>0,"CommissionPercentage"=>0,"CommissionAmount"=>0,"NetAmount"=>0,"TotalPaidAmount"=>0,"BalanceAmount"=>0];
				$sql="SELECT IFNULL(SUM(TaxAmt),0) as TaxAmount, IFNULL(SUM(CGSTAmt),0) as CGSTAmount, IFNULL(SUM(IGSTAmt),0) as IGSTAmount, IFNULL(SUM(SGSTAmt),0) as SGSTAmount, SUM(TotalAmt) as TotalAmount,  IFNULL(SUM(Taxable),0) as SubTotal FROM ".$this->CurrFYDB."tbl_vendor_order_details where VOrderID='".$VOrderID."' and Status<>'Cancelled'";
				$result=DB::SELECT($sql);
				foreach($result as $tmp){
					$tdata['TaxAmount']+=floatval($tmp->TaxAmount);
					$tdata['CGSTAmount']+=floatval($tmp->CGSTAmount);
					$tdata['IGSTAmount']+=floatval($tmp->IGSTAmount);
					$tdata['SGSTAmount']+=floatval($tmp->SGSTAmount);
					$tdata['TotalAmount']+=floatval($tmp->TotalAmount);
					$tdata['SubTotal']+=floatval($tmp->SubTotal);
				}
				$result=DB::Table($this->CurrFYDB."tbl_vendor_orders")->where('VOrderID',$VOrderID)->get();
				foreach($result as $tmp){
					$tdata['DiscountAmount']+=floatval($tmp->DiscountAmount);
					$tdata['AdditionalCost']+=floatval($tmp->AdditionalCost);
					$tdata['TotalPaidAmount']+=floatval($tmp->TotalPaidAmount);
					$tdata['CommissionPercentage']=floatval($tmp->CommissionPercentage);
				}
				$tdata['TotalAmount']=floatval($tdata['SubTotal'])+floatval($tdata['CGSTAmount'])+floatval($tdata['IGSTAmount'])+floatval($tdata['SGSTAmount']);
				$tdata['TotalAmount']-=floatval($tdata['DiscountAmount']);

				$tdata['CommissionPercentage']=(($tdata['TotalAmount']*$tdata['CommissionPercentage'])/100);

				$tdata['NetAmount']=(floatval($tdata['TotalAmount'])-$tdata['CommissionPercentage'])+floatval($tdata['AdditionalCost']);
				$tdata['BalanceAmount']=floatval($tdata['NetAmount'])-floatval($tdata['TotalPaidAmount']);
				$tdata['UpdatedOn']=date("Y-m-d",strtotime("1 minutes"));
				$tdata['UpdatedBy']=$this->UserID;
				$status=DB::Table($this->CurrFYDB."tbl_vendor_orders")->where('VOrderID',$VOrderID)->update($tdata);
			}
			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"Order item Successfully Canceled");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Failed to Cancel Order Item");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function getOrder($data=array()){
		$sql ="SELECT O.OrderID, O.OrderNo, O.OrderDate, O.QID, O.EnqID, O.ExpectedDelivery, O.CustomerID, C.CustomerName, C.MobileNo1, C.MobileNo2, C.Email, C.Address as BAddress, C.CountryID as BCountryID, BC.CountryName as BCountryName, ";
		$sql.=" C.StateID as BStateID, BS.StateName as BStateName, C.DistrictID as BDistrictID, BD.DistrictName as BDistrictName, C.TalukID, BT.TalukName as BTalukName, C.CityID as BCityID, BCI.CityName as BCityName, C.PostalCodeID as BPostalCodeID, ";
		$sql.=" BPC.PostalCode as BPostalCode, BC.PhoneCode, O.ReceiverName, O.ReceiverMobNo, O.DAddress, O.DCountryID, CO.CountryName as DCountryName, O.DStateID, S.StateName as DStateName, O.DDistrictID, D.DistrictName as DDistrictName, O.DTalukID, ";
		$sql.=" T.TalukName as DTalukName, O.DCityID, CI.CityName as DCityName, O.DPostalCodeID, PC.PostalCode as DPostalCode, O.TaxAmount, O.SubTotal, O.DiscountType, O.DiscountPercentage, O.DiscountAmount, O.CGSTAmount, ";
		$sql.=" O.SGSTAmount, O.IGSTAmount, O.TotalAmount, O.AdditionalCost, O.NetAmount, O.PaidAmount,O.LessFromAdvance,O.TotalPaidAmount, O.BalanceAmount, O.PaymentStatus,  O.AdditionalCostData, O.Status,  O.RejectedOn,  O.RejectedBy, O.ReasonID, RR.RReason, O.RDescription, ";
		$sql.=" O.isRated, O.Ratings, O.Review, O.RatedOn ";
		$sql.=" FROM ".$this->CurrFYDB."tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID LEFT JOIN ".$this->generalDB."tbl_countries as BC ON BC.CountryID=C.CountryID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_states as BS ON BS.StateID=C.StateID LEFT JOIN ".$this->generalDB."tbl_districts as BD ON BD.DistrictID=C.DistrictID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_taluks as BT ON BT.TalukID=C.TalukID LEFT JOIN ".$this->generalDB."tbl_cities as BCI ON BCI.CityID=C.CityID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_postalcodes as BPC ON BPC.PID=C.PostalCodeID LEFT JOIN ".$this->generalDB."tbl_countries as CO ON CO.CountryID=O.DCountryID  ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_states as S ON S.StateID=O.DStateID LEFT JOIN ".$this->generalDB."tbl_districts as D ON D.DistrictID=O.DDistrictID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_taluks as T ON T.TalukID=O.DTalukID LEFT JOIN ".$this->generalDB."tbl_cities as CI ON CI.CityID=O.DCityID ";
		$sql.=" LEFT JOIN ".$this->generalDB."tbl_postalcodes as PC ON PC.PID=O.DPostalCodeID LEFT JOIN tbl_reject_reason as RR ON RR.RReasonID=O.ReasonID "; 
		$sql.=" Where 1=1 ";
		if(is_array($data)){
			if(array_key_exists("OrderID",$data)){$sql.=" AND O.OrderID='".$data['OrderID']."'";}
		}
		$result=DB::SELECT($sql);
		for($i=0;$i<count($result);$i++){
			$result[$i]->AdditionalCostData=unserialize($result[$i]->AdditionalCostData);
			$sql="SELECT OD.DetailID, OD.OrderID, OD.QID, OD.QDID, OD.VOrderID, OD.VOrderDetailID, OD.ProductID, P.ProductName, P.HSNSAC, P.UID, U.UCode, U.UName, OD.Qty, OD.Price, OD.TaxType, OD.TaxPer, OD.Taxable, OD.DiscountType, OD.DiscountPer, OD.DiscountAmt, OD.TaxAmt, OD.CGSTPer, OD.SGSTPer, OD.IGSTPer, OD.CGSTAmt, OD.SGSTAmt, OD.IGSTAmt, OD.TotalAmt, OD.VendorID, V.VendorName, OD.Status, OD.RejectedBy, OD.RejectedOn, OD.ReasonID, RR.RReason, OD.RDescription, OD.DeliveredOn, OD.DeliveredBy  ";
			$sql.=" FROM ".$this->CurrFYDB."tbl_order_details as OD LEFT JOIN tbl_products as P ON P.ProductID=OD.ProductID LEFT JOIN tbl_uom as U ON U.UID=P.UID LEFT JOIN tbl_reject_reason as RR ON RR.RReasonID=OD.ReasonID LEFT JOIN tbl_vendors as V ON V.VendorID=OD.VendorID ";
			$sql.=" Where OD.OrderID='".$result[$i]->OrderID."' Order By OD.DetailID ";
			$result[$i]->Details=DB::SELECT($sql);
			$addCharges=[];
			$orderStatus=[];
			$result1=DB::Table($this->CurrFYDB.'tbl_vendor_quotation')->Where('EnqID',$result[$i]->EnqID)->get();
			foreach($result1 as $tmp){
				$addCharges[$tmp->VendorID]=Helper::NumberFormat($tmp->AdditionalCost,$this->Settings['price-decimals']);
			}
			$result1=DB::Table($this->CurrFYDB.'tbl_vendor_orders')->Where('OrderID',$result[$i]->OrderID)->get();
			foreach($result1 as $tmp){
				$orderStatus[$tmp->VendorID]=[
					"isRated"=>$tmp->isRated,
					"Ratings"=>$tmp->Ratings,
					"Review"=>$tmp->Review,
					"RatedOn"=>$tmp->RatedOn,
					"Status"=>$tmp->Status
				];
			}
			$result[$i]->AdditionalCharges=$addCharges;
			$result[$i]->orderStatus=$orderStatus;

		}
		return $result;
	}
	public function getCancelReasons(Request $req){
		$sql="Select * From tbl_reject_reason Where ActiveStatus='Active' and DFlag=0 and (RReasonFor='All' OR RReasonFor='Admin')";
		return DB::Select($sql);
	}
	public function OrderCancel(request $req,$OrderID){
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$tdata=array(
				"Status"=>"Cancelled",
				"ReasonID"=>$req->ReasonID,
				"RDescription"=>$req->Description,
				"RejectedOn"=>now(),
				"RejectedBy"=>$this->UserID
			);
			$status=DB::Table($this->CurrFYDB."tbl_order")->where('OrderID',$OrderID)->update($tdata);
			if($status){
				$status=DB::Table($this->CurrFYDB."tbl_vendor_orders")->where('OrderID',$OrderID)->update($tdata);
			}
			if($status==true){
				DB::commit();
				return array('status'=>true,'message'=>"The order has been successfully cancelled.");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"The cancellation of the order failed.");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}
	public function TableView(Request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
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
						if($this->general->isCrudAllow($this->CRUD,"view")==true){
							$html.='<a href="'.route('admin.transaction.orders.details',$d).'" data-id="'.$d.'"  class="btn btn-outline-info  m-5 '.$this->general->UserInfo['Theme']['button-size'].'  btnView"><i class="fa fa-eye"></i></a>';
						}
						if($this->general->isCrudAllow($this->CRUD,"edit")==true && ($row['Status']=="New" ||$row['Status']=="Partially Delivered") ){
							$html.='<button type="button" data-id="'.$d.'" data-mobile-no="'.$mobileNo.'"  data-order-no="'.$row['OrderNo'].'" class="btn btn-outline-success btnMarkAsDelivery  m-5 '.$this->general->UserInfo['Theme']['button-size'].'" title="Mark  as Delivered"><i class="fa fa-check"></i></button>';
						}
						if($this->general->isCrudAllow($this->CRUD,"delete")==true && $row['Status']=="New" ){
							$html.='<button type="button" data-id="'.$d.'"  data-order-no="'.$row['OrderNo'].'" class="btn btn-outline-danger btnCancelOrder  m-5 '.$this->general->UserInfo['Theme']['button-size'].'" title="Cancel this Order"><i class="fa fa-trash"></i></button>';
						}
						return $html;
					}
				),
				array( 'db' => 'PhoneCode', 'dt' => '11' ),
			);
			$Where=" 1=1 ";
			if($request->orderStatus){
				$orderStatus=json_decode($request->orderStatus,true);
				if(count($orderStatus)>0){
					$Where.=" and O.Status in('".implode("','",$orderStatus)."')";
				}
			}
			if($request->paymentStatus){
				$paymentStatus=json_decode($request->paymentStatus,true);
				if(count($paymentStatus)>0){
					$Where.=" and O.PaymentStatus in('".implode("','",$paymentStatus)."')";
				}
			}
			if($request->customers){
				$customers=json_decode($request->customers,true);
				if(count($customers)>0){
					$Where.=" and O.CustomerID in('".implode("','",$customers)."')";
				}
			}
			if($request->orderDates){
				$orderDates=json_decode($request->orderDates,true);
				if(count($orderDates)>0){
					$Where.=" and O.OrderDate in('".implode("','",$orderDates)."')";
				}
			}
			if($request->deliveryDates){
				$deliveryDates=json_decode($request->deliveryDates,true);
				if(count($deliveryDates)>0){
					$Where.=" and O.ExpectedDelivery in('".implode("','",$deliveryDates)."')";
				}
			}
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$this->CurrFYDB.'tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID = O.CustomerID LEFT JOIN '.$this->generalDB.'tbl_countries as CO On CO.CountryID=C.CountryID';
			$data['PRIMARYKEY']='O.OrderID';
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
	public function getSearchOrderStatus(Request $req){
		$sql="Select DISTINCT(O.Status) as Status From ".$this->CurrFYDB."tbl_order as O Where 1=1 ";
		return DB::SELECT($sql);
	}
	public function getSearchPaymentStatus(Request $req){
		$sql="Select DISTINCT(O.PaymentStatus) as Status From ".$this->CurrFYDB."tbl_order as O Where 1=1 ";
		if($req->orderStatus){
			$orderStatus=json_decode($req->orderStatus,true);
			if(count($orderStatus)>0){
				$sql.=" and O.Status in('".implode("','",$orderStatus)."')";
			}
		}
		return DB::SELECT($sql);
	}
	public function getSearchCustomers(Request $req){
		$sql="Select DISTINCT(O.CustomerID) as CustomerID,C.CustomerName From ".$this->CurrFYDB."tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID Where 1=1 ";
		if($req->orderStatus){
			$orderStatus=json_decode($req->orderStatus,true);
			if(count($orderStatus)>0){
				$sql.=" and O.Status in('".implode("','",$orderStatus)."')";
			}
		}
		if($req->paymentStatus){
			$paymentStatus=json_decode($req->paymentStatus,true);
			if(count($paymentStatus)>0){
				$sql.=" and O.PaymentStatus in('".implode("','",$paymentStatus)."')";
			}
		}
		return DB::SELECT($sql);
	}
	public function getSearchOrderDates(Request $req){
		$sql="Select DISTINCT(O.OrderDate) as OrderDate From ".$this->CurrFYDB."tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID Where 1=1 ";
		if($req->orderStatus){
			$orderStatus=json_decode($req->orderStatus,true);
			if(count($orderStatus)>0){
				$sql.=" and O.Status in('".implode("','",$orderStatus)."')";
			}
		}
		if($req->paymentStatus){
			$paymentStatus=json_decode($req->paymentStatus,true);
			if(count($paymentStatus)>0){
				$sql.=" and O.PaymentStatus in('".implode("','",$paymentStatus)."')";
			}
		}
		if($req->customers){
			$customers=json_decode($req->customers,true);
			if(count($customers)>0){
				$sql.=" and O.CustomerID in('".implode("','",$customers)."')";
			}
		}
		return DB::SELECT($sql);
	}
	public function getSearchDeliveryDates(Request $req){
		$sql="Select DISTINCT(O.ExpectedDelivery) as DeliveryDate From ".$this->CurrFYDB."tbl_order as O LEFT JOIN tbl_customer as C ON C.CustomerID=O.CustomerID Where 1=1 ";
		if($req->status){
			$status=json_decode($req->status,true);
			if(count($orderStatus)>0){
				$sql.=" and O.Status in('".implode("','",$status)."')";
			}
		}
		if($req->paymentStatus){
			$paymentStatus=json_decode($req->paymentStatus,true);
			if(count($paymentStatus)>0){
				$sql.=" and O.PaymentStatus in('".implode("','",$paymentStatus)."')";
			}
		}
		if($req->customers){
			$customers=json_decode($req->customers,true);
			if(count($customers)>0){
				$sql.=" and O.CustomerID in('".implode("','",$customers)."')";
			}
		}
		if($req->orderDates){
			$orderDates=json_decode($req->orderDates,true);
			if(count($orderDates)>0){
				$sql.=" and O.OrderDate in('".implode("','",$orderDates)."')";
			}
		}
		return DB::SELECT($sql);
	}
}
