<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "Attendance By Course-Template"
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*
*
*/

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");

class gevAttendanceByCourseTemplateGUI extends catBasicReportGUI{
	public function __construct() {
		
		parent::__construct();

		$this->title = catTitleGUI::create()
						->title("gev_rep_attendance_by_coursetemplate_title")
						->subTitle("gev_rep_attendance_by_coursetemplate_desc")
						->image("GEV_img/ico-head-edubio.png")
						;

	
		$this->table = catReportTable::create()
						->column("template_title", "title")
						->column("edu_program", "BiPro")
						//->column("above1", "above1")
						
						->column("sum_booked_wbt", "sum_booked_WBT")
						->column("sum_attended_wbt", "sum_attended_WBT")
						
						->column("sum_booked", "sum_booked_nowbt")
						->column("sum_waiting", "sum_waiting")
						->column("sum_attended", "sum_attended_nowbt")
						->column("sum_excused", "sum_excused")
						->column("sum_unexcused", "sum_unexcused")
						->column("sum_exit", "sum_exit")
						
						->template("tpl.gev_attendance_by_coursetemplate_row.html", "Services/GEV/Reports")
						;

		/*$this->order = catReportOrder::create($this->table)
						//->mapping("date", "crs.begin_date")
						//->mapping("odbd", array("org_unit_above1", "org_unit_above2"))
						->defaultOrder("template", "ASC")
						;
		*/


		$this->sql_sum_parts = array(

				"sum_booked" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.booking_status) = 'gebucht'
							AND LCASE(usrcrs.participation_status) = 'nicht gesetzt'
							AND crs.type != 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_booked",

					"sum_booked_wbt" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.booking_status) = 'gebucht'
							AND LCASE(usrcrs.participation_status) = 'nicht gesetzt'
							AND crs.type = 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_booked_wbt",


					"sum_waiting" => "SUM(
						CASE 
							WHEN usrcrs.booking_status = 'auf Warteliste'
							AND participation_status = 'nicht gesetzt'
						THEN 1
						END 
					) AS sum_waiting",

					"sum_attended" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'teilgenommen'
							AND crs.type != 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_attended",

					"sum_attended_wbt" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'teilgenommen'
							AND crs.type = 'Selbstlernkurs'
						THEN 1
						END 
					) AS sum_attended_wbt",


					"sum_excused" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'fehlt entschuldigt'
						THEN 1
						END 
					) AS sum_excused",


					"sum_unexcused" => " SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'fehlt ohne Absage'
						THEN 1
						END 
					) AS sum_unexcused",

					"sum_exit" => "SUM(
						CASE 
							WHEN LCASE(usrcrs.participation_status) = 'canceled_exit'
						THEN 1
						END 
					) AS sum_exit"

			);




		$this->query = catReportQuery::create()
						//->distinct()

						->select("crs.template_title")
						->select("crs.edu_program")
						

						/*->select("usrcrs.booking_status")
						->select("usrcrs.participation_status")
						->select("usr.user_id")
						->select("crs.crs_id")
						*/
						->select_raw($this->sql_sum_parts['sum_booked_wbt'])
						->select_raw($this->sql_sum_parts['sum_attended_wbt'])

						->select_raw($this->sql_sum_parts['sum_booked'])
						->select_raw($this->sql_sum_parts['sum_attended'])
						->select_raw($this->sql_sum_parts['sum_waiting'])
						->select_raw($this->sql_sum_parts['sum_excused'])
						->select_raw($this->sql_sum_parts['sum_unexcused'])
						->select_raw($this->sql_sum_parts['sum_exit'])


						->from("hist_usercoursestatus usrcrs")

						->join("hist_course crs")
							->on("usrcrs.crs_id = crs.crs_id")

						//->join("hist_user usr")
						//	->on("usrcrs.usr_id = usr.user_id")

						->group_by("crs.template_title")
						->compile()
						;



		$this->filter = catFilter::create()
						->static_condition(" usrcrs.hist_historic = 0")
						//->static_condition(" usr.hist_historic = 0")
						->static_condition(" crs.hist_historic = 0")
										  
						  
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;


	}
}

?>
