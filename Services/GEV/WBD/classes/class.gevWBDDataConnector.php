<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* implementation of WBDData-interface
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*/


$SET_LASTWBDRECORD = true;
$SET_BWVID = true;

$GET_NEW_USERS = true;
$GET_UPDATED_USERS = true;
$GET_NEW_EDURECORDS = true;
$GET_NEW_EXIT_USER = true;

$GET_CHANGED_EDURECORDS = false;
$IMPORT_FOREIGN_EDURECORDS = false;
$STORNO_EDURECORDS = false;


/*
$GET_NEW_USERS = false;
$GET_UPDATED_USERS = false;
$GET_NEW_EDURECORDS = false;
$GET_NEW_EXIT_USER = false;
$GET_CHANGED_EDURECORDS = false;
$IMPORT_FOREIGN_EDURECORDS = false;
$STORNO_EDURECORDS = true;
*/

$LIMIT_RECORDS = false;
$ANON_DATA = false;

$DEBUG_HTML_OUT = isset($_GET['debug']);


//reset ilias for calls from somewhere else
$basedir = __DIR__;
$basedir = str_replace('/Services/GEV/WBD/classes', '', $basedir);
chdir($basedir);

if($DEBUG_HTML_OUT){
	require "./Customizing/global/skin/genv/Services/GEV/simplePwdSec.php";
	echo('<pre>');	
}

//context w/o user
require_once "./Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_WEB_NOAUTH);
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();
require_once("./include/inc.header.php");


//get base class
require_once("./Services/WBDData/classes/class.wbdDataConnector.php");


class gevWBDDataConnector extends wbdDataConnector {

	const WBD_NO_SERVICE 		= "0 - kein Service";
	const WBD_EDU_PROVIDER		= "1 - Bildungsdienstleister";
	const WBD_TP_BASIS			= "2 - TP-Basis";
	const WBD_TP_SERVICE		= "3 - TP-Service";


	/*
	const WBD_NO_SERVICE 		= "kein Service";
	const WBD_EDU_PROVIDER		= "Bildungsdienstleister";
	const WBD_TP_BASIS			= "TP-Basis";
	const WBD_TP_SERVICE		= "TP-Service";
	*/

	private $empty_bwv_id_text = "-empty-";
	private $empty_date_text = "0000-00-00";

	public $valid_newusers = array();
	public $broken_newusers = array();
	
	public $broken_updatedusers = array();

	public $broken_exitusers = array();

	public $valid_newedurecords = array();
	public $broken_newedurecords = array();


	public function __construct() {

		parent::__construct();
		
		require_once("./Services/WBDData/classes/class.wbdErrorLog.php");
		wbdErrorLog::_install();
	}


	/**
	 * basically mapping DB-fields to WBD-keys
	 */

	private function _extract_house_nr($streetnr){

		//special cases:
		//Mannheim, Q5
		$i = 0 ;
		if(strtoupper(substr(trim($streetnr), 0, 2)) == 'Q5') {
		    $i = 2;
		}
		if(strtoupper(substr(trim($streetnr), 0, 3)) == 'Q 5') {
		    $i = 3;
		}
		if(strtolower(substr(trim($streetnr), 0, 4)) == '55er') {
		    $i = 4;
		}		
		if(strtolower(substr(trim($streetnr), 0, 5)) == '55-er') {
		    $i = 5;
		}
		
		if(strtolower(substr(trim($streetnr), 0, 9)) == 'straße 4') {
		    return array(
				'street' => 'Straße 4',
				'nr' => trim(substr($streetnr, 9))
			);
		}
		


		//find first number in string
	    $len = strlen($streetnr);
	    $pos = False;
	    for($i; $i < $len; $i++) {
	        if(is_numeric($streetnr[$i])) {
	        	$pos = $i;
	        	break;
	        }
	    }
	    $street = trim(substr($streetnr, 0, $pos));
	    $nr = trim(substr($streetnr, $pos));
		return array(
			'street' => trim($street), 
			'nr' =>trim($nr)
		);
	}

	public function _polish_phone_nr($phone_nr){
		if($phone_nr == '' || preg_match($this->TELNO_REGEXP, $phone_nr)){
			//all well, return
			
			return $phone_nr;
		}
		$nr_raw = $phone_nr;

		//strip country-code
		if(in_array(substr($nr_raw, 0, 4), array('++49', '0049'))){
			$nr_raw = substr($nr_raw, 4);
		}
		if(in_array(substr($nr_raw, 0, 3), array('+49', '049'))){
			$nr_raw = substr($nr_raw, 3);
		}
		//Lösungansatz auf dem Weg zur WBD wenn in der hist_user das "+" oder die "00" fehlen
		if(in_array(substr($nr_raw, 0, 2), array('49'))){
			$nr_raw = substr($nr_raw, 2);
		}
		$nr_raw = trim($nr_raw);

		//nr is in "raw" - w/o country code
		//it hopefully still starts with 0...
		if(substr($nr_raw, 0, 1) == '0'){
			$nr_raw = substr($nr_raw, 1);
		} else {
			//no city-code, nothing we ca don
			$phone_nr = '+49 ' .$nr_raw;
			return $phone_nr;
		}

		//is there a separation for city-code/nr?
		if( strpos($nr_raw, ' ') === false &&
			strpos($nr_raw, '/') === false &&
			strpos($nr_raw, '-') === false 
		){
			//guess city-code for mobile numbers:
			if( in_array(
					substr($nr_raw, 0, 4), 
					array(
						'1511','1512','1513','1514','1515','1516','1517','1518','1519','1510',
						'1521','1522','1523','1524','1525','1526','1527','1528','1529','1520',
						'1571','1572','1573','1574','1575','1576','1577','1578','1579','1570',
						'1591','1592','1593','1594','1595','1596','1597','1598','1599','1590'
					)
				)
			){
				$nr_raw = substr($nr_raw, 0, 4) . ' ' .substr($nr_raw, 4);
			}			
			if( in_array(
					substr($nr_raw, 0, 3), 
					array(
						'160','170','171','175',
						'162','172','173','174',
						'163','177','178',
						'176','179'
					)
				)
			){
				$nr_raw = substr($nr_raw, 0, 3) . ' ' .substr($nr_raw, 3);
			}
		}

		$phone_nr = '+49 ' .$nr_raw;
		return $phone_nr;
	}


