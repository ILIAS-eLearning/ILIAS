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
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

ini_set("memory_limit", "1024M");

class gevAttendanceByEmployeeGUI extends catBasicReportGUI{
	protected $orgu_membeships;
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
						->column("title", "title")
						->column("venue", "gev_location")
						->column("type", "gev_learning_type")
						->column("date", "date")
						->column("credit_points", "gev_credit_points")
						->column("booking_status", "gev_booking_status")
						->column("participation_status", "gev_participation_status")
						->template("tpl.gev_attendance_by_employee_row.html", "Services/GEV/Reports")
						;
		
		$this->order = catReportOrder::create($this->table)
						->mapping("date", "crs.begin_date")
						->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
						->defaultOrder("lastname", "ASC")
						;

		$this->query = catReportQuery::create()
						->distinct()
						->select("usr.user_id")
						->select("usr.lastname")
						->select("usr.firstname")
						->select("usr.email")
						->select("usr.adp_number")
						->select("usr.job_number")
						->select("orgu.org_unit_above1")
						->select("orgu.org_unit_above2")
						->select_raw("GROUP_CONCAT(DISTINCT orgu.orgu_title SEPARATOR ', ') AS org_unit")
						->select("usr.position_key")
						->select("crs.custom_id")
						->select("crs.title")
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
						->from("hist_user usr")
						->left_join("hist_usercoursestatus usrcrs")
							->on("usr.user_id = usrcrs.usr_id AND (usrcrs.hist_historic = 0 OR usrcrs.hist_historic IS NULL)")
						->left_join("hist_course crs")
							->on("crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0")
						->left_join("hist_userorgu orgu")
							->on("orgu.usr_id = usr.user_id")
						->group_by("usr.user_id")
						->group_by("usrcrs.crs_id")
						->compile()
						;

		$this->allowed_user_ids = $this->user_utils->getEmployees();

		$never_skip = $this->user_utils->getOrgUnitsWhereUserIsDirectSuperior();
		array_walk($never_skip, 
			function (&$obj_ref_id) {
				$aux = new ilObjOrgUnit($obj_ref_id["ref_id"]);
				$obj_ref_id = $aux->getTitle();
			}
		);
		$skip_org_units_in_filter_below = array('Nebenberufsagenturen');
		array_walk($skip_org_units_in_filter_below, 
			function(&$title) { 
				$title = ilObjOrgUnit::_getIdsForTitle($title)[0];
				$title = gevObjectUtils::getRefId($title);
				$title = gevOrgUnitUtils::getAllChildrenTitles(array($title));
			}
		);
		$skip_org_units_in_filter = array();
		foreach ($skip_org_units_in_filter_below as $org_units) {
			$skip_org_units_in_filter = array_merge($skip_org_units_in_filter, $org_units);
		}
		array_unique($skip_org_units_in_filter);
		$skip_org_units_in_filter = array_diff($skip_org_units_in_filter, $never_skip);
		$org_units_filter = array_diff($this->user_utils->getOrgUnitNamesWhereUserIsSuperior(), $skip_org_units_in_filter);
		sort($org_units_filter);

		$this->filter = catFilter::create()
						->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									, false
									, " OR usrcrs.hist_historic IS NULL"
									)
						->multiselect( "org_unit"
									 , $this->lng->txt("gev_org_unit_short")
									 , array("orgu.orgu_title", "orgu.org_unit_above1", "orgu.org_unit_above2")
									 , $org_units_filter
									 , array()
									 , ""
									 , 300
									 , 160
									 )
						/*->multiselect("edu_program"
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
									 )*/
						->multiselect("template_title"
									 , $this->lng->txt("crs_title")
									 , "template_title"
									 , gevCourseUtils::getTemplateTitleFromHisto()
									 , array()
									 , ""
									 , 300
									 , 160
									 )
						->multiselect("participation_status"
									 , $this->lng->txt("gev_participation_status")
									 , "participation_status"
									 , array(	"teilgenommen"=>"teilgenommen"
									 			,"fehlt ohne Absage"=>"fehlt ohne Absage"
									 			,"fehlt entschuldigt"=>"fehlt entschuldigt"
									 			,"gebucht, noch nicht abgeschlossen"=>"nicht gesetzt")
									 , array()
									 , ""
									 , 220
									 , 160
									 , "text"
									 , "asc"
									 , true
									 )/*
						->multiselect("position_key"
									 , $this->lng->txt("gev_position_key")
									 , "position_key"
									 , gevUserUtils::getPositionKeysFromHisto()
									 , array()
									 )*/
						->static_condition($this->db->in("usr.user_id", $this->allowed_user_ids, false, "integer"))
						->static_condition(" usr.hist_historic = 0")
						->static_condition("( usrcrs.booking_status != '-empty-'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.participation_status != '-empty-'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.booking_status != 'kostenfrei storniert'"
										  ." OR usrcrs.hist_historic IS NULL )")
						->static_condition("(   usrcrs.booking_status != ".$this->db->quote('-empty-','text')
										  ." OR usrcrs.hist_historic IS NULL )" )
						->static_condition("orgu.action >= 0")
						->static_condition("orgu.hist_historic = 0")
						->static_condition("orgu.rol_title = 'Mitarbeiter'")
						/*->static_condition("IF(UNIX_TIMESTAMP(usrcrs.begin_date)=0 "
                                          ."OR usrcrs.begin_date IS NULL, TRUE,"
                                          ."UNIX_TIMESTAMP(usrcrs.begin_date)> orgu.in_ts)")
                 		->static_condition("IF(UNIX_TIMESTAMP(usrcrs.end_date)=0 "
                                          ."OR usrcrs.end_date IS NULL "
                                          ."OR orgu.out_ts IS NULL, TRUE,"
                                          ."UNIX_TIMESTAMP(usrcrs.end_date)< orgu.out_ts )")*/
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;
			$this->relevant_parameters = array(
				$this->filter->getGETName() => $this->filter->encodeSearchParamsForGET()
			);

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
			$rec["od_bd"] = $rec["org_unit_above2"]."/".$rec["org_unit_above1"];
		}

		if($rec["participation_status"] == "nicht gesetzt") {
			$rec["participation_status"] = "gebucht, noch nicht abgeschlossen";
		}

		return $this->replaceEmpty($rec);
	}
	
	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}
}