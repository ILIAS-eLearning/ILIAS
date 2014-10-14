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


$SET_LASTWBDRECORD = false;
$SET_BWVID = false;

$GET_NEW_USERS = true;
$GET_UPDATED_USERS = false;
$GET_NEW_EDURECORDS = false;
$GET_CHANGED_EDURECORDS = false;
$IMPORT_FOREIGN_EDURECORDS = false;


$DEBUG_HTML_OUT = isset($_GET['debug']);
echo('<pre>');



//reset ilias for calls from somewhere else
$basedir = __DIR__; 
$basedir = str_replace('/Services/GEV/WBD/classes', '', $basedir);
chdir($basedir);

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


	public function __construct() {
		
		parent::__construct();
	}


	/**
	 * basically mapping DB-fields to WBD-keys
	 */

	private function _extract_house_nr($streetnr){
		//find first number in string
	    $len = strlen($streetnr);
	    $pos = False;
	    for($i = 0; $i < $len; $i++) {
	        if(is_numeric($streetnr[$i])) {
	        	$pos = $i-1;
	        }
	    }		
	    $street = trim(substr($streetnr, 0, $pos));
	    $nr = trim(substr($streetnr, $pos));
		return array(
			'street' => $street, 
			'nr' =>$nr
		);
	}

	private function _map_userdata($record) {
		//print '<pre>';
		//print_r($record);
		$street_and_nr = $this->_extract_house_nr( $record['street']);
		$udata = array(
				'internal_agent_id' => $record['user_id']
				,'title' 			=> $this->VALUE_MAPPINGS['salutation'][$record['gender']]
				,'first_name' 		=> $record['firstname']
				,'last_name' 		=> $record['lastname']
				,'birthday' 		=> $record['birthday']
				,'street'			=> $street_and_nr['street']
				,'house_number'		=> $street_and_nr['nr']
				,'zipcode'			=> $record['zipcode']
				,'city'				=> $record['city']
				,'phone_nr'			=> $record['phone_nr']
				,'mobile_phone_nr'	=> $record['mobile_phone_nr']
				,'email'			=> $record['email']

				//....
				,'auth_email' => $record['email']
				,'auth_phone_nr' => $record['mobile_phone_nr']

				,'agent_registration_nr' => '' 				//optional
				,'agency_work' => $record['okz'] 			//OKZ
				,'agent_state' => $this->VALUE_MAPPINGS['agent_status'][$record['agent_status']]	//Status
				,'email_confirmation' => ''					//Benachrichtigung?


				,"row_id" => $record["row_id"]
				
				,'wbd_type' => $record['wbd_type'] //debug
			);

		return $udata;
	}

	private function _map_edudata($record) {
		//print '<pre>';
		//print_r($record);
		$edudata = array(
			"name" 			=> $record["lastname"]
			,"first_name" 	=> $record["firstname"]
			,"birthday_or_internal_agent_id" => $record['user_id']
			,"agent_id" 	=> $record['bwv_id']
			,"from" 		=> date('d.m.Y', $record['begin_date'])
			,"till" 		=> date('d.m.Y', $record['end_date'])
			,"score"		=> $record['credit_points']

			,"training"	 			=> $record['title'] //or template?
			,"study_type_selection" => $this->VALUE_MAPPINGS['course_type'][$record['type']] // "Präsenzveranstaltung" | "Selbstgesteuertes E-Learning" | "Gesteuertes E-Learning";
	
			//....
			/*
			"internal_booking_id" => "", //$record['crs_ref_id'],
			"study_content" => "", //Spartenübergreifend",
			*/
			,"row_id" => $record["row_id"]
		);
		return $edudata;	
	}



	//HISTORY TABLES:
	/**
	 * go back one step in history
	 */


	/*
ONE STEP IS NOT ENOUGH !
	private function _get_previous_historic_record($table, $row_id) {
		switch ($table) {
			case 'hist_user':
				$search  = "
					user_id=(SELECT user_id FROM $table WHERE row_id=$row_id)
				";
				break;
			case 'hist_course':
				$search  = "
					crs_id=(SELECT crs_id FROM $table WHERE row_id=$row_id)
				";
				break;
			case 'hist_usercoursestatus':
				$search  = "
					usr_id=(SELECT usr_id FROM $table WHERE row_id=$row_id)
					AND 
					crs_id=(SELECT crs_id FROM $table WHERE row_id=$row_id)
				";
				break;
		}

		$search .="AND hist_version=(
			SELECT hist_version - 1 as prev_version 
			FROM $table WHERE row_id=$row_id
			)";

		$sql = "
			SELECT * FROM $table 
			WHERE $search;
		";

		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);
		return $record;
	}
*/

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




	/*
	* ------------- IMPLEMENTATION ------------
	*/


	/**
	 * get users that do not have a BWV-ID yet
	 * 
	 * @param 
	 * @return array of user-records
	 */
	public function get_new_users() {
		global $GET_NEW_USERS;
		if(! $GET_NEW_USERS){
			return array();
		}


		//userUtils::hasWBDRelevantRole


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


/*
		// new accounts for TP_Service, TP_Basic only:
		$sql .= " AND wbd_type IN ('"
			.self::WBD_TP_BASIS."', '".self::WBD_TP_SERVICE
			."')";

*/		
		//dev-safety:
		$sql .= ' AND user_id IN (SELECT usr_id FROM usr_data)';
		$sql .= ' AND user_id NOT IN (6, 13)'; //root, anonymous
		
		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {
			$udata = $this->_map_userdata($record);
			$ret[] = wbdDataConnector::new_user_record($udata);

			//set last_wbd_report!
			$this->_set_last_wbd_report('hist_user', $record['row_id']);

		}
		return $ret;
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

		//$sql .= " GROUP BY user_id";


		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {
			$udata = $this->_map_userdata($record);
			$ret[] = wbdDataConnector::new_user_record($udata);

			//set last_wbd_report!
			$this->_set_last_wbd_report('hist_user', $record['row_id']);

		}
		return $ret;
	}



	/**
	 * get edu-records for courses that 
	 * started 3 months ago (or more)
	 * and have not been submitted to the WBD
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
				*,hist_usercoursestatus.row_id as row_id
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
				hist_usercoursestatus.function = 'Mitglied'
			AND 
				hist_usercoursestatus.participation_status = 'teilgenommen'
			AND 
				hist_usercoursestatus.last_wbd_report IS NULL

			";


		// report edupoints for TP_Service, Edu_Provider only:
		$sql .= " AND wbd_type IN ('"
			.self::WBD_TP_SERVICE."', '".self::WBD_EDU_PROVIDER
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

				$edudata = $this->_map_edudata($record);	
				//these are _new_ edu-records:
				$edudata['score_code'] = 'Meldung';
				$ret[] = wbdDataConnector::new_edu_record($edudata);

				//set last_wbd_report!
				$this->_set_last_wbd_report('hist_usercoursestatus', $record['row_id']);
			}
			

		}
		return $ret;
	}


	/**
	 * get edu-records for courses that 
	 * started 3 months ago (or more)
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
				$row_id = $current_record['row_id'];
			} 
			
			//set last_wbd_report!
			$this->_set_last_wbd_report('hist_usercoursestatus', $row_id);
		}

		return $ret;
	}




	/**
	 * set BWV-ID for user
	 *  
	 * @param string $user_id
	 * @param string $bwv_id
	 * @param date $certification_begin
	 * @return boolean
	 */

	public function set_bwv_id($user_id, $bwv_id, $certification_begin) {
		global $SET_BWVID;
		if(! $SET_BWVID){
			return true;
		}

		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		$uutils = gevUserUtils::getInstanceByObjOrId($user_id);
		$uutils->setWBDBWVId($bwv_id);
		//$uutils->setWBDFirstCertificationPeriodBegin($certification_begin);


		//write last_wbd_report....
		$sql = "
			SELECT $row_id FROM hist_user 
			WHERE user_id = $user_id
			AND hist_historic = 0
		";
		$result = $this->ilDB->query($sql);
		$record = $this->ilDB->fetchAssoc($result);
		$this->_set_last_wbd_report('hist_user', $record['row_id']);
		return true;
	}

	
	/**
	 * set edu-record for user
	 *  
	 * @param array $edu_record
	 * @return boolean
	 */

	public function set_edu_record($edu_record) {
		global $IMPORT_FOREIGN_EDURECORDS;
		if(! $IMPORT_FOREIGN_EDURECORDS){
			return true;
		}
		print '<pre>';
		print_r($edu_record);
		die();
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
	print '<hr>';


	print '<h3>updated users:</h3>';
	$cls->export_get_updated_users('html');
	print '<hr>';

	print '<h3>new edu-records:</h3>';
	$cls->export_get_new_edu_records('html');
	print '<hr>';

	print '<h3>changed edu-records:</h3>';
	$cls->export_get_changed_edu_records('html');

}

//$cls->set_bwv_id(255, 'XXXXXXXX');


?>
