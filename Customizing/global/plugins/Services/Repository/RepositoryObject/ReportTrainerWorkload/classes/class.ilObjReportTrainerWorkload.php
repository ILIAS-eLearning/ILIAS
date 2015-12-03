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

	public function initType() {
		 $this->setType("xrtw");
	}

	protected function buildQuery($query) {
		$query	
		return $query;
	}

	protected function buildFilter($filter) {
		$this->getRelevantOrgus();
		$filter	->dateperiod( 	"period"
								 , $this->lng->txt("gev_period")
								 , $this->lng->txt("gev_until")
								 , "ht.begin_date"
								 , "ht.begin_date"
								 , date("Y")."-01-01"
								 , date("Y")."-12-31"
								 , false
								 , " OR ht.hist_historic IS NULL"
								 )
				->multiselect( "org_unit"
								 , $this->lng->txt("gev_org_unit_short")
								 , "orgu_title"
								 , $this->relevant_orgus
								 , array()
								 , "OR TRUE"
								 , 200
								 , 160	
								 )
				->static_condition("hu.hist_historic = 0")
				->static_condition("ht.hist_historic = 0")
				->static_condition("ht.deleted = 0")
			//	->static_condition("ht.row_id > ".MIN_ROW)
				->action($this->filter_action)
				->compile()
				;

		return $filter;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_trainer_workload_row.html";
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

	protected function fetchData(callable $callback) {
		$data = parent::fetchData(null);
		foreach($data as &$trainer_data) {
			foreach ($this->workload_meta as $meta_category => $categories) {
				if(count($categories)>1) {
					$trainer_data[$meta_category.'_sum'] = 0;
					foreach ($categories as $category) {
						$this->sum_row[$category] += $trainer_data[$category];
						$trainer_data[$meta_category.'_sum'] += $trainer_data[$category];
					}
					$this->sum_row[$meta_category.'_sum'] += $trainer_data[$meta_category.'_sum'];
					if( isset($this->norms[$meta_category])) {
						$trainer_data[$meta_category.'_workload'] = $trainer_data[$meta_category.'_sum']/$this->norms[$meta_category];
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				} else {
					$this->sum_row[$meta_category] += $trainer_data[$meta_category];
					if( isset($this->norms[$meta_category])) {
						$trainer_data[$meta_category.'_workload'] = $trainer_data[$meta_category]/$this->norms[$meta_category];
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				}
			}
			$trainer_data = call_user_func($callback,$trainer_data);
		}
		$this->sum_row = call_user_func($callback,$this->sum_row);
 		return $data;
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
		$sql = 	"SELECT huo.usr_id, rpa.rol_id, rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa"
				."	JOIN rbac_operations rop "
				."		ON rop.operation = ".$this->gIldb->quote(OP_TUTOR_IN_ORGU,"text")
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = rpa.ref_id "
				."	JOIN rbac_ua rua "
				."		ON rua.rol_id = rpa.rol_id "
				."	JOIN hist_userorgu huo "
				."		ON `action` = 1 AND hist_historic =0 "
				."			AND huo.usr_id = rua.usr_id "
				."			AND ore.obj_id = huo.orgu_id ";
		$org_unit_filter = $this->filter->get("org_unit")
		if(count($org_unit_filter)>0) {
			$sql .= " 	AND ".$this->gIldb->in('huo.orgu_title',$org_unit_filter,false,'text');
		}


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
			")");
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
		return $this->annual_norm_training;
	}

	public function getAnnualNormOperation() {
		return $this->annual_norm_operation;
	}

	public function getAnnualNormOffice() {
		return $this->annual_norm_office;
	}

	public function setAnnualNormTraining($a_val) {
		$this->annual_norm_training = $a_val;
	}

	public function getAnnualNormOperation($a_val) {
		$this->annual_norm_operation = $a_val;
	}

	public function getAnnualNormOffice($a_val) {
		$this->annual_norm_office = $a_val;
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