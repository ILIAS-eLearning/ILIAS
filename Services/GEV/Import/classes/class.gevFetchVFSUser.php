<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* 
*
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/


/**
PREPARATION NEEDED:

From the VofueDB, import table 

	"edu_biography" 
	"object_data"
	"object_reference"
	"rbac_ua"
	"udf_text" 
	"usr_data" 
	"vf_crs_data"

into the shadowDB gev_ivimport

*/





class gevFetchFVSUser {
	
	static $UDFFIELDS = array(
		'exit_date' => 13,
		'finaccount' => 11,
		'entry_date' => 12,
		'isMizOfMail' => 10,
		//'UDF_VDNR' => 8,
		'isMizOfADP' => 9,
		'vkey_vfs' => 14,
		'paisy' => 15,
		'bcity' => 16,
		'pos_vfs' => 17,
		'bwvid' => 18,
		'last_wbd_update' => 19,
		'wbd_cert_begin' => 20,
		'wbd_okz' => 21,
		'wbd_status' => 22,
	);

	
	static $POS_WITH_OKZ = array(
		 'orga.AL'
		,'orga.HAL'
		,'orga.BAL'
		,'org.BAL'
		,'orga.GAL'
		,'org.GAL'
		,'orga.AgtD'
		,'org.AgtD'
		,'AA/PV-1'
		,'AL/PV-2'
		,'HAL/PV3'
		,'BAL/PV4'
		,'GAL/PV5'
		,'AgtD/PV6'
		,'AA bAV'
		,'AL bAV'
		,'HAL bAV'
		,'GAL bAV'
		,'AgtD bAV'
		,'AA FDL/WA'
		,'AL FDL/WA'
		,'HAL FDL/WA'
		,'BAL FDL/WA'
		,'GAL FDL/WA'
		,'AgtD FDL/WA'
		,'AA Gewerbe'
		,'AL Gewerbe'
		,'HAL Gewerbe'
		,'BAL Gewerbe'
		,'GAL Gewerbe'
		,'AgtD Gewerbe'
		,'org.HAL bAV'
		,'org.BAL bAV'
		,'org.GAL bAV'
		,'org.AgtD bAV'
		,'org.HAL FDL/WA'
		,'org.BAL FDL/WA'
		,'org.GAL FDL/WA'
		,'org.AgtD FDL/WA'
		,'org.HAL Gewerbe'
		,'org.BAL Gewerbe'
		,'org.GAL Gewerbe'
		,'org.AgtD Gewerbe'
		,'§84-org.A'
		,'§84-org.HA'
		,'§84-org.BA'
		,'§84-org.GA'
		,'§84-org.SD'
		,'§84-HV'
		,'§84-A'
		,'§84-HA'
		,'§84-BA'
		,'§84-GA'
		,'§84-SD'
		,'§84-HV bAV'
		,'§84-A bAV'
		,'§84-HA bAV'
		,'§84-BA bAV'
		,'§84-GA bAV'
		,'§84-SD bAV'
		,'§84-HV FDL/WA'
		,'§84-A FDL/WA'
		,'§84-HA FDL/WA'
		,'§84-BA FDL/WA'
		,'§84-GA FDL/WA'
		,'§84-SD FDL/WA'
		,'§84-HV Gewerbe'
		,'§84-A Gewerbe'
		,'§84-HA Gewerbe'
		,'§84-BA Gewerbe'
		,'§84-GA Gewerbe'
		,'§84-SD Gewerbe'
		,'§84-org.HA bAV'
		,'§84-org.BA bAV'
		,'§84-org.GA bAV'
		,'§84-org.SD bAV'
		,'§84-org.HA FDL'
		,'§84-org.BA FDL'
		,'§84-org.GA FDL'
		,'§84-org.SD FDL'
		,'§84-org.HA Gew.'
		,'§84-org.BA Gew.'
		,'§84-org.GA Gew.'
		,'§84-org.SD Gew.'
		,'§84-Badenia'
		,'BAL bAV'
		,'org.AgtD Gewerb'
		,'AA/PV1'
		,'PV'
		,'org.HAL'
		,'§84-org. GA'
		,'§84-HGB'
		,'HGB § 84 - BA'
		,'PV2'
		,'HGB 84'
		,'AA'

	);


	public function __construct() {
		$this->connectShadowDB();
	}

	private function connectShadowDB(){
		global $ilClientIniFile;
		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass) or die(mysql_error());
		mysql_select_db($name, $mysql);
		mysql_set_charset('utf8', $mysql);

