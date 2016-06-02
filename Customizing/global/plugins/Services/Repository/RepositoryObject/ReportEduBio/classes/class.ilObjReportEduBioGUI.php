<?php

require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseGUI.php';
require_once 'Services/Form/classes/class.ilNumberInputGUI.php';
/**
* User Interface class for example repository object.
* ...
* @ilCtrl_isCalledBy ilObjReportEduBioGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReportEduBioGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, gevDBVReportGUI
* @ilCtrl_Calls ilObjReportEduBioGUI: ilCommonActionDispatcherGUI
*/
class ilObjReportEduBioGUI extends ilObjReportBaseGUI {
	protected static $get_cert_img;
	protected static $get_bill_img;
	protected static $success_img;
	protected static $in_progress_img;
	protected static $failed_img;
	protected static $target_user_id;

	public function getType() {
		return 'xreb';
	}


	public function performCustomCommand($cmd) {
		switch ($cmd) {
			case "getCertificate":
				return $this->getCertificate();
			case "getBill":
				return $this->getBill();
			default:
				return false;
		}
	}

	protected function prepareTitle($a_title) {
		if ( (string)$this->gUser->getId() == (string)$this->object->target_user_id) {
			$a_title->title("gev_my_edu_bio")
							->subTitle("gev_my_edu_bio_desc")
							->image("GEV_img/ico-head-edubio.png");
		}
		else {
			$a_title->title(sprintf($this->object->plugin->txt("gev_others_edu_bio"), $this->object->target_user_utils->getFullName()))
					->subTitle(sprintf($this->object->plugin->txt("gev_others_edu_bio_desc"), $this->object->target_user_utils->getFullName()))
					->image("GEV_img/ico-head-edubio.png")
					->useLng(false);
		}
		$a_title->setVideoLink($this->object->settings['video_link'])
				->setVideoLinkText($this->object->master_plugin->txt("rep_video_desc"))
				->setPdfLink($this->object->settings['pdf_link'])
				->setPdfLinkText($this->object->master_plugin->txt("rep_pdf_desc"))
				->setToolTipText($this->object->settings['tooltip_info'])->legend(catLegendGUI::create()
				->item(self::$get_cert_img, "gev_get_certificate")
				->item(self::$get_bill_img, "gev_get_bill")
				->item(self::$success_img, "gev_passed")
				->item(self::$in_progress_img, "gev_in_progress")
				->item(self::$failed_img, "gev_failed"));
		return $a_title;
	}

	/**
	 * render report.
	 */
	public function renderReport() {
		self::$get_cert_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_cert.png").'" />';
		self::$get_bill_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_bill.png").'" />';
		self::$success_img  = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		self::$in_progress_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
		self::$failed_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';
		parent::renderReport();
		self::$target_user_id = $this->object->target_user_id;
	}

	protected function render() {
		$this->gTpl->setTitle(null);
		return 	$this->title->render()
				. ($this->object->deliverFilter() !== null ? $this->object->deliverFilter()->render() : "")
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderOverview()
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderTable();
	}

