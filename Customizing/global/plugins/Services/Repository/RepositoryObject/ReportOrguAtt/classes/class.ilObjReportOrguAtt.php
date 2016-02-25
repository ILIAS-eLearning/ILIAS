<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportOrguAtt extends ilObjReportBase {
	protected $relevant_parameters = array();
	protected $sum_parts = array();
	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);

		require_once $this->plugin->getDirectory().'/config/cfg.att_org_units.php';
	}

	public function initType() {
		 $this->setType("xroa");
	}

	public function prepareReport() {
		$this->sum_table = $this->buildSumTable(catReportTable::create());
		parent::prepareReport();
	}

	protected function buildSumTable(catReportTable $table) {
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title,$this->plugin->txt($title),true);
		}
		$table	->template("tpl.gev_attendance_by_orgunit_sums_row.html", $this->plugin->getDirectory());
		return $table;
	}

	public function deliverSumTable() {
		if($this->sum_table !== null ) {
			return $this->sum_table;
		}
		throw new Exception("ilObjReportBase::deliverSumTable: you need to define a sum table.");	
	}

	protected function buildOrder($order) {
		return $order
			->mapping("odbd",array("orgu.org_unit_above1","orgu.org_unit_above2"))
			->defaultOrder("orgu_title", "ASC");
	}

	protected function buildTable($table) {
		$table	->column("orgu_title", $this->plugin->txt('orgu_title'),true)
				->column("odbd", $this->plugin->txt('od_bd'),true);
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title, $this->plugin->txt($title),true);
		}
		return parent::buildTable($table);
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_attendance_by_orgunit_row.html";
	}

	protected function buildQuery($query) {
		$query	->select("orgu.orgu_title")
				->select("orgu.org_unit_above1")
				->select("orgu.org_unit_above2");
		foreach ($this->sum_parts as $title => $query_term) {
			$query
				->select_raw($query_term["regular"]);
		}
		$this->orgu_filter->addToQuery($query);
		$query	->from("hist_userorgu orgu")
				->join('hist_user usr')
					->on('usr.user_id = orgu.usr_id')
				->left_join("hist_usercoursestatus usrcrs")
					->on("usrcrs.usr_id = orgu.usr_id AND usrcrs.hist_historic = 0 ")
				->left_join("hist_course crs")
					->on("usrcrs.crs_id = crs.crs_id AND crs.hist_historic = 0")
				->group_by("orgu.orgu_id")
				->compile();
		return $query;
	}

	protected function deliverSumQuery() {
		$sum_terms = array();
		foreach ($this->sum_parts as $title => $query_term) {
			$sum_terms[] = $query_term["sum"];
		}
		$sum_sql = 
		"SELECT  "
		."	".implode(', ',$sum_terms)
		." 	FROM("
		."		SELECT DISTINCT orgu.usr_id, crs.crs_id, usrcrs.booking_status, "
		."			usrcrs.participation_status, crs.type "
		."			FROM hist_userorgu orgu "
		."			JOIN hist_user usr"
		."				ON orgu.usr_id = usr.user_id"
		."			LEFT JOIN `hist_usercoursestatus` usrcrs "
		."				ON usrcrs.usr_id = orgu.usr_id AND usrcrs.hist_historic = 0 "
		."			LEFT JOIN `hist_course` crs "
		."				ON usrcrs.crs_id = crs.crs_id AND crs.hist_historic = 0 "
		."			".$this->queryWhere()
		.") as temp";

		return $sum_sql;
	}

	public function insertSumData($table, callable $callback) {
		$res = $this->gIldb->query($this->deliverSumQuery());
		$summed_data = $this->gIldb->fetchAssoc($res);

		if(count($summed_data) == 0) {
			$summed_data = array();
			foreach($this->sum_parts as $name => $query) {
				$summed_data[$name] = 0;
			}
		}
		$table->setData(array(call_user_func($callback,$summed_data)));
		return $table;
	}

	protected function getOrgusForFilter() {
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
		return $org_units_filter;
	}

	protected function buildFilter($filter) {
		$this->orgu_filter = new recursiveOrguFilter('org_unit', 'orgu.orgu_id', true, true);
		$this->orgu_filter->setFilterOptionsByUser($this->user_utils);
		$this->orgu_filter->addToFilter($filter);
		$filter	->dateperiod( "period"
							, $this->plugin->txt("period")
							, $this->plugin->txt("until")
							, "usrcrs.begin_date"
							, "usrcrs.end_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							, false
							," OR usrcrs.hist_historic IS NULL "
							)
				->multiselect("edu_program"
							 , $this->plugin->txt("edu_program")
							 , "edu_program"
							 , gevCourseUtils::getEduProgramsFromHisto()
							 , array()
							 , ""
							 , 200
							 , 160	
							 )
				->multiselect("type"
							 , $this->plugin->txt("course_type")
							 , "type"
							 , gevCourseUtils::getLearningTypesFromHisto()
							 , array()
							 , ""
							 , 200
							 , 160	
							 )
				->multiselect("template_title"
							 , $this->plugin->txt("crs_title")
							 , "template_title"
							 , gevCourseUtils::getTemplateTitleFromHisto()
							 , array()
							 , ""
							 , 300
							 , 160	
							 )
				->multiselect("participation_status"
							 , $this->plugin->txt("participation_status")
							 , "participation_status"
							 , array(	"teilgenommen"=>"teilgenommen"
							 			,"fehlt ohne Absage"=>"fehlt ohne Absage"
							 			,"fehlt entschuldigt"=>"fehlt entschuldigt"
							 			,"nicht gesetzt"=>"gebucht, noch nicht abgeschlossen")
							 , array()
							 , ""
							 , 200
							 , 160
							 , "text"
							 , "asc"
							 , true
							 )
				->multiselect("booking_status"
							 , $this->plugin->txt("booking_status")
							 , "booking_status"
							 , catFilter::getDistinctValues('booking_status', 'hist_usercoursestatus')
							 , array()
							 , ""
							 , 200
							 , 160	
							 )
				->multiselect("gender"
							 , $this->plugin->txt("gender")
							 , "gender"
							 , array('f', 'm')
							 , array()
							 , ""
							 , 100
							 , 160	
							 )
				->multiselect("venue"
							 , $this->plugin->txt("venue")
							 , "venue"
							 , catFilter::getDistinctValues('venue', 'hist_course')
							 , array()
							 , ""
							 , 300
							 , 160	
							 )
				->multiselect("provider"
							 , $this->plugin->txt("provider")
							 , "provider"
							 , catFilter::getDistinctValues('provider', 'hist_course')
							 , array()
							 , ""
							 , 300
							 , 160	
							 )
				->static_condition($this->gIldb->in("orgu.usr_id", $this->user_utils->getEmployees(), false, "integer"))
				->static_condition('usr.hist_historic = 0')
				->static_condition("orgu.hist_historic = 0")
				->static_condition("orgu.action >= 0")
				->static_condition("usrcrs.booking_status != ".$this->gIldb->quote('-empty-','text'));
		if($this->getIsLocal()) {
			$filter->static_condition("(".$this->gIldb->in('crs.template_obj_id',$this->getSubtreeCourseTemplates(),false,'integer')
											." OR crs.hist_historic IS NULL)");
		}
		$filter	->action($this->filter_action)
				->compile();
		return $filter;
	}


	protected function getSubtreeCourseTemplates() {
		$query = 	'SELECT obj_id FROM adv_md_values_text amd_val '
					.'	WHERE '.$this->gIldb->in('obj_id',
							$this->getSubtreeTypeIdsBelowParentType('crs','cat'),false,'integer')
					.'		AND field_id = '.$this->gIldb->quote(
												gevSettings::getInstance()
													->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE)
												,'integer')
					.'		AND value = '.$this->gIldb->quote('Ja','text');
		$return = array();
		$res = $this->gIldb->query($query);
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[] = $rec['obj_id'];
		}
		return $return;
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_roa ".
			"(id, is_online, is_local) VALUES (".
			$this->gIldb->quote($this->getId(), "integer")
			.",".$this->gIldb->quote(0, "integer")
			.",".$this->gIldb->quote(0, "integer")
			.")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_roa ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
			$this->setIslocal($rec["is_local"]);
		}
	}

	public function doUpdate() {
		$this->gIldb->manipulate("UPDATE rep_robj_roa SET "
			." is_online = ".$this->gIldb->quote($this->getOnline(), "integer")
			." ,is_local = ".$this->gIldb->quote($this->getIsLocal(), "integer")
			." WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_roa WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setIsLocal($this->getIslocal());
		parent::doClone($a_target_id,$a_copy_id,$new_obj);
	}

	public function getIslocal() {
		return $this->is_local;
	}

	public function setIslocal($value) {
		$this->is_local = $value ? 1 : 0;
	}
}