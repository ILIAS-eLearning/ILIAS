<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevAMDUtils.php';
require_once 'Services/GEV/Utils/classes/class.gevSettings.php';
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

class ilObjReportCompanyGlobal extends ilObjReportBase {
	
	protected $online;
	protected $relevant_parameters = array();
	protected $query_class;
	protected static $participated = array('teilgenommen','fehlt entschuldigt','fehlt ohne Absage');
	protected static $columns_to_sum = array('book_book' => 'book_book','part_book' => 'part_book','wp_part' => 'wp_part');
	protected static $wbd_relevant = array('OKZ1','OKZ2','OKZ3');
	protected $types;
	protected $filter_orgus = array();
	protected $sql_filter_orgus = null;
	protected $template_ref_field_id;


	public function initType() {
		 $this->setType("xrcg");
		 $amd_utils = gevAMDUtils::getInstance();
		 $this->types = $amd_utils->getOptions(gevSettings::CRS_AMD_TYPE);
	}

	/**
	* We can not use regular query logic here (since there is no outer-join in mysql and i would like to avoid a lot of subqueries)
	* so lets take this opportunity to do some preparation work for the actual query construction in getTrainingTypeQuery at least.
	*/
	protected function buildQuery($query) {
		return $this->prepareQueryComponents($query);
	}

	protected function prepareQueryComponents($query) {
		// this will be used later to invoke other query objects. A cloning of a "virgin" query object would be more formal, 
		// but since right now __clone is not defined for queries...
		$this->query_class = get_class($query);

		// this is quite a hack, but once we have the new filter-api it can be fixed
		$filter_orgus = $this->filter->get('orgu');
		if(count($filter_orgus) > 0) {
			$this->sql_filter_orgus = 
			"SELECT DISTINCT usr_id, ".$this->gIldb->quote($filter_orgus[0],"text")." orgu FROM hist_userorgu"
			."	WHERE ".$this->gIldb->in('orgu_title',$filter_orgus,false,'text')
			." OR ".$this->gIldb->in('org_unit_above1',$filter_orgus,false,'text')
			." OR ".$this->gIldb->in('org_unit_above2',$filter_orgus,false,'text')
			."	AND hist_historic = 0 AND action >= 0 ";
		}
		return null;
	}

	protected function buildFilter($filter) {
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

		$filter ->dateperiod( "period"
							 , $this->lng->txt("gev_period")
							 , $this->lng->txt("gev_until")
							 , "hc.end_date"
							 , "hc.end_date"
							 , date("Y")."-01-01"
							 , date("Y")."-12-31"
							 )
				->multiselect( "orgu"
							 , $this->lng->txt("gev_org_unit_short")
							 , array("type")
							 , $org_units_filter
							 , array()
							 , ""
							 , 200
							 , 160
							 )
				->multiselect("edu_program"
							 , $this->lng->txt("gev_edu_program")
							 , "edu_program"
							 , gevCourseUtils::getEduProgramsFromHisto()
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
							 , 200
							 , 160	
							)
				->multiselect_custom( "dct_type"
							 , $this->lng->txt("gev_course_type")
							 , array($this->lng->txt("gev_dec_trainings_fixed") => "hc.edu_program = ".$this->gIldb->quote("dezentrales Training","text")." AND hc.dct_type = ".$this->gIldb->quote("fixed","text")
							 		,$this->lng->txt("gev_dec_trainings_flexible") => "hc.edu_program = ".$this->gIldb->quote("dezentrales Training","text")." AND hc.dct_type = ".$this->gIldb->quote("flexible","text")
							 		,$this->lng->txt("non_dec_trainings") => "hc.edu_program != ".$this->gIldb->quote("dezentrales Training","text"))
							 , array()
							 , ""
							 , 200
							 , 160
							 , "text"
							 , "desc"
							 )
				->multiselect_custom( 'wbd_relevant'
							 , $this->lng->txt('gev_wbd_relevant')
							 , array( $this->lng->txt('yes') => $this->gIldb->in('hucs.okz',self::$wbd_relevant,false,'text') , $this->lng->txt('no') => $this->gIldb->in('hucs.okz',self::$wbd_relevant,true,'text') )
							 , array()
							 , ""
							 , 200
							 , 50
							 ,"text"
							 , "asc"
							 ,true
							 )
				->multiselect_custom( 'wb_points'
							 , $this->lng->txt('gev_edupoints')
							 , array( $this->lng->txt('gev_trainings_w_points') => ' hc.max_credit_points > 0 ',  $this->lng->txt('gev_trainings_wo_points') => "hc.max_credit_points in (0,'-empty-')")
							 , array()
							 , ""
							 , 200
							 , 50
							 ,"text"
							 ,"none"
							 ,true
							 )
				->static_condition("hucs.hist_historic = 0")
				->static_condition("hc.hist_historic = 0")
				->static_condition($this->gIldb->in('hc.type', $this->types, false, 'text'))
				->static_condition('hucs.function = '.$this->gIldb->quote('Mitglied','text'))
				->action($this->filter_action)
				->compile()
				;
		return $filter;
	}

