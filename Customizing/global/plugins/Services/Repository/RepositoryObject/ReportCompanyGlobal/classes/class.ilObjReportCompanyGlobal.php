<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classses/class.gevAMDUtils.php';
require_once 'Services/GEV/Utils/classses/class.gevSettings.php';
require_once 'Services/GEV/Utils/classses/class.gevCourseUtils.php';

class ilObjReportCompanyGlobal extends ilObjReportBase {
	
	protected $online;
	protected $relevant_parameters = array();
	protected $query_class;
	protected $not_participated = array('-empty-','nicht gesetzt','ausgeschieden');
	protected $filter_orgus = array();
	protected $sql_filter_orgus = null;
	protected $template_ref_field_id;

	public function __construct() {
		parent::__construct();
		$amd_utils = gevAMDUtils::getInstance();
		$this->template_ref_field_id = $amd_utils->getFieldId( gevSettings::CRS_AMD_TEMPLATE_REF_ID);
	}

	public function initType() {
		 $this->setType("xrcg");
	}

	/**
	* We can not use regular query logic here (since there is no outer-join in mysql)
	* so lets take this opportunity to at least do some preparation work for the actual query construction in getTrainingTypeQuery.
	*/
	protected function buildQuery($query) {
		return $this->prepareQueryComponents($query);
	}

	protected function prepareQueryComponents($query) {
		// this will be used later to invoke other query objects. A cloning of a virgin query object would be more formal, but since right now __clone is not defined for queries...
		$this->query_class = get_class($query);

		$this->types = gecCourseUtils::getTypeOptions();
		unset($this->types[$lng->txt("gev_crs_srch_all")]); //not nice, but what you gona do...

		// this also is quite a hack, but once we have the new filter-api it can be fixed
		$filter_orgus = $this->filter->get('orgu');
		if(count($filter_orgus) > 0) {
			$this->sql_filter_orgus = 
			"SELECT DISTINCT usr_id, ".$this->gIldb->quote($filter_orgus[0],"text")." orgu FROM hist_userorgu"
			."	WHERE ".$this->gIldb->in('orgu_title',$filter_orgus,false,'text')
			."	AND hist_historic = 0 AND action >= 0 ";
		}
		return null;
	}

	protected function buildFilter($filter) {
		return $filter;
	}

	protected function fetchData(callable $callback){
		$data = $this->joinPartialDatas(
				$this->fetchPartialData(getPartialQuery(true))
				,$this->fetchPartialData(getPartialQuery(false))
				);
		foreach($data as &$row) {
			$row = call_user_func($callback,$row);
		}
		return $data;
	}

	protected function buildTable($table) {

		$table	->template(self::$config[$this->report_mode]["tpl"], "Services/ReportsRepository");
		return $table;
	}

	protected function buildOrder($order) {
		return $order;
	}

	protected function getPartialQuery($has_participated) {
		$prefix = $has_participated ? 'part' : 'book';
		$query = {$this->query_class}::create()
					->select('hc.type')
					->select('hc.training_programm')
					->select_raw('COUNT(book.usr_id) '.$prefix.'_book')
					->select_raw('COUNT(DISTINCT book.usr_id) '.$prefix.'_user');
		if($has_participated) {
			$query	->select_raw('SUM( IF( part.credit_points IS NOT NULL AND part.credit_points > 0, part.credit_points, 0) ) wp_part');
		}
		$query 		->from('hist_course hc')
					->join('hist_usercoursestatus hucs')
						->on('hc.crs_id = hucs.crs_id'
							.'	AND '.$this->gIldb->in('book.participation_status' ,$this->not_participated, $has_participated, 'text'));
		if($this->sql_filter_orgus) {
			$query	->raw_join('('.$this->sql_filter_orgus.') as orgu ON ')
					->select('orgu.orgu')
		}
					->group_by('hc.type')
					->compile();
		return $query;
	}

	protected function fetchPartialData($a_query) {
		$query = $a_query->sql()."\n "
				. $this->queryWhere()."\n "
				. $a_query->sqlGroupBy()."\n"
				. $this->queryHaving()."\n"
				. $this->queryOrder();
		$res = $this->gIldb->query($query);
		$return = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$return[$rec["type"]] = $rec;
		}
		return $return;
	}

	protected function joinPartialDatas(array $a_data, array $b_data) {
		$return = array();
		foreach ($this->types as $type) {
			if(!isset($a_data[$type])) {
				$a_data[$type] = array();
			}
			if(!isset($b_data[$type])) {
				$b_data[$type] = array();
			}
			$return[] = array_merge($a_data[$type],$b_data[$type])
		}
		return $return;
	}
 
	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rcg ".
			"(id, is_online) VALUES (".
			$this->gIldb->quote($this->getId(), "integer").",".
			$this->gIldb->quote(0, "integer").
			")");
	}

	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rcg ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
		}
	}

	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_rcg SET "
			." is_online = ".$this->gIldb->quote($this->getOnline(), "integer")
			." WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rcg WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}



	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->update();
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