<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* 
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

require_once("Services/GEV/Import/classes/class.gevImportedUser.php");


require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevNAUtils.php");
require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
//settings and imports
ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



class gevUserImport {
	
	public $webmode = true;

	private $shadowDB = NULL;
	private $ilDB = NULL;

	private $fetchers = array(
			'VFS' => NULL,
			'GEV' => NULL
		);


	public function __construct() {
		global $ilDB;

		$this->connectShadowDB();
		$this->createDB();

		$this->ilDB = &$ilDB;

		$this->role_utils = gevRoleUtils::getInstance();
		$this->na_utils = gevNAUtils::getInstance();

		$this->global_roles = $this->role_utils->getGlobalRoles();
		$this->orgu_superior_roles = array();
		foreach (gevSettings::$VMS_ROLE_MAPPING as $key => $value) {
			if($value[1] == 'Vorgesetzter' && ! in_array($value[0], $this->orgu_superior_roles)){
				$this->orgu_superior_roles[] = $value[0];
			}	
		}
		


	}


	private function connectShadowDB(){
		global $ilClientIniFile;
		$host = $ilClientIniFile->readVariable('shadowdb', 'host');
		$user = $ilClientIniFile->readVariable('shadowdb', 'user');
		$pass = $ilClientIniFile->readVariable('shadowdb', 'pass');
		$name = $ilClientIniFile->readVariable('shadowdb', 'name');

		$mysql = mysql_connect($host, $user, $pass) 
				or die( "MySQL: ".mysql_error()." ### "
						." Is the shadowdb initialized?"
						." Are the settings for the shadowdb initialized in the client.ini.php?"
					  );
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
			$crs_id = (int)$record['crs_id'];
		}
		$crs_id = $crs_id -1;
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
		if($this->webmode){
			$m = str_replace('<br>', "\n", $m);
		}
		switch ($mode){
			case -1:
				print $m;
				break;
			case 1:
				if($this->webmode){
					print '<hr><h2>' .$m .'</h2>';
				} else {
					print "--------------------\n";
					print '*** ' .$m ." ***\n";
				}
				break;
			case 2:
				if($this->webmode){
					print '<br><br><b>' .$m .'</b>';
				} else {
					print "\n";
					print ' - ' .$m ." \n";	
				}
				break;
			case 3:
				if($this->webmode){
					print '<br><b><i>' .$m .'</i></b>';
				} else {
					print "\n";
					print ' > ' .$m ." \n";	
				}
				break;
			case 666:
				print '<pre>';
				print_r($m);
				print '</pre>';
				break;

			default:
				if($this->webmode){
					print '<br> &nbsp; &nbsp; ' .$m;
				} else {
					print "\n ..." .$m;
				}
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
		  ." row_id int(11) NOT NULL AUTO_INCREMENT,"
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
		  ." row_id int(11) NOT NULL AUTO_INCREMENT,"
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
		  ." is_decentral tinyint(4) NOT NULL DEFAULT '0',"
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


	private function getTopicSetFor($topic_title){
		$sql = "SELECT topic_set_id FROM hist_topicset2topic"
			." INNER JOIN hist_topics ON hist_topicset2topic.topic_id = hist_topics.topic_id"
			." WHERE hist_topics.topic_title = '$topic_title'";
			
		$result = $this->ilDB->query($sql);
		if($this->ilDB->numRows($result) == 0){
			//insert new topic set
			//and return its id
			return $this->insertNewTopicSetWithSingleTopic($topic_title);
		}

		$rec = $this->ilDB->fetchAssoc($result);

		$topic_set_id = $rec['topic_set_id'];

		//check, if there are other entries in the topic_set
		$sql = "SELECT COUNT(topic_set_id) AS no_ts FROM hist_topicset2topic"
			." WHERE topic_set_id = " .$topic_set_id;
		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);
		if($record['no_ts'] != 1){
			//insert new topic_set
			return $this->insertNewTopicSetWithSingleTopic($topic_title);
		}

		//$this->prnt('got topicSet for ' .$topic_title .' : ' . $topic_set_id, 3);
		return $topic_set_id;
	}




	private function getTopicIdByTitle($topic_title){
		$sql = "SELECT topic_id FROM hist_topics"
			." WHERE topic_title= '$topic_title'";
		$result = $this->ilDB->query($sql);
		if($this->ilDB->numRows($result) > 0){
			$record = $this->ilDB->fetchAssoc($result);
			return $record['topic_id'];
		}

		//insert new
		$this->prnt('new topic: ' .$topic_title, 3);
		$id = $this->ilDB->nextId('hist_topics');
		$this->ilDB->insert(
			'hist_topics',
			array(
				'row_id'      => array( 'integer', $id ),
				'topic_id'    => array( 'integer', $id ),
				'topic_title' => array( 'text', $topic_title )
			)
		);
		return $id;


	}

