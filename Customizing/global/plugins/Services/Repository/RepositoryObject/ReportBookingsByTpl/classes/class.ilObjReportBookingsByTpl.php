<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
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
	protected $sum_table;

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		require_once $this->plugin->getDirectory().'/config/cfg.bk_by_tpl.php';
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rbbt');
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
	
	/**
	 *	@inheritdoc
	 */
	protected function buildOrder($order) {
		return $order
			->defaultOrder("template_title", "ASC");
	}

	/**
	 *	@inheritdoc
	 */
	protected function buildTable($table) {
		$table 		->column("template_title", $this->plugin->txt("title"),true)
					->column("edu_program", $this->plugin->txt("edu_program"),true);
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title, $this->plugin->txt($title),true);
		}
		return parent::buildTable($table);
	}

	protected function buildSumTable(catReportTable $table) {
		foreach ($this->sum_parts as $title => $query) {
			$table
				->column($title,$this->plugin->txt($title),true);
		}
		$table	->template("tpl.gev_bookings_by_tpl_sums_row.html", $this->plugin->getDirectory());
		return $table;
	}

	public function deliverSumTable() {
		if($this->sum_table !== null ) {
			return $this->sum_table;
		}
		throw new Exception("ilObjReportBase::deliverSumTable: you need to define a sum table.");	
	}

	/**
	 *	@inheritdoc
	 */
	protected function buildFilter($filter) {
		$this->orgu_filter = new recursiveOrguFilter("org_unit","orgu_id",true,true);
		$this->orgu_filter->setFilterOptionsAll();
		$filter 		->dateperiod( "period"
									, $this->plugin->txt("period")
									, $this->plugin->txt("until")
									, "usrcrs.begin_date"
									, "usrcrs.end_date"
									, date("Y")."-01-01"
									, date("Y")."-12-31"
									, false
									, " OR usrcrs.hist_historic IS NULL"
									);
		$this->orgu_filter->addToFilter($filter);
		$filter			->multiselect("edu_program"
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
									 , $this->plugin->txt("title")
									 , "crs.template_obj_id"
									 , $this->getTemplates()
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
									 , 220
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
						->static_condition(" crs.hist_historic = 0")
						->static_condition(" usrcrs.hist_historic = 0")
						->static_condition(" crs.template_obj_id != ".$this->gIldb->quote(-1,'integer') )
						->static_condition(" usrcrs.booking_status != ".$this->gIldb->quote('-empty-','text'))
						->action($this->filter_action)
						->compile()
						;
		return $filter;
	}

	/**
	 *	@inheritdoc
	 */
	protected function buildQuery($query) {
		$orgu_filter_query =
				"JOIN (SELECT usr_id  \n"
					."	FROM hist_userorgu \n"
					." 	WHERE ".$this->orgu_filter->deliverQuery()." \n"
					."	AND hist_historic = 0 AND `action` >= 0 GROUP BY usr_id) as orgu ON usrcrs.usr_id = orgu.usr_id \n";
		$query 		->select("crs.template_title")
					->select("crs.edu_program");
		foreach( $this->sum_parts as $title => $query_parts) {
			$query	->select_raw($query_parts["regular"]);
		}
		$query		->from("hist_course crs")
					->join("hist_usercoursestatus usrcrs")
						->on("crs.crs_id = usrcrs.crs_id");
		if($this->orgu_filter->getSelection()) {
			$query	->raw_join($orgu_filter_query );
		}
		$query 		->group_by("crs.template_obj_id")
					->compile();
		return $query;
	}

	protected function deliverSumQuery() {
		$sum_sql = "SELECT ";
		$prefix = "";
		foreach ($this->sum_parts as $title => $query_parts) {
			$sum_sql .= $prefix.$query_parts["sum"];
			$prefix = $prefix === "" ? "," : $prefix;
		}
		$sum_sql .=
			" FROM ( \n"
			."	SELECT usrcrs.usr_id, crs.crs_id, usrcrs.booking_status, \n"
			."		usrcrs.participation_status, crs.type \n"
			."		FROM  `hist_course` crs \n"
			."			JOIN `hist_usercoursestatus` usrcrs  ON usrcrs.crs_id = crs.crs_id \n";
		if($this->orgu_filter->getSelection()) {
			$sum_sql .=
			"			JOIN hist_userorgu orgu ON orgu.usr_id = usrcrs.usr_id \n";
		}
		$sum_sql .=
			"		".$this->queryWhere();
		if($this->orgu_filter->getSelection()) {
			$sum_sql .=
			" 		AND ".$this->orgu_filter->deliverQuery()
			."		AND orgu.action >=0 AND orgu.hist_historic = 0";
		}
		$sum_sql .=
			"		GROUP BY usrcrs.usr_id, crs.crs_id"
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

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function getTemplates() {
		$query = 	'SELECT od.obj_id, od.title FROM adv_md_values_text amd_val '
					.'	JOIN object_data od ON od.obj_id = amd_val.obj_id'
					.'	WHERE amd_val.field_id = '.$this->gIldb->quote(
												gevSettings::getInstance()
													->getAMDFieldId(gevSettings::CRS_AMD_IS_TEMPLATE)
												,'integer')
					.'		AND amd_val.value = '.$this->gIldb->quote('Ja','text');
		$return = array();
		$res = $this->gIldb->query($query);
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec['obj_id']] = $rec['title'];
		}
		return $return;
	}
}