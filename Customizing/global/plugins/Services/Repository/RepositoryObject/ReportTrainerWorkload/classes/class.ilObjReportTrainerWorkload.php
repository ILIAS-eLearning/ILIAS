<?php

require_once 'Services/ReportsRepository/classes/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

const MIN_ROW = "3991";
const OP_TUTOR_IN_ORGU = 'tep_is_tutor';

class ilObjReportTrainerWorkload extends ilObjReportBase {
	
	protected $gUser;
	protected $sum_row;
	protected $relevant_parameters = array();

	protected $norms;


	public function __construct($ref_id) {
		parent::__construct($ref_id);

		require_once $this->plugin->getDirectory().'/config/cfg.trainer_workload.php';
	}

	public function initType() {
		 $this->setType("xrtw");
	}

	protected function hoursPerConditionRatioNorm($condition, $name, $function) {
		$sql = 	"SUM(IF(".$condition
				.",	".$function
				.",	0)"
				.")"
				." as ".$name;
		return $sql;
	}

	protected function buildQuery($query) {
		$query 	->select("hu.user_id")
				->select_raw("CONCAT(hu.lastname, ', ', hu.firstname) as fullname")
				->select_raw($this->gIldb->quote($this->filter->get('org_unit')[0],"text")." as orgu_title");
		foreach($this->cats as $condition => $cat_settings) {
			$query->select_raw($this->hoursPerConditionRatioNorm($cat_settings['condition'],$condition,$cat_settings['weight']));
		}
		$query->from("hist_tep ht")
					->join("hist_user hu")
						->on("ht.user_id = hu.user_id"
								." AND ".$this->gIldb->in("hu.user_id",$this->getRelevantUsers(),false,"integer"))
					->join("hist_tep_individ_days htid")
						->on("individual_days = id")
					->left_join("hist_course hc")
						->on("context_id = crs_id AND ht.category  = 'Training' AND hc.hist_historic = 0")
					->group_by("hu.user_id")
					->compile();
		return $query;
	}

	protected function buildFilter($filter) {
		$this->getRelevantOrgus();
		$filter	->dateperiod( 	"period"
								 , $this->plugin->txt("period")
								 , $this->plugin->txt("until")
								 , "ht.begin_date"
								 , "ht.begin_date"
								 , date("Y")."-01-01"
								 , date("Y")."-12-31"
								 , false
								 )
				->multiselect( "org_unit"
								 , $this->plugin->txt("org_unit_short")
								 , "orgu_title"
								 , $this->getRelevantOrgus()
								 , array()
								 , "OR TRUE"
								 , 200
								 , 160	
								 )
				->static_condition("hu.hist_historic = 0")
				->static_condition("ht.hist_historic = 0")
				->static_condition("ht.deleted = 0")
				->static_condition("ht.row_id > ".MIN_ROW)
				->action($this->filter_action)
				->compile()
				;

		return $filter;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_trainer_workload_row.html";
	}

