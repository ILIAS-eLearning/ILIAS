<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once 'Services/GEV/Utils/classes/class.gevSettings.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportDBV extends ilObjReportBase {
	
	protected $gUser;
	protected $relevant_parameters = array();

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
		global $ilUser;
		$this->gUser = $ilUser;
	}

	public function initType() {
		 $this->setType("xrdv");
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_rdbv')
				->addSetting($this->s_f
								->settingInt('year', $this->plugin->txt('report_year'))
								->setDefaultValue(2016)
									);
	}

	public function prepareRelevantParameters() {
		$this->target_user =
					$_POST["target_user_id"]
					   ? $_POST["target_user_id"]
					   : ( $_GET["target_user_id"]
					     ? $_GET["target_user_id"]
					     : $this->user_utils->getId()
					     );
		if($_GET["target_user_id"] || $_POST["target_user_id"]) {
			$this->addRelevantParameter("target_user_id", $this->target_user);
		}
	}

	protected function buildQuery($query) {
		$query	->select("hu.lastname")
				->select("hu.firstname")
				->select("huo_in.org_unit_above1")
				->select("huo_in.org_unit_above2")
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
					->on("oup.orgunit_id = huo_in.orgu_id AND huo_in.`action` = 1 AND rol_title = ".$this->gIldb->quote("Mitarbeiter","text"))
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
				->group_by("hu.user_id")
				->group_by("hc.crs_id")
				->compile();
		return $query;
	}

	protected function buildFilter($filter) {
		$year = (int)$this->settings['year'];
		$end_of_year_ts = strtotime(($year+1)."-01-01");
		$filter	->static_condition("oup.usr_id = ".$this->gIldb->quote($this->target_user, "integer"))
				->static_condition(
					$this->gIldb->in(	"hucs.participation_status", array("fehlt entschuldigt", "fehlt ohne Absage"), true, "text"))
				->static_condition("hc.begin_date < ".$this->gIldb->quote(($year+1)."-01-01","date"))
				->static_condition("hc.end_date >= ".$this->gIldb->quote($year."-01-01","date"))
				->static_condition("(huo_out.created_ts IS NULL "
									." OR huo_out.created_ts > ".$this->gIldb->quote($end_of_year_ts,"integer")
									.") AND huo_in.created_ts < ".$this->gIldb->quote($end_of_year_ts,"integer"))
				->static_condition("hu.hist_historic = 0")
				->static_condition("hucs.hist_historic = 0")
				->static_condition("hucs.booking_status != ".$this->gIldb->quote('-empty-', 'text'))
				->static_condition("hc.hist_historic = 0")
				->static_condition("huo_out_aux.hist_version IS NULL")
				->static_condition($this->gIldb->in("hc.dbv_hot_topic", gevSettings::$dbv_hot_topics, false, "text"))
				->action($this->filter_action)
				->compile();
		return $filter;
	}

	protected function buildTable($table) {
		$this->table_sums = catReportTable::create()
				->column("sum_credit_points", $this->plugin->txt("sum_credit_points"), true, "", false, false)
				->column("sum_credit_points_forecast", $this->plugin->txt("sum_credit_points_forecast"), true, "", false, false)
				->template("tpl.gev_dbv_report_sum_row.html", $this->plugin->getDirectory());

		$table	->column("lastname", $this->plugin->txt("lastname"), true)
				->column("firstname", $this->plugin->txt("firstname"), true)
				->column("odbd", $this->plugin->txt("od_bd"), true, "", false, false)
				->column("job_number", $this->plugin->txt("job_number"), true)
				->column("title", $this->plugin->txt("title"), true)
				->column("dbv_hot_topic", $this->plugin->txt("dbv_hot_topic"), true)
				->column("type", $this->plugin->txt("type"), true)
				->column("date", $this->plugin->txt("date"), true)
				->column("credit_points", $this->plugin->txt("credit_points"), true)
				->column("max_credit_points",$this->plugin->txt( "credit_points_forecast"), true);
		return parent::buildTable($table);
	}

	protected function buildOrder($order) {
		$order->mapping("date","hc.begin_date");
		return $order;
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_dbv_report_row.html";
	}

	protected function fetchData(callable $callback) {
		$data = parent::fetchData($callback);
		$this->summed_data = $this->sumData($data);
		return $data;
	}

	protected function sumData($data) {
		var_dump($data);
		$to_sum = array("sum_credit_points" => "credit_points","sum_credit_points_forecast" => "max_credit_points");
		$summed_data = array_fill_keys(array_keys($to_sum), 0);
		foreach ($data as $row) {
			foreach($to_sum as $sum_key => $data_key) {
				$summed_data[$sum_key] += is_numeric($row[$data_key]) ? $row[$data_key] : 0;
			}
		}
		var_dump($summed_data);
		return $summed_data;
	}

	public function renderSumTable($gui) {
		$table = new catTableGUI($gui, "showContent");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate(
			$this->table_sums->row_template_filename, 
			$this->table_sums->row_template_module
		);
		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table_sums->columns as $col) {
			$table->addColumn( $col[1], $col[5] ? $col[0] : "", $col[3]);
		}		
		$table->setLimit(1);
		$table->setMaxCount(1);
		$table->setData(array($this->summed_data));
		return $table->getHtml();
	}
}