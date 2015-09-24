<?php

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/Calendar/classes/class.ilDate.php");
//array(3) { [0]=> string(8) "230:1502" [1]=> string(8) "230:1850" [2]=> string(9) "1510:1850" } 
const MIN_ROW = "3991";
const OP_TUTOR_IN_ORGU = 'tep_is_tutor';


class gevTrainerWorkloadGUI extends catBasicReportGUI{
	protected $norms = array();
	protected $role_ops_filter;
	protected $relevant_users;
	protected $relevant_orgus;
	protected $orgu_filter;
	protected $sum_row = array();
	protected $count_rows = 0;
	protected $workload_meta;


	public function __construct() {
		include "Services/GEV/Reports/config/cfg.trainer_workload.php";
		$this->workload_meta = $workload_meta;
		parent::__construct();	

				$this->title = catTitleGUI::create()
						->title("gev_report_trainer_workload")
						->subTitle("gev_report_trainer_workload_desc")
						->image("GEV_img/ico-head-edubio.png")
						;


		$this->getRelevantOrgus();
		$this->filter = catFilter::create()
				->dateperiod( 	"period"
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
				->static_condition("ht.row_id > ".MIN_ROW)
				->action($this->ctrl->getLinkTarget($this, "view"))
				->compile()
				;

		$this->relevant_parameters = array(
			$this->filter->getGETName() => $this->filter->encodeSearchParamsForGET()
			); 

		$this->orgu_filter = $this->filter->get("org_unit");
		$this->getRelevantUsers();


		$dates = $this->filter->get("period");
        foreach($dates as &$il_date_obj) {
            $il_date_obj = $il_date_obj->getUnixTime();
        }

       	$period_days = ($dates["end"] - $dates["start"])/86400+1;
       	foreach ($workload_days_per_yead_norm as $meta_category => $days) {
       		$this->norms[$meta_category] = $days*$period_days/36500;
       	}

       	//$this->createTemplateFile();


		$this->table = catReportTable::create();
		$this->table->column("fullname", "name");

						foreach($workload_meta as $meta_category => $categories) {
							foreach ($categories as $category) {
								$this->table->column($category,$workload_label[$category], true);
							}
							if(count($categories)>1) {
								$this->table->column($meta_category."_sum", "Summe ".$workload_label[$meta_category], true);
							}
							if(isset($workload_days_per_yead_norm[$meta_category])) {
								$this->table->column($meta_category."_wload", "Auslastung ".$workload_label[$meta_category], true);				
							}
						}
						$this->table->template("tpl.gev_trainer_workload_row.html", 
												"Services/GEV/Reports");


		$this->table_sums = catReportTable::create();
						foreach($workload_meta as $meta_category => $categories) {
							foreach ($categories as $category) {
								$this->table_sums->column($category,$workload_label[$category], true);
							}
							if(count($categories)>1) {
								$this->table_sums->column($meta_category."_sum", "Summe ".$workload_label[$meta_category], true);
							}
							if(isset($workload_days_per_yead_norm[$meta_category])) {
								$this->table_sums->column($meta_category."_wload", "Auslastung ".$workload_label[$meta_category], true);				
							}
						}

						$this->table_sums->template("tpl.gev_trainer_workload_sum_row.html", "Services/GEV/Reports");



		$this->query = catReportQuery::create()
				->select("hu.user_id")
				->select_raw("CONCAT(hu.lastname, ', ', hu.firstname) as fullname")
				->select_raw($this->db->quote($this->orgu_filter[0],"text")." as orgu_title");

		foreach($workload_training_conditions as $category => $condition) {
			$this->query->select_raw($this->hoursPerConditionRatioNorm(" ht.category  = 'Training' AND ".$condition, 8, $category));
		}

		foreach($workload_tep_cats as $category => $tep_cats) {
			if(in_array($category, $workload_fullday)) {
				$this->query->select_raw($this->fullDay($this->db->in('ht.category',$tep_cats,false,'text'), $category));
			} else {
				$this->query->select_raw($this->hoursPerConditionRatioNorm($this->db->in('ht.category',$tep_cats,false,'text'), 8, $category));
			}
		}
		$this->query->from("hist_tep ht")
					->join("hist_user hu")
						->on("ht.user_id = hu.user_id"
								." AND ".$this->db->in("hu.user_id",$this->relevant_users,false,"integer"))
					->join("hist_tep_individ_days htid")
						->on("individual_days = id")
					->left_join("hist_course hc")
						->on("context_id = crs_id AND ht.category  = 'Training' AND hc.hist_historic = 0")
					->group_by("hu.user_id")
					->compile();
		$this->tpl->addCSS('Services/GEV/Reports/templates/css/report.css');
	}

	protected function transformResultRow($rec) {
		$this->count_rows++;

		foreach ($this->workload_meta as $meta_category => $categories) {
			if(count($categories)>1) {
				$rec[$meta_category.'_sum'] = 0;
				foreach ($categories as $category) {
					$this->sum_row[$category] += $rec[$category];
					$rec[$meta_category.'_sum'] += $rec[$category];
					$rec[$category] = number_format($rec[$category],2);
				}
				$rec[$meta_category.'_sum'] = number_format($rec[$meta_category.'_sum'],2);
				$this->sum_row[$meta_category.'_sum'] += $rec[$meta_category.'_sum'];
				if( isset($this->norms[$meta_category])) {
					$rec[$meta_category.'_workload'] = $rec[$meta_category.'_sum']/$this->norms[$meta_category];
					$this->sum_row[$meta_category.'_workload'] += $rec[$meta_category.'_workload'];
					$rec[$meta_category.'_workload'] = number_format($rec[$meta_category.'_workload'],2);
				}
			} else {
				$this->sum_row[$meta_category] += $rec[$meta_category];
				$rec[$meta_category] = number_format($rec[$meta_category],2);
				if( isset($this->norms[$meta_category])) {
					$rec[$meta_category.'_workload'] = number_format($rec[$meta_category]/$this->norms[$meta_category],2);
					$this->sum_row[$meta_category.'_workload'] += $rec[$meta_category.'_workload'];
				}
			}
		}

		return $this->replaceEmpty($rec);
	}

	protected function hoursPerConditionRatioNorm($condition,$norm, $name) {
		$sql = 	"SUM(IF(".$condition." ,"
				."		IF(htid.end_time IS NOT NULL AND htid.start_time IS NOT NULL,"
				."			LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( htid.end_time, htid.start_time ) )* htid.weight /720000) *2,8),"
				."			LEAST(CEIL( 28800* htid.weight /720000) *2,8)"
				."		),"
				."	0)"
				.")/"
			.$this->db->quote($norm,"float")." as ".$name;
		return $sql;
	}

	protected function fullDay($condition, $name) {
		$sql = 	"SUM(IF(".$condition." ,
			1,0)) as ".$name;
		return $sql;
	}

	protected function createTemplateFile() {
		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_workload_row.html","w"); 
		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td class = "bordered_right" >{VAL_FULLNAME}';
		foreach($this->workload_meta as $meta_category => $categories) {
			foreach ($categories as $category) {
				$tpl .= "</td>\n".'<td align = "right">{VAL_'.strtoupper($category).'}';
			}
			if(count($categories)>1) {
				$class = "bold_content";
				if(!isset($this->norms[$meta_category])) {
					$class .= " bordered_right";
				}
				$tpl .= "</td>\n".'<td align = "right" class = "'.$class.'">{VAL_'.strtoupper($meta_category).'_SUM}';
			}
			if(isset($this->norms[$meta_category])) {
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
				if(!isset($this->norms[$meta_category])) {
					$class .= " bordered_right";
				}
				$tpl .= "</td>\n".'<td align = "right" class = "'.$class.'">{VAL_'.strtoupper($meta_category).'_SUM}';
			}
			if(isset($this->norms[$meta_category])) {
				$tpl.= "</td>\n".'<td align = "right" class = "bordered_right bold_content">{VAL_'.strtoupper($meta_category).'_WORKLOAD}';
			}
			
		}
		$tpl.= "</td>";
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);
	}

	protected function getOrgus() {
		$sql = "SELECT DISTINCT title FROM object_data "
				." WHERE type = 'orgu'";
		$res = $this->db->query($sql);
		while($rec = $this->db->fetchAssoc($res)) {
			$return[] = $rec["title"];
		}
		return $return;
	}

	protected function getRelevantOrgus() {
		$sql = 	"SELECT DISTINCT oda.title , rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa"
				."	JOIN rbac_operations rop "
				."		ON rop.operation = ".$this->db->quote(OP_TUTOR_IN_ORGU,"text")
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = rpa.ref_id "
				."	JOIN object_data oda"
				."		ON oda.obj_id = ore.obj_id";

		$res = $this->db->query($sql);

		while($rec = $this->db->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if(in_array($rec["chk"], $perm_check)) {
				$this->relevant_orgus[] = $rec['title'];
			}
		}
		$this->relevant_orgus = array_unique($this->relevant_orgus);
	}


	protected function getRelevantUsers() {
		$sql = 	"SELECT huo.usr_id, rpa.rol_id, rpa.ops_id, rop.ops_id AS chk "
				."	FROM rbac_pa rpa"
				."	JOIN rbac_operations rop "
				."		ON rop.operation = ".$this->db->quote(OP_TUTOR_IN_ORGU,"text")
				."			AND LOCATE( CONCAT( ':', rop.ops_id, ';' ) , rpa.ops_id ) >0 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = rpa.ref_id "
				."	JOIN rbac_ua rua "
				."		ON rua.rol_id = rpa.rol_id "
				."	JOIN hist_userorgu huo "
				."		ON `action` = 1 AND hist_historic =0 "
				."			AND huo.usr_id = rua.usr_id "
				."			AND ore.obj_id = huo.orgu_id ";
		if(count($this->orgu_filter)>0) {
			$sql .= " 	AND ".$this->db->in('huo.orgu_title',$this->orgu_filter,false,'text');
		}


		$res = $this->db->query($sql);

		while($rec = $this->db->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if(in_array($rec["chk"], $perm_check)) {
				$this->relevant_users[] = $rec['usr_id'];
			}
		}
		$this->relevant_users = array_unique($this->relevant_users);
	}

	protected function renderView() {
		$main_table = $this->renderTable();
		return 	$this->renderSumTable()
				.$main_table;
	}


	private function renderSumTable(){
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate(
			$this->table_sums->row_template_filename, 
			$this->table_sums->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table_sums->columns as $col) {
			$table->addColumn( $col[2] ? $col[1] : $this->lng->txt($col[1])
							 , $col[0]
							 , $col[3]
							 );
		}		
		if(count($this->sum_row) == 0) {
			foreach(array_keys($this->table_sums->columns) as $field) {
				$this->sum_row[$field] = 0;
			}
		}
		foreach($this->norms as $meta_category => $norm) {
			$this->sum_row[$meta_category.'_workload'] = number_format($this->sum_row[$meta_category.'_workload']/$this->count_rows,2);
		}

		$table->setData(array($this->sum_row));
		return $table->getHtml();
	}


}