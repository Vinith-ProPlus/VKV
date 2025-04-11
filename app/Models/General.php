<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class General extends Model
{
    public function __construct($UserID,$ActiveMenuName){
		$this->UserID=$UserID;
		$this->ActiveMenuName=$ActiveMenuName;
		$this->UserInfo=array("UInfo"=>array());
		$result=$this->getUserInfo($this->UserID);
		if(count($result)>0){
			if (!file_exists(__DIR__.$result[0]->ProfileImage)) {
				$result[0]->ProfileImage="";
			}
			if(($result[0]->ProfileImage=="")||($result[0]->ProfileImage==null)){
                if(strtolower($result[0]->Gender)=="female"){
                    $result[0]->ProfileImage="assets/images/female-icon.png";
                }else{
                    $result[0]->ProfileImage="assets/images/male-icon.png";
                }
			}
			$this->UserInfo['UInfo']=$result[0];
			$this->UserInfo['CRUD']=$this->getUserRights($result[0]->RoleID);
		}
		$this->UserInfo['Theme']=$this->getThemesOption($this->UserID);
		$this->UserInfo['Settings']=$this->getSettings();
		$this->UserInfo['Company']=$this->getCompanySettings();
		$this->UserInfo['FY']=$this->FY;
	}
}