	public function _polish_birthday($bday){
		//is: YYYY-MM-DD
		//should: DD.MM.YYYY
		$bd = explode('-', $bday);
		$bday = $bd[2] .'.' .$bd[1] .'.' .$bd[0];
		return $bday;
	}


	private function _map_userdata($record) {
		global $ANON_DATA;
		if($ANON_DATA){
			$record = $this->_anon_userdata($record);
		}

		$street_and_nr = $this->_extract_house_nr( $record['street']);
		$udata = array(
				'internal_agent_id' 		=> $record['user_id']
				,'title' 					=> $this->VALUE_MAPPINGS['salutation'][$record['gender']]
				,'first_name' 				=> $record['firstname']
				,'last_name' 				=> $record['lastname']
				
				,'birthday' 				=> $record['birthday']

				,'street'					=> $street_and_nr['street']
				,'house_number'				=> $street_and_nr['nr']
				,'zipcode'					=> $record['zipcode']
				,'city'						=> $record['city']
				,'phone_nr'					=> ($record['phone_nr'] == '-empty-') ? '' : $record['phone_nr']
				,'mobile_phone_nr'			=> $record['mobile_phone_nr']
				
				,'auth_email' 				=> ($record['email'] == '-empty-') ? '' : $record['email']
				,'email'					=> ($record['wbd_email'] && $record['wbd_email'] != '-empty-') ? $record['wbd_email'] : $record['email']
				,'auth_phone_nr' 			=> $record['mobile_phone_nr']

				,'agent_registration_nr' 	=> '' // optional
				,'agent_id'					=> ($record['bwv_id'] == '-empty-') ? '' : $record['bwv_id']
				
				,'agency_work' 				=> $record['okz'] //OKZ
				,'agent_state' 				=> ($this->VALUE_MAPPINGS['agent_status'][$record['wbd_agent_status']])	//Status
				//,'email_confirmation' => 'Nein'			//Benachrichtigung?
				,"row_id" 					=> $record["row_id"]
				,'wbd_type' 				=> $record['wbd_type'] //debug
				,'begin_of_certification'	=> $record['begin_of_certification']
			);

	

		//$udata['birthday'] = $this->_polish_birthday($udata['birthday']);
		
		$udata['phone_nr'] = $this->_polish_phone_nr($udata['phone_nr']);
		$udata['mobile_phone_nr'] = $this->_polish_phone_nr($udata['mobile_phone_nr']);
		$udata['auth_phone_nr'] = $this->_polish_phone_nr($udata['auth_phone_nr']);

		//Leerzeichen am Ende der E-Mail entfernen
		$udata['email'] = rtrim($udata['email']);
		$udata['auth_email'] = rtrim($udata['auth_email']);

		return $udata;
	}

	private function _map_edudata($record) {

		$edudata = array(
			//"name" 					=> $record["lastname"]
			//,"first_name" 			=> $record["firstname"]
			
			"name" 					=> '' //will not be imported, anyway.
			,"first_name" 			=> ''

			,"birthday_or_internal_agent_id" => $record['user_id']
			,"agent_id" 			=> $record['bwv_id']
//			,"from" 				=> $record['begin_date']
//			,"till" 				=> $record['end_date']

			,"from" 				=> $record['course_begin']
			,"till" 				=> $record['course_end']

			,"score"				=> $record['credit_points']
			,"study_type_selection" => $this->VALUE_MAPPINGS['course_type'][trim($record['type'])] // "Präsenzveranstaltung" | "Selbstgesteuertes E-Learning" | "Gesteuertes E-Learning";
			,"study_content"		=> $this->VALUE_MAPPINGS['study_content'][trim($record['wbd_topic'])] 
			
			,"training"	 			=> $record['title'] //or template?
			
			,"internal_booking_id" => $record["row_id"]
			/*
			
			//score code is set by get_new_edurecords...
			"score_code" => "" // KennzeichenPunkte 

			"contact_degree" => "",
			"contact_first_name" => "",
			"contact_last_name" => "",
			"contact_phone" => "",
			"contact_email" => "",

			*/
			,"row_id" 				=> $record["row_id"]
			,"training_score_booking_id"	=> $record["wbd_booking_id"]
			,"begin_of_certification" => $record["begin_of_certification"]
		);
		return $edudata;
	}

	private function _anon_userdata($record){
		//$record['firstname'] = $this->fake_string(5,20);
		$record['lastname'] = $this->fake_string(strlen($record['lastname']), strlen($record['lastname']));
		
		$record['phone_nr'] = $this->fake_fon();
		$record['mobile_phone_nr'] = $this->fake_fon();

		$record['email'] = 'il-dev@cat06.de';
		$record['wbd_email'] = 'il-dev@cat06.de';
		$record['street'] = $this->fake_streetnr();
		$record['city'] = $this->fake_string(strlen($record['city']));
		$record['zipcode'] = $this->fill_format_nr('XXXXX');

		return $record;
	}