	protected function fetchData(callable $callback){
		$data = $this->joinPartialDataSets(
				$this->fetchPartialDataSet($this->getPartialQuery(true))
				,$this->fetchPartialDataSet($this->getPartialQuery(false))
				);

		$sum_data = array();

		foreach($data as &$row) {
			$row = call_user_func($callback,$row);
			foreach (self::$columns_to_sum as $column) {
				if(!isset($sum_data[$column])) {
					$sum_data[$column] = 0;
				}
				$sum_data[$column] += $row[$column];
			}
		}

		$sum_data['type'] = $this->lng->txt('gev_sum');
		$sum_data['part_user'] = '--';
		$sum_data['book_user'] = '--';
		$data['sum'] = $sum_data;
		return $data;
	}

	protected function buildTable($table) {
		$table  ->column('type','type')
				->column('book_book','gev_bookings')
				->column('book_user','gev_crs_members')
				->column('part_book','gev_bookings')
				->column('wp_part','gev_edupoints')
				->column('part_user','gev_crs_members')
				->template('tpl.cat_global_company_report_data_row.html', "Services/ReportsRepository");
		return $table;
	}

	protected function buildOrder($order) {
		return $order;
	}

	protected function getPartialQuery($has_participated) {
		$prefix = $has_participated ? 'part' : 'book';

		$query = call_user_func($this->query_class.'::create');
		$query		->select('hc.type')
					->select_raw('COUNT(hucs.usr_id) '.self::$columns_to_sum[$prefix.'_book'])
					->select_raw('COUNT(DISTINCT hucs.usr_id) '.$prefix.'_user');
		if($has_participated) {
			$query	->select_raw('SUM( IF( hucs.credit_points IS NOT NULL AND hucs.credit_points > 0, hucs.credit_points, 0) ) '.self::$columns_to_sum['wp_part']);
		}
		$query 		->from('hist_course hc')
					->join('hist_usercoursestatus hucs')
						->on('hc.crs_id = hucs.crs_id'
							.'	AND '.$this->gIldb->in('hucs.participation_status' , self::$participated, !$has_participated, 'text'));
		if($this->sql_filter_orgus) {
			$query	->raw_join('('.$this->sql_filter_orgus.') as orgu ON ')
					->select('orgu.orgu');
		}
			$query	->group_by('hc.type')
					->compile();
		return $query;
	}

	protected function fetchPartialDataSet($a_query) {
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

	protected function joinPartialDataSets(array $a_data, array $b_data) {
		$return = array();
		//seems like a nice usecase for linq
		foreach ($this->types as $type) {
			if(!isset($a_data[$type])) {
				$a_data[$type] = array('type' => $type);
			}
			if(!isset($b_data[$type])) {
				$b_data[$type] = array('type' => $type);
			}
			$return[$type] = array_merge($a_data[$type],$b_data[$type]);
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