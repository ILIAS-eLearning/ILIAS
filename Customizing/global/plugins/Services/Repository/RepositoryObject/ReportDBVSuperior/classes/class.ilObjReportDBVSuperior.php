<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once 'Services/GEV/Utils/classes/class.gevSettings.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportDBVSuperior extends ilObjReportBase {
	
	protected $gUser;
	protected $relevant_parameters = array();

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
		global $ilUser;
		$this->gUser = $ilUser;
	}

	public function initType() {
		 $this->setType("xrds");
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rds')
				->addSetting($this->s_f
								->settingInt('year', $this->plugin->txt('report_year'))
								->setDefaultValue(2016)
									)
				->addSetting($this->s_f
								->settingInt('dbv_report_ref', $this->plugin->txt('dbv_report_ref'))
								->setDefaultValue(0));
	}


	protected function buildQuery($query) {
		$query	->select("dbv.user_id")
				->select("dbv.lastname")
				->select("dbv.firstname")
				->select("huo_in.org_unit_above1")
				->select("huo_in.org_unit_above2")
				->select_raw(
					"SUM(IF(hucs.participation_status != 'nicht gesetzt', hucs.credit_points, 0)) as credit_points")
				->select_raw(
					"SUM(IF(hucs.participation_status != 'nicht gesetzt', hucs.credit_points,
						hc.max_credit_points)) as max_credit_points")
				->from("org_unit_personal oup")
				->join("hist_userorgu huo_in")
					->on("oup.orgunit_id = huo_in.orgu_id AND huo_in.`action` = 1 AND rol_title = ".$this->gIldb->quote("Mitarbeiter","text"))
				->left_join("hist_userorgu huo_out")
					->on(" huo_out.`action` = -1"
						." AND huo_in.usr_id = huo_out.usr_id AND huo_in.orgu_id = huo_out.orgu_id"
						." AND huo_in.rol_id = huo_out.rol_id AND huo_in.hist_version < huo_out.hist_version")
				->left_join("hist_userorgu huo_out_aux")
					->on(" huo_out_aux.`action` = -1"
						." AND huo_in.usr_id = huo_out_aux.usr_id AND huo_in.orgu_id = huo_out_aux.orgu_id"
						." AND huo_in.rol_id = huo_out_aux.rol_id AND huo_in.hist_version < huo_out_aux.hist_version"
						." AND huo_out.hist_version > huo_out_aux.hist_version")
				->join("hist_usercoursestatus hucs")
					->on("huo_in.usr_id = hucs.usr_id")
				->join("hist_course hc")
					->on("hucs.crs_id = hc.crs_id")
				->join("hist_user dbv")
					->on("dbv.user_id = oup.usr_id")
				->group_by("oup.usr_id")
				->compile();
		return $query;
	}

	protected function buildFilter($filter) {

		$user_utils = gevUserUtils::getInstanceByObj($this->gUser);
		$roles = gevRoleUtils::getInstance();
		$dbv_fin_uvg = $roles->usersHavingRole("DBV-Fin-UVG");
		$end_of_year_ts = strtotime(($this->settings['year']+1)."-01-01");
		if ($user_utils->isAdmin()) {
			$dbv_fin_uvg_employees = $dbv_fin_uvg;
		}
		else {
			$employees = $user_utils->getEmployees();
			$dbv_fin_uvg_employees = array_intersect($dbv_fin_uvg, $employees);
		}
		$filter ->checkbox( "critical"
						  , $this->plugin->txt("filter_show_critical_dbvs")
						  , " credit_points < ".$this->gIldb->quote(200,"integer")
						  , " TRUE "								  
						  , true
						  )
				->textinput( "lastname"
						   , $this->plugin->txt("lastname_filter")
						   , "dbv.lastname"
						   )
				->static_condition($this->gIldb->in("oup.usr_id", $dbv_fin_uvg_employees, false, "integer"))
				->static_condition("hc.begin_date < ".$this->gIldb->quote(($this->settings['year']+1)."-01-01","date"))
				->static_condition("hc.end_date >= ".$this->gIldb->quote($this->settings['year']."-01-01","date"))
				->static_condition("(huo_out.created_ts IS NULL "
									." OR huo_out.created_ts > ".$this->gIldb->quote($end_of_year_ts,"integer")
									.") AND huo_in.created_ts < ".$this->gIldb->quote($end_of_year_ts,"integer"))
				->static_condition(
					$this->gIldb->in(
						"hucs.participation_status", array("fehlt entschuldigt", "fehlt ohne Absage"), true, "text"))
				->static_condition("hucs.hist_historic = 0")
				->static_condition("hucs.booking_status != ".$this->gIldb->quote('-empty-', 'text'))
				->static_condition("huo_out_aux.hist_version IS NULL")
				->static_condition("hc.hist_historic = 0")
				->static_condition("dbv.hist_historic = 0")
				->static_condition($this->gIldb->in("hc.dbv_hot_topic", gevSettings::$dbv_hot_topics, false, "text"))
				->action($this->filter_action)
				->compile();
		return $filter;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_dbv_report_superior_row.html";
	}

	protected function buildTable($table) {
		$table	->column("lastname", $this->plugin->txt("lastname"),true)
				->column("firstname", $this->plugin->txt("firstname"),true)
				->column("odbd", $this->plugin->txt("bd"),true)
				->column("credit_points", $this->plugin->txt("credit_points"),true)
				->column("max_credit_points", $this->plugin->txt("credit_points_forecast"),true);
		return parent::buildTable($table);
	}

	protected function buildOrder($order) {
		$order 	->defaultOrder("lastname", "ASC")
				;
		return $order;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}