	private function insertNewTopicSetWithSingleTopic($topic_title){

		$this->prnt('new topicset: ' .$topic_title, 3);

		$topic_set_id = $this->ilDB->nextId( 'hist_topicset2topic' );
		$topic_id = $this->getTopicIdByTitle($topic_title);

		$this->ilDB->insert(
			'hist_topicset2topic',
			array(
				'row_id'       => array( 'integer', $topic_set_id),
				'topic_set_id' => array( 'integer', $topic_set_id ),
				'topic_id'     => array( 'integer', $topic_id )
			)
		);

		return $topic_set_id;
	}





	private function getLiveTopicSetId($old_topic_set_id){
		//from shadowdb, get topic titles
		$sql = "SELECT hist_topics.topic_id, topic_title"
			." FROM hist_topicset2topic"
			." INNER JOIN hist_topics ON hist_topicset2topic.topic_id = hist_topics.topic_id"
			." WHERE hist_topicset2topic.topic_set_id = $old_topic_set_id";
			
		$result = $this->queryShadowDB($sql);
		
		$topic_ids = array();
		while ($record = mysql_fetch_assoc($result)){
			$topic_ids[] = $this->getTopicIdByTitle($record['topic_title']); //this operate on the live-db
		}

		//from live-db, get matching topic_set
		$ts_index = array();
		$topic_index = array();

		$sql = "SELECT * FROM hist_topicset2topic ORDER BY topic_set_id";
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)){
			if(! array_key_exists($record['topic_set_id'], $ts_index)){
				$ts_index[$record['topic_set_id']] = array();
			}
			$ts_index[$record['topic_set_id']][] = $record['topic_id'];
		}

		foreach ($ts_index as $ts => $topics) {
			$t_index = implode('#', $topics);
			$topic_index[$t_index] = $ts;
		}

		$topics_compare = implode('#', $topic_ids);
		if (array_key_exists($topics_compare, $topic_index)){
			return $topic_index[$topics_compare];
		}
		//otherwise, insert into topic_sets....
		$topic_set_id = -1;

		foreach ($topic_ids as $tid) {
			$row_id = $this->ilDB->nextId('hist_topicset2topic');
			if($topic_set_id == -1){
				$topic_set_id = $row_id;
			}
			
			$this->ilDB->insert(
				'hist_topicset2topic',
				array(
					'row_id'       => array( 'integer', $row_id),
					'topic_set_id' => array( 'integer', $topic_set_id ),
					'topic_id'     => array( 'integer', $tid )
				)
			);
		}
		return $topic_set_id;

	}





	private function normalizeCourseEntry($entry, $client){
		if($client == 'VFS'){

			$begin_date = date('Y-m-d', $entry['crs_start_date']);
			$end_date = date('Y-m-d', $entry['crs_end_date']);
		
			$entry['topic_set'] = $this->getTopicSetFor($entry['crs_topic_title']);

			$entry['custom_id'] = '';
			$entry['is_expert_course'] = 0;
			$entry['hours'] = -1;
			$entry['edu_program'] = '';
			$entry['bill_id']  = -1;
			$entry['certificate']  = -1;

			$entry['begin_date'] = $begin_date;
			$entry['end_date'] = $end_date;
			$entry['usr_begin_date'] = $begin_date;
			$entry['usr_end_date'] = $end_date;

			$entry['last_wbd_report']  = $entry['wbd_transfer_ts'];
			$entry['old_usr_id'] = $entry['user_id'];
			$entry['title'] = $entry['crs_template_title'];
			$entry['type'] = $entry['crs_type_title'];
			$entry['venue'] = $entry['crs_venue_title'];
			$entry['provider'] = $entry['crs_provider_title'];
			$entry['fee'] = $entry['crs_cost_per_part'];
			$entry['max_credit_points']  = $entry['crs_credit_points'];
			$entry['booking_status']  = $entry['part_booking_state_title'];
			$entry['participation_status']  = $entry['part_participation_state_title'];
			$entry['function']  = $entry['part_function_title'];
			$entry['wbd_booking_id']  = $entry['wbd_case_id'];
			$entry['okz']  = $entry['part_okz'];
			$entry['org_unit']  = $entry['part_org_unit_title'];
			$entry['overnights']  = $entry['part_accomodation_nights'];
			$entry['wbd_topic'] =  $entry['crs_wbd_content'];

		}
		if($client == 'GEV'){
			$entry['old_usr_id'] = $entry['usr_id'];
			$entry['is_decentral'] = ($entry['edu_program'] == 'dezentrales Training') ? 1 : 0;
			$entry['last_wbd_report'] = ($entry['last_wbd_report']) ? $entry['last_wbd_report'] : 'NULL';

		}


		return $entry;
	}


	private function storeCourseToInterimsBD($entry){
		//to interimCourse
		//is there a course with this title and dates?
		//return crs_id
		$title = mysql_real_escape_string($entry['title']);
		$begin_date = $entry['begin_date'];
		$end_date = $entry['end_date'];

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
					."is_expert_course,"
					."hours,"
					."venue,"
					."provider,"
					."is_decentral,"

					."max_credit_points,"
					."fee,"
					."wbd_topic,"
					."edu_program"
				.") VALUES ("
					.$next_id .","
					."'" .$entry['custom_id'] ."',"
					."'" .mysql_real_escape_string($entry['title']) ."',"
					."'" .$entry['type'] ."'," //selbstlern...

					."'" .$entry['begin_date'] ."'," 
					."'" .$entry['end_date'] ."'," 

					.$entry['topic_set'] ."," 
					.$entry['is_expert_course'] ."," 
					.$entry['hours'] .","
					."'" .mysql_real_escape_string($entry['venue']) ."'," 
					."'" .mysql_real_escape_string($entry['provider']) ."'," 
					
					.$entry['is_decentral'] .","

					."'" .$entry['max_credit_points'] ."'," 
					.$entry['fee'] ."," 
					."'" .$entry['wbd_topic'] ."'," 
					."'" .$entry['edu_program'] ."'" 
				.")";

		$this->queryShadowDB($sql);

		return $next_id;
	}



	private function storeEduRecordForUser($crs_id, $entry, $client){
		//gets new crs-id which matches interimCourse.crs_id

		// delete entries for this user/course first
		$sql = "DELETE FROM  interimUsercoursestatus WHERE "
			. "usr_id_" .strtolower($client) ." = '" .$entry['old_usr_id'] ."'"
			." AND crs_id = " .$crs_id;
		$this->queryShadowDB($sql);

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

				."'" .$entry['usr_begin_date'] ."',"
				."'" .$entry['usr_end_date'] ."'," 
				.$entry['hist_version'] ."," 
				.$entry['hist_historic'] ."," 
				.$entry['created_ts'] ."," 
				.$entry['last_wbd_report'] ."," 
				.$entry['max_credit_points'] ."," 
				."'" .$entry['bill_id'] ."'," 
				."'" .$entry['booking_status'] ."'," 
				."'" .$entry['participation_status'] ."'," 
				."'" .$entry['okz'] ."'," 
				."'" .$entry['org_unit'] ."'," 
				.$entry['certificate'] ."," 
				.$entry['overnights'] ."," 
				."'" .$entry['function'] ."'," 
				."'" .$entry['wbd_booking_id'] ."'" 
			.")";

			$this->queryShadowDB($sql);

			$this->prnt('edurecord for user ' . $entry['old_usr_id'] .': ' .$entry['title']);
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

		foreach ($edu_records as $entry) {
			$entry = $this->normalizeCourseEntry($entry, 'VFS');
			$crs_id = $this->storeCourseToInterimsBD($entry);
		  	$this->storeEduRecordForUser($crs_id, $entry, 'VFS');
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
	
		$edu_records = $fetcher->getEduRecordsForImportedUsers();

		foreach ($edu_records as $entry) {
			$entry = $this->normalizeCourseEntry($entry, 'GEV');
			$crs_id = $this->storeCourseToInterimsBD($entry);
		  	$this->storeEduRecordForUser($crs_id, $entry, 'GEV');
		}


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



	

	public function createUser($rec){

		$user = new ilObjUser();
		$user->setLogin($rec['login']);
		$user->setEmail($rec['mail']);

		//$user->setPasswd($rec['password"));
		$user->setLastname($rec['lastname']);
		$user->setFirstname($rec['firstname']);
		$user->setGender($rec['gender']);
		$user->setUTitle($rec['title']);
		$birthday = $rec['bday'];
		$user->setBirthday($birthday);
		$user->setStreet($rec['street']);
		$user->setCity($rec['city']);
		$user->setZipcode($rec['plz']);
		$user->setCountry($rec['country']);
		$user->setPhoneOffice($rec['fon_work']);
		$user->setPhoneMobile($rec['fon_mobil']);

		// is not active, owner is root
		$user->setActive(0, 6);
		$user->setTimeLimitUnlimited(true);
		$user->setIsSelfRegistered(true);
		
		$user->create();
		$user->saveAsNew();

		$user_id = $user->getId();
		
		//$now = new ilDateTime(time(),IL_CAL_UNIX);
		$create_date = new ilDateTime($rec['created'], IL_CAL_DATETIME);
		$user->setAgreeDate($create_date->get(IL_CAL_DATETIME));

	
		$user->setActive(true, 6);
		$user->update();

		return $user_id;
	}

	

	public function	updateUser($user_record){
		$user = new ilObjUser($user_record['ilid']);

		//deactivate users
		/*
		if(! $user_record['active']){
			$user->setActive(false, 6);
		}
		*/

		if(! $user->getPhoneMobile()){
 			if(! $user_record['ilid_vfs']){

				$sql= "SELECT * FROM interim_gevUserUpdate_mobilePhone WHERE usr_id = " .$user_record['ilid_gev'];
				$res = $this->queryShadowDB($sql);
				if(mysql_num_rows($res) > 0){
					$rec = mysql_fetch_assoc($res);
					$user->setPhoneMobile($rec['value']);
					$this->prnt('mobile: ' .$user->getPhoneMobile());
					$user->update();
				}
 			}

		}


	}



	public function	setUsersFromGroupExitToInactive(){
		$this->prnt('setUsersFromGroupExitToInactive', 1);

		$sql = "SELECT ilid, login"
		." FROM interimOrguAssignments"
		." INNER JOIN interimUsers ON interimOrguAssignments.interim_usr_id = interimUsers.id"
		." WHERE orgu_id = 'exit' ";


		$result = $this->queryShadowDB($sql);
		while ($record = mysql_fetch_assoc($result)){

			//deactivate users
			$user = new ilObjUser($record['ilid']);
			$user->setActive(false, 6);
			$user->update();

			$this->prnt($record['login']);
		}

		$this->prnt('setUsersFromGroupExitToInactive: done', 2);
	}






	public function	update_pass($user_id, $rec){
		//update pass, creation, agreement
		$sql = "UPDATE usr_data SET"
			." passwd='" .$rec['pwd'] ."',"
			." approve_date='" .$rec['approved'] ."',"
			." last_login='" .$rec['last_login'] ."'"
			." WHERE usr_id=" .$user_id;
	
			//print $sql;

		$this->ilDB->query($sql);

	}

	public function setUserAdditionalData($il_user_id, $user_record){
		$this->prnt('setUserAdditionalData', 3);

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$user_utils = gevUserUtils::getInstance($il_user_id);


		$user_utils->setBirthplace($user_record['bcity']);
		$user_utils->setBirthname($user_record['bname']);

		$user_utils->setIHKNumber($user_record['ihknr']);
		$user_utils->setPrivateEmail($user_record['mail_priv']);
		$user_utils->setPrivateStreet($user_record['street_priv']);
		$user_utils->setPrivateCity($user_record['city_priv']);
		$user_utils->setPrivateZipcode($user_record['plz_priv']);

		$user_utils->setEntryDate(new ilDate($user_record['entry_date'], IL_CAL_DATE));
		if($user_record['exit_date'] != '00.00.0000' && $user_record['exit_date'] != ''){
			$user_utils->setExitDate(new ilDate($user_record['exit_date'], IL_CAL_DATE));
		}else{
			$udf_utils = gevUDFUtils::getInstance();
			$udf_utils->setField($il_user_id, gevSettings::USR_UDF_EXIT_DATE, NULL);
		}

		
		if($user_record['tp_type']){
			$user_utils->setWBDTPType($user_record['tp_type']);
		}

		if($user_record['bwvid']){
			$user_utils->setWBDBWVId($user_record['bwvid']);
		}
		if($user_record['wbd_okz']){
			$okz = $user_record['wbd_okz'];
			$okz = str_replace('aus Stellung', 'aus Rolle', $okz);
			$user_utils->setRawWBDOKZ($okz);
		}
		if($user_record['wbd_status']){
			$status = $user_record['wbd_status'];
			$status = str_replace('aus Stellung', 'aus Rolle', $status);
			$user_utils->setRawWBDAgentStatus($status);
		}
		if($user_record['wbd_registered'] == '1 - Ja'){
			$user_utils->setWBDRegistrationDone();
		}
		$user_utils->setWBDCommunicationEmail($user_record['mail_wbd']);
		$user_utils->setIHKNumber($user_record['ihknr']);
		$user_utils->setAgentPositionVFS($user_record['pos_vfs']);


		$user_utils->setJobNumber($user_record['vnr_gev']);
		$user_utils->setAgentKey($user_record['vkey_gev']);
		$user_utils->setAgentKeyVFS($user_record['vkey_vfs']);

		$user_utils->setPaisyNr($user_record['paisy']);


		if($user_record['adp_vfs']){
			$user_utils->setADPNumberVFS($user_record["adp_vfs"]);
		}
		if($user_record['adp_gev']){
			$user_utils->setADPNumberGEV($user_record["adp_gev"]);
		}

		//setFinancialAccountVFS


		//$user_utils->setWBDFirstCertificationPeriodBegin($user_record['wbd_cert_begin']));


		$user = new ilObjUser($il_user_id);
		$user->update();

	}






	public function matchRole($role_title, $position_key){
		require_once("Services/GEV/Import/classes/class.gevUserImportMatching.php");
		if(! array_key_exists($role_title, gevUserImportMatching::$ROLEMAPPINGS)){
			$this->prnt('role does not map: ' .$role_title, 3);
			die();
		}
		$new_role = gevUserImportMatching::$ROLEMAPPINGS[$role_title];
		if($new_role == '#FROMKEY'){
			$this->prnt('...key...');
			$sql = "SELECT role_title FROM interimRoleFromKey WHERE position_key='$position_key'";
			$result = $this->queryShadowDB($sql);
			$rec = mysql_fetch_assoc($result);
			$new_role = trim($rec['role_title']);
		}
		return $new_role;
	}



	public function assignUserRoles($interim_user_id, $il_user_id, $user_record){
		$this->prnt('assignUserRoles', 3);

		$client = ($user_record['ilid_vfs'] == '') ? 'gev' : 'vfs';
		$agentkey = $user_record['vkey_' .$client];
		if($client == 'vfs'){
			$this->prnt($user_record['login'] .': +VFS');
			$this->role_utils->assignUserToGlobalRole($il_user_id, 'VFS');
		}

		$sql="SELECT interimRoles.title AS role_title FROM interimRoles"
		." INNER JOIN interimUserRoles ON interimRoles.id=interimUserRoles.interim_role_id"
		." WHERE interimUserRoles.interim_usr_id = $interim_user_id";

		$result = $this->queryShadowDB($sql);
		if(mysql_num_rows($result) == 0){
			print_r($user_record);
			print_r($sql);
			print ('no role.');
		}

		$new_roles = array();
		$this->prnt($user_record['login'] .': ' .$record['role_title'] .' -> ');
		while ($record = mysql_fetch_assoc($result)){
			$new_role = $this->matchRole($record['role_title'], $agentkey);
			if($new_role != '#DROP'){
				$this->role_utils->assignUserToGlobalRole($il_user_id, $new_role);
				$this->prnt($new_role, -1);
				$new_roles[] = $new_role;
			}else{
				$this->prnt('-drop-', -1);
			}
		}	

	}




	public function assignAsMiZ($user_record, $mizcoach){
		$il_user_id = $user_record['ilid'];

		if($user_record['isMizOfADP']){


			if(is_numeric( $user_record['isMizOfADP'])){

				$sql = "SELECT ilid as il_adviser_user_id FROM interimUsers"
					." WHERE adp_vfs = '" . $user_record['isMizOfADP'] ."'";
			} else {

				$sql = "SELECT ilid as il_adviser_user_id FROM interimUsers"
					." WHERE mail = '" . $user_record['isMizOfADP'] ."'";
			}

			$result = $this->queryShadowDB($sql);
			$record = mysql_fetch_assoc($result);
			$il_adviser_user_id = $record['il_adviser_user_id'];
			
			$this->prnt('... by interimUser: MIZ of ' .$il_adviser_user_id);
			if($il_adviser_user_id){
				try{
					$this->na_utils->assignAdviser($il_user_id, $il_adviser_user_id);
				}
				catch(Exception $e){
					print_r($e);
					//pass				
				}
			}
			$this->prnt(' .... ok', -1);
		}


		if(is_numeric($mizcoach)){
			//get mizcoach
			$sql = "SELECT ilid as il_adviser_user_id FROM interimUsers"
					." WHERE id = $mizcoach";

			$result = $this->queryShadowDB($sql);
			$record = mysql_fetch_assoc($result);
			$il_adviser_user_id = $record['il_adviser_user_id'];

			$this->prnt('... by interimOrguAssignments: MIZ of ' .$il_adviser_user_id);
			if($il_adviser_user_id){
				try{
					$this->na_utils->assignAdviser($il_user_id, $il_adviser_user_id);
				}
				catch(Exception $e){
					print_r($e);
					//pass				
				}
			}
			$this->prnt(' .... ok', -1);
		}

	}



	public function assignUserToOrgUnits($interim_user_id, $il_user_id, $user_record){
		//$this->prnt('assignUserToOrgUnits', 3);
		require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

		$sql = "SELECT * FROM interimOrguAssignments WHERE interim_usr_id='$interim_user_id'";
		$result = $this->queryShadowDB($sql);
		if(mysql_num_rows($result) == 0){
			//keine zuordnung
			$this->prnt('Keine Zuordnung: ' .$user_record['login'], 3);
			//$orgu_id = 'ohne_zuordnung';
			$client = ($user_record['ilid_vfs'] == '') ? 'gev' : 'vfs';
			$orgu_id = 'nogroup_' .$client;


		} else {

			$record = mysql_fetch_assoc($result);
			$orgu_id = $record['orgu_id'];
		}


		if($orgu_id == 'root'){
			$this->prnt('root-user? : ' .$user_record['login'], 3);
		}

		if($orgu_id == 'makler'){
			$this->prnt($user_record['login'] .' (' .$interim_user_id .') is a DBV-Makler ');
			// fromRegistrationGUI, DBV assign
			
			require_once("Services/GEV/Utils/classes/class.gevDBVUtils.php");
			gevDBVUtils::getInstance()->assignUserToDBVsByShadowDB($il_user_id);
			
		}
		if($orgu_id == 'na'){
			$this->prnt($user_record['login'] .' (' .$interim_user_id .') is a NA ');
			$this->assignAsMiZ($user_record, $record['interim_usr_id_mizcoach']);
		}




		if(	$orgu_id != 'na' 
			&& $orgu_id != 'makler'
			&& $orgu_id != 'root'){

			$sql = "SELECT ilid, id, title FROM interimOrgUnits "
				." WHERE id = '" .$orgu_id ."'";

			$res = $this->queryShadowDB($sql);
			if(mysql_num_rows($res) == 0){
				die($sql);
			}

			$rec = mysql_fetch_assoc($res);

			$org_unit_id = $rec['ilid'];

			$this->prnt($user_record['login'] .' [il ' .$il_user_id .'] in OrgUnit '.$rec['title'] . ' (' .$org_unit_id .' )');

			$org_role_title = 'Mitarbeiter';
			$user_roles = $this->role_utils->getGlobalRolesOf($il_user_id);
			
			foreach ($user_roles as $urole) {
				if(in_array($this->global_roles[$urole], $this->orgu_superior_roles)){
					$org_role_title = 'Vorgesetzter';
				}
			}

			$org_unit_utils = gevOrgUnitUtils::getInstance($org_unit_id);
			$org_unit_utils->getOrgUnitInstance();
			$org_unit_utils->assignUser($il_user_id, $org_role_title);
		}





		
	}



	public function createOrUpdateUserAccounts(){
		$this->prnt('Creating/Updating UserAccounts', 1);

		//exclude users from creation  that are already there...
		$sql = "SELECT login FROM usr_data";
		$result = $this->ilDB->query($sql);
		$exclude = array();
		while($record = $this->ilDB->fetchAssoc($result)) {
			$exclude[] = $record['login'];
		}


		foreach ($this->getUsersFromInterimsDB() as $record) {
			
			//$this->prnt($record, 666);
			$this->prnt($record['login']);
			
			if(! in_array(trim($record['login']), $exclude)){
				//create
				
				$user_id = $this->createUser($record);
				$sql = "UPDATE interimUsers SET ilid = '$user_id' WHERE"
					." id=" .$record['id'];

				$this->queryShadowDB($sql);
			} else{
				$user_id = $record['ilid'];

				//this should not happen?!
				if(! $user_id){
					$sql = "SELECT usr_id FROM usr_data"
					." WHERE login='".$record['login']."'";
					$res = $this->ilDB->query($sql);
					$rec = $this->ilDB->fetchAssoc($res);
				
					$user_id = $rec['usr_id'];
				
					$sql = "UPDATE interimUsers SET ilid = '$user_id' WHERE"
					." id=" .$record['id'];
					$this->queryShadowDB($sql);
				}

				$this->updateUser($record);
			}


			$this->update_pass($user_id, $record);
			$this->setUserAdditionalData($user_id, $record);

		};
		$this->prnt('Creating/Updating UserAccounts: done', 2);
	}
	

	public function assignAllUserRoles(){
		$this->prnt('assignAllUserRoles', 1);
		foreach ($this->getUsersFromInterimsDB() as $record) {
			$user_id = (int)$record['ilid'];
			$this->assignUserRoles($record['id'], $user_id, $record);
		}
		$this->prnt('assignAllUserRoles: done', 2);
	}


	public function assignAllUsersToOrgUnits(){
		$this->prnt('assignAllUsersToOrgUnits', 1);
		foreach ($this->getUsersFromInterimsDB() as $record) {
			//$client = ($record['ilid_vfs'] == '') ? 'gev' : 'vfs';
			$this->assignUserToOrgUnits($record['id'], $record['ilid'], $record);
		}
		$this->prnt('assignAllUsersToOrgUnits: done', 2);
	}






	public function getUsersFromInterimsDB(){
		$sql = "SELECT * FROM interimUsers"
		." WHERE login NOT IN ('root', 'anonymous', 'cron')"
		." AND mail NOT LIKE '%@qualitus.de'"

//." AND isMizOfADP != '' "		
//." AND id BETWEEN 2755 AND 2765"
//." LIMIT 200 OFFSET 400"
//." AND id in (4352, 5632)"
		;

		$result = $this->queryShadowDB($sql);
		$ret = array();
		while ($record = mysql_fetch_assoc($result)){
			$ret[] = $record;
		}
		return $ret;
	}









	private function writeCourseEntry($crs_record){
	/*
	hist_course
		row_id 				int(11) 		
		hist_version 		int(11) 		
		hist_historic 		int(11) 		
		creator_user_id 	int(11) 		
		created_ts 			int(11) 		
		crs_id 				int(11) 		
		custom_id 			varchar(255) 	
		title 				varchar(255) 	
		template_title 		varchar(255) 	
		type 				varchar(255) 	
		topic_set 			int(11) 		
		begin_date 			date 			
		end_date 			date 			
		hours 				int(11) 		
		is_expert_course 	tinyint(4) 		
		venue 				varchar(255) 	
		provider 			varchar(255) 	
		tutor 				varchar(255) 	
		max_credit_points 	varchar(255) 	
		fee 				double 			
		is_template 		varchar(8) 	
		wbd_topic 			varchar(255) 	
		edu_program			varchar(255)
	*/


		//insert courses (negative ids is enough, there are no other negatives yet)
		$id = $this->ilDB->nextId('hist_course');

		$fee = ($crs_record['fee']) ? $crs_record['fee'] : 0 ;

		$sql = " INSERT INTO hist_course ("
			."
			 row_id
			,hist_version
			,hist_historic
			,creator_user_id
			,created_ts
			,crs_id
			,custom_id
			,title
			,template_title
			,type
			,topic_set
			,begin_date
			,end_date
			,hours
			,is_expert_course
			,venue
			,provider
			,tutor
			,max_credit_points
			,fee
			,is_template
			,wbd_topic
			,edu_program
			"
			.") VALUES ("

			.$id .","
			."0, " //hist_version
			."0, " //hist_historic
			."-201, " //creator
			
			."UNIX_TIMESTAMP() , "//created_ts
			.$crs_record['crs_id'] .", " //crs_id
			."'" .$crs_record['custom_id'] ."', " //custom_id
			.$this->ilDB->quote($crs_record['title'], 'text') .", " //title
			."'', " //template_title
			."'" .$crs_record['type'] ."', " //type
			.$crs_record['topic_set'] .", " //topic_set
			.$this->ilDB->quote($crs_record['begin_date'], 'text') .", " //begin_date
			.$this->ilDB->quote($crs_record['end_date'], 'text') .", " //end_date
			.$crs_record['hours'] .", " //hours
			.$crs_record['is_expert_course'] .", " //is_expert_course
			.$this->ilDB->quote($crs_record['venue'], 'text') .", " //venue
			."'" .$crs_record['provider'] ."', " //provider
			."'-empty-', " //tutor
			.$this->ilDB->quote($crs_record['max_credit_points'], 'text') .", " //max_credit_points
			.$fee .", " //fee
			."0, " //is_template
			."'" .$crs_record['wbd_topic'] ."', " //wbd_topic
			."'" .$crs_record['edu_program'] ."'"//edu_program
			.")"
			;

		$this->ilDB->query($sql);

		
		//insert (negative) id into object_reference, so course shows up in edu-bio
		$ref = $this->ilDB->nextId('object_reference');
		$sql = "INSERT INTO object_reference (ref_id, obj_id)"
		." VALUES ("
		.$ref ."," 
		.$crs_record['crs_id']
		. ")";
		$this->ilDB->query($sql);



		$this->prnt(' .', -1);


	}

	private function writeUserCourseEntry($edu_record){
 	/*
	hist_usercoursestatus

	 	row_id 				int(11) 
		hist_version 		int(11)
		hist_historic 		int(11)
		creator_user_id 	int(11)
		created_ts 			int(11)
		last_wbd_report 	date 			
		usr_id 				int(11)
		crs_id 				int(11)
		credit_points 		int(11)
		bill_id 			varchar(16)
		booking_status 		varchar(255)
		participation_status	varchar(255) 	
		okz 				varchar(255) 	
		org_unit 			varchar(255)
		certificate 		int(11) 
		begin_date 			date 		
		end_date 			date 		
		overnights 			int(11) 	
		function 			varchar(255)
		wbd_booking_id 		varchar(255)
	*/

	//only for new course-entries...
	$sql = " SELECT * FROM hist_usercoursestatus"
	." WHERE usr_id=" .$edu_record['usr_id']
	." AND crs_id=" .$edu_record['crs_id']
	;
	$result = $this->ilDB->query($sql);

	if($this->ilDB->numRows($result) == 0){

		$id = $this->ilDB->nextId('hist_course');

		$sql = " INSERT INTO hist_usercoursestatus ("
			."
			 row_id
			,hist_version 
			,hist_historic
			,creator_user_id
			,created_ts
			,last_wbd_report
			,usr_id
			,crs_id
			,credit_points
			,bill_id
			,booking_status
			,participation_status
			,okz
			,org_unit
			,certificate
			,begin_date
			,end_date
			,overnights
			,function
			,wbd_booking_id
			"
			
			.") VALUES ("

			.$id .", "
			.$edu_record['hist_version'] .", " //hist_version
			.$edu_record['hist_historic'] .", " //hist_historic
			.$edu_record['creator_user_id'] .", " //creator
			."UNIX_TIMESTAMP() , "//created_ts
			//.$edu_record['last_wbd_report'] .", " //last_wbd_report
			.'0000-00-00' .", " //last_wbd_report
			.$edu_record['usr_id'] .", " //usr_id
			.$edu_record['crs_id'] .", " //crs_id
			.$edu_record['credit_points'] .", " //credit_points
			.$this->ilDB->quote($edu_record['bill_id'], 'text') .", " //bill_id
			.$this->ilDB->quote($edu_record['booking_status'], 'text') .", " //booking_status
			.$this->ilDB->quote($edu_record['participation_status'], 'text') .", " //participation_status
			.$this->ilDB->quote($edu_record['okz'], 'text') .", " //okz
			.$this->ilDB->quote($edu_record['org_unit'], 'text') .", " //org_unit
			.$edu_record['certificate'] .", " //certificate
			.$this->ilDB->quote($edu_record['begin_date'], 'text') .", " //begin_date
			.$this->ilDB->quote($edu_record['end_date'], 'text') .", " //end_date
			.$edu_record['overnights'] .", " //overnights
			.$this->ilDB->quote($edu_record['function'], 'text') .", " //function
			.$this->ilDB->quote($edu_record['wbd_booking_id'], 'text')  //wbd_booking_id
			.")"
			;

			$this->ilDB->query($sql);
			$this->prnt(' .', -1);
		} else {
			$this->prnt(' x', -1);
		}

	}




	public function importEduRecords(){
		$this->prnt('importEduRecords', 1);
/*
		//get all usercoursestatus from interim
		$this->prnt('courses (and topics)', 3);
		$sql = "SELECT * FROM interimCourse";

		$result = $this->queryShadowDB($sql);
		while ($record = mysql_fetch_assoc($result)){
			if($record['topic_set'] > 0){
				//get topicset from interim_hist_topic and (maybe) insert new topic in live
				$record['topic_set'] = $this->getLiveTopicSetId($record['topic_set']);
			}

			//write course
			$this->writeCourseEntry($record);

		}
*/

		//get all interimUserCourseStatus
		$this->prnt('user-course status', 3);

		$sql = "SELECT * FROM interimUsercoursestatus";
		$result = $this->queryShadowDB($sql);
		while ($record = mysql_fetch_assoc($result)){
			//match user_id againts interimUsers.ilid
			$client = ($record['usr_id_vfs'] == '' || $record['usr_id_vfs'] == 0) ? 'gev' : 'vfs';

			$sql = "SELECT ilid FROM interimUsers"
				." WHERE ilid_" .$client ."='" .$record['usr_id_' .$client] ."'"
				;
			$res = $this->queryShadowDB($sql);
			$r = mysql_fetch_assoc($res);
			$ilid = $r['ilid'];

			if($ilid){//there are test/dummy-users w/o ilid

				//write edurecord
				$record['creator_user_id'] = ($client == 'gev') ? -202 : -203;
				$record['usr_id'] = $ilid;

				$this->writeUserCourseEntry($record);
			}

		}

		$this->prnt('importEduRecords: done', 2);
	}


	public function fixEduRecords(){
		$this->prnt('fixEduRecords', 1);
		$sql = "SELECT * FROM hist_usercoursestatus";
		
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)){

			//fix booking id			
			if($record['wbd_booking_id'] && $record['wbd_booking_id'] != '-empty-'){

				//update last_wbd_report from booking_id
				//2014-12-30-2472
				$sql = "UPDATE hist_usercoursestatus SET"
				." last_wbd_report='" .substr($record['wbd_booking_id'], 0, 10) ."'"
				." WHERE row_id = " .$record['row_id'];
				
				$beep = '.';

			} else {
				//set booking_id to "-empty-", if NULL
				$sql = "UPDATE hist_usercoursestatus SET"
				." last_wbd_report=NULL "
				." ,wbd_booking_id=NULL"
				." WHERE row_id = " .$record['row_id'];

				$beep = '-';
			}

			$this->ilDB->query($sql);
			$this->prnt($beep,-1);

		}


		$this->prnt('fixEduRecords: done', 2);
	}


}
