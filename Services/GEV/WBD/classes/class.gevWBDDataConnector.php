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
				'internal_agent_id' => $record['user_id']
				,'title' => $this->VALUE_MAPPINGS['salutation'][$record['gender']]
				,'first_name' => $record['firstname']
				,'last_name' => $record['lastname']
				,'birthday' => $record['birthday']
				//....

				,"row_id" => $record["row_id"]
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
	 * only users with bwv-id, though - it must be set to 
	 * update a user!
	 *
	 * @param 
	 * @return array of user-records
	 */
	public function get_updated_users() {

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

		//dev-safety:
		$sql .= ' AND user_id in (SELECT usr_id FROM usr_data)';

		//$sql .= " GROUP BY user_id";


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
		//dev-safety:
		$sql .= ' AND usr_id in (SELECT usr_id FROM usr_data)';



		$ret = array();
		$result = $this->ilDB->query($sql);
		while($record = $this->ilDB->fetchAssoc($result)) {

			//there must not be a last_wbd_report fot the pair of usr_id, crs_id!
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
			}
			

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
		$sql = "
			SELECT
				row_id
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
			$current_record = $this->_get_current_record('hist_usercoursestatus', $record['row_id']);
			//if there is a newer record, use this.
			if ($current_record['row_id'] != $record['row_id']){
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

				//set last_wbd_report!

			}
		}

		return $ret;
	}








}


/*
* ------------- DEBUG ------------
*/


$cls = new gevWBDDataConnector();

//$cls->_set_last_wbd_report('hist_user', 1);

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

?>
