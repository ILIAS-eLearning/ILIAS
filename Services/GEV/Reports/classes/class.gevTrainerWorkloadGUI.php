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
	protected $meta_categories;
	protected $norms;
	protected $role_ops_filter;
	protected $relevant_users;

	public function __construct() {
		include "Services/GEV/Reports/config/cfg.tep_reports_config.php";
		// $meta_categories in config
		$this->meta_categories = $meta_categories;
		// $norms in config
		$this->norms = $norms;
		parent::__construct();	


		//$this->createTemplateFile();
	

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
								 , $this->lng->txt("gev_report_filter_crs_region")
								 , "orgu_title"
								 , $this->getOrgus()
								 , array()
								 , ""
								 , 200
								 , 160	
								 )
				->static_condition("hu.hist_historic = 0")
				->static_condition("ht.hist_historic = 0")
				->static_condition("ht.deleted = 0")
				->static_condition("ht.user_id != 0")
				->static_condition("ht.orgu_title != '-empty-'")
				->static_condition("ht.row_id > ".MIN_ROW)
				->action($this->ctrl->getLinkTarget($this, "view"))
				->compile()
				;

		$this->getFilterForOperationInOrgu();
		$this->getRelevantUsers();

		$dates = $this->filter->get("period");
        foreach($dates as &$il_date_obj) {
            $il_date_obj = $il_date_obj->getUnixTime();
        }

       	$period_days = ($dates["end"] - $dates["start"])/86400+1;

        foreach ($this->norms as $meta_category => &$norm) {
			$norm["days"] = $period_days*$norm["days"]/30;
			$norm["hours"] = $period_days*$norm["hours"]/30;
		}

		$this->table = catReportTable::create();
		$this->table->column("fullname", "name");

		foreach($this->meta_categories as $meta_category => $categories) {
			$this->table->column(strtolower($meta_category."_d"), $meta_category, true);
			$this->table->column(strtolower($meta_category."_h"), "Std.", true);
		}
		$this->table->template("tpl.gev_trainer_workload_row.html", 
								"Services/GEV/Reports");

		$this->query = catReportQuery::create()
				->distinct()
				->select("hu.user_id")
				->select_raw("CONCAT(hu.lastname, ', ', hu.firstname) as fullname");

		foreach($this->meta_categories as $meta_category => $categories) {
			$this->query->select_raw($this->daysPerTEPMetaCategoryRatio($categories, $this->norms[$meta_category]["days"], strtolower($meta_category)."_d"));
			$this->query->select_raw($this->hoursPerTEPMetaCategoryRatio($categories, $this->norms[$meta_category]["hours"],strtolower($meta_category)."_h"));
		}
		$this->query->from("hist_tep ht")
					->join("hist_user hu")
						->on("ht.user_id = hu.user_id")
					->join("hist_tep_individ_days htid")
						->on("individual_days = id")
					->left_join("hist_course hc")
						->on("context_id = crs_id AND ht.category  = 'Training'")
					->group_by("hu.user_id")
					->compile();
	}

	protected function daysPerTEPMetaCategoryRatio($categories, $norm, $name) {
		$sql = "100 * SUM(IF(".$this->db->in('category',$categories,false,"text")." ,1,0))/"
				.$this->db->quote($norm,"float")." as ".$name;
		return $sql;
	}

	protected function hoursPerTEPMetaCategoryRatio($categories,$norm, $name) {
		$sql = 	"100 * SUM(IF(".$this->db->in('category',$categories,false,"text")." ,
			LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( htid.end_time, htid.start_time ) )* htid.weight /720000) *2,8),0))/"
			.$this->db->quote($norm,"float")." as ".$name;
		return $sql;
	}

	protected function createTemplateFile() {
		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_workload_row.html","w"); 
		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td>{VAL_FULLNAME}</td>';
		foreach($this->meta_categories as $meta_category => $categories) {
			$tpl .= "\n".'<td align = "right">{VAL_'.strtoupper($meta_category).'_D}</td>';
			$tpl .= "\n".'<td align = "right">{VAL_'.strtoupper($meta_category).'_H}</td>';
			$i++;
		}
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);
	}

	protected function getOrgus() {
		$sql = "SELECT DISTINCT title FOM object_data "
				." WHERE type = 'orgu'";
		$res = $this->db->query($sql);
		while($rec = $this->db->fetchAssoc($res)) {
			$return[] = $rec["title"];
		}
		return $return;
	}

	protected function getFilterForOperationInOrgu() {
		$sql = "SELECT CONCAT(oda.obj_id,':',rpa.rol_id) both_id , rpa.ops_id, rop.ops_id as chk FROM rbac_pa rpa"
				."	JOIN rbac_operations rop"
				."		ON rop.operation = ".$this->db->quote(OP_TUTOR_IN_ORGU,'text')
				."		AND LOCATE(CONCAT(':',rop.ops_id,';'), rpa.ops_id) > 0"
				."	JOIN object_reference ore "
				."		ON rpa.ref_id = ore.ref_id"
				."	JOIN object_data oda "
				."		ON oda.obj_id = ore.obj_id AND oda.type = 'orgu'"
				."	WHERE ore.deleted IS NULL";
		$res = $this->db->query($sql);
		$return = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$perm_check = unserialize($rec['ops_id']);
			if(in_array($rec["chk"], $perm_check)) {
				$return[] = $rec['both_id'];
			}
		}
		$this->role_ops_filter = $return;	
	}

	protected function getRelevantUsers() {
		$sql = "SELECT DISTINCT usr_id FROM hist_userorgu huo "
				."	LEFT JOIN hist_userrole hur "
				."		ON huo.usr_id = hur.usr_id "
				."		AND ".$this->db->in("CONCAT(huo.orgu_id,':',hur.rol_id)", $this->role_ops_filter, false, 'text')
				."	WHERE (hur.rol_id IS NOT NULL OR ".$this->db->in("CONCAT(huo.orgu_id,':',huo.rol_id)",$this->role_ops_filter,false,'text').")";

		$orgu_filter = $this->filter->get("org_unit");
		if(count($orgu_filter) > 0) {
			$sql .= " AND ".$this->db->in("huo.orgu_title", $orgu_filter, false, "text");	
		}

		$res = $this->db->query($sql);
		while($res = $this->db->fetchAssoc($res)) {
			$return[] = $res["usr_id"];
		}
		$this->relevant_users = $return;
	}
}
?>