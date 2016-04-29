<?php

require_once("Services/GEV/Reports/classes/class.catBasicReportGUI.php");
require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
require_once("Services/GEV/WBD/classes/class.gevWBD.php");

class gevEduBiographyGUI extends catBasicReportGUI {
	public function __construct() {
		parent::__construct();

		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");

		$this->target_user_id = $_POST["target_user_id"]
							  ? $_POST["target_user_id"]
							  : ( $_GET["target_user_id"]
							  	? $_GET["target_user_id"]
							  	: $this->user->getId()
							  	);
		$this->target_user_utils = gevUserUtils::getInstance($this->target_user_id);

		if ($this->user->getId() == $this->target_user_id) {
			$this->title = catTitleGUI::create()
							->title("gev_my_edu_bio")
							->subTitle("gev_my_edu_bio_desc")
							->image("GEV_img/ico-head-edubio.png");
		}
		else {
			$this->title = catTitleGUI::create()
							->title(sprintf($this->lng->txt("gev_others_edu_bio"), $this->target_user_utils->getFullName()))
							->subTitle(sprintf($this->lng->txt("gev_others_edu_bio_desc"), $this->target_user_utils->getFullName()))
							->image("GEV_img/ico-head-edubio.png")
							->useLng(false);
		}

		$this->get_cert_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_cert.png").'" />';
		$this->get_bill_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_bill.png").'" />';
		$this->success_img  = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		$this->in_progress_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
		$this->failed_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';
		$this->action_img = '<img src="'.ilUtil::getImagePath("gev_action.png").'" />';

		$this->title->legend(catLegendGUI::create()
						->item($this->get_cert_img, "gev_get_certificate")
						->item($this->get_bill_img, "gev_get_bill")
						->item($this->success_img, "gev_passed")
						->item($this->in_progress_img, "gev_in_progress")
						->item($this->failed_img, "gev_failed")
						);

		$this->table = catReportTable::create()
						->column("custom_id", "gev_training_id")
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
						->column("action", $this->action_img, true, "", true)
						->template('tpl.gev_edu_bio_row.html', 'Services/GEV/Reports');

		$this->order = catReportOrder::create($this->table)
						->mapping('date',array('usrcrs.begin_date'))
						->mapping('status',array("usrcrs.participation_status"))
						->mapping('wbd',array("usrcrs.okz"));

		$this->query = catReportQuery::create()
						->select("crs.custom_id")
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

		$this->ctrl->setParameter($this, "target_user_id", $this->target_user_id);

		$this->filter = catFilter::create()
						->dateperiod( "period"
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
						->action($this->ctrl->getLinkTarget($this, "view"))
						->compile();
		$this->ctrl->setParameter($this, "target_user_id",null);

		$this->relevant_parameters = array(
			"target_user_id" => $this->target_user_id
			,$this->filter->getGETName() => $this->filter->encodeSearchParamsForGET()
			);
	}

	public function executeCommand() {
		$this->checkPermission();
		$cmd = $this->ctrl->getCmd();

		switch ($cmd) {
			case "getCertificate":
				return $this->getCertificate();
			case "getBill":
				return $this->getBill();
			default:
				return parent::executeCommand();
		}
	}

	protected function checkPermission() {
		if(    $this->user->getId() == $this->target_user_id
			|| in_array($this->target_user_id, $this->user_utils->getEmployeesWhereUserCanViewEduBios())
			|| $this->user_utils->isAdmin()) {
			return;
		}
		ilUtil::sendFailure($this->lng->txt("no_edu_bio_permission"), true);
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}

	public function renderView() {
		$spacer = new catHSpacerGUI();
		return	  $this->renderOverview()
				. $spacer->render()
				. $this->renderTable();
	}

	public function renderOverview() {
		$wbd = gevWBD::getInstance($this->target_user_id);
		$tpl = new ilTemplate("tpl.gev_edu_bio_overview.html", true, true, "Services/GEV/Reports");

		$this->renderAcademyPoints($tpl);

		if ($wbd->transferPointsFromWBD()) {
			$this->renderWBDPoints($tpl);
			$tpl->setVariable("WBDPOINTSVISIBIBLE", "visible");
		}
		else {
			$tpl->setVariable("WBDPOINTSVISIBIBLE", "invisible");
			if($wbd->transferPointsToWBD()) {
				$tpl->setVariable("WBDTRANSVISIBIBLE", "visible");
				$tpl->setCurrentBlock("wbd_transfer");
				$tpl->setVariable("TRANSFER_TITLE", $this->lng->txt("gev_wbd_transfer_on"));
				$tpl->parseCurrentBlock();
			}
			else if ($wbd->wbdRegistrationIsPending()){
				$tpl->setVariable("WBDPOINTSVISIBIBLE", "invisible");
				$tpl->setCurrentBlock("wbd_reg_pending");
				$tpl->setVariable("WBDREGPENDINGVISIBIBLE", "visible");
				$tpl->setVariable("WBD_REG_PENDING", $this->lng->txt("gev_wbd_reg_pending"));
				$tpl->parseCurrentBlock();
			}
			else {
				$tpl->setVariable("WBDTRANSVISIBIBLE", "visible");
			}
		}

		return $tpl->get();
	}

	protected function renderAcademyPoints($tpl) {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$tpl->setVariable("ACADEMY_SUM_TITLE", $this->lng->txt("gev_points_in_academy"));
		$tpl->setVariable("ACADEMY_SUM_FIVE_YEAR_TITLE", $this->lng->txt("gev_points_in_five_years"));

		$period = $this->filter->get("period");

		$start_date = $period["start"]->get(IL_CAL_FKT_GETDATE);
		$fy_start = new ilDate($start_date["year"]."-01-01", IL_CAL_DATE);
		$fy_end = new ilDate($start_date["year"]."-12-31", IL_CAL_DATE);
		$fy_end->increment(ilDateTime::YEAR, 4);

		$tpl->setVariable("ACADEMY_FIVE_YEAR", ilDatePresentation::formatPeriod($fy_start, $fy_end));

		$query = $this->academyQuery($period["start"], $period["end"]);
		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			$tpl->setVariable("ACADEMY_SUM", $rec["sum"] ? $rec["sum"] : 0);
		}

		$query = $this->academyQuery($fy_start, $fy_end);
		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			$tpl->setVariable("ACADEMY_SUM_FIVE_YEAR", $rec["sum"] ? $rec["sum"] : 0);
		}
	}

	protected function academyQuery(ilDate $start, ilDate $end) {
		return   "SELECT SUM(usrcrs.credit_points) sum "
				.$this->query->sqlFrom()
				.$this->queryWhere($start, $end)
				." AND usrcrs.participation_status = 'teilgenommen'"
				." AND usrcrs.credit_points > 0";
	}

	protected function renderWBDPoints($tpl) {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$wbd = gevWBD::getInstance($this->target_user_id);

		$tpl->setVariable("WBD_SUM_TITLE", $this->lng->txt("gev_points_in_wbd"));
		$tpl->setVariable("WBD_SUM_CERT_PERIOD_TITLE", $this->lng->txt("gev_points_in_wbd_cert_period"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_TITLE", $this->lng->txt("gev_points_in_wbd_cert_year"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_PRED_TITLE", $this->lng->txt("gev_points_at_end_of_cert_year"));

		$cy_start = $wbd->getStartOfCurrentCertificationYear();
		$cy_end = $wbd->getStartOfCurrentCertificationYear();
		$cy_end->increment(ilDateTime::YEAR, 1);

		$cp_start = $wbd->getStartOfCurrentCertificationPeriod();
		$cp_end = $wbd->getStartOfCurrentCertificationPeriod();
		$cp_end->increment(ilDateTime::YEAR, 5);

		$tpl->setVariable("WBD_CERT_PERIOD", ilDatePresentation::formatPeriod($cp_start, $cp_end));
		$tpl->setVariable("WBD_CERT_YEAR", ilDatePresentation::formatPeriod($cy_start, $cy_end));

		$period = $this->filter->get("period");

		$query = $this->wbdQuery($period["start"], $period["end"]);
		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			$tpl->setVariable("WBD_SUM", $rec["sum"] ? $rec["sum"] : 0);
		}

		$query = $this->wbdQuery($cy_start, $cy_end);
		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			$tpl->setVariable("WBD_SUM_CUR_YEAR", $rec["sum"] ? $rec["sum"] : 0);
		}

		$query = $this->wbdQuery($cp_start, $cp_end);
		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			$tpl->setVariable("WBD_SUM_CERT_PERIOD", $rec["sum"] ? $rec["sum"] : 0);
		}

		$query = "SELECT SUM(usrcrs.credit_points) sum "
				." FROM hist_usercoursestatus usrcrs"
				." JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
				." JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"
				.$this->queryWhere($cy_start, $cy_end)
				." AND usrcrs.booking_status = 'gebucht'"
				." AND ".$this->db->in("usrcrs.participation_status", array("teilgenommen", "nicht gesetzt"), false, "text")
				." AND ".$this->db->in("usrcrs.okz", array("OKZ1", "OKZ2", "OKZ3"), false, "text");

		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			$tpl->setVariable("WBD_SUM_CUR_YEAR_PRED", $rec["sum"] ? $rec["sum"] : 0);
		}
	}

	protected function wbdQuery(ilDate $start, ilDate $end) {
		return   "SELECT SUM(usrcrs.credit_points) sum "
				." FROM hist_usercoursestatus usrcrs"
				.$this->queryWhere($start, $end, false)
				." AND NOT usrcrs.wbd_booking_id IS NULL";
	}

	protected function transformResultRow($rec) {
		$no_entry = $this->lng->txt("gev_table_no_entry");

		$rec["fee"] = (($rec["bill_id"] != -1 || $this->target_user_utils->paysFees())&& $rec["fee"] != -1)
					? $rec["fee"] = gevCourseUtils::formatFee($rec["fee"])." &euro;"
					: $rec["fee"] == "-empty-";

		if ($rec["participation_status"] == "teilgenommen") {
			$rec["status"] = $this->success_img;
		}
		else if (in_array($rec["participation_status"], array("fehlt entschuldigt", "fehlt ohne Absage"))
			 ||  in_array($rec["booking_status"], array("kostenpflichtig storniert", "kostenfrei storniert"))
			) {
			$rec["status"] = $this->failed_img;
		}
		else {
			$rec["status"] = $this->in_progress_img;
		}

		if ($rec["begin_date"] == "0000-00-00" && $rec["end_date"] == "0000-00-00") {
			$rec["date"] = $no_entry;
		}
		else if ($rec["end_date"] == "0000-00-00") {
			$dt = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$rec["date"] = $this->lng->txt("gev_from")." ".ilDatePresentation::formatDate($dt);
		}
		else if ($rec["begin_date"] == "0000-00-00") {
			$dt = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = $this->lng->txt("gev_until")." ".ilDatePresentation::formatDate($dt);
		}
		else {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = ilDatePresentation::formatDate($start)." - <br/>".ilDatePresentation::formatDate($end);
		}

		$rec["wbd"] = in_array($rec["okz"], array("OKZ1", "OKZ2", "OKZ3"))
					? $this->lng->txt("yes")
					: $this->lng->txt("no");

		$rec["action"] = "";
		if ($rec["bill_id"] != -1 && $rec["bill_id"] != "-empty-") {
			$this->ctrl->setParameter($this, "bill_id", $rec["bill_id"]);
			$this->ctrl->setParameter($this, "target_user_id", $this->target_user_id);
			$rec["action"] = "<a href='".$this->ctrl->getLinkTarget($this, "getBill")."'>"
						   . $this->get_bill_img."</a>";
			$this->ctrl->setParameter($this,  "bill_id", null);
			$this->ctrl->setParameter($this,  "target_user_id", null);
		}
		if ($rec["certificate"] != -1 && $rec["certificate"] != 0) {
			$this->ctrl->setParameter($this, "cert_id", $rec["certificate"]);
			$this->ctrl->setParameter($this, "target_user_id", $this->target_user_id);
			$rec["action"] .= "<a href='".$this->ctrl->getLinkTarget($this, "getCertificate")."'>"
						   . $this->get_cert_img."</a>";
			$this->ctrl->setParameter($this, "cert_id", null);
			$this->ctrl->setParameter($this, "target_user_id", null);
		}
		if ($rec["ref_id"] !== null) {
			$rec["link_open"] = "<a href='goto.php?target=crs_".$rec["ref_id"]."'>";
			$rec["link_close"] = "</a>";
		}
		else {
			$rec["link_open"] = "";
			$rec["link_close"] = "";
		}

		foreach ($rec as $key => $value) {
			if ($value == '-empty-' || $value == -1) {
				$rec[$key] = $no_entry;
				continue;
			}
		}
		
		return $rec;
	}

	protected function getBill() {
		// check weather this bill really belongs to an edu bio record of the current user.
		$bill_id = $_GET["bill_id"];
		$res = $this->db->query( "SELECT crs_id"
								."  FROM hist_usercoursestatus "
								." WHERE usr_id = ".$this->db->quote($this->target_user_id, "integer")
								."   AND bill_id = ".$this->db->quote($bill_id, "text")
								."   AND hist_historic = 0"
								);
		
		if ($this->db->numRows($res) != 1) {
			return $this->render();
		}
		$rec = $this->db->fetchAssoc($res);

		require_once("Services/GEV/Utils/classes/class.gevBillStorage.php");
		require_once 'Services/Utilities/classes/class.ilUtil.php';

		$fname = "Rechnung_".$bill_id.".pdf";
		$bill_storage = gevBillStorage::getInstance();
		$path = $bill_storage->getPathByBillNumber($bill_id);
		ilUtil::deliverFile($path, $fname, 'application/pdf', false, false, true);
	}

	protected function getCertificate() {
		// check weather this cert really belongs to an edu bio of the current user
		$cert_id = $_GET["cert_id"];
		$res = $this->db->query( "SELECT COUNT(*) cnt"
								."  FROM hist_usercoursestatus "
								." WHERE usr_id = ".$this->db->quote($this->target_user_id, "integer")
								."   AND certificate = ".$this->db->quote($cert_id, "integer"));
		if ($rec = $this->db->fetchAssoc($res)) {
			if ($rec["cnt"] == 0) {
				return $this->render();
			}
		}

		// query certificate data
		$res = $this->db->query( "SELECT hc.certfile, hs.crs_id "
								."  FROM hist_certfile hc"
								." JOIN hist_usercoursestatus hs ON hs.certificate = hc.row_id"
								." WHERE hc.row_id = ".$this->db->quote($cert_id, "integer"));
		if ($rec = $this->db->fetchAssoc($res)) {
			require_once("Services/Utilities/classes/class.ilUtil.php");
			require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
			$crs_utils = gevCourseUtils::getInstance($rec["crs_id"]);
			ilUtil::deliverData(base64_decode($rec["certfile"]), "Zertifikat_".$crs_utils->getCustomId().".pdf", "application/pdf");
		}
		else {
			return $this->render();
		}
	}

	protected function queryWhere(ilDate $start = null, ilDate $end = null, $no_historic = true) {
		if ($start === null) {
			return parent::queryWhere();
		}

		return		 " WHERE usrcrs.usr_id = ".$this->db->quote($this->target_user_id, "integer")
					.($no_historic ? "   AND usrcrs.hist_historic = 0 " : "")
					."   AND ( usrcrs.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
					."        OR usrcrs.end_date = '0000-00-00')"
					."   AND usrcrs.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
					."   AND ".$this->db->in("usrcrs.booking_status", array("gebucht", "kostenpflichtig storniert", "kostenfrei storniert"), false, "text")
					;
	}

	protected function _process_xlsx_status($val) {

		$this->lng->loadLanguageModule("assessment");
		$val = str_replace($this->success_img, $this->lng->txt("passed_official") ,$val);
		$val = str_replace($this->failed_img, $this->lng->txt("failed_official") ,$val);
		$val = str_replace($this->in_progress_img, $this->lng->txt("tst_status_progress") ,$val);
		return $val;
	}

	protected function _process_xlsx_date($val) {
		$val = str_replace('<br>', '',$val);
		$val = str_replace('<br/>', '',$val);

		return $val;
	}
}