	protected function renderOverview() {
		$tpl = new ilTemplate("tpl.gev_edu_bio_overview.html", true, true, "Services/GEV/Reports");
		$this->insertAcademyPoints($tpl);
		if (gevWBD::getInstance($this->object->target_user_id)->transferPointsFromWBD()) {
			$this->insertWBDPoints($tpl);
			$tpl->setVariable("WBDPOINTSVISIBIBLE", "visible");
		}
		else {
			$tpl->setVariable("WBDPOINTSVISIBIBLE", "invisible");
			if(gevWBD::getInstance($this->object->target_user_id)->transferPointsToWBD()) {
				$tpl->setVariable("WBDTRANSVISIBIBLE", "visible");
				$tpl->setCurrentBlock("wbd_transfer");
				$tpl->setVariable("TRANSFER_TITLE", $this->lng->txt("gev_wbd_transfer_on"));
				$tpl->parseCurrentBlock();
			}
			elseif (gevWBD::getInstance($this->object->target_user_id)->wbdRegistrationIsPending()){
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

	protected function insertAcademyPoints($tpl) {
		$tpl->setVariable("ACADEMY_SUM_TITLE", $this->lng->txt("gev_points_in_academy"));
		$tpl->setVariable("ACADEMY_SUM_FIVE_YEAR_TITLE", $this->lng->txt("gev_points_in_five_years"));

		$tpl->setVariable("ACADEMY_FIVE_YEAR", $this->object->academy_data["five_year"]);

		if ($aux = $this->object->academy_data["sum"]) {
			$tpl->setVariable("ACADEMY_SUM", $aux ? $aux : 0);
		}
		if ($aux = $this->object->academy_data["sum_five_year"]) {
			$tpl->setVariable("ACADEMY_SUM_FIVE_YEAR", $aux ? $aux : 0);
		}
	}

	protected function insertWBDPoints($tpl) {
		$tpl->setVariable("WBD_SUM_TITLE", $this->lng->txt("gev_points_in_wbd"));
		$tpl->setVariable("WBD_SUM_CERT_PERIOD_TITLE", $this->lng->txt("gev_points_in_wbd_cert_period"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_TITLE", $this->lng->txt("gev_points_in_wbd_cert_year"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_PRED_TITLE", $this->lng->txt("gev_points_at_end_of_cert_year"));

		$tpl->setVariable("WBD_CERT_PERIOD", $this->object->wbd_data["cert_period"]);
		$tpl->setVariable("WBD_CERT_YEAR", $this->object->wbd_data["cert_year"]);

		if ($aux = $this->object->wbd_data["sum"]) {
			$tpl->setVariable("WBD_SUM", $aux ? $aux : 0);
		}
		if ($aux = $this->object->wbd_data["sum_year"]) {
			$tpl->setVariable("WBD_SUM_CUR_YEAR", $aux ? $aux : 0);
		}
		if ($aux = $this->object->wbd_data["sum_cert_period"]) {
			$tpl->setVariable("WBD_SUM_CERT_PERIOD", $aux ? $aux : 0);
		}
		if ($aux = $this->object->wbd_data["sum_cur_year_pred"]) {
			$tpl->setVariable("WBD_SUM_CUR_YEAR_PRED", $aux ? $aux : 0);
		}
	}

	protected function getBill() {
		// check weather this bill really belongs to an edu bio record of the current user.
		$bill_id = $_GET["bill_id"];
		$res = $this->db->query( "SELECT crs_id"
								."  FROM hist_usercoursestatus "
								." WHERE usr_id = ".$this->db->quote($this->object->target_user_id, "integer")
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
								." WHERE usr_id = ".$this->db->quote($this->object->target_user_id, "integer")
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

	public static function transformResultRow($rec) {
		global $lng, $ilCtrl;
		$no_entry = $lng->txt("gev_table_no_entry");

		$rec["fee"] = (($rec["bill_id"] != -1 || $this->target_user_utils->paysFees())&& $rec["fee"] != -1)
					? $rec["fee"] = gevCourseUtils::formatFee($rec["fee"])." &euro;"
					: $rec["fee"] == "-empty-";

		if ($rec["participation_status"] == "teilgenommen") {
			$rec["status"] = self::$success_img;
		}
		else if (in_array($rec["participation_status"], array("fehlt entschuldigt", "fehlt ohne Absage"))
			 ||  in_array($rec["booking_status"], array("kostenpflichtig storniert", "kostenfrei storniert"))
			) {
			$rec["status"] = self::$failed_img;
		}
		else {
			$rec["status"] = self::$in_progress_img;
		}

		if ($rec["begin_date"] == "0000-00-00" && $rec["end_date"] == "0000-00-00") {
			$rec["date"] = $no_entry;
		}
		else if ($rec["end_date"] == "0000-00-00") {
			$dt = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$rec["date"] = $lng->txt("gev_from")." ".ilDatePresentation::formatDate($dt);
		}
		else if ($rec["begin_date"] == "0000-00-00") {
			$dt = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = $lng->txt("gev_until")." ".ilDatePresentation::formatDate($dt);
		}
		else {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = ilDatePresentation::formatDate($start)." - <br/>".ilDatePresentation::formatDate($end);
		}

		$rec["wbd"] = in_array($rec["okz"], array("OKZ1", "OKZ2", "OKZ3"))
					? $lng->txt("yes")
					: $lng->txt("no");

		$rec["action"] = "";
		if ($rec["bill_id"] != -1 && $rec["bill_id"] != "-empty-") {
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI", "bill_id", $rec["bill_id"]);
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI", "target_user_id", self::$target_user_id);
			$rec["action"] = "<a href='".$ilCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportEduBioGUI"), "getBill")."'>"
						   . self::$get_bill_img."</a>";
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",   "bill_id", null);
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",   "target_user_id", null);
		}
		if ($rec["certificate"] != -1 && $rec["certificate"] != 0) {
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",  "cert_id", $rec["certificate"]);
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",  "target_user_id", self::$target_user_id);
			$rec["action"] .= "<a href='".$this->ctrl->getLinkTarget($this, "getCertificate")."'>"
						   . self::$get_cert_img."</a>";
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",  "cert_id", null);
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",  "target_user_id", null);
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
}