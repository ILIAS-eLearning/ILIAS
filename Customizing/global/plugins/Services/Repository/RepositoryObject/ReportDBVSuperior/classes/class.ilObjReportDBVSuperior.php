<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once 'Services/GEV/Utils/classes/class.gevSettings.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportDBVSuperior extends ilObjReportBase {
	
	protected $gUser;
	protected $year;
	protected $relevant_parameters = array();

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
		global $ilUser;
		$this->gUser = $ilUser;
	}

	public function initType() {
		 $this->setType("xrds");
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
						." AND huo_in.usr_id = huo_out.usr_id AND huo_in.orgu_id = huo_out.orgu_id"
						." AND huo_in.rol_id = huo_out.rol_id AND huo_in.hist_version < huo_out_aux.hist_version"
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
		$end_of_year_ts = strtotime(($this->year+1)."-01-01");
		if ($user_utils->isAdmin()) {
			$dbv_fin_uvg_employees = $dbv_fin_uvg;
		}
		else {
			$employees = $user_utils->getEmployees();
			$dbv_fin_uvg_employees = array_intersect($dbv_fin_uvg, $employees);
		}
		$filter ->checkbox( "critical"
						  , $this->lng->txt("gev_rep_filter_show_critical_dbvs")
						  , " credit_points < ".$this->gIldb->quote(200,"integer")
						  , " TRUE "								  
						  , true
						  )
				->textinput( "lastname"
						   , $this->lng->txt("gev_lastname_filter")
						   , "dbv.lastname"
						   )
				->static_condition($this->gIldb->in("oup.usr_id", $dbv_fin_uvg_employees, false, "integer"))
				->static_condition("hc.begin_date < ".$this->gIldb->quote(($this->year+1)."-01-01","date"))
				->static_condition("hc.end_date >= ".$this->gIldb->quote("2015-01-01","date"))
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
		$table	->column("lastname", "lastname")
				->column("firstname", "firstname")
				->column("odbd", "gev_bd")
				->column("credit_points", "gev_credit_points")
				->column("max_credit_points", "gev_credit_points_forecast");
		return parent::buildTable($table);
	}

	protected function buildOrder($order) {
		$order 	->defaultOrder("lastname", "ASC")
				;
		return $order;
	}

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rds ".
			"(id, is_online, year) VALUES (".
			$this->gIldb->quote($this->getId(), "integer").",".
			$this->gIldb->quote(0, "integer").",".
			$this->gIldb->quote(2015, "integer").
			")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rds ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
			$this->setYear($rec["year"]);
			break;
		}
	}

	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_rds SET ".
			" is_online = ".$this->gIldb->quote($this->getOnline(), "integer").
			" ,year = ".$this->gIldb->quote($this->getYear(), "integer").
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rds WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->setYear($this->getYear());
		$new_obj->update();
	}

	public function setYear($a_val) {
		$this->year = (int)$a_val;
	}

	public function getYear() {
		return $this->year;
	}

	public function setOnline($a_val) {
		$this->online = (int)$a_val;
	}

	public function getOnline() {
		return $this->online;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}