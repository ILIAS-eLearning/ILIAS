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
						->column("bwv_id", "gev_bwv_id")
						->column("position_key", "gev_agent_key")
						->column("gender", "gender")
						->column("org_unit", "gev_org_unit_short")
						->column("title", "title")
						->column("custom_id", "gev_training_id")
						->column("venue", "gev_location")
						->column("provider", "gev_provider")
						->column("type", "gev_learning_type")
						->column("date", "date")
						->column("booking_status", "gev_booking_status")
						->column("participation_status", "gev_participation_status")
						->template("tpl.gev_attendance_by_employee_row.html", "Services/GEV/Reports")
						;

		$this->query = catReportQuery::create()
						->select("usrcrs.usr_id")
						->select("usrcrs.crs_id")
						->select("usrcrs.booking_status")
						->select("usrcrs.participation_status")
						->select("usrcrs.okz")
						->select("usrcrs.org_unit")
						->select("usr.firstname")
						->select("usr.lastname")
						->select("usr.gender")
						->select("usr.bwv_id")
						->select("usr.position_key")
						->select("crs.custom_id")
						->select("crs.title")
						->select("crs.type")
						->select("crs.venue")
						->select("crs.provider")
						->select("crs.begin_date")
						->select("crs.end_date")
						->from("hist_usercoursestatus usrcrs")
						->join("hist_user usr")
							->on("usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0")
						->join("hist_course crs")
							->on("crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0")
						->compile()
						;

		$allowed_user_ids = $this->user_utils->getEmployees();
		$this->filter = catFilter::create()
						->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									)
						->static_condition($this->db->in("usr.user_id", $allowed_user_ids, false, "integer"))
						->static_condition("usrcrs.hist_historic = 0")
						->static_condition("(usrcrs.booking_status != '-empty-' OR usrcrs.participation_status != '-empty-')")
						->static_condition("usrcrs.function NOT IN ('Trainingsbetreuer', 'Trainer')")
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;

	}

	protected function transformResultRow($rec) {
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
		$rec['date'] = str_replace("<nobr>", "", str_replace("</nobr>", "", $date));

		return $rec;
	}
}

?>
