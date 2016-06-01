<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBase.php';
require_once 'Services/GEV/Utils/classes/class.gevUserUtils.php';
require_once 'Services/GEV/Utils/classes/class.gevSettings.php';

ini_set("memory_limit","2048M"); 
ini_set('max_execution_time', 0);
set_time_limit(0);

class ilObjReportEduBio extends ilObjReportBase {
	protected $relevant_parameters = array();

	public function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}

	public function initType() {
		 $this->setType("xreb");
	}

	protected function createLocalReportSettings() {
		$this->local_report_settings =
			$this->s_f->reportSettings('rep_robj_reb');
	}

	protected function buildTable($table) {
		$table	->column("custom_id", "gev_training_id")
				->column("title", "title")
				->column("type", "gev_learning_type")
				->column("date", "date", false, "112px")
				->column("venue", "gev_location")
				->column("provider", "gev_provider")
				->column("tutor", "il_crs_tutor")
				->column("credit_points", "gev_points")
				->column("fee", "gev_costs")
				->column("status", "status")
				->column("wbd", "gev_wbd_relevant")
				->column("action", $this->action_img, true, "", true);
		return parent::buildTable();
	}

	protected function buildOrder($order) {
		$order	->mapping('date',array('usrcrs.begin_date'))
				->mapping('status',array("usrcrs.participation_status"))
				->mapping('wbd',array("usrcrs.okz"));
				return $order;
	}

	protected function buildFilter($filter) {
		$filter	->dateperiod( "period"
							, $this->lng->txt("gev_period")
							, $this->lng->txt("gev_until")
							, "usrcrs.begin_date"
							, "usrcrs.end_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							)
				->static_condition("usr.user_id = ".$this->db->quote($this->target_user_id, "integer"))
				->static_condition("usrcrs.hist_historic = 0")
				->static_condition($this->db
										->in(	"usrcrs.booking_status"
												, array( "gebucht", "kostenpflichtig storniert")
												, false, "text")
								)
				->static_condition("(crs.crs_id < 0 OR oref.deleted IS NULL)")
				->action($this->filter_action)
				->compile();
		return $filter;
	}

	protected function buildQuery($query) {
		$query 	->select("crs.custom_id")
				->select("crs.title")
				->select("crs.type")
				->select("usrcrs.begin_date")
				->select("usrcrs.end_date")
				->select("crs.venue")
				->select("crs.provider")
				->select("crs.tutor")
				->select("usrcrs.credit_points")
				->select("crs.fee")
				->select("usrcrs.participation_status")
				->select("usrcrs.okz")
				->select("usrcrs.bill_id")
				->select("usrcrs.certificate")
				->select("usrcrs.booking_status")
				->select("oref.ref_id")
				->from("hist_usercoursestatus usrcrs")
				->join("hist_user usr")
					->on("usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0")
				->join("hist_course crs")
					->on("crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0")
				->left_join("object_reference oref")
					->on("crs.crs_id = oref.obj_id")
				->compile();
		return $query;
	}

	public function prepareReport() {
		$this->target_user_id = $_POST["target_user_id"]
					  ? $_POST["target_user_id"]
					  : ( $_GET["target_user_id"]
					  	? $_GET["target_user_id"]
					  	: $this->user->getId()
					  	);
		$this->addToRelevantParameters("target_user_id", $this->target_user_id);
		$this->target_user_utils = gevUserUtils::getInstance($this->target_user_id);
		
		parent::prepareReport();
		
		$this->getWBDData();
	}

	protected function getWBDData() {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$wbd = gevWBD::getInstance($this->target_user_id);

		$cy_start = $wbd->getStartOfCurrentCertificationYear();
		$cy_end = $wbd->getStartOfCurrentCertificationYear();
		$cy_end->increment(ilDateTime::YEAR, 1);

		$cp_start = $wbd->getStartOfCurrentCertificationPeriod();
		$cp_end = $wbd->getStartOfCurrentCertificationPeriod();
		$cp_end->increment(ilDateTime::YEAR, 5);

		$period = $this->filter->get("period");

		$query = $this->wbdQuery($period["start"], $period["end"]);
		$wbd_sum = $this->db->fetchAssoc($this->db->query($query))["sum"];

		$query = $this->wbdQuery($cy_start, $cy_end);
		$wbd_sum_cur_year = $this->db->fetchAssoc($this->db->query($query))["sum"];

		$query = $this->wbdQuery($cp_start, $cp_end);
		$wbd_sum_cert_period = $this->db->fetchAssoc($this->db->query($query))["sum"];

		$query =  $this->curYearPredQuery($cy_start, $cy_end);

		$wbd_sum_cur_year_pred = $this->db->fetchAssoc($this->db->query($query))["sum"];
	}

	protected function curYearPredQuery(ilDate $start = null, ilDate $end = null) {
		return "SELECT SUM(usrcrs.credit_points) sum "
				." FROM hist_usercoursestatus usrcrs"
				." JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
				." JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"
				.$this->curYearPredQueryWhere($start, $end)
				." AND usrcrs.booking_status = 'gebucht'"
				." AND ".$this->db->in("usrcrs.participation_status", array("teilgenommen", "nicht gesetzt"), false, "text")
				." AND ".$this->db->in("usrcrs.okz", array("OKZ1", "OKZ2", "OKZ3"), false, "text");
	}

	protected function curYearPredQueryWhere(ilDate $start = null, ilDate $end = null, $no_historic = true) {
		return $start === null ?
				$this->queryWhere() :
				" WHERE usrcrs.usr_id = ".$this->db->quote($this->target_user_id, "integer")
				.($no_historic ? "   AND usrcrs.hist_historic = 0 " : "")
				."   AND ( usrcrs.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
				."        OR usrcrs.end_date = '0000-00-00')"
				."   AND usrcrs.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
				."   AND ".$this->db->in("usrcrs.booking_status", array("gebucht", "kostenpflichtig storniert", "kostenfrei storniert"), false, "text");
	}

	protected function wbdQuery(ilDate $start, ilDate $end) {
		return   "SELECT SUM(usrcrs.credit_points) sum "
				." FROM hist_usercoursestatus usrcrs"
				.$this->queryWhere($start, $end, false)
				." AND NOT usrcrs.wbd_booking_id IS NULL";
	}
}