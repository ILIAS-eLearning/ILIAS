<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "AttendanceByEmployees"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*	Define title, table_cols and row_template.
*	Implement fetchData to retrieve the data you want
*
*	Add special _process_xls_XXX and _process_table_XXX methods
*	to modify certain entries after retrieving data.
*	Those methods must return a proper string.
*
*/

require_once("Services/GEV/Reports/classes/class.gevBasicReportGUI.php");

class gevAttendanceByEmployeeGUI extends gevBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = array(
			'title' => 'gev_rep_attendance_by_employee_title',
			'desc' => 'gev_rep_attendance_by_employee_desc',
			'img' => 'GEV_img/ico-head-edubio.png'
		);


		$this->table_cols = array(
			array("lastname", "lastname"),
			array("firstname", "firstname"),
			array("gev_bwv_id", "bwv_id"),
			array("gev_agent_key", "position_key"),
			array("gender", "gender"),
			array("gev_org_unit_short", "org_unit"),
			array("title", "title"),
			array("gev_training_id", "custom_id"),
			//array("gev_location", "venue"),
			//array("gev_provider", "provider"),
			array("gev_learning_type", "type"),
			array("date", "date"),
			array("gev_booking_status", "booking_status"),
			array("gev_participation_status", "participation_status")
		);

		$this->table_row_template= array(
			"filename" => "tpl.gev_attendance_by_employee_row.html", 
			"path" => "Services/GEV/Reports"
		);
	}
	
	protected function fetchData(){ 
		//fetch retrieves the data 
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$no_entry = $this->lng->txt("gev_table_no_entry");
		$user_utils = gevUserUtils::getInstance($this->target_user_id);
		$data = array();

		//when ordering the table, watch out for date!
		//_table_nav=date:asc:0
		//btw, what is the third parameter?
		if(isset($_GET['_table_nav'])){
			$this->external_sorting = true; //set to false again, 
											//if the field is not relevant

			$table_nav_cmd = split(':', $_GET['_table_nav']);
			switch ($table_nav_cmd[0]) { //field
				case 'date':
					$direction = strtoupper($table_nav_cmd[1]);
					$sql_order_str = " ORDER BY crs.begin_date ";
					$sql_order_str .= $direction;
					break;
				
				//append more fields, simply for performance...

				default:
					$this->external_sorting = false;
					$sql_order_str = " ORDER BY usr.lastname ASC";
					break;
			}
			
		}

		//get data
		$query =	 "SELECT usrcrs.usr_id, usrcrs.crs_id, "
					."		 usrcrs.booking_status, usrcrs.participation_status, usrcrs.okz, usrcrs.org_unit,"
					."		 usr.firstname, usr.lastname, usr.gender, usr.bwv_id, usr.position_key,"
					."		 crs.custom_id, crs.title, crs.type, crs.venue, crs.provider, crs.begin_date, crs.end_date "

 					."  FROM hist_usercoursestatus usrcrs "
					."  JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
					."  JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"

					."  WHERE ("
					."		(usrcrs.booking_status != '-empty-' OR usrcrs.participation_status != '-empty-')"
					."  	AND usrcrs.function NOT IN ('Trainingsbetreuer', 'Trainer')"
					."  )"
					
					. $this->queryWhen($this->start_date, $this->end_date)
					. $this->queryAllowedUsers()
					
					//."  ORDER BY usr.lastname ASC";
					.$sql_order_str;


		$res = $this->db->query($query);

		while($rec = $this->db->fetchAssoc($res)) {
			/*	
				modify record-entries here.
			*/			
			foreach ($rec as $key => $value) {
				
				if ($value == '-empty-' || $value == -1) {
					$rec[$key] = $no_entry;
					continue;
				}

				//date
				if( $rec["begin_date"] && $rec["end_date"] 
					&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
					){
					$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
					$end = new ilDate($rec["end_date"], IL_CAL_DATE);
					$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
					//$date = ilDatePresentation::formatPeriod($start,$end);
				} else {
					$date = '-';
				}
				$rec['date'] = $date;
			}
			
			$data[] = $rec;
		}

		return $data;
	}

	protected function queryWhen(ilDate $start, ilDate $end) {
		if ($this->query_when === null) {
			$this->query_when =
					 //" WHERE usr.user_id = ".$this->db->quote($this->target_user_id, "integer")
					//"  WHERE ".$this->db->in("usrcrs.function", array("Mitglied", "Teilnehmer", "Member"), false, "text")
					//."   AND ".$this->db->in("usrcrs.booking_status", array("gebucht", "kostenpflichtig storniert", "kostenfrei storniert"), false, "text")
					"   AND usrcrs.hist_historic = 0 "
					."   AND ( usrcrs.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
					."        OR usrcrs.end_date = '-empty-' OR usrcrs.end_date = '0000-00-00')"
					."   AND usrcrs.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
					;
		}
		
		return $this->query_when;
	}
	
	protected function queryAllowedUsers() {
		
		//get all users the current user is superior of:
		$allowed_user_ids = $this->user_utils->getEmployees();
		$query = " AND ".$this->db->in("usr.user_id", $allowed_user_ids, false, "integer");
	
		return $query;
	}

	//_process_ will modify record entries
	// xls means: only for Excel-Export
	// date is the key in data-array 
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}
}

?>
