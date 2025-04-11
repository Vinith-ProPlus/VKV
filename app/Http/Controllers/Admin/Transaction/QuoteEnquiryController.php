<?php
namespace App\Http\Controllers\web\Transaction;

use App\helper\helper;
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
use ValidUnique;
use logs;
use activeMenuNames;
use docTypes;
use cruds;
use Hamcrest\Arrays\IsArray;
use PHPUnit\TextUI\Help;

class QuoteEnquiryController extends Controller{
	private $general;
	private $UserID;
	private $ActiveMenuName;
	private $PageTitle;
	private $CRUD;
	private $Settings;
    private $Menus;
	private $generalDB;
	private $logDB;
    private $currfyDB;

    public function __construct(){
		$this->ActiveMenuName=activeMenuNames::QuoteEnquiry->value;
		$this->PageTitle="Quote Enquiry";
        $this->middleware('auth');
		$this->middleware(function ($request, $next) {
			$this->UserID=auth()->user()->UserID;
			$this->general=new general($this->UserID,$this->ActiveMenuName);
			$this->Menus=$this->general->loadMenu();
			$this->CRUD=$this->general->getCrudOperations($this->ActiveMenuName);
			$this->Settings=$this->general->getSettings();
			$this->generalDB=Helper::getGeneralDB();
			$this->currfyDB=Helper::getcurrfyDB();
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
            return view('app.transaction.quote-enquiry.view',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"add")==true){
			return Redirect::to('admin/transaction/quote-enquiry/create');
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
            return view('app.transaction.quote-enquiry.trash',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
			return Redirect::to('admin/transaction/quote-enquiry/');
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
			$FormData['QNo']=DocNum::getInvNo($this->ActiveMenuName);
			$FormData['Customers'] = DB::Table('tbl_customer')->where('DFlag', 0)->where('ActiveStatus', 'Active')->get();
            return view('app.transaction.quote-enquiry.quote',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
            return Redirect::to('/admin/transaction/quote-enquiry/');
        }else{
            return view('errors.403');
        }
    }
    public function ImageQuoteCreate(Request $req){
        if($this->general->isCrudAllow($this->CRUD,"add")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=false;
			$FormData['Customers'] = DB::Table('tbl_customer')->where('DFlag', 0)->where('ActiveStatus', 'Active')->get();
			$FormData['Vendors'] = DB::Table('tbl_vendors')->where('DFlag', 0)->where('ActiveStatus', 'Active')->where('isApproved', 1)->get();
			$FormData['QNo']=DocNum::getInvNo($this->ActiveMenuName);
            return view('app.transaction.quote-enquiry.image-quote',$FormData);
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
            return Redirect::to('/admin/transaction/quote-enquiry/');
        }else{
            return view('errors.403');
        }
    }
	public function Save(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"add")==true){
			DB::beginTransaction();
			$OldData=$NewData=[];
			$status=false;
			try {
				$CustomerID=$req->CustomerID;
				$CustomerData = DB::table('tbl_customer')->where('CustomerID',$CustomerID)->first();
				$AddressData = DB::table('tbl_customer_address')->where('AID',$req->AID)->first();
				$EnqID = DocNum::getDocNum(docTypes::Enquiry->value,$this->currfyDB,Helper::getCurrentFY());
				$data=[
					'EnqID' => $EnqID,
					'EnqNo' =>DocNum::getInvNo("Quote-Enquiry"),
					'EnqDate' => $req->EnqDate,
					'EnqExpiryDate' => date('Y-m-d', strtotime('+15 days')),
					'CustomerID' => $CustomerID,
					'ReceiverName' => $CustomerData->CustomerName,
					'ReceiverMobNo' => $req->ReceiverMobNo,
					'ExpectedDeliveryDate' => $req->ExpectedDeliveryDate,
					'AID'=>$req->AID,
					"DAddress"=>$AddressData->Address,
					"DPostalCodeID"=>$AddressData->PostalCodeID,
					"DCityID"=>$AddressData->CityID,
					"DTalukID"=>$AddressData->TalukID,
					"DDistrictID"=>$AddressData->DistrictID,
					"DStateID"=>$AddressData->StateID,
					"DCountryID"=>$AddressData->CountryID,
					'StageID' => $req->StageID,
					'BuildingMeasurementID' => $req->BuildingMeasurementID,
					'BuildingMeasurement' => $req->BuildingMeasurement,
					'CreatedOn' => date('Y-m-d H:i:s'),
					'CreatedBy' => $this->UserID,
				];
				$status=DB::table($this->currfyDB.'tbl_enquiry')->insert($data);
				if($status){
					$ProductData = json_decode($req->ProductData,true);
					foreach($ProductData as $item){
						$EnquiryDetailID = DocNum::getDocNum(docTypes::EnquiryDetails->value,$this->currfyDB,Helper::getCurrentFY());
						$data1=[
							'DetailID' => $EnquiryDetailID,
							'EnqID'=>$EnqID,
							'CID'=>$item['PCID'],
							'SCID'=>$item['PSCID'],
							'ProductID'=>$item['ProductID'],
							'Qty'=>$item['Qty'],
							'UOMID'=>$item['UID'],
							'CreatedOn'=>date('Y-m-d H:i:s'),
							'CreatedBy' => $this->UserID,
						];
						$status = DB::table($this->currfyDB.'tbl_enquiry_details')->insert($data1);
						if($status){
							DocNum::updateDocNum(docTypes::EnquiryDetails->value,$this->currfyDB);
						}
					}
				}
			}catch(Exception $e) {
				$status=false;
			}

			if($status==true){
				DB::commit();
				DocNum::updateDocNum(docTypes::Enquiry->value,$this->currfyDB);
				DocNum::updateInvNo(docTypes::Enquiry->value);
				$NewData=DB::table($this->currfyDB.'tbl_enquiry_details as ED')->leftJoin($this->currfyDB.'tbl_enquiry as E','E.EnqID','ED.EnqID')->where('ED.EnqID',$EnqID)->get();
				$logData=array("Description"=>"New Quote Enquiry Created","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::ADD->value,"ReferID"=>$EnqID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Quote Enquiry Created Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Quote Enquiry Create Failed");
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}

	}
	public function ImageQuoteSave(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"add")==true){
			DB::beginTransaction();
			$OldData=$NewData=[];
			$status=false;
			try {
				$CustomerID=$req->CustomerID;
				$VendorIDs=json_decode($req->VendorIDs);
				$CustomerData = DB::table('tbl_customer')->where('CustomerID',$CustomerID)->first();
				$EnqID = DocNum::getDocNum(docTypes::Enquiry->value,$this->currfyDB,Helper::getCurrentFY());
				$QuoteImage = "";
				$dir = "uploads/transaction/enquiry/" . $EnqID . "/";
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
				if (Helper::isJSON($req->QuoteImage) == true) {
                    $Img = json_decode($req->QuoteImage);
                    if (file_exists($Img->uploadPath)) {
                        $fileName1 = $Img->fileName != "" ? $Img->fileName : Helper::RandomString(10) . "png";
                        copy($Img->uploadPath, $dir . $fileName1);
                        $QuoteImage = $dir . $fileName1;
                        // unlink($Img->uploadPath);
                    }
                }
				$AddressData = DB::table('tbl_customer_address')->where('AID',$req->AID)->first();
				$data=[
					'EnqID' => $EnqID,
					'EnqNo' =>DocNum::getInvNo("Quote-Enquiry"),
					'EnqDate' => $req->EnqDate,
					'EnqExpiryDate' => date('Y-m-d', strtotime('+15 days')),
					'CustomerID' => $CustomerID,
					'ReceiverName' => $CustomerData->CustomerName,
					'ReceiverMobNo' => $req->ReceiverMobNo,
					'VendorIDs' => serialize($VendorIDs),
					'ExpectedDeliveryDate' => $req->ExpectedDeliveryDate,
					'AID'=>$req->AID,
					"DAddress"=>$AddressData->Address,
					"DPostalCodeID"=>$AddressData->PostalCodeID,
					"DCityID"=>$AddressData->CityID,
					"DTalukID"=>$AddressData->TalukID,
					"DDistrictID"=>$AddressData->DistrictID,
					"DStateID"=>$AddressData->StateID,
					"DCountryID"=>$AddressData->CountryID,
					'QuoteImage' => $QuoteImage,
					'isImageQuote' => 1,
					'CreatedBy' => $this->UserID
				];
				$status=DB::table($this->currfyDB.'tbl_enquiry')->insert($data);
				if($status){
					$SelectedVendors = json_decode($req->VendorIDs, true);
					foreach ($SelectedVendors as $VendorID) {
						$isQuoteRequested =  DB::table($this->currfyDB . 'tbl_vendor_quotation')->where('VendorID',$VendorID)->where('EnqID',$EnqID)->first();
						$EnqData = DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->select('CustomerID','AID')->first();
						$CustomerLatLong = DB::table('tbl_customer_address')->where('AID',$EnqData->AID)->select('Latitude','Longitude')->first();
						if(!$isQuoteRequested){
							$StockPoints = DB::table('tbl_vendors_stock_point')->where('VendorID',$VendorID)->where('DFlag',0)->where('ActiveStatus',1)->select('StockPointID','Latitude','Longitude')->get();
							$Distance = Helper::findNearestStockPoint($CustomerLatLong, $StockPoints);
							$VQuoteID = DocNum::getDocNum(docTypes::VendorQuotation->value, $this->currfyDB,Helper::getCurrentFy());
							$data = [
								"VQuoteID" => $VQuoteID,
								"VendorID" => $VendorID,
								"EnqID" => $EnqID,
								"Distance" => $Distance,
								'QuoteImage' => $QuoteImage,
								'isImageQuote' => 1,
								"QReqOn" => date('Y-m-d'),
								"QReqBy" => $this->UserID,
								"CreatedBy" => $this->UserID,
								"CreatedOn" => date("Y-m-d H:i:s"),
							];
							$status = DB::table($this->currfyDB . 'tbl_vendor_quotation')->insert($data);
							if($status){
								DocNum::updateDocNum(docTypes::VendorQuotation->value, $this->currfyDB);

								$Title = "New Image Enquiry Received.";
								$Message = "You have a new image enquiry! Check now for details and respond promptly.";
								Helper::saveNotification($VendorID,$Title,$Message,'ImageEnquiry',$VQuoteID);
							}
						}
					}
					$status = DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->update(['Status'=>'Quote Requested','VendorIDs'=>serialize($SelectedVendors),"UpdatedBy"=>$this->UserID,"UpdatedOn"=>date("Y-m-d H:i:s")]);
				}

			}catch(Exception $e) {
				$status=false;
			}

			if($status==true){
				DB::commit();
				DocNum::updateDocNum(docTypes::Enquiry->value,$this->currfyDB);
				DocNum::updateInvNo(docTypes::Enquiry->value);
				$NewData=DB::table($this->currfyDB.'tbl_enquiry_details as ED')->leftJoin($this->currfyDB.'tbl_enquiry as E','E.EnqID','ED.EnqID')->where('ED.EnqID',$EnqID)->get();
				$logData=array("Description"=>"New Image Quote Enquiry Created","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::ADD->value,"ReferID"=>$EnqID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Image Quote Enquiry Created Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Image Quote Enquiry Create Failed");
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}

	}
    public function QuoteView(Request $req,$EnqID){
        if($this->general->isCrudAllow($this->CRUD,"edit")==true){
            $FormData=$this->general->UserInfo;
            $FormData['menus']=$this->Menus;
            $FormData['crud']=$this->CRUD;
			$FormData['ActiveMenuName']=$this->ActiveMenuName;
			$FormData['PageTitle']=$this->PageTitle;
			$FormData['isEdit']=false;
			$FormData['Settings']=$this->Settings;
			$EnqData=DB::Table($this->currfyDB.'tbl_enquiry as E')
			->leftJoin('tbl_customer as CU', 'CU.CustomerID', 'E.CustomerID')
			->leftJoin('tbl_customer_address as CA', 'CA.AID', 'E.AID')
			->whereNot('E.Status','Cancelled')->Where('E.EnqID',$EnqID)
			->select('EnqID','EnqNo','EnqDate','VendorIDs','Status','ReceiverName','ReceiverMobNo','ExpectedDeliveryDate','CU.Email','CU.CompleteAddress as BillingAddress','CA.CompleteAddress as ShippingAddress','E.AID','E.isImageQuote','E.QuoteImage')
			->first();
			$EnqData->BillingAddress=Helper::formatAddress($EnqData->BillingAddress);
			$EnqData->ShippingAddress=Helper::formatAddress($EnqData->ShippingAddress);

			$FormData['EnqData']=$EnqData;
			if($EnqData){
				$VendorQuote = [];
				$FinalQuoteData = [];
				$PData=DB::table($this->currfyDB.'tbl_enquiry_details as ED')->leftJoin('tbl_products as P','P.ProductID','ED.ProductID')->leftJoin('tbl_uom as UOM','UOM.UID','ED.UOMID')->where('ED.EnqID',$EnqID)->select('ED.ProductID','ED.CID','ED.SCID','ED.Qty','P.ProductName','UOM.UID','UOM.UName','UOM.UCode')->get();
				if(count($PData) > 0){
					foreach($PData as $row){
						$row->AvailableVendors=[];
						$AvailableVendors = Helper::getAvailableVendors($EnqData->AID);
						// return $AvailableVendors;
						$AllVendors = DB::table('tbl_vendors as V')->whereIn('V.VendorID',$AvailableVendors)->select('V.VendorID','V.VendorName')->get();
						if(count($AllVendors)>0){
							foreach($AllVendors as $item){
								$isProductAvailable= DB::Table('tbl_vendors_product_mapping')->where('Status',1)->Where('VendorID',$item->VendorID)->where('ProductID',$row->ProductID)->first();
									if($isProductAvailable){
										$AdminRating = DB::table($this->currfyDB.'tbl_vendor_orders')->where('VendorID',$item->VendorID)->where('Status','Delivered')->value(DB::raw('ROUND(AVG(Ratings))'));
										$CustomerRating = DB::table($this->currfyDB.'tbl_vendor_orders')->where('VendorID',$item->VendorID)->where('Status','Delivered')->value(DB::raw('ROUND(AVG(CustomerRatings))'));
										$OverAll = $AdminRating + $CustomerRating;
										$row->AvailableVendors[] = [
											"VendorID" => $item->VendorID,
											"VendorName" => $item->VendorName,
											"OverAll" => $OverAll."/10",
											"ProductID" => $isProductAvailable->ProductID,
										];
									}
							}
						}
					}
				}
				if($EnqData->Status == "Quote Requested" && $EnqData->VendorIDs && count(unserialize($EnqData->VendorIDs)) > 0){
					$VendorQuote = DB::Table($this->currfyDB.'tbl_vendor_quotation as VQ')->join('tbl_vendors as V','V.VendorID','VQ.VendorID')/* ->where('VQ.Status','Sent') */->where('VQ.EnqID',$EnqID)->select('VQ.VendorID','V.VendorName','VQ.VQuoteID','VQ.Status','VQ.AdditionalCost')->get();
					foreach($VendorQuote as $row){
						$row->ProductData = DB::table($this->currfyDB.'tbl_vendor_quotation_details as VQD')->where('VQuoteID',$row->VQuoteID)
						->select('DetailID','ProductID','Price','VQuoteID')
						->get();
					}
				}elseif($EnqData->Status == "Converted to Quotation" || $EnqData->Status == "Accepted"){
					$FinalQuoteData = DB::Table($this->currfyDB.'tbl_quotation_details as QD')->join($this->currfyDB.'tbl_quotation as Q','Q.QID','QD.QID')->join('tbl_vendors as V','V.VendorID','QD.VendorID')->join('tbl_products as P','P.ProductID','QD.ProductID')->join('tbl_uom as UOM','UOM.UID','P.UID')->where('QD.isCancelled',0)->where('Q.EnqID',$EnqID)->get();
				}

				$FormData['PData'] = $PData;
				$FormData['VendorQuote'] = $VendorQuote;
				$FormData['FinalQuoteData'] = $FinalQuoteData;
				$FormData['RequestedVendors'] = DB::table($this->currfyDB . 'tbl_vendor_quotation')->where('EnqID',$EnqID)->pluck('VendorID')->toArray();
				// return $FormData['RequestedVendors'];
				return view('app.transaction.quote-enquiry.quote-view', $FormData);
			}else{
				return view('errors.403');
			}
        }elseif($this->general->isCrudAllow($this->CRUD,"view")==true){
            return Redirect::to('admin/transaction/quote-enquiry/');
        }else{
            return view('errors.403');
        }
    }

    public function RequestQuote(Request $req,$EnqID){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$OldData=$NewData=[];
			DB::beginTransaction();
			$status=false;
			try {
				$EnqData = DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->select('CustomerID','AID')->first();
				$isNewEnq = DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->where('Status','New')->exists();
				$SelectedVendors = json_decode($req->SelectedVendors, true);
				foreach ($SelectedVendors as $VendorID) {
					$isQuoteRequested =  DB::table($this->currfyDB . 'tbl_vendor_quotation')->where('VendorID',$VendorID)->where('EnqID',$EnqID)->first();
					if(!$isQuoteRequested){
						$CustomerLatLong = DB::table('tbl_customer_address')->where('AID',$EnqData->AID)->where('Latitude','!=',NULL)->where('Longitude','!=',NULL)->select('Latitude','Longitude')->first();
						if(!$CustomerLatLong && $CustomerLatLong->Latitude && !$CustomerLatLong->Longitude){
							return ['status' => false, 'message' =>'Customer Lat Long doesnt exists!'];
						}

						$StockPoints = DB::table('tbl_vendors_stock_point')->where('VendorID',$VendorID)->where('DFlag',0)->where('ActiveStatus',1)->where('Latitude','!=',NULL)->where('Longitude','!=',NULL)->select('VendorID','StockPointID','Latitude','Longitude')->get();
						if(count($StockPoints) == 0){
							return ['status' => false, 'message' =>'Vendor ('.$VendorID.') Stock points not found'];
						}

						$Distance = Helper::findNearestStockPoint($CustomerLatLong, $StockPoints);
						$VQuoteID = DocNum::getDocNum(docTypes::VendorQuotation->value, $this->currfyDB,Helper::getCurrentFy());
						$data = [
							"VQuoteID" => $VQuoteID,
							"VendorID" => $VendorID,
							"EnqID" => $EnqID,
							"Distance" => $Distance,
							"QReqOn" => date('Y-m-d'),
							"QReqBy" => $this->UserID,
							"CreatedBy" => $this->UserID,
							"CreatedOn" => date("Y-m-d H:i:s"),
						];
						$status = DB::table($this->currfyDB . 'tbl_vendor_quotation')->insert($data);
						if ($status) {
							$ProductDetails = json_decode($req->ProductDetails);
							foreach ($ProductDetails as $item) {
								$isProductMapped = DB::table('tbl_vendors_product_mapping')->where('ProductID', $item->ProductID)->where('VendorID', $VendorID)->where('Status', 1)->exists();
								if ($isProductMapped) {
									$DetailID = DocNum::getDocNum(docTypes::VendorQuotationDetails->value, $this->currfyDB, Helper::getCurrentFy());
									$data1 = [
										"DetailID" => $DetailID,
										"VQuoteID" => $VQuoteID,
										"ProductID" => $item->ProductID,
										"Qty" => $item->Qty,
									];

									$status = DB::table($this->currfyDB . 'tbl_vendor_quotation_details')->insert($data1);
									if ($status) {
										DocNum::updateDocNum(docTypes::VendorQuotationDetails->value, $this->currfyDB);
									}
								}
							}
							DocNum::updateDocNum(docTypes::VendorQuotation->value, $this->currfyDB);
						}
						$Title = "New Enquiry Received.";
						$Message = "You have a new enquiry! Check now for details and respond promptly.";
						Helper::saveNotification($VendorID,$Title,$Message,'Enquiry',$VQuoteID);
					}
				}
				$status = DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->update(['Status'=>'Quote Requested','VendorIDs'=>serialize($SelectedVendors),"UpdatedBy"=>$this->UserID,"UpdatedOn"=>date("Y-m-d H:i:s")]);
			}catch(Exception $e) {
                logger($e);
				$status=false;
			}
			if($status==true){
				DB::commit();
				if($isNewEnq){
					$Title = "Quotation Accepted";
					$Message = "Your quotation has been accepted. The admin will update your quote pricing.";
					Helper::saveNotification($EnqData->CustomerID,$Title,$Message,'Enquiry',$EnqID);
				}
				$NewData=DB::table($this->currfyDB.'tbl_vendor_quotation as VQ')->join($this->currfyDB.'tbl_vendor_quotation_details as VQD','VQD.VQuoteID','VQ.VQuoteID')->where('VQ.EnqID',$EnqID)->get();
				$logData=array("Description"=>"Quotation Request Sent","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::ADD->value,"ReferID"=>$EnqID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Quotation Request Sent Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Quotation Request Failed");
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}
	}

	public function AddQuotePrice(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			try {
				$OldData=$NewData=[];
				$ProductData = json_decode($req->ProductData);
				$totalTaxable = 0;
				$totalTaxAmount = 0;
				$totalCGST = 0;
				$totalSGST = 0;
				$totalIGST = 0;
				$totalQuoteValue = 0;
				foreach ($ProductData as $item) {
					$ProductDetails = DB::table('tbl_products as P')->leftJoin('tbl_tax as T', 'T.TaxID', 'P.TaxID')->where('P.ProductID', $item->ProductID)->select('P.TaxType', 'T.TaxPercentage','P.TaxID')->first();
					$Amt = $item->Qty * $item->Price;
					if($ProductDetails->TaxType == 'Include'){
						$taxAmount =  $Amt * ($ProductDetails->TaxPercentage / 100);
						$taxableAmount = $Amt - $taxAmount;
					}else{
						$taxAmount =  $Amt * ($ProductDetails->TaxPercentage / 100);
						$taxableAmount = $Amt;
					}

					$cgstPercentage = $sgstPercentage = $ProductDetails->TaxPercentage / 2;
					$cgstAmount = $sgstAmount = $taxAmount / 2;

					$totalAmount = $taxableAmount + $taxAmount;

					$totalTaxable += $taxableAmount;
					$totalTaxAmount += $taxAmount;
					$totalCGST += $cgstAmount;
					$totalSGST += $sgstAmount;
					$totalQuoteValue += $totalAmount;

					$data=[
						'Taxable'=>$taxableAmount,
						'TaxAmt'=>$taxAmount,
						'TaxID'=>$ProductDetails->TaxID,
						'TaxPer'=>$ProductDetails->TaxPercentage,
						'TaxType'=>$ProductDetails->TaxType,
						"CGSTPer" => $cgstPercentage,
						"SGSTPer" => $sgstPercentage,
						"CGSTAmt" => $cgstAmount,
						"SGSTAmt" => $sgstAmount,
						'TotalAmt'=>$totalAmount,
						'Price'=>$item->Price,
						'Status'=>'Price Sent',
						'UpdatedOn'=>date('Y-m-d H:i:s')
					];
					$status = DB::Table($this->currfyDB.'tbl_vendor_quotation_details')->where('VQuoteID',$req->VQuoteID)->where('ProductID',$item->ProductID)->update($data);
				}
				if ($status) {
					$data=[
						'SubTotal' => $totalTaxable,
						'TaxAmount' => $totalTaxAmount,
						'TotalAmount' => $totalQuoteValue,
						'LabourCost'=>$req->LabourCost ?? 0,
						'TransportCost'=>$req->TransportCost ?? 0,
						'AdditionalCost'=>$req->TransportCost + $req->LabourCost ?? 0,
						'Status' => 'Sent',
						'QSentOn'=>date('Y-m-d'),
						'UpdatedBy'=>$this->UserID,
						'UpdatedOn'=>date('Y-m-d H:i:s')
					];
					$status = DB::Table($this->currfyDB.'tbl_vendor_quotation')->where('VendorID',$req->VendorID)->where('VQuoteID',$req->VQuoteID)->update($data);
				}
			}catch(Exception $e) {
                logger($e);
				$status=false;
			}
			if($status==true){
				DB::commit();
				$NewData=DB::table($this->currfyDB.'tbl_vendor_quotation_details')->where('VQuoteID',$req->VQuoteID)->get();
				$logData=array("Description"=>"Vendor Quote Price Updated","ModuleName"=>"Quote Enquiry","Action"=>"Add","ReferID"=>$req->VQuoteID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return response()->json(['status' => true ,'message' => "Quote Price Updated Successfully!"]);
			}else{
				DB::rollback();
				return response()->json(['status' => false,'message' => "Quote Price Update Failed!"]);
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}

	}

	public function RejectQuote(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			try {
				$status = DB::Table($this->currfyDB.'tbl_vendor_quotation')->where('VendorID',$req->VendorID)->where('VQuoteID',$req->VQuoteID)->update(['Status'=>'Rejected','UpdatedOn'=>date('Y-m-d H:i:s')]);
			}catch(Exception $e) {
                logger($e);
				$status=false;
			}
			if($status==true){
				DB::commit();
				return response()->json(['status' => true ,'message' => "Quote Rejected Successfully!"]);
			}else{
				DB::rollback();
				return response()->json(['status' => false,'message' => "Quote Reject Failed!"]);
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}
	}

	public function DeleteQuoteItem(Request $req){
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			DB::beginTransaction();
			try {
				$status = DB::Table($this->currfyDB.'tbl_quotation_details')->where('DetailID',$req->DetailID)->update(['isCancelled'=>1,'CancelledBy'=>$this->UserID,'CancelledOn'=>date('Y-m-d'),'UpdatedOn'=>date('Y-m-d H:i:s')]);
				if($status){
					$QData = DB::table($this->currfyDB.'tbl_quotation_details as QD')->leftJoin($this->currfyDB.'tbl_quotation as Q','Q.QID','QD.QID')->where('QD.QID',$req->QID)->where('QD.isCancelled',0)->get();
						$totalTaxable = 0;
						$totalTaxAmount = 0;
						$totalCGST = 0;
						$totalSGST = 0;
						$totalIGST = 0;
						$totalQuoteValue = 0;
						foreach ($QData as $item) {
							$totalTaxable += $item->Taxable;
							$totalTaxAmount += $item->TaxAmt;
							$totalCGST += $item->CGSTAmt;
							$totalSGST += $item->SGSTAmt;
							$totalIGST += $item->IGSTAmt;
							$totalQuoteValue += $item->TotalAmt;
						}
						$data=[
							'SubTotal' => $totalTaxable,
							'TaxAmount' => $totalTaxAmount,
							'CGSTAmount' => $totalCGST,
							'SGSTAmount' => $totalSGST,
							'IGSTAmount' => $totalIGST,
							'TotalAmount' => $totalQuoteValue,
							'OverAllAmount' => $totalQuoteValue + $QData[0]->AdditionalCost,
							'UpdatedOn' => date('Y-m-d H:i:s'),
							'UpdatedBy' => $this->UserID,
						];
						$status=DB::table($this->currfyDB.'tbl_quotation')->where('QID',$req->QID)->update($data);
				}
			}catch(Exception $e) {
                logger($e);
				$status=false;
			}
			if($status==true){
				DB::commit();
				return response()->json(['status' => true ,'message' => "Quote Item Rejected Successfully!"]);
			}else{
				DB::rollback();
				return response()->json(['status' => false,'message' => "Quote Item Reject Failed!"]);
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}
	}

    public function QuoteConvert(Request $req,$EnqID){ logger($req);
		if($this->general->isCrudAllow($this->CRUD,"edit")==true){
			$OldData=array();$NewData=array();
			DB::beginTransaction();
			try {
				$EnqData = DB::table($this->currfyDB.'tbl_enquiry_details as ED')->join($this->currfyDB.'tbl_enquiry as E','E.EnqID','ED.EnqID')->where('ED.EnqID',$EnqID)->get();
				$FinalQuote = json_decode($req->FinalQuote);
				$AdditionalCostData = json_decode($req->AdditionalCost);
				$AdditionalCost = 0;
				foreach($AdditionalCostData as $cost){
					$AdditionalCost += $cost->ACost;
				}
				$QData = DB::table($this->currfyDB.'tbl_quotation')->where('EnqID',$EnqID)->first();
				if(!$QData){
					$QID = DocNum::getDocNum(docTypes::Quotation->value, $this->currfyDB,Helper::getCurrentFy());
					$totalTaxable = 0;
					$totalTaxAmount = 0;
					$totalCGST = 0;
					$totalSGST = 0;
					$totalIGST = 0;
					$totalQuoteValue = 0;
					foreach ($FinalQuote as $item) {
						$ProductDetails = DB::table('tbl_products as P')->leftJoin('tbl_tax as T', 'T.TaxID', 'P.TaxID')->where('P.ProductID', $item->ProductID)->select('P.TaxType', 'T.TaxPercentage','P.TaxID')->first();
						$Amt = $item->Qty * $item->FinalPrice;
						if($ProductDetails->TaxType == 'Include'){
							$taxAmount =  $Amt * ($ProductDetails->TaxPercentage / 100);
							$taxableAmount = $Amt - $taxAmount;
						}else{
							$taxAmount =  $Amt * ($ProductDetails->TaxPercentage / 100);
							$taxableAmount = $Amt;
						}

						$cgstPercentage = $sgstPercentage = $ProductDetails->TaxPercentage / 2;
						$cgstAmount = $sgstAmount = $taxAmount / 2;

						$totalAmount = $taxableAmount + $taxAmount;

						$totalTaxable += $taxableAmount;
						$totalTaxAmount += $taxAmount;
						$totalCGST += $cgstAmount;
						$totalSGST += $sgstAmount;
						$totalQuoteValue += $totalAmount;

						$QDetailID = DocNum::getDocNum(docTypes::QuotationDetails->value, $this->currfyDB,Helper::getCurrentFy());
						$data1=[
							"DetailID" => $QDetailID,
							"QID" => $QID,
							"VQDetailID" => $item->DetailID,
							"ProductID" => $item->ProductID,
							"TaxType" => $ProductDetails->TaxType,
							"Qty" => $item->Qty,
							"Price" => $item->FinalPrice,
							"TaxAmt" => $taxAmount,
							"TaxPer" => $ProductDetails->TaxPercentage,
							"Taxable" => $taxableAmount,
							"CGSTPer" => $cgstPercentage,
							"SGSTPer" => $sgstPercentage,
							"CGSTAmt" => $cgstAmount,
							"SGSTAmt" => $sgstAmount,
							"TotalAmt" => $totalAmount,
							"VendorID" => $item->VendorID,
							'CreatedOn'=>date('Y-m-d H:i:s'),
							'CreatedBy'=>$this->UserID,
						];
						$status = DB::table($this->currfyDB.'tbl_quotation_details')->insert($data1);
						$isNotifiedVendor = DB::table($this->currfyDB.'tbl_quotation_details')->where('QID',$QID)->where('VendorID',$item->VendorID)->where('isCancelled',0)->exists();
						if(!$isNotifiedVendor){
							$VQuoteID = DB::table($this->currfyDB.'tbl_vendor_quotation_details')->where('DetailID',$item->DetailID)->value('VQuoteID');
							$Title = "Quotation Accepted";
							$Message = "Great news! Your quotation has been accepted. We'll proceed accordingly. Thank you.";
							Helper::saveNotification($item->VendorID,$Title,$Message,'Quotation',$VQuoteID);
						}
						if($status){
							DocNum::updateDocNum(docTypes::QuotationDetails->value, $this->currfyDB);
						}
					}
					if ($status) {
						$QuoteNo = DocNum::getInvNo(docTypes::Quotation->value);
						$data=[
							'QID' => $QID,
							'EnqID' => $EnqID,
							'QNo' => $QuoteNo,
							'QDate' => date('Y-m-d'),
							'QExpiryDate' => date('Y-m-d', strtotime('+15 days')),
							'CustomerID' => $EnqData[0]->CustomerID,
							'ReceiverName' => $EnqData[0]->ReceiverName,
							'ReceiverMobNo' => $EnqData[0]->ReceiverMobNo,
							'AID' => $EnqData[0]->AID,
							'DAddress' => $EnqData[0]->DAddress,
							'DCountryID' => $EnqData[0]->DCountryID,
							'DStateID' => $EnqData[0]->DStateID,
							'DDistrictID' => $EnqData[0]->DDistrictID,
							'DTalukID' => $EnqData[0]->DTalukID,
							'DCityID' => $EnqData[0]->DCityID,
							'DPostalCodeID' => $EnqData[0]->DPostalCodeID,
							'SubTotal' => $totalTaxable,
							'TaxAmount' => $totalTaxAmount,
							'CGSTAmount' => $totalCGST,
							'SGSTAmount' => $totalSGST,
							'IGSTAmount' => $totalIGST,
							'TotalAmount' => $totalQuoteValue,
							'AdditionalCost' => $AdditionalCost,
							'OverAllAmount' => $totalQuoteValue + $AdditionalCost,
							'AdditionalCostData' => serialize($AdditionalCostData),
							'CreatedOn' => date('Y-m-d H:i:s'),
							'CreatedBy' => $this->UserID,
						];
						$status=DB::table($this->currfyDB.'tbl_quotation')->insert($data);
					}
				}
				$status = DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->update(['Status'=>'Converted to Quotation','UpdatedOn'=>date('Y-m-d H:i:s'),'UpdatedBy'=>$this->UserID]);

			}catch(Exception $e) {
				$status=false;
			}
			if($status==true){
				DB::commit();
				DocNum::updateDocNum(docTypes::Quotation->value, $this->currfyDB);
				DocNum::updateInvNo(docTypes::Quotation->value);
				$QData = DB::table($this->currfyDB.'tbl_quotation')->where('EnqID',$EnqID)->first();
				$Title = "Quotation updated with price";
                $Message = "Quotation" . $QData->QNo . " updated with price";
				Helper::saveNotification($EnqData[0]->CustomerID,$Title,$Message,'Quotation',$EnqID);

				$NewData=DB::table($this->currfyDB.'tbl_quotation_details as QD')->join($this->currfyDB.'tbl_quotation as Q','QD.QID','Q.QID')->where('QD.QID',$QData->QID)->get();
				$logData=array("Description"=>"Quotation Converted","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::UPDATE->value,"ReferID"=>$QData->QID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Quotation Converted Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Quotation Convert Failed");
			}
		}else{
			return array('status'=>false,'message'=>'Access denined');
		}
	}

	public function Delete(Request $req,$EnqID){
		$OldData=$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"delete")==true){
			DB::beginTransaction();
			$status=false;
			try{
				$OldData=DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->get();
				$status=DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->update(array("Status"=>"Cancelled","DeletedBy"=>$this->UserID,"CancelledBy"=>$this->UserID,"DeletedOn"=>date("Y-m-d H:i:s")));
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
				$Title = "Quotation Request Rejected.";
                $Message = "Your quotation enquiry has been rejected by admin. We appreciate your interest. Should you require a reason for the rejection, please contact the admin through support ticket.";
                Helper::saveNotification($OldData[0]->CustomerID,$Title,$Message,'Enquiry',$EnqID);
				$NewData=DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->get();
				$logData=array("Description"=>"Quotation has been Cancelled ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::DELETE->value,"ReferID"=>$EnqID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Quotation Cancelled Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Quotation Cancelled Failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function Restore(Request $req,$EnqID){
		$OldData=$NewData=array();
		if($this->general->isCrudAllow($this->CRUD,"restore")==true){
			DB::beginTransaction();
			$status=false;
			try{
				$OldData=DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->get();
				$status=DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->update(array("Status"=>"New","UpdatedBy"=>$this->UserID,"UpdatedOn"=>date("Y-m-d H:i:s")));
			}catch(Exception $e) {

			}
			if($status==true){
				DB::commit();
				$NewData=DB::table($this->currfyDB.'tbl_enquiry')->where('EnqID',$EnqID)->get();
				$logData=array("Description"=>"Quotation has been Restored ","ModuleName"=>$this->ActiveMenuName,"Action"=>cruds::RESTORE->value,"ReferID"=>$EnqID,"OldData"=>$OldData,"NewData"=>$NewData,"UserID"=>$this->UserID,"IP"=>$req->ip());
				logs::Store($logData);
				return array('status'=>true,'message'=>"Quotation Restored Successfully");
			}else{
				DB::rollback();
				return array('status'=>false,'message'=>"Quotation Restore Failed");
			}
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function TableView(Request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){
			$columns = array(
				array( 'db' => 'E.EnqNo', 'dt' => '0' ),
				array( 'db' => 'E.EnqDate', 'dt' => '1' ),
				array( 'db' => 'C.CustomerName', 'dt' => '2' ),
				array( 'db' => 'C.MobileNo1', 'dt' => '3' ),
				array( 'db' => 'C.Email', 'dt' => '4' ),
				array( 'db' => 'E.ExpectedDeliveryDate', 'dt' => '5' ),
				array( 'db' => 'E.Status', 'dt' => '6' ),
				array( 'db' => 'E.EnqID', 'dt' => '7' ),
			);
			$columns1 = array(
				array( 'db' => 'EnqNo', 'dt' => '0' ),
				array( 'db' => 'EnqDate', 'dt' => '1','formatter' => function( $d, $row ) {return date($this->Settings['date-format'],strtotime($d));}),
				array( 'db' => 'CustomerName', 'dt' => '2' ),
				array( 'db' => 'MobileNo1', 'dt' => '3' ),
				array( 'db' => 'Email', 'dt' => '4'),
				array( 'db' => 'ExpectedDeliveryDate', 'dt' => '5','formatter' => function( $d, $row ) {return date($this->Settings['date-format'],strtotime($d));}),
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
							$html='<div class="d-flex align-items-center">';
							/* if($this->general->isCrudAllow($this->CRUD,"edit")==true){
								$html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-success '.$this->general->UserInfo['Theme']['button-size'].'  mr-10 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
							} */
							if($this->general->isCrudAllow($this->CRUD,"view")==true){
								// $html.='<button type="button" data-id="'.$d.'" class="btn  btn-outline-info '.$this->general->UserInfo['Theme']['button-size'].'  mr-10 btnView" data-original-title="View Quotation"><i class="fa fa-eye"></i></button>';
								$html.='<button type="button" data-id="'.$d.'" class="btn btn-outline-info '.$this->general->UserInfo['Theme']['button-size'].'  mr-10 btnView">View&nbsp;Enquiry</button>';
							}
							if($this->general->isCrudAllow($this->CRUD,"delete")==true && $row['Status'] !== "Allocated"){
								$html.='<button type="button" data-id="'.$d.'" class="btn btn-outline-danger '.$this->general->UserInfo['Theme']['button-size'].'  btnDelete" data-original-title="Delete">Cancel</button>';
							}
                            $html .= '</div>';
							return $html;
						}
				)
			);
			$Where=" Status != 'Cancelled' ";
			if($request->status){
				$status=json_decode($request->status,true);
				if(count($status)>0){
					$Where.=" and Status in('".implode("','",$status)."')";
				}
			}
			if($request->customers){
				$customers=json_decode($request->customers,true);
				if(count($customers)>0){
					$Where.=" and C.CustomerID in('".implode("','",$customers)."')";
				}
			}
			if($request->quoteDates){
				$quoteDates=json_decode($request->quoteDates,true);
				if(count($quoteDates)>0){
					$Where.=" and EnqDate in('".implode("','",$quoteDates)."')";
				}
			}
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$this->currfyDB . 'tbl_enquiry as E LEFT JOIN tbl_customer as C ON C.CustomerID = E.CustomerID LEFT JOIN '.$this->generalDB.'tbl_countries as CO On CO.CountryID=C.CountryID';
			$data['PRIMARYKEY']='E.EnqID';
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


	public function TrashTableView(Request $request){
		if($this->general->isCrudAllow($this->CRUD,"view")==true){

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
						return "<span class='badge badge-danger m-1'>".$d."</span>";
					}
				),
				array(
						'db' => 'EnqID',
						'dt' => '7',
						'formatter' => function( $d, $row ) {
							$html='';
							if($this->general->isCrudAllow($this->CRUD,"restore")==true){
								$html='<button type="button" data-id="'.$d.'" class="btn btn-outline-success btn-sm  m-2 btnRestore"> <i class="fa fa-repeat" aria-hidden="true"></i> </button>';
							}
							return $html;
						}
				)
			);
			$data=array();
			$data['POSTDATA']=$request;
			$data['TABLE']=$this->currfyDB . 'tbl_enquiry';
			$data['PRIMARYKEY']='EnqID';
			$data['COLUMNS']=$columns;
			$data['COLUMNS1']=$columns;
			$data['GROUPBY']=null;
			$data['WHERERESULT']=null;
			$data['WHEREALL']=" Status = 'Cancelled'";
			return SSP::SSP( $data);
		}else{
			return response(array('status'=>false,'message'=>"Access Denied"), 403);
		}
	}

	public function GetCustomers(Request $req){
		$Customers = DB::Table('tbl_customer as CU')
			->join($this->generalDB.'tbl_countries as C','C.CountryID','CU.CountryID')
			->join($this->generalDB.'tbl_states as S', 'S.StateID', 'CU.StateID')
			->join($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'CU.DistrictID')
			->join($this->generalDB.'tbl_taluks as T', 'T.TalukID', 'CU.TalukID')
			->join($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CU.CityID')
			->join($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CU.PostalCodeID')
			->where('CU.DFlag', 0)->where('CU.ActiveStatus', 'Active')
			->select('CU.*','C.CountryName','S.StateName','D.DistrictName','T.TalukName','CI.CityName','PC.PostalCode')
			->get();
			/* foreach($Customers as $customer){
				$billingAddressParts = array_map('trim', [
					$customer->Address,
					$customer->CityName,
					$customer->TalukName,
					$customer->DistrictName,
					$customer->StateName,
					$customer->CountryName,
					$customer->PostalCode
				]);
				$customer->BillingAddress = json_encode($billingAddressParts);

				$customer->DeliverAddress = [];
				$ShippingAddresses = DB::table('tbl_customer_address as CA')
					->join($this->generalDB.'tbl_countries as C','C.CountryID','CA.CountryID')
					->join($this->generalDB.'tbl_states as S', 'S.StateID', 'CA.StateID')
					->join($this->generalDB.'tbl_districts as D', 'D.DistrictID', 'CA.DistrictID')
					->join($this->generalDB.'tbl_taluks as T', 'T.TalukID', 'CA.TalukID')
					->join($this->generalDB.'tbl_cities as CI', 'CI.CityID', 'CA.CityID')
					->join($this->generalDB.'tbl_postalcodes as PC', 'PC.PID', 'CA.PostalCodeID')
					->where('CA.CustomerID', $customer->CustomerID)
					->select('CA.*','C.CountryName','S.StateName','D.DistrictName','T.TalukName','CI.CityName','PC.PostalCode')
					->get();

				foreach($ShippingAddresses as $shippingAddress){
					$addressParts = array_map('trim', [
						$shippingAddress->Address,
						$shippingAddress->CityName,
						$shippingAddress->TalukName,
						$shippingAddress->DistrictName,
						$shippingAddress->StateName,
						$shippingAddress->CountryName,
						$shippingAddress->PostalCode
					]);
					$customer->DeliverAddress[] = json_encode($addressParts);
				}

				$customer->DeliverAddress = count($customer->DeliverAddress) > 0 ? json_encode($customer->DeliverAddress) : [];
			} */
		return $Customers;
	}

	public function GetVendorQuote(request $req){
		$QuoteReqData = DB::table($this->currfyDB.'tbl_vendor_quotation as VQ')
		->leftJoin($this->currfyDB.'tbl_enquiry as E','E.EnqID','VQ.EnqID')
        ->where('VQ.VendorID',$req->VendorID)->where('VQ.EnqID',$req->EnqID)
        ->select('VQ.VQuoteID','E.EnqNo')
        ->first();

        $QuoteReqData->ProductData = DB::table($this->currfyDB.'tbl_vendor_quotation_details as VQD')->leftJoin('tbl_vendors_product_mapping as VPM','VPM.ProductID','VQD.ProductID')
            ->leftJoin('tbl_products as P','P.ProductID','VQD.ProductID')
            ->leftJoin('tbl_tax as T', 'T.TaxID', 'P.TaxID')
			->leftJoin('tbl_product_subcategory as PSC', 'PSC.PSCID', 'P.SCID')
			->leftJoin('tbl_product_category as PC', 'PC.PCID', 'PSC.PCID')
            ->leftJoin('tbl_uom as U', 'U.UID', 'P.UID')
            ->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
            ->where('VQD.VQuoteID',$QuoteReqData->VQuoteID)/* ->where('VQD.Status',NULL) */
            ->where('VPM.VendorID',$req->VendorID)
            ->select('VQD.DetailID','P.ProductName','P.ProductID','VQD.Qty', 'PC.PCName', 'PC.PCID', 'PSC.PSCName','U.UName','U.UCode','U.UID', 'PSC.PSCID','VPM.VendorPrice','P.TaxType','P.TaxID','T.TaxPercentage','T.TaxName')
            ->get();
		return $QuoteReqData;
	}

	public function GetVendorQuoteDetails(request $req){
		$VendorDB = Helper::getVendorDB($req->VendorID, $this->UserID);
		return DB::Table($VendorDB.'tbl_quotation_sent_details as QSD')->join('tbl_products as P','P.ProductID','QSD.ProductID')->join('tbl_uom as UOM','UOM.UID','QSD.UOMID')->where('QSD.QuoteSentID',$req->QuoteSentID)
		->select('QSD.Amount','QSD.Price','QSD.TaxAmount','QSD.Taxable','QSD.TaxType','QSD.CGSTPer','QSD.SGSTPer','QSD.CGSTAmount','QSD.SGSTAmount','QSD.Qty','P.ProductName','UOM.UCode','UOM.UName')->get();
	}

	public function GetVendorRatings(request $req){
		$VendorRatings = DB::Table('tbl_vendors as V')
				->join($this->generalDB.'tbl_states as S','S.StateID','V.StateID')
				->join($this->generalDB.'tbl_districts as D','D.DistrictID','V.DistrictID')
				->join($this->generalDB.'tbl_taluks as T','T.TalukID','V.TalukID')
				->join($this->generalDB.'tbl_cities as C','C.CityID','V.CityID')
				->join($this->generalDB.'tbl_postalcodes as P','P.PID','V.PostalCode')
				->where('V.VendorID',$req->VendorID)->first();

		$createdDate = strtotime($VendorRatings->CreatedOn);
		$currentDate = time();
		$difference = $currentDate - $createdDate;
		$years = floor($difference / (365 * 24 * 60 * 60));
		$months = floor(($difference - $years * 365 * 24 * 60 * 60) / (30 * 24 * 60 * 60));

		$yearLabel = ($years > 1) ? 'Years' : 'Year';
		$formattedOutput = date('M Y', $createdDate).' (';

		$formattedOutput .= ($years > 0) ? $years . ' ' . $yearLabel : '';
		if ($months > 0) {
			$monthLabel = ($months > 1) ? 'Months' : 'Month';
			$formattedOutput .= ' ' . $months . ' ' . $monthLabel . ' )';
		} else {
			$formattedOutput .= ')';
		}
		$VendorRatings->TotalYears = $formattedOutput;

		$VendorRatings->TotalOrders = DB::table($this->currfyDB.'tbl_vendor_orders')->where('VendorID',$VendorRatings->VendorID)->where('Status','Delivered')->count();
		$VendorRatings->OrderValue = DB::table($this->currfyDB.'tbl_vendor_orders')->where('VendorID',$VendorRatings->VendorID)->where('Status','Delivered')->sum('NetAmount');
		$VendorRatings->Outstanding = DB::table($this->currfyDB.'tbl_vendor_orders')->where('VendorID',$VendorRatings->VendorID)->where('Status','Delivered')->sum('BalanceAmount');
		$AdminRating = DB::table($this->currfyDB.'tbl_vendor_orders')->where('VendorID',$VendorRatings->VendorID)->where('Status','Delivered')->avg('Ratings');
		$VendorRatings->AdminRating = round($AdminRating);
		$CustomerRating = DB::table($this->currfyDB.'tbl_vendor_orders')->where('VendorID',$VendorRatings->VendorID)->where('Status','Delivered')->avg('CustomerRatings');
		$VendorRatings->CustomerRating = round($CustomerRating);
		$VendorRatings->OverAll = round($VendorRatings->AdminRating + $VendorRatings->CustomerRating);
		return $VendorRatings;
	}

	public function GetCategory(Request $req){
        $AllVendors = Helper::getAvailableVendors($req->AID);

		return DB::table('tbl_vendors_product_mapping as VPM')
			->leftJoin('tbl_product_category as PC', 'PC.PCID', 'VPM.PCID')
			->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
			->where('VPM.Status', 1)->WhereIn('VPM.VendorID', $AllVendors)
			->groupBy('PC.PCID', 'PC.PCName')
			->select('PC.PCID', 'PC.PCName')
			->get();
    }
	public function GetSubCategory(request $req){
		$AllVendors = Helper::getAvailableVendors($req->AID);
		return DB::table('tbl_vendors_product_mapping as VPM')
			->leftJoin('tbl_product_subcategory as PSC','PSC.PSCID','VPM.PSCID')
			->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
			->where('VPM.Status', 1)->WhereIn('VPM.VendorID', $AllVendors)->where('PSC.PCID', $req->PCID)
			->groupBy('PSC.PSCID', 'PSCName')
			->select('PSC.PSCID', 'PSCName')
			->get();
	}
    public function GetProducts(Request $req){
        $AllVendors = Helper::getAvailableVendors($req->AID);

		return DB::table('tbl_vendors_product_mapping as VPM')
			->leftJoin('tbl_products as P','P.ProductID','VPM.ProductID')
			->leftJoin('tbl_product_subcategory as PSC','PSC.PSCID','P.SCID')
			->leftJoin('tbl_product_category as PC', 'PC.PCID', 'PSC.PCID')
			->leftJoin('tbl_uom as U', 'U.UID', 'P.UID')
			->where('VPM.Status', 1)->WhereIn('VPM.VendorID', $AllVendors)
			->where('P.ActiveStatus', 'Active')->where('P.DFlag', 0)
			->where('PC.ActiveStatus', 'Active')->where('PC.DFlag', 0)
			->where('PSC.ActiveStatus', 'Active')->where('PSC.DFlag', 0)
			->where('PSC.PCID', $req->PCID)->where('P.SCID', $req->PSCID)
			->groupBy('PSC.PSCID', 'PSCName', 'PC.PCID', 'PCName', 'P.ProductID', 'ProductName','UName','UCode','U.UID')
			->select('PSC.PSCID', 'PSCName','PC.PCID', 'PCName', 'P.ProductID', 'ProductName','UName','UCode','U.UID')
			->get();
    }
	public function getSearchStatus(Request $req){
		$sql="Select DISTINCT(Status) as Status From ".$this->currfyDB."tbl_enquiry Where 1=1 ";
		return DB::SELECT($sql);
	}
	public function getSearchCustomers(Request $req){
		$sql="Select DISTINCT(Q.CustomerID) as CustomerID,C.CustomerName From ".$this->currfyDB."tbl_enquiry as Q LEFT JOIN tbl_customer as C ON C.CustomerID=Q.CustomerID Where 1=1 ";
		if($req->status){
			$status=json_decode($req->status,true);
			if(count($status)>0){
				$sql.=" and Q.Status in('".implode("','",$status)."')";
			}
		}
		return DB::SELECT($sql);
	}
	public function getSearchQuoteDates(Request $req){
		$sql="Select DISTINCT(Q.EnqDate) as QuoteDate From ".$this->currfyDB."tbl_enquiry as Q LEFT JOIN tbl_customer as C ON C.CustomerID=Q.CustomerID Where 1=1 ";
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
