<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



class ilObjReportTrainerWorkload extends ilObjReportBase {
	const MIN_ROW = "3991";
	const OP_TUTOR_IN_ORGU = 'tep_is_tutor';

	protected $table_sums;
	protected $relevant_parameters = array();

	protected $norms;


	public function __construct($ref_id = 0) {
		parent::__construct($ref_id);

		$this->ou_ids = null;

		require_once $this->plugin->getDirectory().'/config/cfg.trainer_workload.php';
	}

	public function initType() {
		 $this->setType("xrtw");
	}


	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rtw')
				->addSetting($this->s_f
								->settingInt('annual_norm_training', $this->plugin->txt('annual_norm_training'))
								->setDefaultValue(1))
				->addSetting($this->s_f
								->settingInt('annual_norm_operation', $this->plugin->txt('annual_norm_operation'))
								->setDefaultValue(1))
				->addSetting($this->s_f
								->settingInt('annual_norm_office', $this->plugin->txt('annual_norm_office'))
								->setDefaultValue(1));
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
		$this->orgu_filter = new recursiveOrguFilter('org_unit', 'huo.orgu_id', true, true);
		$this->orgu_filter->setFilterOptionsByArray($this->getRelevantOrgus());
		$this->orgu_filter->addToFilter($filter);
		$filter	->dateperiod( 	"period"
								 , $this->plugin->txt("period")
								 , $this->plugin->txt("until")
								 , "ht.begin_date"
								 , "ht.begin_date"
								 , date("Y")."-01-01"
								 , date("Y")."-12-31"
								 , false
								 )
				->static_condition("hu.hist_historic = 0")
				->static_condition("ht.hist_historic = 0")
				->static_condition("(ht.category != 'Training' OR (ht.context_id != 0 AND ht.context_id IS NOT NULL))")
				->static_condition("ht.deleted = 0")
				->static_condition("ht.row_id > ".self::MIN_ROW)
				->action($this->filter_action)
				->compile()
				;

