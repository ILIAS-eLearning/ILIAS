<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "DBV Report für Führungskräfte"
* for Generali
*
* @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
* @version	$Id$
*
*
*/

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Services/GEV/Utils/classes/class.gevRoleUtils.php");


class gevDBVReportSuperiorGUI extends catBasicReportGUI{

	public function __construct() {

		parent::__construct();

		$viewer = $this->user_utils->getId();
		$roles = gevRoleUtils::getInstance();

		$this->title = catTitleGUI::create()
						->title("gev_rep_dbv_report_superior_title")
						->subTitle("gev_rep_dbv_report_superior_desc")
						->image("GEV_img/ico-head-edubio.png");

		$this->table = catReportTable::create()
						->column("lastname", "lastname")
						->column("firstname", "firstname")
						->column("odbd", "gev_bd")
						->column("credit_points", "gev_credit_points")
						->column("max_credit_points", "gev_credit_points_forecast")
						->template("tpl.gev_dbv_report_superior_row.html", "Services/GEV/Reports");

	$this->order = catReportOrder::create($this->table)
						->defaultOrder("lastname", "ASC");

		//internal ordering:
		$this->internal_sorting_numeric = array(
			'lastname'
		);
		$this->internal_sorting_fields = array_merge(
			$this->internal_sorting_numeric,
			array(
		 	  'odbd'
			));

		$this->query = catReportQuery::create()
						->select("dbv.user_id")
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
							->on("oup.orgunit_id = huo_in.orgu_id AND huo_in.`action` = 1 AND rol_title = ".$this->db->quote("Mitarbeiter","text"))
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
						
		$dbv_fin_uvg = $roles->usersHavingRole("DBV-Fin-UVG");
		if ($this->user_utils->isAdmin()) {
			$dbv_fin_uvg_employees = $dbv_fin_uvg;
		}
		else {
			$employees = $this->user_utils->getEmployees();
			$dbv_fin_uvg_employees = array_intersect($dbv_fin_uvg, $employees);
		}

		$this->filter = catFilter::create()
						->checkbox( "critical"
								  , $this->lng->txt("gev_rep_filter_show_critical_dbvs")
								  , " credit_points < ".$this->db->quote(200,"integer")
								  , " TRUE "								  
								  , true
								  )
						->textinput( "lastname"
								   , $this->lng->txt("gev_lastname_filter")
								   , "dbv.lastname"
								   )
						->static_condition($this->db->in("oup.usr_id", $dbv_fin_uvg_employees, false, "integer"))
						->static_condition("hc.begin_date < ".$this->db->quote("2016-01-01","date"))
						->static_condition("hc.end_date >= ".$this->db->quote("2015-01-01","date"))
						->static_condition("(huo_out.created_ts IS NULL "
											." OR huo_out.created_ts > UNIX_TIMESTAMP(".$this->db->quote("2016-01-01","date").")"
											.") AND huo_in.created_ts < UNIX_TIMESTAMP(".$this->db->quote("2016-01-01","date").")")
						->static_condition(
							$this->db->in(
								"hucs.participation_status", array("fehlt entschuldigt", "fehlt ohne Absage"), true, "text"))
						->static_condition("hucs.hist_historic = 0")
						->static_condition("huo_out_aux.hist_version IS NULL")
						->static_condition("hc.hist_historic = 0")
						->static_condition("dbv.hist_historic = 0")
						->static_condition($this->db->in("hc.dbv_hot_topic", gevSettings::$dbv_hot_topics, false, "text"))
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile();
		$this->relevant_parameters = array(
				$this->filter->getGETName() => $this->filter->encodeSearchParamsForGET()
			); 
	}
	
	protected function checkPermission($a_target_user_id) {
		if (!$this->user_utils->isAdmin() && !$this->user_utils->isSuperior()) {
			throw new Exception("No permission to view report");
		}
	}

	protected function transformResultRow($rec) {
		$rec['odbd'] = $rec['org_unit_above1'];
		$this->ctrl->setParameterByClass("gevDBVReportGUI", "target_user_id", $rec["user_id"]);
		$rec["dbv_report_link"] = $this->ctrl->getLinkTargetByClass("gevDBVReportGUI");
		$this->ctrl->setParameterByClass("gevDBVReportGUI", "target_user_id", null);

		return $this->replaceEmpty($rec);
	}

	protected function renderView() {
		$main_table = $this->renderTable();
		return $main_table;
	}

}