	//HISTORY TABLES:
	/**
	 * get current (is_historic=0) record from any historic version
	 */
	private function _get_current_record($table, $row_id) {
		switch ($table) {
			case 'hist_user':
				$search  = "
					user_id=(SELECT user_id FROM $table WHERE row_id=$row_id)
				";
				break;
			/* actually, this will not occur.
			case 'hist_course':
				$search  = "
					crs_id=(SELECT crs_id FROM $table WHERE row_id=$row_id)
				";
				break;
			*/
			case 'hist_usercoursestatus':
				$search  = "
					usr_id=(SELECT usr_id FROM $table WHERE row_id=$row_id)
					AND
					crs_id=(SELECT crs_id FROM $table WHERE row_id=$row_id)
				";
				break;
		}

		$search .="AND hist_historic=0";

		$sql = "
			SELECT * FROM $table
			WHERE $search;
		";

		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);
		return $record;
	}



	/**
	 *
	 */
	//private function _set_last_wbd_report($table, $row_id) {
	public function _set_last_wbd_report($table, $row_id) {
		global $SET_LASTWBDRECORD;
		if(! $SET_LASTWBDRECORD){
			return;
		}

		$sql = "
			UPDATE $table
			SET last_wbd_report = NOW()
			WHERE row_id=$row_id
		";
		$result = $this->ilDB->query($sql);
	}

	/**
	* sets the WBD EXIT DATA on the GOA-User and hist_user
	*
	*@param 	integer 	$a_row_id to determin the user_id
	*/
	private function setWbdExitUserData($a_row_id) {
		$sql = "SELECT user_id FROM hist_user WHERE row_id = ".$this->ilDB->quote($a_row_id, "integer")."";

		$res = $this->ilDB->query($sql);

		assert($this->ilDB->numRows($res) == 1);

		if($this->ilDB->numRows($res) == 1) {
			$row = $this->ilDB->fetchAssoc($res);
			$usr_id = $row["user_id"];
			
			require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
			$udf_utils = gevUDFUtils::getInstance();

			$wbd_exit_date = date("Y-m-d");
			$udf_utils->setField($usr_id,gevSettings::USR_WBD_EXIT_DATE, $wbd_exit_date);
			$udf_utils->setField($usr_id,gevSettings::USR_TP_TYPE, "1 - Bildungsdienstleister");

			//Create new History Row
			$this->raiseEventUserChanged($usr_id);
		}
	}

	/**
	 * new entry for foreign wbd-course
	 * or matching for existing seminar
	 * returns course_id
	**/
	private function importSeminar($rec){

		$title 		= $rec['title'];
		$type 		= $rec['type']; 
		$wbd_topic 	= $rec['wbd_topic']; 
		$begin_date	= $rec['begin']; // date('Y-m-d', strtotime($rec['Beginn']));
		$end_date 	= $rec['end']; //date('Y-m-d', strtotime($rec['Ende']));
		$creator_id = -200;


		$sql = "SELECT crs_id FROM hist_course WHERE 
			title = '$title'
			AND
			begin_date = '$begin_date'
			AND 
			end_date = '$end_date'
		";
		$result = $this->ilDB->query($sql);
		if($this->ilDB->numRows($result) > 0){
			$record = $this->ilDB->fetchAssoc($result);
			return $record['crs_id'];
		}
		
		//new seminar
		$sql = "SELECT crs_id FROM hist_course WHERE 
				crs_id < 0
				ORDER BY crs_id ASC
				LIMIT 1
		";	
		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);
		
		$crs_id = $record['crs_id'] - 1;
		//start with 4 digits
		if($crs_id == -1){
			$crs_id = -1000;
		}

		$next_id = $this->ilDB->nextId('hist_course');

		$sql = "INSERT INTO hist_course
			(
				row_id,
				hist_version,
				created_ts,
				creator_user_id,
		 		is_template,
		 		crs_id,
		 		title,
		 		type, 
		 		wbd_topic,
		 		begin_date,
		 		end_date,
		 		
		 		custom_id,
		 		template_title,
		 		max_credit_points
			) 
			VALUES 
			(
				$next_id,
				0,
				NOW(),
				$creator_id,
				'Nein',
				$crs_id,
				'$title',
				'$type',
		 		'$wbd_topic',
		 		'$begin_date',
		 		'$end_date',
		 		'-empty-',
		 		'-empty-',
		 		'-empty-'
			)";

			

