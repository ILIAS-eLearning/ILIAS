<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevOrgUnitUtils.php';
ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportTrainerOpTepCat extends ilObjReportBase {
	const MIN_ROW = "0";
	protected $categories;
	protected $relevant_parameters = array();	

	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);
		require_once $this->plugin->getDirectory().'/config/cfg.trainer_op_tep_cat.php';
	}

	public function initType() {
		 $this->setType("xttc");
	}


	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rttc');
	}

	protected function buildOrder($order) {
		return $order->defaultOrder("fullname", "ASC");
	}

	
	protected function buildTable($table) {
		$table->column("fullname", $this->plugin->txt("name"), true);
		foreach($this->categories as $key => $category) {
			$table	->column('cat_'.$key, $category, true)
					->column('cat_'.$key.'_h', 'Std.', true);
		}
		return parent::buildTable($table);
	}

	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function buildQuery($query) {
		$query 	->select("hu.user_id")
				->select_raw("CONCAT(hu.lastname, ', ', hu.firstname) as fullname");
		foreach($this->categories as $key => $category) {
			$query->select_raw($this->daysPerTEPCategory($category, 'cat_'.$key));
			$query->select_raw($this->hoursPerTEPCategory($category, 'cat_'.$key.'_h'));
		}
		$query->from("hist_tep ht")
				->join("hist_user hu")
					->on("ht.user_id = hu.user_id")
				->join("hist_tep_individ_days htid")
					->on("individual_days = id")
				->left_join("hist_course hc")
					->on("context_id = crs_id AND ht.category  = 'Training'")
				->group_by("hu.user_id");
		return $query->compile();
	}

	protected function daysPerTEPCategory($category,$name) {
		$sql = "SUM(IF(category = "
				.$this->gIldb->quote($category,"text")." ,1,0)) as ".$name;
		return $sql;
	}

	protected function hoursPerTEPCategory($category, $name) {
		$sql = 
		"SUM(IF(category = ".$this->gIldb->quote($category,"text")." ,"
		."		IF(htid.end_time IS NOT NULL AND htid.start_time IS NOT NULL,"
		."			LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( end_time, start_time ) )* weight /720000) *2,8),"
		."			LEAST(CEIL( 28800* htid.weight /720000) *2,8)"
		."		)"
		."	,0)) as ".$name;
		return $sql;
	}

	protected function buildFilter($filter) {
		$filter	->multiselect( "edu_program"
							 , $this->plugin->txt("edu_program")
							 , "hc.edu_program"
							 , gevCourseUtils::getEduProgramsFromHisto()
							 , array()
							 , ""
							 , 200
							 , 160	
							 )
				->multiselect( "template_title"
							 , $this->plugin->txt("crs_title")
							 , "hc.template_title"
							 , gevCourseUtils::getTemplateTitleFromHisto()
							 , array()
							 , ""
							 , 300
							 , 160	
							 )
				->multiselect( "type"
							 , $this->plugin->txt("course_type")
							 , "type"
							 , gevCourseUtils::getLearningTypesFromHisto()
							 , array()
							 , ""
							 , 200
							 , 160	
							 )
				->dateperiod( "period"
							 , $this->plugin->txt("period")
							 , $this->plugin->txt("until")
							 , "ht.begin_date"
							 , "ht.end_date"
							 , date("Y")."-01-01"
							 , date("Y")."-12-31"
							 , false
							 , " OR ht.hist_historic IS NULL"
							 )
				->multiselect( "org_unit"
							 , $this->plugin->txt("report_filter_crs_region")
							 , "ht.orgu_title"
							 , $this->getOrgusFromTep()
							 , array()
							 , ""
							 , 200
							 , 160	
							 )
				->multiselect( "venue"
							 , $this->plugin->txt("venue")
							 , "ht.location"
							 , gevOrgUnitUtils::getVenueNames()
							 , array()
							 , ""
							 , 300
							 , 160
							 )
				->static_condition("(hc.hist_historic = 0 OR hc.hist_historic IS NULL)")
				->static_condition("ht.hist_historic = 0")
				->static_condition("ht.deleted = 0")
				->static_condition("hu.hist_historic = 0")
				->static_condition("(ht.category != 'Training' OR (ht.context_id != 0 AND ht.context_id IS NOT NULL))")
				->static_condition($this->gIldb->in('ht.category',$this->categories,false,'text'))
				->static_condition(' ht.row_id > '.self::MIN_ROW) 
				->action($this->filter_action)
				->compile();
		return $filter;
	}

	protected function getOrgusFromTep() {
		$orgus = array();
		$sql = "SELECT DISTINCT orgu_title FROM hist_tep WHERE orgu_title != '-empty-'";
		$res = $this->gIldb->query($sql);
		while( $rec = $this->gIldb->fetchAssoc($res)) {
			$orgus[] = $rec["orgu_title"];
		}
		return $orgus;
	}

	protected function createTemplateFile() {
		$str = fopen($this->plugin->getDirectory()."/templates/default/"
			."tpl.trainer_op_by_tep_cat_row.html","w"); 
		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td class = "bordered_right">{VAL_FULLNAME}</td>';
		foreach($this->categories as $key => $category) {
			$tpl .= "\n".'<td align = "right">{VAL_CAT_'.$key.'}</td>';
			$tpl .= "\n".'<td align = "right" class = "bordered_right">{VAL_CAT_'.$key.'_H}</td>';
		}
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);
	}

	protected function getRowTemplateTitle() {
		return "tpl.trainer_op_by_tep_cat_row.html";
	}

}