	protected function buildTable($table) {

		$table->column("fullname", $this->plugin->txt("fullname"),true);
		foreach($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$table->column($category,$this->plugin->txt($category),true);
			}
			if(count($categories)>1) {
				$table->column($meta_category."_sum", $this->plugin->txt($meta_category."_sum"),true);
			}
			if(isset($this->norms[$meta_category])) {
				$table->column($meta_category."_workload", $this->plugin->txt($meta_category."_workload"),true);
			}
		}
		$this->buildSumTable();
		return parent::buildTable($table);
	}

	protected function buildSumTable() {
		$this->table_sums = catReportTable::create();
		foreach($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$this->table_sums->column($category, $this->plugin->txt($category),true);
			}
			if(count($categories)>1) {
				$this->table_sums->column($meta_category."_sum", $this->plugin->txt($meta_category."_sum"),true);
			}
			if(isset($this->norms[$meta_category])) {
				$this->table_sums->column($meta_category."_workload", $this->plugin->txt($meta_category."_workload"),true);
			}
		}
		$this->table_sums->template("tpl.gev_trainer_workload_sum_row.html", $this->plugin->getDirectory());
	}

	public function deliverSumTable() {
		return $this->table_sums;
	}

	protected function buildOrder($order) {
		$order 	->defaultOrder("fullname", "ASC")
				;
		return $order;
	}

	protected function fetchData(callable $callback) {
		$data = parent::fetchData('static::identity');
		$count_rows = 0;
		$this->sum_row = array();
		foreach ($this->meta_cats as $meta_category => $categories) {
			foreach($categories as $category) {
				$this->sum_row[$category] = 0;
			}
			if(count($categories)>1) {
				$this->sum_row[$meta_category.'_sum'] = 0;
			}
			if(isset($this->norms[$meta_category])) {
				$this->sum_row[$meta_category.'_workload'] = 0;
			}
		}
		$period_days_factor = $this->getPeriodDays()/365;
		foreach($data as &$trainer_data) {
			$count_rows++;
			foreach ($this->meta_cats as $meta_category => $categories) {
				if(count($categories)>1) {
					$trainer_data[$meta_category.'_sum'] = 0;
					foreach ($categories as $category) {
						$this->sum_row[$category] += $trainer_data[$category];
						$trainer_data[$meta_category.'_sum'] += $trainer_data[$category];
					}
					$this->sum_row[$meta_category.'_sum'] += $trainer_data[$meta_category.'_sum'];
					if( isset($this->norms[$meta_category])) {
						$trainer_data[$meta_category.'_workload'] = 100*$trainer_data[$meta_category.'_sum']/($this->norms[$meta_category]*$period_days_factor);
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				} else {
					$this->sum_row[$meta_category] += $trainer_data[$meta_category];
					if( isset($this->norms[$meta_category])) {
						$trainer_data[$meta_category.'_workload'] = 100*$trainer_data[$meta_category.'_sum']/($this->norms[$meta_category]*$period_days_factor);
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				}
			}
			$trainer_data = call_user_func($callback,$trainer_data);
		}
		//die();
		foreach($this->norms as $meta_category => $norm) {
			$this->sum_row[$meta_category.'_workload'] = $this->sum_row[$meta_category.'_workload']/$count_rows;
		}
		$this->sum_row = call_user_func($callback,$this->sum_row);
 		return $data;
	}

	protected function getPeriodDays() {
		$dates = $this->filter->get("period");
        foreach($dates as &$il_date_obj) {
            $il_date_obj = $il_date_obj->getUnixTime();
        }

       	return $period_days = ($dates["end"] - $dates["start"])/86400+1;
	}

	static protected function identity ($rec) {
		return $rec;
	}

	public function fetchSumData() {
		return $this->sum_row;
	}

	protected function getRelevantOrgus() {
		$sql = 	"SELECT DISTINCT oda.title , rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa"
				."	JOIN rbac_operations rop "
				."		ON rop.operation = ".$this->gIldb->quote(OP_TUTOR_IN_ORGU,"text")
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = rpa.ref_id "
				."	JOIN object_data oda"
				."		ON oda.obj_id = ore.obj_id";

		$res = $this->gIldb->query($sql);
		$relevan_users = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if(in_array($rec["chk"], $perm_check)) {
				$relevant_orgus[] = $rec['title'];
			}
		}
		return array_unique($relevant_orgus);
	}

	protected function getRelevantUsers() {
		require_once './Services/AccessControl/classes/class.ilObjRole.php';
		$ignore_roles_ids = array();
		foreach ($this->ignore_roles as $role_title) {
			array_merge($ignore_roles_ids,	ilObjRole::_getIdsForTitle($role_title,'role'));
		}

		$sql = 	"SELECT huo.usr_id, rpa.rol_id, rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa"
				."	JOIN rbac_operations rop "
				."		ON rop.operation = ".$this->gIldb->quote(OP_TUTOR_IN_ORGU,"text")
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = rpa.ref_id "
				."	JOIN rbac_ua rua "
				."		ON rua.rol_id = rpa.rol_id "
				."	LEFT JOIN hist_userrole hur "
				."		ON hur.usr_id = rua.usr_id "
				."			AND ".$this->gIldb->in('hur.rol_id',$ignore_roles_ids,false,'integer')
				."			AND hur.hist_version = 0 "
				."			AND hur.action = 1 "
				."	JOIN hist_userorgu huo "
				."		ON huo.`action` >= 0 AND huo.hist_historic = 0 "
				."			AND huo.usr_id = rua.usr_id "
				."			AND ore.obj_id = huo.orgu_id ";

		if(count($org_unit_filter)>0) {
			$sql .= " 	AND ".$this->gIldb->in('huo.orgu_title',$this->filter->get("org_unit"),false,'text');
		}
		$sql .= "	WHERE hur.hist_historic IS NULL";

		//die($sql);

		$res = $this->gIldb->query($sql);
		$relevan_users = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if(in_array($rec["chk"], $perm_check)) {
				$relevant_users[] = $rec['usr_id'];
			}
		}
		return array_unique($relevant_users);
	}

	public function doCreate() {
		$this->gIldb->manipulate("INSERT INTO rep_robj_rtw ".
			"(id, is_online, annual_norm_training, annual_norm_operation, annual_norm_office) VALUES (".
			$this->gIldb->quote($this->getId(), "integer")
			.",".$this->gIldb->quote(0, "integer")
			.",".$this->gIldb->quote(1, "integer")
			.",".$this->gIldb->quote(1, "integer")
			.",".$this->gIldb->quote(1, "integer")
			.")");
	}


	public function doRead() {
		$set = $this->gIldb->query("SELECT * FROM rep_robj_rtw ".
			" WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
		while ($rec = $this->gIldb->fetchAssoc($set)) {
			$this->setOnline($rec["is_online"]);
			$this->setAnnualNormTraining($rec["annual_norm_training"]);
			$this->setAnnualNormOperation($rec["annual_norm_operation"]);
			$this->setAnnualNormOffice($rec["annual_norm_office"]);
		}
	}

	public function doUpdate() {
		$this->gIldb->manipulate($up = "UPDATE rep_robj_rtw SET "
			." is_online = ".$this->gIldb->quote($this->getOnline(), "integer")
			." ,annual_norm_training = ".$this->gIldb->quote($this->getAnnualNormTraining(), "integer")
			." ,annual_norm_operation = ".$this->gIldb->quote($this->getAnnualNormOperation(), "integer")
			." ,annual_norm_office = ".$this->gIldb->quote($this->getAnnualNormOffice(), "integer")
			." WHERE id = ".$this->gIldb->quote($this->getId(), "integer")
			);
	}

	public function doDelete() {
		$this->gIldb->manipulate("DELETE FROM rep_robj_rtw WHERE ".
			" id = ".$this->gIldb->quote($this->getId(), "integer")
		); 
	}

	public function doClone($a_target_id,$a_copy_id,$new_obj) {
		$new_obj->setOnline($this->getOnline());
		$new_obj->setAnnualNormTraining($this->getAnnualNormTraining());
		$new_obj->setAnnualNormOperation($this->getAnnualNormOperation());
		$new_obj->setAnnualNormOffice($this->getAnnualNormOffice());
		$new_obj->update();
	}

	public function getAnnualNormTraining() {
		return $this->norms['training'];
	}

	public function getAnnualNormOperation() {
		return $this->norms['operation'];
	}

	public function getAnnualNormOffice() {
		return $this->norms['office'];
	}

	public function setAnnualNormTraining($a_val) {
		$this->norms['training'] = $a_val;
	}

	public function setAnnualNormOperation($a_val) {
		$this->norms['operation'] = $a_val;
	}

	public function setAnnualNormOffice($a_val) {
		$this->norms['office'] = $a_val;
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