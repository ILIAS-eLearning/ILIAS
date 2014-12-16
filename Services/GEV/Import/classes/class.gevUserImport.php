<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* 
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/



//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/Import/classes', '', $basedir);
chdir($basedir);

//SIMPLE SEC !
require "./Customizing/global/skin/genv/Services/GEV/simplePwdSec.php";

//context w/o user
require_once "./Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();


//settings and imports
ini_set("memory_limit","1024M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);
require_once("Services/GEV/Import/classes/class.gevImportedUser.php");



class gevUserImport {
	
	private $shadowDB = NULL;
	private $fetchers = array(
			'VFS' => NULL,
			'GEV' => NULL
		);


	public function __construct() {
		$this->connectShadowDB();
		$this->createDB();
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

	private function queryShadowDB($sql){
		//$this->prnt($sql);
		$result = mysql_query($sql, $this->shadowDB);
		if(!$result){
			print $sql;
			die("<br>ERROR WHILE DOING QUERY ABOVE");
		}
		if(substr($sql, 0, 6) == 'SELECT'){
			return $result;
		} else {
			//mysql_free_result($result);
		}
	}
	
	private function entryExistsInInterimsDB($table, $field, $value) {
		$sql = "SELECT id FROM $table WHERE $field='$value'";
		$result = $this->queryShadowDB($sql);
		if($result && mysql_num_rows($result) >0){
			mysql_free_result($result);
			return true;
		}
		mysql_free_result($result);	
		return false;
	}

	private function getInterimsId($table, $field, $searchvalue) {
		$sql = "SELECT id FROM $table WHERE $field='$searchvalue'";
		$result = $this->queryShadowDB($sql);
		$record = mysql_fetch_assoc($result);
		mysql_free_result($result);
		return $record['id'];
	}

	private function getNextCourseId() {
		//! negative courseIds
		$sql = "SELECT crs_id FROM interimCourse ORDER BY crs_id ASC LIMIT 1";
		$result = $this->queryShadowDB($sql);
		if(mysql_num_rows($result) == 0){
			$crs_id	= -999;
		} else {
			$record = mysql_fetch_assoc($result);
			mysql_free_result($result);
		}
		$crs_id = (int)$record['crs_id'] -1;
		return $crs_id;
	}





	private function getFetchterVFS(){
		if(! $this->fetchers['VFS']){
			require_once("Services/GEV/Import/classes/class.gevFetchVFSUser.php");
			$this->fetchers['VFS'] = new gevFetchFVSUser();
		}
		return $this->fetchers['VFS'];
	}
	private function getFetchterGEV(){
		if(! $this->fetchers['GEV']){
			require_once("Services/GEV/Import/classes/class.gevFetchGEVUser.php");
			$this->fetchers['GEV'] = new gevFetchGEVUser();
		}
		return $this->fetchers['GEV'];
	}



	private function prnt($m, $mode=0){
		switch ($mode){
			case -1:
				print $m;
				break;
			case 1:
				print '<hr><h2>' .$m .'</h2>';
				break;
			case 2:
				print '<br><br><b>' .$m .'</b>';
				break;
			case 3:
				print '<br><b><i>' .$m .'</i></b>';
				break;
			case 666:
				print '<pre>';
				print_r($m);
				print '</pre>';
				break;

			default:
				print '<br> &nbsp; &nbsp; ' .$m;
		} 
		flush();
	}


	private function createDB(){
		//users
		$fields = gevImportedUser::$USERFIELDS;
		$fstring = implode(' varchar(128) DEFAULT NULL,', $fields);
		$fstring .= ' varchar(128) DEFAULT NULL,';

		$sql = "CREATE TABLE IF NOT EXISTS interimUsers ("
			." id int(11) NOT NULL AUTO_INCREMENT,"
			.$fstring
		  	." PRIMARY KEY (id)"
			." ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0";

		$this->queryShadowDB($sql);

		//roles
		$sql = "CREATE TABLE IF NOT EXISTS interimRoles ("
			." id int(11) NOT NULL AUTO_INCREMENT,"
			." ilid varchar(128) COLLATE utf8_unicode_ci NOT NULL,"
			." ilid_vfs varchar(128) COLLATE utf8_unicode_ci NOT NULL,"
			." ilid_gev varchar(128) COLLATE utf8_unicode_ci NOT NULL,"
			." title varchar(128) COLLATE utf8_unicode_ci NOT NULL,"
			." PRIMARY KEY (id)"
			.") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1";
		$this->queryShadowDB($sql);


		//userroles
		$sql = "CREATE TABLE IF NOT EXISTS interimUserRoles ("
		  	." interim_usr_id int(11) NOT NULL,"
		  	." interim_role_id int(11) NOT NULL"
			.") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$this->queryShadowDB($sql);

		//hist_usercoursestatus
		$sql = "CREATE TABLE IF NOT EXISTS interimUsercoursestatus ("
		  ." row_id int(11) NOT NULL,"
		  ." usr_id_vfs int(11) NOT NULL,"
		  ." usr_id_gev int(11) NOT NULL,"
		  ." hist_version int(11) NOT NULL DEFAULT '1',"
		  ." hist_historic int(11) NOT NULL DEFAULT '0',"
		  ." created_ts int(11) NOT NULL DEFAULT '0',"
		  ." last_wbd_report date DEFAULT NULL,"
		  ." crs_id int(11) NOT NULL," //matches interimCourse.crs_id
		  ." credit_points int(11) NOT NULL,"
		  ." bill_id varchar(16) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." booking_status varchar(255) COLLATE utf8_unicode_ci NOT NULL,"
		  ." participation_status varchar(255) COLLATE utf8_unicode_ci NOT NULL,"
		  ." okz varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." org_unit varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." certificate int(11) DEFAULT NULL,"
		  ." begin_date date DEFAULT NULL,"
		  ." end_date date DEFAULT NULL,"
		  ." overnights int(11) DEFAULT NULL,"
		  ." function varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." wbd_booking_id varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." PRIMARY KEY (row_id)"
		." ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
		$this->queryShadowDB($sql);

		//hist_course
		$sql = "CREATE TABLE IF NOT EXISTS interimCourse ("
		  ." row_id int(11) NOT NULL,"
//		  ." hist_version int(11) NOT NULL DEFAULT '1',"
//		  ." hist_historic int(11) NOT NULL DEFAULT '0',"
		  ." crs_id int(11) NOT NULL," //matches interimUsercoursestatus.crs_id
//		  ." created_ts int(11) NOT NULL DEFAULT '0',"
		  ." custom_id varchar(255) COLLATE utf8_unicode_ci NOT NULL,"
		  ." title varchar(255) COLLATE utf8_unicode_ci NOT NULL,"
//		  ." template_title varchar(255) COLLATE utf8_unicode_ci NOT NULL,"
		  ." type varchar(255) COLLATE utf8_unicode_ci NOT NULL,"
		  ." topic_set int(11) NOT NULL,"
		  ." begin_date date DEFAULT NULL,"
		  ." end_date date DEFAULT NULL,"
		  ." hours int(11) DEFAULT '0',"
		  ." is_expert_course tinyint(4) NOT NULL DEFAULT '0',"
		  ." venue varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." provider varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
//		  ." tutor varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." max_credit_points varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." fee double DEFAULT NULL,"
//		  ." is_template varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." wbd_topic varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." edu_program varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,"
		  ." PRIMARY KEY (row_id)"
		." ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		$this->queryShadowDB($sql);

	}






	private function addSingleUserToInterimsDB($usr) {
		$fields = gevImportedUser::$USERFIELDS;
		$sql = "INSERT INTO interimUsers ("
			.implode(', ', $fields)
			.") VALUES ('"
			.implode("', '", $usr->userdata)
			."')";

		$this->queryShadowDB($sql);
	}


	private function updateSingleUserInInterimsDB($usr) {
		$id_field = ($usr->userdata['ilid_gev'] != '') ? 'ilid_gev' : 'ilid_vfs';
		$id = $usr->userdata[$id_field];

		$fbuffer = array();
		foreach ($usr->userdata as $field=>$val){
			$fbuffer[] = "$field='$val'";
		}
		$sql = "UPDATE interimUsers SET "
			.implode(', ', $fbuffer)
			." WHERE $id_field='$id'";

		$this->queryShadowDB($sql);
	}


	private function userExistsInInterimsDB($usr) {
		$id_field = ($usr->userdata['ilid_gev'] != '') ? 'ilid_gev' : 'ilid_vfs';
		$id = $usr->userdata[$id_field];
		return $this->entryExistsInInterimsDB('interimUsers', $id_field, $id);
	}


	private function storeUsersToInterimsDB($users) {
		$fields = gevImportedUser::$USERFIELDS;
		foreach ($users as $usr) {
			$this->prnt($usr->userdata['login'] .': ');
			if($this->userExistsInInterimsDB($usr)){
				$this->updateSingleUserInInterimsDB($usr);
				$this->prnt('update', -1);
			} else {
				$this->addSingleUserToInterimsDB($usr);
				$this->prnt('insert', -1);
			}
		}
	}


	private function storeGlobalRolesToInterimsDB($roles, $client) {
		$this->prnt('store global roles', 3);

		$id_field = 'ilid_' .strtolower($client);
		foreach ($roles as $old_il_id => $title) {
			$this->prnt($title .': ');
			if($this->entryExistsInInterimsDB('interimRoles', $id_field, $old_il_id)){
				$sql = "UPDATE interimRoles SET title='$title'"
					." WHERE $id_field=$old_il_id";
				$this->prnt('update', -1);
			} else {
				$sql = "INSERT INTO interimRoles ($id_field, title)"
					." VALUES ('$old_il_id', '$title')";
				$this->prnt('insert', -1);
			}
			$this->queryShadowDB($sql);
		}
	}

	private function storeUserRolesToInterimsBD($userroles, $client) {
		$this->prnt('store roles for users', 3);
		
		$id_field = 'ilid_' .strtolower($client);

		foreach ($userroles as $old_usr_id => $old_role_ids) {
			$interims_user_id = $this->getInterimsId('interimUsers', $id_field, $old_usr_id);
			
			if(! $interims_user_id){
				$this->prnt('<b>NO USER with id '. $old_usr_id .'</b>');
			} else {
				$this->prnt($interims_user_id .': ');
				
				$sql = "DELETE FROM interimUserRoles WHERE interim_usr_id=$interims_user_id";
				$this->queryShadowDB($sql);

				foreach ($old_role_ids as $old_role_id) {
					$interims_role_id = $this->getInterimsId('interimRoles', $id_field, $old_role_id);
					$sql = "INSERT INTO interimUserRoles (interim_usr_id, interim_role_id)"
						." VALUES ($interims_user_id, $interims_role_id)";
					$this->queryShadowDB($sql);
					
					$this->prnt($interims_role_id.',', -1);
				}
			}
		}
	}



	private function normalizeCourseEntry($entry, $client){
		if($client == 'VFS'){
			/*
			course:
				.$entry['custom_id'] .","
				.$entry['title'] .","
				.$entry['type'] ."," //selbstlern...

				.$entry['begin'] ."," 
				.$entry['end'] ."," 

				.$entry['topic_set'] ."," 
				.$entry['hours'] ."," 
				.$entry['venue'] ."," 
				.$entry['provider'] ."," 
				
				.$entry['max_credit_points'] ."," 
				.$entry['fee'] ."," 
				.$entry['wbd_topic'] ."," 
				.$entry['edu_program']
			*/

			$entry['title'] = $entry['crs_template_title'];
			$entry['begin_date'] = $entry['crs_start_date'];
			$entry['end_date'] = $entry['crs_end_date'];
			/*
			user:
				.$entry['old_usr_id'] .","

				.$entry['usr_begin_date'] .","
				.$entry['usr_end_date'] ."," 

				.$entry['hist_version'] ."," 
				.$entry['hist_historic'] ."," 
				.$entry['created_ts'] ."," 
				.$entry['last_wbd_report'] ."," 

				.$entry['credit_points'] ."," 
				.$entry['bill_id'] ."," 
				.$entry['booking_status'] ."," 
				.$entry['participation_status'] ."," 

				.$entry['function'] ."," 
				.$entry['wbd_booking_id']
			*/

			$entry['old_usr_id'] = $entry['user_id'];
			$entry['usr_begin_date'] = $entry['crs_start_date'];
			$entry['usr_end_date'] = $entry['crs_end_date'];
			$entry['last_wbd_report']  = $entry['wbd_transfer_ts'];
			$entry['credit_points']  = $entry['crs_credit_points'];

			$entry['bill_id']  = '';
			$entry['booking_status']  = $entry['part_booking_state_title'];
			$entry['participation_status']  = $entry['part_participation_state_title'];
			$entry['function']  = $entry['part_function_title'];
			$entry['wbd_booking_id']  = $entry['wbd_case_id'];

		}
		if($client == 'GEV'){
			$entry['old_usr_id'] = $entry['usr_id'];
		}


		return $entry;
	}


	private function storeCourseToInterimsBD($entry){
		//to interimCourse
		//is there a course with this title and dates?
		//return crs_id
		$title = $entry['title'];
		$begin_date = $entry['begin'];
		$end_date = $entry['end'];

		$sql = "SELECT crs_id FROM interimCourse WHERE 	
			title = '$title'
			AND
			begin_date = '$begin_date'
			AND 
			end_date = '$end_date'
		";
		$result = $this->queryShadowDB($sql);
		if($result && mysql_num_rows($result) > 0){
			//found a matching course, return id.
			$record = mysql_fetch_assoc($result);
			mysql_free_result($result);
			return $record['crs_id'];
		}

		//new course
		$next_id = $this->getNextCourseId();
		$sql = "INSERT INTO interimCourse ("
					."crs_id,"
					."custom_id,"
					."title,"
					."type,"

					."begin_date,"
					."end_date,"

					."topic_set,"
					."hours,"
					."venue,"
					."provider,"

					."max_credit_points,"
					."fee,"
					."wbd_topic,"
					."edu_program"
				.") VALUES ("
					.$next_id .","
					.$entry['custom_id'] .","
					.$entry['title'] .","
					.$entry['type'] ."," //selbstlern...

					.$entry['begin'] ."," 
					.$entry['end'] ."," 

					.$entry['topic_set'] ."," 
					.$entry['hours'] ."," 
					.$entry['venue'] ."," 
					.$entry['provider'] ."," 
					
					.$entry['max_credit_points'] ."," 
					.$entry['fee'] ."," 
					.$entry['wbd_topic'] ."," 
					.$entry['edu_program']
				.")";

		$this->queryShadowDB($sql);

		return $next_id;
	}



	private function storeEduRecordForUser($crs_id, $entry, $client){
		//gets new crs-id which matches interimCourse.crs_id


		// !check, if user has a relation to that course already!
		

		$sql = "INSERT INTO interimUsercoursestatus ("
				. "usr_id_" .strtolower($client) .","
				." crs_id," //matches interimCourse.crs_id

				." begin_date,"
				." end_date,"

				." hist_version,"
				." hist_historic,"
				." created_ts,"
				." last_wbd_report,"

				." credit_points,"
				." bill_id,"
				." booking_status,"
				." participation_status,"

				." okz,"
				." org_unit,"
				." certificate,"
				." overnights,"

				." function,"
				." wbd_booking_id"

			.") VALUES ("
				.$entry['old_usr_id'] .","
				.$crs_id .","

				.$entry['usr_begin_date'] .","
				.$entry['usr_end_date'] ."," 

				.$entry['hist_version'] ."," 
				.$entry['hist_historic'] ."," 
				.$entry['created_ts'] ."," 
				.$entry['last_wbd_report'] ."," 

				.$entry['credit_points'] ."," 
				.$entry['bill_id'] ."," 
				.$entry['booking_status'] ."," 
				.$entry['participation_status'] ."," 

				.$entry['function'] ."," 
				.$entry['wbd_booking_id']
			.")";

			$this->queryShadowDB($sql);
	}





	// --------------------------------------------

	/**
	* VOLKSFÜRSORGE
	*/
	public function fetchVFSUsers(){
		$this->prnt('Fetching and updating VFS users', 1);
		
		$fetcher = $this->getFetchterVFS();
		$users = $fetcher->fetchUsers();
		$this->storeUsersToInterimsDB($users);
		$fetcher->updateOrgUnitNameForImportedUsers();

		$this->prnt('Fetching and updating VFS users: done', 2);
	}

	public function fetchVFSUserRoles(){
		$this->prnt('Fetching roles for VFS-users', 1);
		
		$fetcher = $this->getFetchterVFS();
		//global roles
		$all_roles = $fetcher->getGlobalRoles();
		$this->storeGlobalRolesToInterimsDB($all_roles, 'VFS');
		//user roles
		$user_roles = $fetcher->getGlobalRolesForUsers();
		$this->storeUserRolesToInterimsBD($user_roles, 'VFS');
			
		$this->prnt('Fetching roles for VFS-users: done', 2);
	}


	public function fetchVFSEduRecords(){
		$this->prnt('Fetching VFS EduRecords', 1);
		
		$fetcher = $this->getFetchterVFS();
		$edu_records = $fetcher->getEduRecordsForImportedUsers();
		$this->prnt($edu_records, 666);
die();
		foreach ($edu_records as $entry) {
			$entry = $this->normalizeCourseEntry($entry, 'VFS');
		//	$crs_id = $this->storeCourseToInterimsBD($entry);
		//  $this->storeEduRecordForUser($crs_id, $entry, 'VFS');
			$this->prnt($entry, 666);
		}

		$this->prnt('Fetching VFS EduRecords: done', 2);
	}


	/**
	* GENERALI
	*/
	public function fetchGEVUsers(){
		$this->prnt('Fetching and updating GEV users', 1);
		
		$fetcher = $this->getFetchterGEV();
		$users = $fetcher->fetchUsers();
		$this->storeUsersToInterimsDB($users);
		$fetcher->updateOrgUnitNameForImportedUsers();
		
		$this->prnt('Fetching and updating GEV users: done', 2);
	}
	
	public function fetchGEVUserRoles(){
		$this->prnt('Fetching roles for GEV-users', 1);
		
		$fetcher = $this->getFetchterGEV();
		//global roles
		$all_roles = $fetcher->getGlobalRoles();
		$this->storeGlobalRolesToInterimsDB($all_roles, 'GEV');
		//user roles
		$user_roles = $fetcher->getGlobalRolesForUsers();
		$this->storeUserRolesToInterimsBD($user_roles, 'GEV');
			
		$this->prnt('Fetching roles for GEV-users: done', 2);
	}
	

	public function fetchGEVEduRecords(){
		$this->prnt('Fetching GEV EduRecords', 1);
		
		$fetcher = $this->getFetchterGEV();
	
		$this->prnt('Fetching GEV EduRecords: done', 2);
	}



	/**
	* GOA2
	*/
	public function createOrgStructure(){
		$this->prnt('Creating OrgUnits', 1);
		require_once("Services/GEV/Import/classes/class.gevImportOrgStructure.php");
		$importer = new gevImportOrgStructure();
		$importer->createOrgUnits();
		$this->prnt('Creating OrgUnits: done', 2);
	}





}


$imp = new gevUserImport();

//$imp->createOrgStructure();

//$imp->fetchGEVUsers();
//$imp->fetchGEVUserRoles();
//$imp->fetchGEVEduRecords();

//$imp->fetchVFSUsers();
//$imp->fetchVFSUserRoles();
//$imp->fetchVFSEduRecords();



print '<br><br><hr>all through.';