		return $filter;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_trainer_workload_row.html";
	}

	protected function buildTable($table) {
		var_dump($this->getNorms());
		$norms = $this->getNorms();
		$table->column("fullname", $this->plugin->txt("fullname"),true);
		foreach($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$table->column($category,$this->plugin->txt($category),true);
			}
			if(count($categories)>1) {
				$table->column($meta_category."_sum", $this->plugin->txt($meta_category."_sum"),true);
			}
			if(isset($norms[$meta_category])) {
				$table->column($meta_category."_workload", $this->plugin->txt($meta_category."_workload"),true);
			}
		}
		$this->buildSumTable();
		return parent::buildTable($table);
	}

	protected function buildSumTable() {
		$norms = $this->getNorms();
		$this->table_sums = catReportTable::create();
		foreach($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$this->table_sums->column($category, $this->plugin->txt($category),true);
			}
			if(count($categories)>1) {
				$this->table_sums->column($meta_category."_sum", $this->plugin->txt($meta_category."_sum"),true);
			}
			if(isset($norms[$meta_category])) {
				$this->table_sums->column($meta_category."_workload", $this->plugin->txt($meta_category."_workload"),true);
			}
		}
		$this->table_sums->template("tpl.gev_trainer_workload_sum_row.html", $this->plugin->getDirectory());
	}

	public function deliverSumTable() {
		return $this->table_sums;
	}

	protected function buildOrder($order) {
		return null;
	}

	protected function getNorms() {
		$norms = array();
		$norms['training']  = $this->settings['annual_norm_training'];
		$norms['operation']  = $this->settings['annual_norm_operation'];		
		$norms['office']  = $this->settings['annual_norm_office'];
		return $norms;
	}

	protected function fetchData(callable $callback) {
		$norms = $this->getNorms();
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
			if(isset($norms[$meta_category])) {
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
					if( isset($norms[$meta_category])) {
						$trainer_data[$meta_category.'_workload'] = 100*$trainer_data[$meta_category.'_sum']/($norms[$meta_category]*$period_days_factor);
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				} else {
					$this->sum_row[$meta_category] += $trainer_data[$meta_category];
					if( isset($this->norms[$meta_category])) {
						$meta_category_sum = count($categories)>1 ? $trainer_data[$meta_category.'_sum'] : $trainer_data[ $categories[0]];
						$trainer_data[$meta_category.'_workload'] = 100*$meta_category_sum/($norms[$meta_category]*$period_days_factor);
						$this->sum_row[$meta_category.'_workload'] += $trainer_data[$meta_category.'_workload'];
					}
				}
			}
			$trainer_data = call_user_func($callback,$trainer_data);
		}
		$count_rows = ($count_rows == 0) ? 1 : $count_rows;
		foreach($norms as $meta_category => $norm) {
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

	protected function getOrgusWhereUserIsSuperior() {
		if ($this->ou_ids !== null) {
			return $this->ou_ids;
		}

		$ds_ous = $this->user_utils->getOrgUnitsWhereUserIsDirectSuperior();
		$s_ous = $this->user_utils->getOrgUnitsWhereUserIsSuperior();

		$ou_ids = array();
		foreach (array_merge($ds_ous, $s_ous) as $ou) {
			$ou_ids[] = $ou["obj_id"];
		}

		$this->ou_ids = $ou_ids;
		return $ou_ids;
	}

	protected function getRelevantOrgus() {

		$sql = 	"SELECT DISTINCT oda.title, oda.obj_id, rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa"
				."	JOIN rbac_operations rop "
				."		ON rop.operation = ".$this->gIldb->quote(self::OP_TUTOR_IN_ORGU,"text")
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = rpa.ref_id "
				."	JOIN object_data oda"
				."		ON oda.obj_id = ore.obj_id";

		$res = $this->gIldb->query($sql);
		$relevant_orgus = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if(in_array($rec["chk"], $perm_check)) {
				$relevant_orgus[] = $rec['obj_id'];
			}
		}
		return array_unique($relevant_orgus);
	}

	protected function getRelevantUsers() {
		require_once './Services/AccessControl/classes/class.ilObjRole.php';
		$ignore_roles_ids = array();
		foreach ($this->ignore_roles as $role_title) {
			$ignore_roles_ids = array_merge($ignore_roles_ids,	ilObjRole::_getIdsForTitle($role_title,'role'));
		}
		$sql = 	"SELECT huo.usr_id, rpa.rol_id, rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa"
				."	JOIN rbac_operations rop "
				."		ON rop.operation = ".$this->gIldb->quote(self::OP_TUTOR_IN_ORGU,"text")
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = rpa.ref_id "
				."	JOIN rbac_ua rua "
				."		ON rua.rol_id = rpa.rol_id "
				."	LEFT JOIN hist_userrole hur "
				."		ON hur.usr_id = rua.usr_id "
				."			AND ".$this->gIldb->in('hur.rol_id',$ignore_roles_ids,false,'integer')
				."			AND hur.hist_historic = 0 "
				."			AND hur.action = 1 "
				."	JOIN hist_userorgu huo "
				."		ON huo.`action` >= 0 AND huo.hist_historic = 0 "
				."			AND huo.usr_id = rua.usr_id "
				."			AND ore.obj_id = huo.orgu_id ";
		$org_units_filter = $this->filter->get("org_unit");
		if(count($org_units_filter)>0) {
			$sql .= " 	AND ".$this->orgu_filter->deliverQuery();
		}
		$sql .= "	WHERE hur.hist_historic IS NULL"
				."	AND ".$this->gIldb->in("huo.usr_id", $this->user_utils->getEmployees(), false, "integer");

		$res = $this->gIldb->query($sql);
		$relevant_users = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if(in_array($rec["chk"], $perm_check)) {
				$relevant_users[] = $rec['usr_id'];
			}
		}
		return array_unique($relevant_users);
	}

	
	public function getRelevantParameters() {
		return $this->relevant_parameters;
	}

	protected function createTemplateFile() {
		$norms = $this->getNorms();
		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_workload_row.html","w"); 

		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td class = "bordered_right" >{VAL_FULLNAME}';
		foreach($this->meta_cats as $meta_category => $categories) {
			foreach ($categories as $category) {
				$tpl .= "</td>\n".'<td align = "right">{VAL_'.strtoupper($category).'}';
			}
			if(count($categories)>1) {
				$class = "bold_content";
				if(!isset($norms[$meta_category])) {
					$class .= " bordered_right";
				}
				$tpl .= "</td>\n".'<td align = "right" class = "'.$class.'">{VAL_'.strtoupper($meta_category).'_SUM}';
			}
			if(isset($norms[$meta_category])) {
				$tpl.= "</td>\n".'<td align = "right" class = "bordered_right bold_content">{VAL_'.strtoupper($meta_category).'_WORKLOAD}';
			}
			
		}
		$tpl.= "</td>";
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);

		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_workload_sum_row.html","w"); 
		$tpl = '<tr class="{CSS_ROW}"><td>';
		foreach($this->workload_meta as $meta_category => $categories) {
			foreach ($categories as $category) {
				$tpl .= "</td>\n".'<td align = "right">{VAL_'.strtoupper($category).'}';
			}
			if(count($categories)>1) {
				$class = "bold_content";
				if(!isset($norms[$meta_category])) {
					$class .= " bordered_right";
				}
				$tpl .= "</td>\n".'<td align = "right" class = "'.$class.'">{VAL_'.strtoupper($meta_category).'_SUM}';
			}
			if(isset($norms[$meta_category])) {
				$tpl.= "</td>\n".'<td align = "right" class = "bordered_right bold_content">{VAL_'.strtoupper($meta_category).'_WORKLOAD}';
			}
			
		}
		$tpl.= "</td>";
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);
	}
}