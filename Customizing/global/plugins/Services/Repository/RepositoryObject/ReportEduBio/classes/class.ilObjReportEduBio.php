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

	public function prepareRelevantParameters() {
		$self_id = $this->user_utils->getId();
		$target_user_id = $_POST["target_user_id"]
					  ? $_POST["target_user_id"]
					  : ( $_GET["target_user_id"]
					  	? $_GET["target_user_id"]
					  	: $self_id
					  	);
		if ($target_user_id != $self_id) {
			if ( !in_array($target_user_id, $this->user_utils->getEmployeesWhereUserCanViewEduBios())) {
				$target_user_id = $self_id;
			}
		}
		$this->target_user_id = $target_user_id;
		$this->addRelevantParameter("target_user_id", $this->target_user_id);
	}

	protected function buildTable($table) {
		$table	->column("custom_id", $this->plugin->txt("training_id"), true)
				->column("title", $this->plugin->txt("title"), true)
				->column("type", $this->plugin->txt("learning_type"), true)
				->column("date", $this->plugin->txt("date"), true, "112px", true)
				->column("venue", $this->plugin->txt("location"), true)
				->column("provider", $this->plugin->txt("provider"), true)
				->column("tutor", $this->plugin->txt("crs_tutor"), true)
				->column("credit_points", $this->plugin->txt("points"), true)
				->column("fee", $this->plugin->txt("fee"), true)
				->column("status", $this->plugin->txt("status"), true)
				->column("wbd", $this->plugin->txt("wbd_relevant"), true)
				->column("action", '<img src="'.ilUtil::getImagePath("gev_action.png").'" />', true, "", true);
		return parent::buildTable($table);
	}

	protected function getRowTemplateTitle() {
		return "tpl.gev_edu_bio_row.html";
	}

	protected function buildOrder($order) {
		$order	->mapping('date',array('usrcrs.begin_date'))
				->mapping('status',array("usrcrs.participation_status"))
				->mapping('wbd',array("usrcrs.okz"));
				return $order;
	}

	protected function buildFilter($filter) {
		$filter	->dateperiod( "period"
							, $this->plugin->txt("period")
							, $this->plugin->txt("until")
							, "usrcrs.begin_date"
							, "usrcrs.end_date"
							, date("Y")."-01-01"
							, date("Y")."-12-31"
							)
				->static_condition("usr.user_id = ".$this->gIldb->quote($this->target_user_id, "integer"))
				->static_condition("usrcrs.hist_historic = 0")
				->static_condition($this->gIldb
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
		parent::prepareReport();		

		$this->target_user_utils = gevUserUtils::getInstance($this->target_user_id);
		$this->wbd_data = $this->getWBDData();
		$this->academy_data = $this->getAcademyData();
	}

	protected function getWBDData() {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$wbd_data = array();
		$wbd = gevWBD::getInstance($this->target_user_id);

		$cy_start = $wbd->getStartOfCurrentCertificationYear();
		$cy_end = $wbd->getStartOfCurrentCertificationYear();
		$cy_end->increment(ilDateTime::YEAR, 1);

		$cp_start = $wbd->getStartOfCurrentCertificationPeriod();
		$cp_end = $wbd->getStartOfCurrentCertificationPeriod();
		$cp_end->increment(ilDateTime::YEAR, 5);

		$wbd_data["cert_period"] = ilDatePresentation::formatPeriod($cp_start, $cp_end);
		$wbd_data["cert_year"] = ilDatePresentation::formatPeriod($cy_start, $cy_end);

		$period = $this->filter->get("period");

		$query = $this->wbdQuery($period["start"], $period["end"]);
		$wbd_data["sum"] =  $this->gIldb->fetchAssoc($this->gIldb->query($query))["sum"];

		$query = $this->wbdQuery($cy_start, $cy_end);
		$wbd_data["sum_cur_year"] =  $this->gIldb->fetchAssoc($this->gIldb->query($query))["sum"];

		$query = $this->wbdQuery($cp_start, $cp_end);
		$wbd_data["sum_cert_period"] = $this->gIldb->fetchAssoc($this->gIldb->query($query))["sum"];

		$query =  $this->curYearPredQuery($cy_start, $cy_end);
		$wbd_data["sum_cur_year_pred"] = $this->gIldb->fetchAssoc($this->gIldb->query($query))["sum"];
		return $wbd_data;
	}

	protected function getAcademyData() {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$academy_data = array();

		$period = $this->filter->get("period");
		$start_date = $period["start"]->get(IL_CAL_FKT_GETDATE);
		$fy_start = new ilDate($start_date["year"]."-01-01", IL_CAL_DATE);
		$fy_end = new ilDate($start_date["year"]."-12-31", IL_CAL_DATE);
		$fy_end->increment(ilDateTime::YEAR, 4);

		$academy_data["five_year"] = ilDatePresentation::formatPeriod($fy_start, $fy_end);

		$query = $this->academyQuery($period["start"], $period["end"]);
		$academy_data["sum"] = $this->gIldb->fetchAssoc($this->gIldb->query($query))["sum"];

		$query = $this->academyQuery($fy_start, $fy_end);
		$academy_data["sum_five_year"] = $this->gIldb->fetchAssoc($this->gIldb->query($query))["sum"];
		return $academy_data;
	}

	protected function curYearPredQuery(ilDate $start = null, ilDate $end = null) {
		return "SELECT SUM(usrcrs.credit_points) sum "
				." FROM hist_usercoursestatus usrcrs"
				." JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
				." JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"
				.$this->wbdQueryWhere($start, $end)
				." AND usrcrs.booking_status = 'gebucht'"
				." AND ".$this->gIldb->in("usrcrs.participation_status", array("teilgenommen", "nicht gesetzt"), false, "text")
				." AND ".$this->gIldb->in("usrcrs.okz", array("OKZ1", "OKZ2", "OKZ3"), false, "text");
	}

	protected function wbdQueryWhere(ilDate $start = null, ilDate $end = null, $no_historic = true) {
		return $start === null ?
				$this->queryWhere() :
				" WHERE usrcrs.usr_id = ".$this->gIldb->quote($this->target_user_id, "integer")
				.($no_historic ? "   AND usrcrs.hist_historic = 0 " : "")
				."   AND ( usrcrs.end_date >= ".$this->gIldb->quote($start->get(IL_CAL_DATE), "date")
				."        OR usrcrs.end_date = '0000-00-00')"
				."   AND usrcrs.begin_date <= ".$this->gIldb->quote($end->get(IL_CAL_DATE), "date")
				."   AND ".$this->gIldb->in("usrcrs.booking_status", array("gebucht", "kostenpflichtig storniert", "kostenfrei storniert"), false, "text");
	}

	protected function academyQuery(ilDate $start, ilDate $end) {
		return   "SELECT SUM(usrcrs.credit_points) sum "
				.$this->query->sqlFrom()
				.$this->queryWhere($start, $end)
				." AND usrcrs.participation_status = 'teilgenommen'"
				." AND usrcrs.credit_points > 0";
	}

	protected function wbdQuery(ilDate $start, ilDate $end) {
		return   "SELECT SUM(usrcrs.credit_points) sum "
				." FROM hist_usercoursestatus usrcrs"
				.$this->wbdQueryWhere($start, $end, false)
				." AND NOT usrcrs.wbd_booking_id IS NULL";
	}

	public function validateBill($bill_id) {
		$res = $this->gIldb->query( "SELECT crs_id"
								."  FROM hist_usercoursestatus "
								." WHERE usr_id = ".$this->gIldb->quote($this->target_user_id, "integer")
								."   AND bill_id = ".$this->gIldb->quote($bill_id, "text")
								."   AND hist_historic = 0");
		return $this->gIldb->numRows($res) == 1;
	}

	public function validateCertificate($cert_id) {
		$res = $this->gIldb->query( "SELECT COUNT(*) cnt"
						."  FROM hist_usercoursestatus "
						." WHERE usr_id = ".$this->gIldb->quote($this->target_user_id, "integer")
						."   AND certificate = ".$this->gILdb->quote($cert_id, "integer"));
		if($this->gIldb->fetchAssoc($res)['cnt'] == 0) {
			return false;
		}
		return true;
	}

	public function certificateData($cert_id) {
		$res = $this->gIldb->query( "SELECT hc.certfile, hs.crs_id "
								."  FROM hist_certfile hc"
								." JOIN hist_usercoursestatus hs ON hs.certificate = hc.row_id"
								." WHERE hc.row_id = ".$this->gIldb->quote($cert_id, "integer"));
		return $this->gIldb->fetchAssoc($res);
	}

	/**
	 *	Deivers the link to a users edubio.
	 *	We aussume for now that there is exactly one edu bio in the whole academy.
	 *
	 *	@param	int|null	$usr_id	if null, edubio points to calling user.
	 *	@return string	$return	link to a users edubio
	 */
	public static function getEduBioLinkFor($usr_id = null) {
		global $ilCtrl;
		$ref_id = current(ilObject::_getAllReferences(current(ilObject::_getObjectsDataForType('xreb', true))["id"]));
		$ilCtrl->setParameterByClass("ilObjReportEduBioGUI", "target_user_id", $usr_id);
		$ilCtrl->setParameterByClass("ilObjReportEduBioGUI", "ref_id", $ref_id);
		$return = $ilCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI", "ilObjReportEduBioGUI"), '');
		$ilCtrl->clearParametersByClass("ilObjReportEduBioGUI");
		return $return;
	}
}