<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

 class ilObjReportAttendanceByEmployee extends ilObjReportBase {

	public function initType() {
		 $this->setType("xrae");
	}

	protected function buildQuery($query) {
		$query
			->select("usr.user_id")
			->select("usr.lastname")
			->select("usr.firstname")
			->select("usr.email")
			->select("usr.adp_number")
			->select("usr.job_number")
			->select("orgu.org_unit_above1")
			->select("orgu.org_unit_above2")
			->select_raw("GROUP_CONCAT(DISTINCT orgu.orgu_title SEPARATOR ', ') AS org_unit")
			->select_raw("GROUP_CONCAT(DISTINCT role.rol_title ORDER BY role.rol_title SEPARATOR ', ') AS roles")
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
				->on("usr.user_id = usrcrs.usr_id AND usrcrs.hist_historic = 0")
			->left_join("hist_course crs")
				->on("crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0")
			->left_join("hist_userorgu orgu")
				->on("orgu.usr_id = usr.user_id")
			->left_join("hist_userrole role")
				->on("role.usr_id = usr.user_id")
			->group_by("usr.user_id")
			->group_by("usrcrs.crs_id")
			->compile()
			;
		return $query;
	}

	protected function buildOrder($order) {
		$order->mapping("date", "crs.begin_date")
				->mapping("od_bd", array("org_unit_above1", "org_unit_above2"))
				->defaultOrder("lastname", "ASC")
				;
		return $order;
	}

	protected function buildTable($table) {
		$table
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
			;
		return $table();
	}

	protected function buildFilter($filter) {
		$filter->dateperiod( "period"
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
							 ,	$this->report_utils->getFilterOrgus($this->user_utils)
							 , array()
							 , ""
							 , 300
							 , 160
							 )
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
							 )
			->static_condition($this->db->in("usr.user_id", $this->report_utils->getAllowedUserIds($this->user_utils), false, "integer"))
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
			->static_condition("role.action = 1")
			->static_condition("role.hist_historic = 0")
			->action($this->filter_action)
			->compile()
		return $filter;
	}
}