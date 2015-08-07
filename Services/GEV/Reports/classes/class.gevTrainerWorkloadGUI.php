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

const MIN_ROW = "3991";

class gevTrainerWorkloadGUI extends catBasicReportGUI{
	protected $meta_categories;
	protected $norms;
	public function __construct() {
		include "Services/GEV/Reports/config/cfg.tep_reports_config.php";
		// $meta_categories in config
		$this->meta_categories = $meta_categories;
		// $norms in config
		$this->norms = $norms;
		$this->createTemplateFile();
		parent::__construct();
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
				->static_condition("hu.hist_historic = 0")
				->static_condition("ht.hist_historic = 0")
				->static_condition("ht.deleted = 0")
				->static_condition("ht.user_id != 0")
				->static_condition("ht.orgu_title != '-empty-'")
				->static_condition("ht.row_id > ".MIN_ROW)
				->action($this->ctrl->getLinkTarget($this, "view"))
				->compile()
				;

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
		$tpl = '<tr class="{CSS_ROW}"><td></td>'."\n".'<td>{VAL_TITLE}</td>';
		foreach($this->meta_categories as $meta_category => $categories) {
			$tpl .= "\n".'<td align = "right">{VAL_'.strtoupper($meta_category).'_D}</td>';
			$tpl .= "\n".'<td align = "right">{VAL_'.strtoupper($meta_category).'_H}</td>';
			$i++;
		}
		$tpl .= "\n</tr>";
		fwrite($str,$tpl);
		fclose($str);
	}
}
?>