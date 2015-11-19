<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classses/class.gevAMDUtils.php';
require_once 'Services/GEV/Utils/classses/class.gevSettings.php';

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

	protected function buildQuery($query) {
		$this->$query_class = get_class($query);
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
		$created = $this->filter->get("created");
		if ($created["end"]->get(IL_CAL_UNIX) < $created["start"]->get(IL_CAL_UNIX) ) {
			return array();
		}
		
		//fetch retrieves the data 
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$no_entry = $this->lng->txt("gev_table_no_entry");
		$data = array();


		//when ordering the table, watch out for date!
		//_table_nav=date:asc:0
		//btw, what is the third parameter?
		if(isset($_GET['_table_nav'])){
			$this->external_sorting = true; //set to false again, 
											//if the field is not relevant

			$table_nav_cmd = split(':', $_GET['_table_nav']);
			
			if ($table_nav_cmd[1] == "asc") {
				$direction = " ASC";
			}
			else {
				$direction = " DESC";
			}
			
			switch ($table_nav_cmd[0]) { //field
				case 'date':
					$direction = strtoupper($table_nav_cmd[1]);
					$sql_order_str = " ORDER BY crs.begin_date ";
					$sql_order_str .= $direction;
					break;
				
				//append more fields, simply for performance...

				default:
					$this->external_sorting = true;
					$sql_order_str = " ORDER BY ".$this->gIldb->quoteIdentifier($table_nav_cmd[0])." ".$direction;
					break;
			}
		}


		$res = $this->gIldb->query($query);

		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = call_user_func($callback,$rec);
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

	protected function getTrainingTypeData($training_type) {

	}

	protected function getTrainingTypeQuery($training_type,$has_participated) {
		$query = {$this->query_class}::create()
					->select('hc.type')
					->select('hc.training_programm')
					->select_raw('COUNT(book.usr_id) book')
					->select_raw('COUNT(DISTINCT book.usr_id) user');
		if($has_participated) {
			$query	->select_raw('SUM( IF( part.credit_points IS NOT NULL AND part.credit_points > 0, part.credit_points, 0) ) wp_part');
		}
		$query 		->from('hist_course hc')
					->join('hist_usercoursestatus hucs')
						->on('hc.crs_id = hucs.crs_id'
							.'	AND '.$this->gIldb->in('book.participation_status' ,$this->not_participated, $has_participated, 'text'));
					->left_join('adv_md_values_int amd')
						->on('hc.crs_id = amd.obj_id'
							.' AND amd.field_id = '.$this->gIldb->quote($this->template_ref_field_id,'integer')	)
		if($this->sql_filter_orgus) {
			$query	->raw_join('('.$this->sql_filter_orgus.') as orgu ON ')
					->select('orgu.orgu')
		}
					->group_by('hc_type')
					->compile();
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