		$this->shadowDB = $mysql;
	}



	public function getAllUsers(){
		$vals = array(
			'usr_data.usr_id as ilid_vfs',
			'login',
			'passwd as pwd',
			'create_date as created',
			'approve_date as approved',
			'last_login',
			'active',
			'time_limit_unlimited as account',
			'gender',
			'firstname',
			'lastname',
			'title',
			'birthday as bday',
			'street',
			'city',
			'zipcode as plz',
			'country',
			'phone_office as fon_mobil',
			'email as mail',
			'matriculation as adp_vfs',
			//'orgunit_name.part_org_unit_title as orgunit_name',
		);
		
		$addjoins = array(
			//"INNER JOIN edu_biography orgunit_name ON usr_data.usr_id=orgunit_name.user_id AND orgunit_name.hist_historic=0 LIMIT 1"
		);

		foreach (self::$UDFFIELDS as $name => $id) {
			$vals[] = "$name.value as $name";
			$addjoins[] = "LEFT JOIN udf_text $name ON usr_data.usr_id=$name .usr_id AND $name.field_id=$id";
		}	

		$sql = "SELECT "
			.implode(',', $vals)
			.' FROM usr_data '
			.implode(' ', $addjoins);
	
		$ret = array();
		$result = mysql_query($sql, $this->shadowDB);
		while($record = mysql_fetch_assoc($result)) {
			$ret[] = $this->mkImportUser($record);
		}
		return $ret;
	}


	public function updateOrgUnitNameForImportedUsers(){
		//the joins from above over edu_biography is maximum memory greedy,
		//and awfully slow. 
		//Thus: separate function

		$sql = "SELECT id, ilid_vfs from interimUsers WHERE ilid_vfs != ''";
		$result = mysql_query($sql, $this->shadowDB);
		while($record = mysql_fetch_assoc($result)) {
			$sql = "SELECT  part_org_unit_title FROM edu_biography WHERE"
				." user_id=" .$record['ilid_vfs']
				." AND hist_historic=0 ORDER BY hist_version DESC LIMIT 1";
			$temp_result = mysql_query($sql, $this->shadowDB);
			$temp_rec = mysql_fetch_assoc($temp_result);
			$sql = "UPDATE interimUsers SET "
				." orgunit_name='" .$temp_rec['part_org_unit_title'] ."'"
				." WHERE id=" .$record['id'];

			mysql_query($sql, $this->shadowDB);
		}

	}


	private function mkImportUser($entry){
		$usr = new gevImportedUser();

		foreach ($entry as $key => $value) {
			$usr->set($key, $value);
		}

		$usr->set('wbd_registered', '1 - Ja');

		switch ($usr->userdata['wbd_okz']){
			case '1 - OKZ1':
			    $usr->set('tp_type', '3 - TP-Service');
			    break;

			case '0 - aus Stellung':
				if(in_array($usr->userdata['pos_vfs'], self::$POS_WITH_OKZ)){
			    	$usr->set('tp_type', '3 - TP-Service');
				} else {
					$usr->set('tp_type', '0 - kein Service');
				}
			    break;
			    
			default:
				$usr->set('tp_type', '0 - kein Service');
			    break;
		}

		return $usr;
	}



	public function fetchUsers(){
		$ret = array();
		$usrs =  $this->getAllUsers();
		return $usrs;
	}



	public function getGlobalRoles(){
		$roles = array();
		$sql = "SELECT obj_id, title FROM object_data WHERE type='role'"
			." AND title NOT LIKE 'il_%'"
			." AND title NOT LIKE 'loc_%'";

		$result = mysql_query($sql, $this->shadowDB);	
		while($record = mysql_fetch_assoc($result)) {
			$roles[$record['obj_id']] = $record['title'];
		}
		mysql_free_result($result);
		return $roles;
	}


	public function getGlobalRolesForUsers(){
		$ret = array();

		$global_roles = array_keys($this->getGlobalRoles());

		$sql = "SELECT usr_id, rol_id FROM rbac_ua"
			." WHERE rol_id IN ("
			.implode(',', $global_roles)
			.')';

		$result = mysql_query($sql, $this->shadowDB);	
		while($record = mysql_fetch_assoc($result)) {
			if(! array_key_exists($record['usr_id'], $ret)){
				$ret[$record['usr_id']] = array();
			}
			$ret[$record['usr_id']][] = $record['rol_id'];
		}
		mysql_free_result($result);
		return $ret;
	}
	


	public function getEduRecordsForImportedUsers(){
		$ret = array();	
		//if we don't have the user, we will not need his/her records...
		$sql = "SELECT id, ilid_vfs FROM interimUsers WHERE ilid_vfs != ''";

		$result = mysql_query($sql, $this->shadowDB);
		while($record = mysql_fetch_assoc($result)) {
			
			$sql = "SELECT * from edu_biography"
				." WHERE user_id = " .$record['ilid_vfs']
				." AND ("
				." hist_historic=0 OR wbd_case_id != '-empty-'"
				.")";
			
			$res = mysql_query($sql, $this->shadowDB);
			while($rec = mysql_fetch_assoc($res)) {

				$rec['is_decentral'] = 0;	
				if($rec['crs_ref_id'] > 0){
					$sql = "SELECT decentral FROM vf_crs_data"
						." INNER JOIN object_reference ON "
						." vf_crs_data.id = object_reference.obj_id"
						." WHERE object_reference.ref_id="
						.$rec['crs_ref_id'];

					$res_temp = mysql_query($sql, $this->shadowDB);
					$rec_temp = mysql_fetch_assoc($res_temp);

					if($rec_temp['decentral']){
						$rec['is_decentral'] = 1;
					}
					mysql_free_result($res_temp);
				
				}


				$ret[] = $rec;
			}
			mysql_free_result($res);

		}
		mysql_free_result($result);
		return $ret;
	}


}