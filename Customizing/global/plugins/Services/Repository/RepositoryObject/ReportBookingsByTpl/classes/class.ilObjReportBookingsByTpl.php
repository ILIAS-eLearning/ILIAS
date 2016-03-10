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

class ilObjReportBookingsByTpl extends ilObjReportBase {
	protected $relevant_parameters = array();
	protected $sum_parts = array();

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		require_once $this->plugin->getDirectory().'/config/cfg.bk_by_tpl.php';
	}

	public function initType() {
		 $this->setType("xrbt");
	}

	public function prepareReport() {
		$this->sum_table = $this->buildSumTable(catReportTable::create());
		parent::prepareReport();
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_bookings_by_tpl_row.html";
	}

	protected function buildFilter($filter) {
		$this->orgu_filter = new recursiveOrguFilter("org_unit","orgu_id",true,true);
		$this->orgu_filter->setFilterOptionsByUser($this->user_utils);
		$filter 		->dateperiod( "period"
									, $this->lng->txt("gev_period")
									, $this->lng->txt("gev_until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									, false
									, " OR usrcrs.hist_historic IS NULL"
									);
		$this->orgu_filter->addToFilter($this->filter);
		$filter	->multiselect("edu_program"
									 , $this->lng->txt("gev_edu_program")
									 , "edu_program"
									 , gevCourseUtils::getEduProgramsFromHisto()
									 , array()
									 , ""
									 , 200
									 , 160	
									 )
						->multiselect("type"
									 , $this->lng->txt("gev_course_type")
									 , "type"
									 , gevCourseUtils::getLearningTypesFromHisto()
									 , array()
									 , ""
									 , 200
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
									 			,"nicht gesetzt"=>"gebucht, noch nicht abgeschlossen")
									 , array()
									 , ""
									 , 220
									 , 160
									 , "text"
									 , "asc"
									 , true
									 )
						->multiselect("booking_status"
									 , $this->lng->txt("gev_booking_status")
									 , "booking_status"
									 , catFilter::getDistinctValues('booking_status', 'hist_usercoursestatus')
									 , array()
									 , ""
									 , 200
									 , 160	
									 )
						->multiselect("venue"
									 , $this->lng->txt("gev_venue")
									 , "venue"
									 , catFilter::getDistinctValues('venue', 'hist_course')
									 , array()
									 , ""
									 , 300
									 , 160	
									 )
						->multiselect("provider"
									 , $this->lng->txt("gev_provider")
									 , "provider"
									 , catFilter::getDistinctValues('provider', 'hist_course')
									 , array()
									 , ""
									 , 300
									 , 160	
									 )
						->static_condition(" crs.hist_historic = 0")
						->static_condition(" usrcrs.hist_historic = 0")
						->static_condition(" crs.template_title != ".$this->db->quote('-empty-','text') )
						->static_condition(" usrcrs.booking_status != ".$this->db->quote('-empty-','text'))
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile()
						;
		return $filter;
	}

	protected function buildQuery($query) {
		$this->orgu_filter_query = 	
				"JOIN (SELECT usr_id  \n"
					."	FROM hist_userorgu \n"
					." 	WHERE ".$this->orgu_filter->deliverQuery()." \n"
					."	AND hist_historic = 0 AND `action` >= 0 GROUP BY usr_id) as orgu ON usrcrs.usr_id = orgu.usr_id \n";

		$query 		->select("crs.template_title")
					->select("crs.edu_program");
		foreach( $this->sum_parts as $title => $query_parts)
			$query	->select_raw($query_parts["regular"]);
		}
		$query		->from("hist_course crs")
					->join("hist_usercoursestatus usrcrs")
						->on("crs.crs_id = usrcrs.crs_id");
		if($this->orgu_filter->getSelection()) {
			$query	->raw_join($this->orgu_filter_query );
		}
		$query 		->group_by("crs.template_obj_id")
					->compile();
		return $query;
	}

	protected function deliverSumQuery() {
		$sum_sql = "SELECT ";
		foreach ($this->sum_parts as $title => $query_parts) {
			$sum_sql .= $query_parts["sum"];
		}
		$sum_sql .=
			"FROM( \n"
			."	SELECT usrcrs.usr_id, crs.crs_id, usrcrs.booking_status, \n"
			."		usrcrs.participation_status, crs.type \n"
			."		FROM `hist_usercoursestatus` usrcrs \n" 
			."			JOIN `hist_course` crs ON usrcrs.crs_id = crs.crs_id \n"
			."			LEFT JOIN hist_userorgu orgu ON orgu.usr_id = usrcrs.usr_id \n"
			.$this->queryWhere()
			." AND ".$this->orgu_filter->deliverQuery()
			."		GROUP BY usrcrs.usr_id, crs.crs_id"
			.") as temp";
		return $sum_sql;
	}

	protected function buildSumTable(catReportTable $table) {
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title,$this->plugin->txt($title),true);
		}
		$table	->template( "tpl.gev_booking_by_tpl_sum_row.html", $this->plugin->getDirectory());
		return $table;
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

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rbbt ".
			"(id, is_online) VALUES (".
			$this->gIldb->quote($this->getId(), "integer").",".
			$this->gIldb->quote(0, "integer").
			")");
	}

	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rbbt ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
		}
	}

	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_rbbt SET "
			." is_online = ".$this->gIldb->quote($this->getOnline(), "integer")
			." WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rbbt WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}
}