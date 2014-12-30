<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* 
*
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/


require_once("Services/GEV/Import/classes/class.gevImportedUser.php");


class gevFetchGEVUser {
	
	public function __construct() {
		global $ilUser, $ilDB;
		$this->db = &$ilDB;
		$this->user = &$ilUser;

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

	private function getAllUserIds(){
		$ret = array();
		$sql = 'SELECT usr_id FROM usr_data';
		$result = $this->db->query($sql);
		while($record = $this->db->fetchAssoc($result)) {
			$ret[] = $record['usr_id'];
		}
		return $ret;
	}
	

	private function getAllUsers(){
		require_once("Services/User/classes/class.ilObjUser.php");
		$ret = array();
		$usr_ids = $this->getAllUserIds();
		foreach ($usr_ids as $usr_id) {
			$ret[] = new ilObjUser($usr_id);
		}
		return $ret;
	}


	private function getLastWBDUpdate($usr_id){
		$sql = 'SELECT last_wbd_report FROM hist_user'
		//." WHERE last_wbd_report IS NOT NULL"
		." WHERE user_id = $usr_id"
		." ORDER BY hist_version DESC";
		$result = $this->db->query($sql);
		$record = $this->db->fetchAssoc($result);
		return $record['last_wbd_report'];
	}



	private function mkImportUser($entry){
		$usr = new gevImportedUser();

		$usr->set('ilid_gev', $entry->getId());
		$usr->set('login', $entry->getLogin());
		$usr->set('pwd', $entry->getPasswd());
		$usr->set('created', $entry->getCreateDate());
		$usr->set('approved', $entry->getApproveDate());
		$usr->set('last_login', $entry->getLastLogin());
//$usr->set('owner', $entry->getLastLogin());
		$usr->set('active', $entry->getActive());
		$usr->set('account', $entry->getTimeLimitUnlimited()); // zeitlich begrenzt?
		$usr->set('gender', $entry->getGender()); 
		$usr->set('firstname', $entry->getFirstname());	//Vorname
		$usr->set('lastname', $entry->getLastname());		//Nachname
		$usr->set('title', $entry->getUTitle());		//Titel
		$usr->set('bday', $entry->getBirthday());			//Geburtstag
		$usr->set('street', $entry->getStreet());		//Straße
		$usr->set('city', $entry->getCity());			//Ort
		$usr->set('plz', $entry->getZipcode());			//Postleitzahl
//$usr->set('country', $entry->getCountry());		//Land
		$usr->set('fon_work', $entry->getPhoneOffice());	//Telefon Arbeit
		//$usr->set('fon_mobil', $entry->getPhoneMobile());	//Telefon Mobil
		$usr->set('fon_mobil',  $entry->user_defined_data['f_14']); //ex fon privat, jetzt phone mobile
		$usr->set('mail', $entry->getEmail());			//E-Mail

		$usr->set('adp_gev', $entry->user_defined_data['f_1']);		//ADP-Nummer GEV
		//$usr->set('adp_vfs', $entry->getGender());		//ADP-Nummer VFS
		$usr->set('vnr_gev', $entry->user_defined_data['f_2']);		//Vermittlernummer GEV
		$usr->set('bcity', $entry->user_defined_data['f_3']);		//Geburtsort
		$usr->set('bname', $entry->user_defined_data['f_4']);		//Geburtstname
		$usr->set('ihknr', $entry->user_defined_data['f_5']);		//IHK Registernummer
		$usr->set('ad_title', $entry->user_defined_data['f_6']);		//AD-Titel
		$usr->set('vkey_gev', $entry->user_defined_data['f_7']);		//Vermittlerschlüssel GEV
		//$usr->set('vkey_vfs', $entry->getGender());		//Stellungsschlüssel VFS
		//$usr->set('pos_vfs', $entry->getGender());		//Stellung VFS
		//$usr->set('paisy', $entry->getGender());		//Paisy-Personalnummer VFS
		//$usr->set('finaccount', $entry->getGender());	//Kostenstelle VFS
		$usr->set('mail_priv', $entry->user_defined_data['f_9']);	//Emailadresse (privat)
		$usr->set('street_priv', $entry->user_defined_data['f_10']);	//Straße (privat)
		$usr->set('city_priv', $entry->user_defined_data['f_11']);	//Ort (privat)
		$usr->set('plz_priv', $entry->user_defined_data['f_12']);		//Postleitzahl (privat)
		$usr->set('entry_date', $entry->user_defined_data['f_16']);	//Eintrittsdatum
		$usr->set('exit_date', $entry->user_defined_data['f_17']);	//Austrittsdatum
		$usr->set('tp_type', $entry->user_defined_data['f_20']);	//TP-Typ
		$usr->set('bwvid', $entry->user_defined_data['f_21']);		//BWV-ID
		$usr->set('wbd_okz', $entry->user_defined_data['f_22']);	//Zuweisung WBD OKZ
		$usr->set('wbd_status', $entry->user_defined_data['f_23']);	//Zuweisung WBD Vermittlerstatus
		$usr->set('wbd_cert_begin', $entry->user_defined_data['f_24']);//Beginn erste Zertifizierungsperiode
		$usr->set('wbd_registered', $entry->user_defined_data['f_25']);//Hat WBD-Registrierung durchgeführt
		$usr->set('mail_wbd', $entry->user_defined_data['f_26']);	//Email WBD

		$usr->set('last_wbd_update', $this->getLastWBDUpdate($entry->getId()));	//lastWBDReport

//orgunit_name
		return $usr;
	}


	public function fetchUsers(){
		$ret = array();
		$ilsusrs =  $this->getAllUsers();

		foreach ($ilsusrs as $entry) {
			$ret[] = $this->mkImportUser($entry);
		}

		return $ret;

		//roles, udf...
	}
	
	public function updateOrgUnitNameForImportedUsers(){
		//the joins from above over edu_biography is maximum memory greedy,
		//and awfully slow. 
		//Thus: separete function

		$sql = "SELECT id, ilid_gev from interimUsers WHERE ilid_gev != ''";
		$result = mysql_query($sql, $this->shadowDB);
		while($record = mysql_fetch_assoc($result)) {
			$sql = "SELECT org_unit FROM hist_user WHERE"
				." user_id=" .$record['ilid_gev']
				." AND hist_historic=0 ORDER BY hist_version DESC LIMIT 1";

			$temp_result = $this->db->query($sql);
			$temp_rec = $this->db->fetchAssoc($temp_result);

			$sql = "UPDATE interimUsers SET "
				." orgunit_name='" .$temp_rec['org_unit'] ."'"
				." WHERE id=" .$record['id'];
				
			mysql_query($sql, $this->shadowDB);
		}
	}

	
	public function getGlobalRoles(){
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$role_utils = gevRoleUtils::getInstance();
		$roles = $role_utils->getGlobalRoles();
		return $roles;
	}

	public function getGlobalRolesForUsers(){
		require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
		$ret = array();
		$role_utils = gevRoleUtils::getInstance();

		$usr_ids = $this->getAllUserIds();
		foreach ($usr_ids as $usr_id) {
			$ret[$usr_id] = $role_utils->getGlobalRolesOf($usr_id);
		}
		return $ret;
	}
	
	public function getEduRecordsForImportedUsers(){
		/*$sql = "SELECT ilid_vfs FROM interimUsers WHERE ilid_gev != ''";
		$result = mysql_query($sql, $this->shadowDB);
		$user_ids = array();
		while($record = mysql_fetch_assoc($result)) {
			$user_ids[]=$record['ilid_vfs'];
		}

		$sql = "SELECT * from hist_usercoursestatus"
			." WHERE user_id IN (" 
			.implode(',', $user_ids)
			.") AND ("
			." hist_historic=0 OR wbd_booking_id IS NOT NULL"
			.")";
			//I looked: there are no historic records with booking-id. (nlz)
		$result = $this->db->query($sql);
		while($record = mysql_fetch_assoc($result)) {
			$user_ids[]=$record['ilid_vfs'];
		}
		*/

		$ret = array();
		$sql = "SELECT *"
			.", hist_usercoursestatus.begin_date as usr_begin_date"
			.", hist_usercoursestatus.end_date as usr_end_date"
			." FROM hist_usercoursestatus"
			." INNER JOIN hist_course ON hist_usercoursestatus.crs_id = hist_course.crs_id "
			." AND hist_course.hist_historic=0 AND is_template='Nein'"
			." WHERE (hist_usercoursestatus.hist_historic=0 OR wbd_booking_id IS NOT NULL)"
			." AND hist_usercoursestatus.function IN ('Mitglied', 'canceled')";

		$result = $this->db->query($sql);
		while($record = $this->db->fetchAssoc($result)) {
			$ret[] = $record;
		}
		return $ret;
	}



}
