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

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevAttendanceByEmployeeGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = catTitleGUI::create()
						->title("gev_rep_attendance_by_employee_title")
						->subTitle("gev_rep_attendance_by_employee_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

		$this->table = catReportTable::create()
						->column("lastname", "lastname")
						->column("firstname", "firstname")
						->column("email", "email")
						->column("adp_number", "gev_adp_number")
						->column("job_number", "gev_job_number")
						->column("od_bd", "gev_od_bd")
						->column("org_unit", "gev_org_unit_short")
						->column("position_key", "gev_agent_key")
						->column("custom_id", "gev_training_id")
						->column("venue", "gev_location")
						->column("type", "gev_learning_type")
						->column("date", "date")
						->column("credit_points", "gev_credit_points")
						->column("booking_status", "gev_booking_status")
						->column("participation_status", "gev_participation_status")
						->template("tpl.gev_attendance_by_employee_row.html", "Services/GEV/Reports")
						;

		$this->query = catReportQuery::create()
						->select("usr.user_id")
						->select("usr.lastname")
						->select("usr.firstname")
						->select("usr.email")
						->select("usr.adp_number")
						->select("usr.job_number")
						->select("usr.org_unit_above1")
						->select("usr.org_unit_above2")
						->select("usr.org_unit")
						->select("usr.position_key")
						->select("crs.custom_id")
						->select("crs.venue")
						->select("crs.type")
						->select("usrcrs.credit_points")
						->select("usrcrs.booking_status")
						->select("usrcrs.participation_status")
						->select("usrcrs.usr_id")
						->select("usrcrs.crs_id")
						->select("crs.begin_date")
						->select("crs.end_date")
						->select("crs.edu_program")
						->select("crs.title")
						->from("hist_usercoursestatus usrcrs")
						->join("hist_user usr")
							->on("usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0")
						->join("hist_course crs")
							->on("crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0")
						->compile()
						;

		$this->allowed_user_ids = $this->user_utils->getEmployees();
		$this->filter = catFilter::create()
						->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									)
						->multiselect( "org_unit"
									 , $this->lng->txt("gev_org_unit_short")
									 , array("usr.org_unit", "org_unit_above1", "org_unit_above2")
									 , $this->user_utils->getOrgUnitNamesWhereUserIsSuperior()
									 , array()
									 )
						->multiselect("edu_program"
									 , $this->lng->txt("gev_edu_program")
									 , "edu_program"
									 , gevCourseUtils::getEduProgramsFromHisto()
									 , array()
									 )
						->multiselect("type"
									 , $this->lng->txt("gev_course_type")
									 , "type"
									 , gevCourseUtils::getLearningTypesFromHisto()
									 , array()
									 )
						->multiselect("template_title"
									 , $this->lng->txt("crs_title")
									 , "template_title"
									 , gevCourseUtils::getTemplateTitleFromHisto()
									 , array()
									 )
						->multiselect("participation_status"
									 , $this->lng->txt("gev_participation_status")
									 , "participation_status"
									 , gevCourseUtils::getParticipationStatusFromHisto()
									 , array()
									 )
						->multiselect("position_key"
									 , $this->lng->txt("gev_position_key")
									 , "position_key"
									 , gevUserUtils::getPositionKeysFromHisto()
									 , array()
									 )
						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
						->static_condition("usrcrs.hist_historic = 0")
						->static_condition("(usrcrs.booking_status != '-empty-' OR usrcrs.participation_status != '-empty-')")
						->static_condition("usrcrs.booking_status != 'kostenfrei storniert'")
						->static_condition("usrcrs.function NOT IN ('Trainingsbetreuer', 'Trainer')")
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;

	}

	protected function transformResultRow($rec) {
		// credit_points
		if ($rec["credit_points"] == -1) {
			$rec["credit_points"] = $this->lng->txt("gev_table_no_entry");
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
		
		// od_bd
		if ( $rec["org_unit_above2"] == "-empty-") {
			if ($rec["org_unit_above1"] == "-empty-") {
				$rec["od_bd"] = $this->lng->txt("gev_table_no_entry");
			}
			else {
				$rec["od_bd"] = $rec["org_unit_above1"];
			}
		}
		else {
			$rec["od_bd"] = $rec["org_unit_above1"]."/".$rec["org_unit_above2"];
		}

		return $this->replaceEmpty($rec);
	}
	
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}
	
	
	protected function fetchData() {
		if ($this->query === null) {
			throw new Exception("catBasicReportGUI::fetchData: query not defined.");
		}
		
		$query = $this->query->sql()
			   . $this->queryWhere()
			   ; //die($query);
		
		$res = $this->db->query($query);
		$data = array();
		
		$found_users = array();
		
		while($rec = $this->db->fetchAssoc($res)) {
			$row = $this->transformResultRow($rec);
			$data[] = $row;
			$found_users[] = $row["user_id"]; 
		}
		
		$not_found_users = array_diff($this->allowed_user_ids, array_unique($found_users));
		
		$res = $this->db->query("SELECT usr.user_id"
								."     , usr.lastname"
								."     , usr.firstname"
								."     , usr.email"
								."     , usr.adp_number"
								."     , usr.job_number"
								."     , usr.org_unit_above1"
								."     , usr.org_unit_above2"
								."     , usr.org_unit"
								."     , usr.position_key"
								."  FROM hist_user usr"
								." WHERE ".$this->db->in("user_id", $not_found_users, false, "integer")
								."   AND hist_historic = 0"
								);

		while ($rec = $this->db->fetchAssoc($res)) {
			$rec["custom_id"] = null;
			$rec["venue"] = null;
			$rec["type"] = null;
			$rec["credit_points"] = null;
			$rec["booking_status"] = null;
			$rec["participation_status"] = null;
			$rec["usr_id"] = null;
			$rec["crs_id"] = null;
			$rec["begin_date"] = null;
			$rec["end_date"] = null;
			$rec["edu_program"] = null;
			$rec["title"] = null;
			$data[] = $this->transformResultRow($rec);
		}

		return $data;
	}
}

?>