//print "\n\n$sql\n\n";

			if(! $this->ilDB->query($sql)){
				die($sql);
			}

		return $crs_id;
	}

	/**
	 * new entry for foreign wbd-courses in hist_usercoursestatus
	**/
	private function assignUserToSeminar($rec, $crs_id){
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		// get user (hist_user is ok, since bwv-id must not change 
		// and was the starting point, anyway)
		$sql = "SELECT user_id FROM hist_user "
			." WHERE bwv_id = '" .$rec['bwv_id'] ."'"
			." AND hist_historic=0";
		$result = $this->ilDB->query($sql);
		$user_rec = $this->ilDB->fetchAssoc($result);

		$usr_id = $user_rec['user_id'];

		$uutils = gevUserUtils::getInstanceByObjOrId($usr_id);

		$okz 			= $uutils->getWBDOKZ();
		$booking_id		= $rec['wbd_booking_id'];
		$credit_points 	= $rec['credit_points'];
		$begin_date 	= $rec['begin']; // date('Y-m-d', strtotime($rec['Beginn']));
		$end_date 		= $rec['end']; //date('Y-m-d', strtotime($rec['Ende']));
		$creator_id 	= -200;
		$next_id 		= $this->ilDB->nextId('hist_usercoursestatus');

		$sql = "INSERT INTO hist_usercoursestatus
			(
				row_id,
				wbd_booking_id,
				created_ts,
				creator_user_id,
				usr_id,
		 		crs_id,
		 		credit_points,
		 		hist_historic,
		 		hist_version,
		 		okz,
		 		function,
		 		booking_status,
		 		participation_status,
		 		begin_date,
		 		end_date,
		 		bill_id,
		 		certificate
			) 
			VALUES 
			(
				$next_id,
				'$booking_id',
				UNIX_TIMESTAMP(),
				$creator_id,
				$usr_id,
				$crs_id,
				$credit_points,
				0,
				0,
				'$okz',
				'Mitglied',
				'gebucht',
				'teilgenommen',
				'$begin_date',
				'$end_date',
				-1,
				-1
			)";
		

//print "\n\n$sql\n\n";

			if(! $this->ilDB->query($sql)){
				die($sql);
			}

	
	}






	/*
	* ------------- IMPLEMENTATION ------------
	*/

	public function about_to_die($e){
	
	    print_r($e);
	}




	/**
	 * set BWV-ID for user
	 *
	 * @param string $user_id
	 * @param string $bwv_id
	 * @return boolean
	 */

	public function set_bwv_id($user_id, $bwv_id) {
		global $SET_BWVID;
		if(! $SET_BWVID){
			return true;
		}

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		global $ilAppEventHandler;
		$uutils = gevUserUtils::getInstance($user_id);
		$uutils->setWBDBWVId($bwv_id);
		
		return true;
	}


	/**
	* set begin-of-certification for user
	*
	* @param string $a_user_id
	* @param date $a_certification_begin
	*/
	public function setBeginOfCertification($a_user_id, $a_certification_begin) {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDate.php");
		$uutils = gevUserUtils::getInstance($a_user_id);
		$str = explode("T",$a_certification_begin);
		$begin_of_certification = new ilDate($str[0],IL_CAL_DATE);
		$uutils->setWBDFirstCertificationPeriodBegin($begin_of_certification);

		return;
	}

	/**
	 * raises the event user has changed
	 *
	 * @param string $a_user_id
	 */
	public function raiseEventUserChanged($a_user_id) {
		global $ilAppEventHandler;

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$uutils = gevUserUtils::getInstance($a_user_id);
		$ilAppEventHandler->raise("Services/User", "afterUpdate", array("user_obj" => $uutils->getUser()));

		$this->setLastWBDReportForAutoHistRows($a_user_id);
	}

	/**
	* set last_wbd_report for automaticly created hist rows
	*/
	public function setLastWBDReportForAutoHistRows($a_user_id) {
		$sql = "
			SELECT row_id FROM hist_user
			WHERE user_id = $a_user_id
			AND hist_historic = 0
			";

		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);
		$this->_set_last_wbd_report('hist_user', $record['row_id']);
	}

	/**
	 * get users that do not have a BWV-ID yet
	 *
	 * @param
	 * @return array of user-records
	 */
	public function get_new_users() {
		global $GET_NEW_USERS, $LIMIT_RECORDS;

		if(! $GET_NEW_USERS){
			return array();
		}

		$sql = "
			SELECT
				*
			FROM
				hist_user
			WHERE
				hist_historic = 0
			AND
				deleted = 0
			AND
				bwv_id = '-empty-'
			AND
				last_wbd_report IS NULL
			"
			//exclude pending users;
			//pending users were reported, changed,
			//but still do not have an bwv_id
			."
			AND user_id NOT IN (
				SELECT DISTINCT user_id
				FROM hist_user
				WHERE
					hist_historic = 1
				AND NOT
					last_wbd_report IS NULL
			)
			";


		// new accounts for TP_Service, TP_Basic only:
		$sql .= " AND wbd_type IN ('"
			.self::WBD_TP_BASIS."', '".self::WBD_TP_SERVICE
			."')";

// 2015-01-06
//NA, FD will not get an OKZ, i.e.
//only report with valid okz!
//$sql .= " AND okz IN ('OKZ1', 'OKZ2','OKZ3')";
	

		//dev-safety:
		$sql .= ' AND user_id IN (SELECT usr_id FROM usr_data)';
		$sql .= ' AND user_id NOT IN (6, 13)'; //root, anonymous
		$sql .= ' AND hist_user.is_active = 1'; //exclude inactive users
		



		//ERROR-LOG:
		$sql .= " AND user_id NOT IN ("
			." SELECT DISTINCT usr_id FROM wbd_errors WHERE"
			." resolved=0"
			." AND reason IN ('WRONG_USERDATA','USER_EXISTS_TP', 'USER_EXISTS', 'USER_SERVICETYPE')"
			//." AND action='new_user'"
			.")";


		
		
		if($LIMIT_RECORDS){
			$sql .= 'LIMIT ' .$LIMIT_RECORDS;
		}

		$ret = array();
		$result = $this->ilDB->query($sql);

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		while($record = $this->ilDB->fetchAssoc($result)) {

			$uutils = gevUserUtils::getInstanceByObjOrId($record['user_id']);
			if ($uutils->hasDoneWBDRegistration()) {

				$udata = $this->_map_userdata($record);

				$valid = $this->validateUserRecord($udata);

				if($valid === true){

					$ret[] = wbdDataConnector::new_user_record($udata);
					//set last_wbd_report!
					//better wait for success, here?!
					//$this->_set_last_wbd_report('hist_user', $record['row_id']);
				} else {

					/*storeWBDError(	
						$action, 
						$reason_str, 
						$internal=0,
						$usr_id=0, 
						$crs_id=0, 
						$booking_id=0
					)
					*/

					$this->log->storeWBDError('new_user',
							str_replace('<br>', '', $valid),
							1,
							$udata['internal_agent_id'],
							0,
							$udata['row_id']
						);



					$this->broken_newusers[] = array(
						$valid,
						$udata
					);
				}
			
			}

		}
		$this->valid_newusers = $ret;
		return $ret;
	}

	public function success_new_user($row_id){
		$this->_set_last_wbd_report('hist_user', $row_id);
	}

	public function fail_new_user($row_id, $e){
		print "\n";
		print 'ERROR on newUser: ';
		print $row_id;
		print "\n";
		print_r($e->getReason());
		//print_r($e);
		
		print "\n\n";

		//ERROR-LOG:
		$sql = " SELECT user_id FROM hist_user WHERE"
			." row_id=" .$row_id; 
		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);

		$this->log->storeWBDError('new_user',
			$e->getReason(),
			0,
			$record['user_id'],
			0,
			$row_id
		);
	}



	/**
	 * get users with outdated records in BWV-DB:
	 * userdata changed after last reporting
	 *
	 * only users with bwv-id, though - it must be set to
	 * update a user!
	 *
	 * @param
	 * @return array of user-records
	 */
	public function get_updated_users() {

		global $GET_UPDATED_USERS;
		if(! $GET_UPDATED_USERS){
			return array();
		}


		$sql = "
			SELECT
				*
			FROM
				hist_user
			WHERE
				hist_historic = 0
			AND NOT
				bwv_id = '-empty-'
			AND
				last_wbd_report IS NULL
			";


		// manage accounts for TP_Service only:
		$sql .= " AND wbd_type = '" .self::WBD_TP_SERVICE ."'";


		//dev-safety:
		$sql .= ' AND user_id in (SELECT usr_id FROM usr_data)';
		$sql .= ' AND user_id NOT IN (6, 13)'; //root, anonymous

		//ERROR-LOG:
		$sql .= " AND user_id NOT IN ("
			." SELECT DISTINCT usr_id FROM wbd_errors WHERE"
			." resolved=0"
			." AND reason IN ('WRONG_USERDATA','USER_EXISTS_TP', 'USER_SERVICETYPE', 'USER_DIFFERENT_TP', 'USER_DEACTIVATED', 'USER_UNKNOWN', 'CREATE_DUPLICATE')"
			//." AND action='new_user'"
			.")";


		//$sql .= " GROUP BY user_id";

		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {
			$udata = $this->_map_userdata($record);

			$valid = $this->validateUserRecord($udata);

			if($valid === true){
				$ret[] = wbdDataConnector::new_user_record($udata);
			} else {
			
				$this->log->storeWBDError('update_user',
					str_replace('<br>', '', $valid),
					1,
					$udata['internal_agent_id'],
					0,
					$udata['row_id']
				);

				$this->broken_updatedusers[] = array(
					$valid,
					$udata
				);
			}
		}
		return $ret;
	}

	public function success_update_user($row_id){
		$this->_set_last_wbd_report('hist_user', $row_id);
	}

	public function fail_update_user($row_id, $e){
		print "\n";
		print 'ERROR on updateUser: ';
		print $row_id;
		print "\n";
		print_r($e->getReason());
		print "\n\n";


		//ERROR-LOG:
		$sql = " SELECT user_id FROM hist_user WHERE"
			." row_id=" .$row_id; 
		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);

		$this->log->storeWBDError('update_user',
			$e->getReason(),
			0,
			$record['user_id'],
			0,
			$row_id
		);



	}



	/**
	 * get edu-records for courses 
	 * that have not been submitted to the WBD
	 *
	 *
	 * @param
	 * @return array of edu-records
	 */
	public function get_new_edu_records() {
		
		global $GET_NEW_EDURECORDS;
		if(! $GET_NEW_EDURECORDS){
			return array();
		}

		$sql = "
			SELECT
				*, hist_usercoursestatus.row_id as row_id,
				hist_usercoursestatus.begin_date as course_begin,
				hist_usercoursestatus.end_date as course_end
			FROM
				hist_usercoursestatus

			INNER JOIN
				hist_course
			ON
				hist_usercoursestatus.crs_id = hist_course.crs_id

			INNER JOIN
				hist_user
			ON
				hist_usercoursestatus.usr_id = hist_user.user_id

			WHERE
				hist_usercoursestatus.hist_historic = 0
				AND
				hist_course.hist_historic = 0
				AND
				hist_user.hist_historic = 0
				AND
				hist_user.bwv_id != '-empty-'

			AND
				hist_usercoursestatus.function  IN ('Mitglied', 'Teilnehmer')
			AND 
				hist_usercoursestatus.okz IN ('OKZ1', 'OKZ2','OKZ3')
			AND
				hist_usercoursestatus.participation_status = 'teilgenommen'
			AND
				hist_usercoursestatus.last_wbd_report IS NULL
			AND
				hist_usercoursestatus.wbd_booking_id IS NULL

			AND
				hist_usercoursestatus.credit_points > 0
			AND 
				(hist_usercoursestatus.end_date > '2013-12-31' 
					OR
					(hist_course.type = 'Selbstlernkurs' 
						AND 
					hist_usercoursestatus.begin_date > '2013-12-31' 
					)

				)
			";


		// report edupoints for TP_Service, TP_Basis and Edu_Provider only:
		$sql .= " AND wbd_type IN ('"
			.self::WBD_TP_SERVICE."', '"
			.self::WBD_EDU_PROVIDER."', '"
			.self::WBD_TP_BASIS
			."')";


		//dev-safety:
		$sql .= ' AND usr_id in (SELECT usr_id FROM usr_data)';
		$sql .= ' AND user_id NOT IN (6, 13)'; //root, anonymous

		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {

			//there must not be a last_wbd_report for the pair of usr_id, crs_id!
			$sql ="
				SELECT row_id FROM hist_usercoursestatus
				WHERE usr_id = " .$record['usr_id']
			 ."	AND crs_id = ".$record['crs_id']
			 ." AND NOT last_wbd_report IS NULL" ;



			$temp_result = $this->ilDB->query($sql);
			$num_rows = $temp_result->result->num_rows;

			if($num_rows == 0){


				//ERROR-LOG:
				$sql = " SELECT id FROM wbd_errors WHERE"
					." resolved=0"
					." AND usr_id = ".$record['usr_id']
			 		." AND crs_id = ".$record['crs_id']
					;

				$temp_result = $this->ilDB->query($sql);
				$num_rows = $temp_result->result->num_rows;
	
				if($num_rows == 0){

					$edudata = $this->_map_edudata($record);
					
					if($edudata['study_type_selection'] == 'selbstgesteuertes E-Learning'){
						$edudata['till'] = $edudata['from'];
					}

					//these are _new_ edu-records:
					$edudata['score_code'] = 'Meldung';


					$valid = $this->validateEduRecord($edudata);

					if($valid === true){
						$ret[] = wbdDataConnector::new_edu_record($edudata);
					} else {


						$this->log->storeWBDError('new_edurecord',
							str_replace('<br>', '', $valid),
							1,
							$record['usr_id'],
							$record['crs_id'],
							$edudata['internal_booking_id']
						);



						$this->broken_newedurecords[] = array(
							$valid,
							$edudata
						);
					}
				}
			}


		}

		$this->valid_newedurecords = $ret;
		return $ret;
	}


	public function success_new_edu_record($row_id){
		//set last_wbd_report!
		$this->_set_last_wbd_report('hist_usercoursestatus', $row_id);
	}
	public function set_booking_id($row_id, $booking_id){
		//also, set booking id
		$sql = "
			UPDATE hist_usercoursestatus
			SET wbd_booking_id = '$booking_id'
			WHERE row_id=$row_id
		";
		$result = $this->ilDB->query($sql);
	}

	public function fail_new_edu_record($row_id, $e){
		print "\n";
		print 'ERROR on newEduRecord: ';
		print $row_id;
		print "\n";
		print_r($e->getReason());
		print "\n\n";


		//ERROR-LOG:
		$sql = " SELECT usr_id, crs_id FROM hist_usercoursestatus WHERE"
			." row_id=" .$row_id; 
		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);

		$this->log->storeWBDError('new_edurecord',
			$e->getReason(),
			0,
			$record['usr_id'],
			$record['crs_id'],
			$row_id
		);



	}





	/**
	 * get edu-records for courses 
	 * if the current record differs from a record
	 * that was allready sent to the WBD
	 *
	 * @param
	 * @return array of edu-records
	 */
	public function get_changed_edu_records() {

		global $GET_CHANGED_EDURECORDS;
		if(! $GET_CHANGED_EDURECORDS){
			return array();
		}
		
		die('NOT IMPLEMENTED');

		$sql = "
			SELECT
				row_id, last_wbd_report
			FROM
				hist_usercoursestatus
			WHERE
				NOT last_wbd_report IS NULL
			AND
				hist_historic = 1
			GROUP BY
				usr_id,
				crs_id
			";

		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {
			$row_id = $record['row_id'];
			$current_record = $this->_get_current_record('hist_usercoursestatus', $record['row_id']);
			//if there is a newer record, use this.
			if (($current_record['row_id'] != $record['row_id'])
				&& !$current_record['last_wbd_report']
				){
				//get all info on that row:
				$sql = "
					SELECT
						*, hist_usercoursestatus.row_id as row_id
					FROM
						hist_usercoursestatus

					INNER JOIN
						hist_course
					ON
						hist_usercoursestatus.crs_id = hist_course.crs_id

					INNER JOIN
						hist_user
					ON
						hist_usercoursestatus.usr_id = hist_user.user_id
					";
				$sql .= " WHERE	hist_usercoursestatus.row_id = " .$current_record['row_id'];

				$temp_result = $this->ilDB->query($sql);
				$temp_record = $this->ilDB->fetchAssoc($temp_result);

				$edudata = $this->_map_edudata($temp_record);
				$ret[] = wbdDataConnector::new_edu_record($edudata);

				//set last_wbd_report on the current record
				//$row_id = $current_record['row_id'];
			}

			//set last_wbd_report!
			//$this->_set_last_wbd_report('hist_usercoursestatus', $row_id);
		}

		return $ret;
	}




	/**
	 * get all bwv-ids
	 *
	 * @param 
	 * @return array
	 */	
	public function get_all_bwv_ids() {

		global $IMPORT_FOREIGN_EDURECORDS;
		if(! $IMPORT_FOREIGN_EDURECORDS){
			return array();
		}
		
//		return array('20141021-101537-86');
		
		$ret = array();
		$sql = "SELECT bwv_id FROM hist_user "
			." WHERE bwv_id != '-empty-'"
			." AND hist_historic=0";

		// for TP_Service only:
		/*
		$sql .= " AND wbd_type IN ('"
			.self::WBD_TP_SERVICE."', '"
			.self::WBD_TP_BASIS
			."')";
		*/

		$sql .= " AND wbd_type='" .self::WBD_TP_SERVICE ."'"; 
	
//		$sql .= " AND user_id=6776"; 


		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {
			$ret[] = $record['bwv_id'];
		}
		return $ret;
	}
	
	public function fail_get_external_edu_records($bwv_id, $e) {
		print 'ERROR while getting edurecords: ';
		print($bwv_id);
		print "\n";
		print_r($e->getReason());
		//print_r($e);
		print "\n";
	}
	

	/**
	 * save external edu-record for user
	 *
	 * @param string $bwv_id
	 * @param array $edu_records
	 * @return boolean
	 */

	public function save_external_edu_records($bwv_id, $edu_records) {
		global $IMPORT_FOREIGN_EDURECORDS;
		if(! $IMPORT_FOREIGN_EDURECORDS){
			return true;
		}

		//print_r($edu_records);

		$recs = $edu_records['WeiterbildungsPunkteBuchungListe'];
		if(count($recs) > 0){
			foreach ($recs as $wpentry) {
				//check, if the booking-ids are under our control
				$booking_id = $wpentry['WeiterbildungsPunkteBuchungsId'];
				$sql = "SELECT wbd_booking_id 
						FROM hist_usercoursestatus 
						WHERE wbd_booking_id = '$booking_id'";
				
				$temp_result = $this->ilDB->query($sql);
				$num_rows = $temp_result->result->num_rows;

				if($num_rows == 0){
					// this is truly a foreign record

					// ! Storno/Korrektur !
					if($wpentry['Korrekturbuchung'] != 'false'){
						print_r($wpentry);
						die('Korrekturbuchung - not implemented');
					}



					if($wpentry['Storniert'] != 'false'){
						
						print "\n\n STORNO: \n";
						print_r($wpentry);

						die('.');	
					}



					if( $wpentry['Storniert'] == 'false' &&
						$wpentry['Korrekturbuchung'] == 'false'){

						$rec = array(
							'bwv_id' 		=> $wpentry['VermittlerId'],
							'wbd_booking_id'=> $booking_id,
							'credit_points'	=> $wpentry['WeiterbildungsPunkte'],
							'begin'			=> $wpentry['SeminarDatumVon'],
							'end'			=> $wpentry['SeminarDatumBis'],
							'title' 		=> $wpentry['Weiterbildung'],
							'wbd_topic'		=> $wpentry['LernInhalt'],
							'type'			=> $wpentry['LernArt']
						);

						$crs_id = $this->importSeminar($rec);
						$this->assignUserToSeminar($rec, $crs_id);
						print "\n\n imported seminar: \n";
						print_r($wpentry);
					}


				} else {
					//print "\n not a foreign record";
				}
			} 
		} else {
			print "\n no records.";
		}
		
		return true;
	}




	public function get_storno_edu_records() {
		global $STORNO_EDURECORDS;
		if(! $STORNO_EDURECORDS){
			return array();
		}

		$sql = "
			SELECT
				*, hist_usercoursestatus.row_id as row_id,
				hist_usercoursestatus.begin_date as course_begin,
				hist_usercoursestatus.end_date as course_end
			FROM
				hist_usercoursestatus

			INNER JOIN
				hist_course
			ON
				hist_usercoursestatus.crs_id = hist_course.crs_id
			AND	hist_course.hist_historic = 0 
			INNER JOIN
				hist_user
			ON
				hist_usercoursestatus.usr_id = hist_user.user_id
			AND	hist_user.hist_historic = 0
			WHERE
				hist_user.bwv_id != '-empty-'
		
			";


		// report edupoints for TP_Service, TP_Basis and Edu_Provider only:
		$sql .= " AND wbd_type IN ('"
			.self::WBD_TP_SERVICE."', '"
			.self::WBD_EDU_PROVIDER."', '"
			.self::WBD_TP_BASIS
			."')";


		//dev-safety:
		$sql .= ' AND usr_id in (SELECT usr_id FROM usr_data)';
		$sql .= ' AND user_id NOT IN (6, 13)'; //root, anonymous
		

		$sql .= ' and hist_usercoursestatus.row_id IN (
260604,
260603		
		
		)';
		$sql .= ' AND FALSE';
		
