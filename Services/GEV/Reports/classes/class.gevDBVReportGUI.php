<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* Report "DBV Report"
* for Generali
*
* @author	Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
* @version	$Id$
*
*
*/

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);



require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
require_once("Modules/OrgUnit/classes/class.ilObjOrgUnit.php");
require_once("Services/GEV/Utils/classes/class.gevObjectUtils.php");
require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");


class gevDBVReportGUI extends catBasicReportGUI{
	protected $summed_data = array();
	protected static $to_sum = array("sum_credit_points" => "credit_points","sum_max_credit_points" => "max_credit_points");
	public function __construct() {
		
		parent::__construct();
		$target_user = $_POST["target_user_id"]
					   ? $_POST["target_user_id"]
					   : ( $_GET["target_user_id"]
					     ? $_GET["target_user_id"]
					     : $this->user_utils->getId()
					     );
		
		$this->checkPermissionOnTarget($target_user);
		
		foreach (self::$to_sum as $key => $value) {
			$this->summed_data[$key] = 0;
		}

		$this->title = catTitleGUI::create()
						->title("gev_rep_dbv_report_title")
						->subTitle("gev_rep_dbv_report_desc")
						->image("GEV_img/ico-head-edubio.png");

		$this->table = catReportTable::create()
						->column("lastname", "lastname")
						->column("firstname", "firstname")
						->column("odbd", "gev_od_bd")
						->column("job_number", "gev_job_number")
						->column("title", "title")
						->column("dbv_hot_topic", "gev_dbv_hot_topic")
						->column("type", "type")
						->column("date", "date")
						->column("credit_points", "gev_credit_points")
						->column("max_credit_points", "gev_credit_points_forecast")
						->template("tpl.gev_dbv_report_row.html", "Services/GEV/Reports");

		$this->table_sums = catReportTable::create()
						->column("sum_credit_points", "gev_overall_points")
						->column("sum_max_credit_points", "gev_overall_credit_points_forecast")
						->template("tpl.gev_dbv_report_sum_row.html", "Services/GEV/Reports");

	$this->order = catReportOrder::create($this->table)
						->defaultOrder("lastname", "ASC");
		
		//internal ordering:
		$this->internal_sorting_numeric = array(
			'lastname'
		);
		$this->internal_sorting_fields = array_merge(
			$this->internal_sorting_numeric,
			array(
		 	  'odbd'
			));

		$this->query = catReportQuery::create()
						->distinct()
						->select("hu.lastname")
						->select("hu.firstname")
						->select("hu.org_unit_above1")
						->select("hu.org_unit_above2")
						->select("hu.job_number")
						->select("hc.title")
						->select("hc.crs_id")
						->select("hc.dbv_hot_topic")
						->select("hc.type")
						->select("hc.begin_date")
						->select("hc.end_date")
						->select_raw(
							"IF(hucs.participation_status != 'nicht gesetzt', hucs.credit_points, 0) credit_points")
						->select_raw(
							"IF(hucs.participation_status != 'nicht gesetzt', hucs.credit_points,
								hc.max_credit_points) max_credit_points")
						->from("org_unit_personal oup")
						->join("hist_userorgu huo_in")
							->on("oup.orgunit_id = huo_in.orgu_id AND huo_in.`action` = 1 AND rol_title = ".$this->db->quote("Mitarbeiter","text"))
						->left_join("hist_userorgu huo_out")
							->on(" huo_out.`action` = -1"
								." AND huo_in.usr_id = huo_out.usr_id AND huo_in.orgu_id = huo_out.orgu_id"
								." AND huo_in.rol_id = huo_out.rol_id AND huo_in.hist_version < huo_out.hist_version")
						->left_join("hist_userorgu huo_out_aux")
							->on(" huo_out_aux.`action` = -1"
								." AND huo_in.usr_id = huo_out_aux.usr_id AND huo_in.orgu_id = huo_out_aux.orgu_id"
								." AND huo_in.rol_id = huo_out_aux.rol_id AND huo_in.hist_version < huo_out_aux.hist_version"
								." AND huo_out.hist_version > huo_out_aux.hist_version")
						->join("hist_user hu")
							->on("huo_in.usr_id = hu.user_id")						
						->join("hist_usercoursestatus hucs")
							->on("hu.user_id = hucs.usr_id")
						->join("hist_course hc")
							->on("hucs.crs_id = hc.crs_id")
						->compile();

		$this->filter = catFilter::create()
						->static_condition("oup.usr_id = ".$this->db->quote($target_user, "integer"))
						->static_condition(
							$this->db->in(
								"hucs.participation_status", array("fehlt entschuldigt", "fehlt ohne Absage"), true, "text"))
						->static_condition("hc.begin_date < ".$this->db->quote("2016-01-01","date"))
						->static_condition("hc.end_date >= ".$this->db->quote("2015-01-01","date"))
						->static_condition("(huo_out.created_ts IS NULL "
											." OR huo_out.created_ts > UNIX_TIMESTAMP(".$this->db->quote("2016-01-01","date").")"
											.") AND huo_in.created_ts < UNIX_TIMESTAMP(".$this->db->quote("2016-01-01","date").")")
						->static_condition("hu.hist_historic = 0")
						->static_condition("hucs.hist_historic = 0")
						->static_condition("hucs.booking_status != ".$this->gIldb->quote('-empty-', 'text')))
						->static_condition("hc.hist_historic = 0")
						->static_condition("huo_out_aux.hist_version IS NULL")
						->static_condition($this->db->in("hc.dbv_hot_topic", gevSettings::$dbv_hot_topics, false, "text"))
						//->static_condition("hc.dbv_hot_topic IS NOT NULL")
						//->static_condition("hc.dbv_hot_topic != '-empty-'")
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile();
		$this->relevant_parameters = array(
			"target_user_id" => $this->target_user_id
			,$this->filter->getGETName() => $this->filter->encodeSearchParamsForGET()
			); 
	}

	protected function _process_xls_date($val) {
		$val = str_replace('<nobr>', '', $val);
		$val = str_replace('</nobr>', '', $val);
		return $val;
	}

	protected function checkPermissionOnTarget($a_target_user_id) {
		if (   true//gevUserUtils::getInstance($a_target_user_id)->hasRoleIn(array("DBV-Fin-UVG"))
			&& (    $this->user_utils->isAdmin() 
				 || $a_target_user_id == $this->user_utils->getId()
			     || $this->user_utils->isSuperiorOf($a_target_user_id))) {
			return;
		}
		throw new Exception("No permission to view report for user $a_target_user_id");
	}
	
	protected function transformResultRow($rec) {
		$rec['odbd'] = $rec['org_unit_above1'];

		if( $rec["begin_date"] && $rec["end_date"] 
			&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )) {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
			//$date = ilDatePresentation::formatPeriod($start,$end);
		} else {
			$date = '-';
		}
		$rec['date'] = $date;
		foreach (self::$to_sum as $key => $value) {
			$this->summed_data[$key] += is_numeric($rec[$value]) ? $rec[$value] : 0;
		}
		return $this->replaceEmpty($rec);
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

		$cnt = 1;
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);

		if(count($this->summed_data) == 0) {
			foreach(array_keys($this->table_sums->columns) as $field) {
				$this->summed_data[$field] = 0;
			}
		}

		$table->setData(array($this->summed_data));
		return $table->getHtml();
	}

}