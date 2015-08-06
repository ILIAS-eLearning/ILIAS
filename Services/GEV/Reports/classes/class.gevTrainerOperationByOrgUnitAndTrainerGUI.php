<?php
require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/GEV/Reports/classes/class.catFilter.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");

const MIN_ROW = "3991";
const shift = "&nbsp;&nbsp;&nbsp;";

class gevTrainerOperationByOrgUnitAndTrainerGUI extends catBasicReportGUI{
	protected $meta_categories = array("Training"=>array("Training", "Veranstaltung / Tagung (Zentral)"));
	protected $tree;
	protected $orgu_utils;
	protected $report_data;
	protected $top_nodes = array(2271,2275);

	public function __construct() {
		global $tree;
		$this->tree = $tree;
		parent::__construct();

		$this->createTemplateFile();

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

		$this->title = catTitleGUI::create()
						->title("gev_report_trainer_operation_by_orgu_trainer")
						->subTitle("gev_report_trainer_operation_by_orgu_trainer_desc")
						->image("GEV_img/ico-head-edubio.png");

		$this->table = catReportTable::create();
		$this->table->column("title", "title");

		foreach($this->meta_categories as $meta_category => $categories) {
			$this->table->column($meta_category."_d", $meta_category, true);
			$this->table->column($meta_category."_h", "Std.", true);
		}
		$this->table->template("tpl.gev_trainer_operation_by_orgu_and_trainer_row.html", 
								"Services/GEV/Reports");



	}

	protected function fetchData() {

		$sql = 	"SELECT ht.orgu_title,  CONCAT(hu.lastname,', ',hu.firstname) as title,";
		foreach($this->meta_categories as $meta_category => $categories) {
			$sql .= $this->daysPerTEPMetaCategory($categories, $meta_category."_d").",\n\t";
			$sql .= $this->hoursPerTEPMetaCategory($categories, $meta_category."_h").",\n\t";
		}
		$sql .= "	hu.user_id "
				."	FROM hist_tep ht\n"
				."	JOIN hist_user hu\n"
				."		ON ht.user_id = hu.user_id\n"
				."	JOIN hist_tep_individ_days htid\n"
				."		ON ht.individual_days = htid.id\n"
				."	".$this->queryWhere()."\n"
				."	GROUP BY ht.orgu_title, ht.user_id";

		$this->pre_data = array();
		$res = $this->db->query($sql);

		while ($rec = $this->db->fetchAssoc($res)) {
			$this->pre_data[$rec["orgu_title"]][] = $rec;
		}

		$top_sup_orgus = $this->getTopSuperiorNodesOfUser($this->top_nodes);
		$tree_data = array();

		foreach ($top_sup_orgus as $orgu) {
			$tree_data[] = $this->buildReportTree($orgu);
		}

		foreach($tree_data as $branch) {
			$this->fillData($branch);
		}

		return $this->report_data;
	}

	protected function daysPerTEPMetaCategory($categories, $name) {
		$sql = "SUM(IF(".$this->db->in('category',$categories,false,"text")." ,1,0)) as ".$name;
		return $sql;
	}

	protected function hoursPerTEPMetaCategory($categories, $name) {
		$sql = 
		"SUM(IF(".$this->db->in('category',$categories,false,"text")." ,
			LEAST(CEIL( TIME_TO_SEC( TIMEDIFF( htid.end_time, htid.start_time ) )* htid.weight /720000) *2,8),0)) as ".$name;
		return $sql;
	}

	protected function arrayAddMetaCategories(array $factor1, array $factor2) {
		foreach($this->data_fields as $data_field) {
			$factor1[$meta_category] += $factor2[$meta_category];
		}
	}

	protected function getTopSuperiorNodesOfUser($below_orgus = null) {
		$all_sup_orgus = $this->user_utils->getOrgUnitsWhereUserIsSuperior();
		$all_sup_orgus_ref = array();

		foreach ($all_sup_orgus as $orgu) {
			$all_sup_orgus_ref[] = $orgu["ref_id"];
		}
		$all_sup_orgus_ref = array_unique($all_sup_orgus_ref);

		if($below_orgus !== null) {
			$childs = gevOrgUnitUtils::getAllChildren($below_orgus);
			foreach ($childs as &$orgu) {
				$orgu = $orgu["ref_id"];
			}
			$below_orgus = array_unique(array_merge($childs,$below_orgus));
			$all_sup_orgus_ref = array_intersect($all_sup_orgus_ref,$below_orgus);
		}
 
		$sql = 	"SELECT obj_id, t1.child as ref_id, t2.child FROM tree t1 "
				."	JOIN object_reference ore "
				."		ON ore.ref_id = t1.child "
				."	LEFT JOIN tree t2 "
				." 		ON t1.lft > t2.lft AND t1.rgt < t2.rgt "
				."		AND ".$this->db->in("t2.child",$all_sup_orgus_ref,false,"text")
				." WHERE ".$this->db->in("t1.child",$all_sup_orgus_ref,false,"text")
				."		AND ore.deleted IS NULL "
				." HAVING t2.child IS NULL";

		$top_sup_orgus = array();
		$res = $this->db->query($sql);
		
		while($rec = $this->db->fetchAssoc($res)) {
			$top_sup_orgus[] = $rec["ref_id"];
		}

		return $top_sup_orgus;
	}

	protected function buildReportTree($ref_id,$offset = "") {
		$title =  gevOrgUnitUtils::getTitleByRefId($ref_id);
		$children = $this->tree->getChildsByType($ref_id,'orgu');
		$return = array("title"=>$title,"trainers"=>$this->pre_data[$title],"children"=>array());

		foreach ($return["trainers"] as &$trainers) {
			$trainers["title"] = $offset.shift.$trainers["title"];
		}


		foreach($children as $child) {
			$return["children"][] = $this->buildReportTree($child["ref_id"],$offset.shift);
		}

		$return["sum"] = $this->sumMetaCategories($return["trainers"]);

		foreach ($return["children"] as $child) {
			$return["sum"] = $this->sumMetaCategories(array($return["sum"],$child["sum"]));
		}
		$return["sum"]["title"] = $offset."<b>".$return["title"]."</b>";
		return $return;
	}

	protected function  fillData($data_level) {

		$this->report_data[] = $data_level["sum"];

		foreach ($data_level["trainers"] as $values) {
			$this->report_data[] = $values; 
		}
		foreach ($data_level["children"] as $child) {
			$this->fillData($child);
		}
	}

	protected function sumMetaCategories ($arrays) {
		$return = array();
		foreach ($this->meta_categories as $meta_category => $categories) {
			$auxh = 0;
			$auxd = 0;
			foreach ($arrays as $array) {
				$auxh += $array[strtolower($meta_category)."_h"];
				$auxd += $array[strtolower($meta_category)."_d"];
			}
			$return[strtolower($meta_category)."_h"] = $auxh;
			$return[strtolower($meta_category)."_d"] = $auxd; 
		}
		return $return;
	}

	protected function createTemplateFile() {
		$str = fopen("Services/GEV/Reports/templates/default/"
			."tpl.gev_trainer_operation_by_orgu_and_trainer_row.html","w"); 
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