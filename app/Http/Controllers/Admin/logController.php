<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use DocNum;
use docTypes;
use Helper;
class logController extends Controller{

	public static function Store($data){
		$LogDB=Helper::getLogTableName();
		$data['OldData']=json_decode(json_encode($data['OldData']),true);
		$data['NewData']=json_decode(json_encode($data['NewData']),true);
        $tdata=array(
			'LogID'=>DocNum::getDocNum(docTypes::Log->value),
			'Description'=>$data['Description'],
			'ModuleName'=>$data['ModuleName'],
			'Action'=>$data['Action'],
			'ReferID'=>$data['ReferID'],
			'OldData'=>serialize($data['OldData']),
			'NewData'=>serialize($data['NewData']),
			'IPAddress'=>$data['IP'],
			'UserID'=>$data['UserID'],
			'logTime'=>date("Y-m-d H:i:s")
		);
		$status=DB::Table($LogDB)->insert($tdata);
		if($status){
			DocNum::updateDocNum(docTypes::Log->value);
		}
	}
}
