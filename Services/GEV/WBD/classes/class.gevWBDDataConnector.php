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

	public function __construct() {
		
		parent::__construct();
	}


	/**
	 * basically mapping DB-fields to WBD-keys
	 */
	private function _map_userdata($record) {
		//print '<pre>';
		//print_r($record);
		$udata = array(
				'title' => $this->VALUE_MAPPINGS['salutation'][$record['gender']]
				,'first_name' => $record['firstname']
				,'last_name' => $record['lastname']
				,'birthday' => $record['birthday']
				//....
			);

		return $udata;
	}

	private function _map_edudata($record) {
		//print '<pre>';
		//print_r($record);
		$edudata = array(
			"name" => $record["lastname"]
			,"first_name" => $record["firstname"]
			,"birthday_or_internal_agent_id" => $record['user_id']
			,"agent_id" => $record['bwv_id']
			,"from" => date('d.m.Y', $record['begin_date'])
			,"till" => date('d.m.Y', $record['end_date'])
			,"score" => $record['credit_points']
			
			,"training" => $record['title'] //or template?

			,"study_type_selection" => $this->VALUE_MAPPINGS['course_type'][$record['type']], // "Präsenzveranstaltung" | "Selbstgesteuertes E-Learning" | "Gesteuertes E-Learning";
	
			//....
			/*
			"internal_booking_id" => "", //$record['crs_ref_id'],
			"study_content" => "", //Spartenübergreifend",
			*/
		);
		return $edudata;	
	}




	/**
	 *
	 */
	/*
	private function _set_last_wbd_report_for_user($user_id) {
		//set hist_user.last_wbd_report to NOW;
	}
	*/


	/**
	 *
	 */
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






	/**
	 * get users that do not have a BWV-ID yet
	 * 
	 * @param 
	 * @return array of user-records
	 */
	public function get_new_users() {
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
			";

		//dev-safety:
		$sql .= ' AND user_id in (SELECT usr_id FROM usr_data)';

		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {
			$udata = $this->_map_userdata($record);
			$ret[] = wbdDataConnector::new_user_record($udata);

			//set last_wbd_report!

		}
		return $ret;
	}


	/**
	 * get users with outdated records in BWV-DB:
	 * userdata changed after last reporting
	 *
	 * @param 
	 * @return array of user-records
	 */
	public function get_updated_users() {

		//TODO: after last reporting.
		//now: changed at all!	

		//get all users, where 
		// -last_wbd_report is set 
		// -bwv-id is set 
		// -and are historic
		//order by hist-ts
		//
		//get currenct record of this user
		//report this record.

		$sql = "
			SELECT
				*
			FROM 
				hist_user
			WHERE
				hist_historic = 1
			AND 
				deleted = 0
			
			";

		//dev-safety:
		$sql .= ' AND user_id in (SELECT usr_id FROM usr_data)';

		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {

			$udata = $this->_get_user_record_for_id($record['user_id']);

			$hist_udata_record = $this->_get_previous_historic_record('hist_user', $record['row_id']);
			$hist_udata = $this->_get_user_record_for_id($record['user_id']);
			
			if($udata != $hist_udata){
				$ret[] = wbdDataConnector::new_user_record($udata);
			}
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
		$sql = "
			SELECT
				*
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
		//dev-safety:
		$sql .= ' AND usr_id in (SELECT usr_id FROM usr_data)';

		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {

			$edudata = $this->_map_edudata($record);
			
			//these are _new_ edu-records:
			$edudata['score_code'] = 'Meldung';

			$ret[] = wbdDataConnector::new_edu_record($edudata);

			//set last_wbd_report!

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


	}








}



//print '<pre>';
$cls = new gevWBDDataConnector();
//print_r($cls);
//$cls->export_get_new_users('html');
//$cls->export_get_updated_users('html');
$cls->export_get_new_edu_records('html');


/*
$a = $cls->new_user_record();
$b = $cls->new_user_record();
$c = $cls->new_user_record(array('first_name'=>'C'));

$a['first_name'] = 'A';
$b['last_name'] = 'B';



print_r($a);
print '<hr>';
print_r($b);
print '<hr>';
print_r($c);
*/
?>