print $sql;

		$result = $this->ilDB->query($sql);
		$ret = array();
		while($record = $this->ilDB->fetchAssoc($result)) {
		
			$edudata = $this->_map_edudata($record);
			$ret[] = wbdDataConnector::new_edu_record($edudata);
		}
		
		return $ret;
		
		
	}
	
	//on success/failure:
	public function success_storno_edu_records($row_id, $booking_id){
		/*
		$sql = "UPDATE  hist_usercoursestatus SET"
		." hist_historic=1, "
		." wbd_booking_id='$booking_id'" 
		." WHERE row_id=" .$row_id;
		*/
		//duplicate entry?

	}

	public function fail_storno_edu_records($row_id, $e){
		print 'ERROR during cancellation of edurecords: ';
		print($row_id);
		print "\n";
		print_r($e->getReason());
		//print_r($e);
		print "\n";
	}

	/**
	* BLOCK exit user
	*/
	public function get_exit_users() {
		global $GET_NEW_EXIT_USER;
		if(! $GET_NEW_EXIT_USER){
			return array();
		}
		
		$sql = "SELECT * FROM hist_user"
					." WHERE hist_historic = ".$this->ilDB->quote(0, "integer")
					." AND NOT	bwv_id = ".$this->ilDB->quote($this->empty_bwv_id_text, "text").""
					." AND NOT exit_date = ".$this->ilDB->quote($this->empty_date_text, "text").""
					." AND exit_date < CURDATE()"
					." AND exit_date_wbd = ".$this->ilDB->quote($this->empty_date_text, "text")."";
		// manage accounts for TP_Service only:
		$sql .= " AND ".$this->ilDB->in("wbd_type", array(self::WBD_TP_SERVICE), false, "text")."";

		//dev-safety:
		$sql .= ' AND user_id in (SELECT usr_id FROM usr_data)';
		$sql .= ' AND user_id NOT IN (6, 13)'; //root, anonymous

		//ERROR-LOG:
		$sql .= " AND user_id NOT IN ("
			." SELECT DISTINCT usr_id FROM wbd_errors WHERE"
			." resolved=0"
			." AND ".$this->ilDB->in("reason", 
										array('WRONG_USERDATA', 'USER_SERVICETYPE', 'USER_DIFFERENT_TP', 'USER_UNKNOWN', 'NO_RELEASE', 'USER_DEACTIVATED'), false, "text"
									).""
			//." AND action='new_user'"
			.")";

		$res = $this->ilDB->query($sql);
		$ret = array();
		while($row = $this->ilDB->fetchAssoc($res)) {
			$udata = $this->_map_userdata($row);

			$valid = $this->validateUserRecord($udata);

			if($valid === true){
				$ret[] = wbdDataConnector::new_user_record($udata);
			} else {
				$this->log->storeWBDError('exit_user',
					str_replace('<br>', '', $valid),
					1,
					$udata['internal_agent_id'],
					0,
					$udata['row_id']
				);

				$this->broken_exitusers[] = array(
					$valid,
					$udata
				);
			}

		}

		return $ret;
	}

	public function success_exit_user($row_id) {
		$this->setWbdExitUserData($row_id);
		$this->_set_last_wbd_report('hist_user', $row_id);
	}
	
	public function fail_exit_user($row_id, $a_exception) {
		print "\n";
		print 'ERROR on updateUser: ';
		print $row_id;
		print "\n";
		print_r($a_exception->getReason());
		print "\n\n";


		//ERROR-LOG:
		$sql = " SELECT user_id FROM hist_user WHERE"
			." row_id=" .$row_id; 
		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);

		$this->log->storeWBDError('exit_user',
			$a_exception->getReason(),
			0,
			$record['user_id'],
			0,
			$row_id
		);
	}

}

//normalize classname for wdb-connector-script
class WBDDataAdapter extends gevWBDDataConnector {}








/*
* ------------- DEBUG ------------
*/
if($DEBUG_HTML_OUT){

	$cls = new gevWBDDataConnector();
	
	print '<h3>new users:</h3>';
	$cls->export_get_new_users('html');

	print '<h2> total new users: ' .count($cls->valid_newusers) .'</h2>';
	print '<h2> invalid records: ' .count($cls->broken_newusers) .'</h2>';
//	print_r($cls->broken_newusers);
	
	
	
	print '<br>';
	print 'error';
	foreach($cls->broken_newusers[0][1] as $hl=>$v){
		print ', ' .$hl;
	}
	
	foreach($cls->broken_newusers as $entry){
		print '<br>';
		print str_replace('<br>', '', $entry[0]);
		
		foreach( $entry[1] as $k=>$v){
			print ', ' .$v;
		}
	
	}
	
	print '<hr>';


	print '<h3>updated users:</h3>';
	$cls->export_get_updated_users('html');

	print '<br>';
	print '<h2> invalid records: ' .count($cls->broken_updatedusers) .'</h2>';
	print 'error';
	foreach($cls->broken_updatedusers[0][1] as $hl=>$v){
		print ', ' .$hl;
	}
	
	foreach($cls->broken_updatedusers as $entry){
		print '<br>';
		print str_replace('<br>', '', $entry[0]);
		
		foreach( $entry[1] as $k=>$v){
			print ', ' .$v;
		}
	}

	

	print '<hr>';



	print '<h3>new edu-records:</h3>';
	$cls->export_get_new_edu_records('html');

	print '<h2> total new edurecords: ' .count($cls->valid_newedurecords) .'</h2>';
	print '<h2> invalid edurecords: ' .count($cls->broken_newedurecords) .'</h2>';
//	print_r($cls->broken_newedurecords);
	
	print 'error';
	foreach($cls->broken_newedurecords[0][1] as $hl=>$v){
		print ', ' .$hl;
	}
	
	foreach($cls->broken_newedurecords as $entry){
		print '<br>';
		print str_replace('<br>', '', $entry[0]);
		
		foreach( $entry[1] as $k=>$v){
			print ', ' .$v;
		}
	
	}
	
	print '<hr>';


	print '<h3>exit user:</h3>';
	$cls->export_get_exit_users('html');

	print '<h2> invalid records: ' .count($cls->broken_exitusers) .'</h2>';
//	print_r($cls->broken_newedurecords);
	
	print 'error';
	foreach($cls->broken_exitusers[0][1] as $hl=>$v){
		print ', ' .$hl;
	}
	
	foreach($cls->broken_exitusers as $entry){
		print '<br>';
		print str_replace('<br>', '', $entry[0]);
		
		foreach( $entry[1] as $k=>$v){
			print ', ' .$v;
		}
	
	}
	/*
	print '<hr>';
	print '<h3>changed edu-records:</h3>';
	$cls->export_get_changed_edu_records('html');
	
	*/
}

?>
