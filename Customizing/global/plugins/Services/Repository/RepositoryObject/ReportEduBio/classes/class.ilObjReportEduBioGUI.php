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
				$this->object->prepareRelevantParameters();
				$this->getCertificate();
				break;
			case "getBill":
				$this->object->prepareRelevantParameters();
				$this->getBill();
				break;
			default:
				return false;
		}
		return true;
	}

	protected function prepareTitle($a_title) {
		if ( (string)$this->gUser->getId() == (string)$this->object->target_user_id) {
			$a_title->title($this->object->plugin->txt("my_edu_bio"))
							->subTitle($this->object->plugin->txt("my_edu_bio_desc"))
							->image("GEV_img/ico-head-edubio.png");
		} else {
			$a_title->title(sprintf($this->object->plugin->txt("others_edu_bio"), $this->object->target_user_utils->getFullName()))
					->subTitle(sprintf($this->object->plugin->txt("others_edu_bio_desc"), $this->object->target_user_utils->getFullName()))
					->image("GEV_img/ico-head-edubio.png");
		}
		$a_title->useLng(false)
				->setVideoLink($this->object->settings['video_link'])
				->setVideoLinkText($this->object->master_plugin->txt("rep_video_desc"))
				->setPdfLink($this->object->settings['pdf_link'])
				->setPdfLinkText($this->object->master_plugin->txt("rep_pdf_desc"))
				->setToolTipText($this->object->settings['tooltip_info'])
				->legend(catLegendGUI::create()
					->item(self::$get_cert_img, "gev_get_certificate")
					->item(self::$get_bill_img, "gev_get_bill")
					->item(self::$success_img, "gev_passed")
					->item(self::$in_progress_img, "gev_in_progress")
					->item(self::$failed_img, "gev_failed")
					);
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
		self::$target_user_id = $this->object->target_user_id;
		parent::renderReport();
	}

	protected function render() {
		$this->gTpl->setTitle(null);
		if(!$this->object->userTPStatusOK()) {
			ilUtil::sendInfo($this->plugin->txt("wbd_role_no_service_warning"));
		}
		return 	$this->title->render()
				. ($this->object->deliverFilter() !== null ? $this->object->deliverFilter()->render() : "")
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderOverview()
				. ($this->spacer !== null ? $this->spacer->render() : "")
				. $this->renderTable();
	}

	protected function renderOverview() {
		$tpl = new ilTemplate("tpl.gev_edu_bio_overview.html", true, true, $this->object->plugin->getDirectory());
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
				$tpl->setVariable("TRANSFER_TITLE", $this->object->plugin->txt("wbd_transfer_on"));
				$tpl->parseCurrentBlock();
			}
			elseif (gevWBD::getInstance($this->object->target_user_id)->wbdRegistrationIsPending()){
				$tpl->setVariable("WBDPOINTSVISIBIBLE", "invisible");
				$tpl->setCurrentBlock("wbd_reg_pending");
				$tpl->setVariable("WBDREGPENDINGVISIBIBLE", "visible");
				$tpl->setVariable("WBD_REG_PENDING", $this->object->plugin->txt("wbd_reg_pending"));
				$tpl->parseCurrentBlock();
			}
			else {
				$tpl->setVariable("WBDTRANSVISIBIBLE", "visible");
			}
		}
		return $tpl->get();
	}

	protected function insertAcademyPoints($tpl) {
		$tpl->setVariable("ACADEMY_SUM_TITLE",$this->object->plugin->txt("points_in_academy"));
		$tpl->setVariable("ACADEMY_SUM_FIVE_YEAR_TITLE", $this->object->plugin->txt("points_in_five_years"));

		$tpl->setVariable("ACADEMY_FIVE_YEAR", $this->object->academy_data["five_year"]);

		if ($aux = $this->object->academy_data["sum"]) {
			$tpl->setVariable("ACADEMY_SUM", $aux ? $aux : 0);
		}
		if ($aux = $this->object->academy_data["sum_five_year"]) {
			$tpl->setVariable("ACADEMY_SUM_FIVE_YEAR", $aux ? $aux : 0);
		}
	}

	protected function insertWBDPoints($tpl) {
		$tpl->setVariable("WBD_SUM_TITLE", $this->object->plugin->txt("points_in_wbd"));
		$tpl->setVariable("WBD_SUM_CERT_PERIOD_TITLE", $this->object->plugin->txt("points_in_wbd_cert_period"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_TITLE", $this->object->plugin->txt("points_in_wbd_cert_year"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_PRED_TITLE", $this->object->plugin->txt("points_at_end_of_cert_year"));

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
		
		if (!$this->object->validateBill($bill_id)) {
			$this->gCtrl->redirect($this, "showContent");
		}
		require_once("Services/GEV/Utils/classes/class.gevBillStorage.php");
		$year = ilBill::getInstanceByBillNumber($bill_id)->getBillYear();
		$fname = "Rechnung_".$bill_id.".pdf";
		$bill_storage = gevBillStorage::getInstance($year);
		$path = $bill_storage->getPathByBillNumber($bill_id);
		ilUtil::deliverFile($path, $fname, 'application/pdf', false, false, true);
	}

	protected function getCertificate() {
		// check weather this cert really belongs to an edu bio of the current user
		$cert_id = $_GET["cert_id"];
		if (!$this->object->validateCertificate($cert_id)) {
			$this->gCtrl->redirect($this, "showContent");
		}
		$cert_data = $this->object->certificateData($cert_id);
		if ($cert_data) {
			require_once("Services/Utilities/classes/class.ilUtil.php");
			require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
			$crs_utils = gevCourseUtils::getInstance($cert_data["crs_id"]);
			ilUtil::deliverData(base64_decode($cert_data["certfile"]), "Zertifikat_".$crs_utils->getCustomId().".pdf", "application/pdf");
		} else {
			$this->gCtrl->redirect($this, "showContent");
		}
	}

	public static function transformResultRow($rec) {
		global $lng;
		$no_entry = $lng->txt("gev_table_no_entry");

		$rec["fee"] = (($rec["bill_id"] != -1 || gevUserUtils::getInstance(self::$target_user_id)->paysFees()) && $rec["fee"] != -1)
					? $rec["fee"] = gevCourseUtils::formatFee($rec["fee"])." &euro;"
					: $rec["fee"] == "-empty-";

		if ($rec["participation_status"] == "teilgenommen") {
			$rec["status"] = self::$success_img;
		} elseif (in_array($rec["participation_status"], array("fehlt entschuldigt", "fehlt ohne Absage"))
			 ||  in_array($rec["booking_status"], array("kostenpflichtig storniert", "kostenfrei storniert"))
			) {
			$rec["status"] = self::$failed_img;
		} else {
			$rec["status"] = self::$in_progress_img;
		}

		if ($rec["begin_date"] == "0000-00-00" && $rec["end_date"] == "0000-00-00") {
			$rec["date"] = $no_entry;
		} elseif ($rec["end_date"] == "0000-00-00") {
			$dt = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$rec["date"] = $lng->txt("gev_from")." ".ilDatePresentation::formatDate($dt);
		} elseif ($rec["begin_date"] == "0000-00-00") {
			$dt = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = $lng->txt("gev_until")." ".ilDatePresentation::formatDate($dt);
		} else {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = ilDatePresentation::formatDate($start)." - <br/>".ilDatePresentation::formatDate($end);
		}

		if(in_array($rec["okz"], array("OKZ1", "OKZ2", "OKZ3"))) {
			$rec['credit_points'] = $rec['credit_points'] >= 0 ? $rec['credit_points'] : 0;

			if($rec["wbd_booking_id"] && $rec["credit_points"] > 0) {
				$rec['wbd_reported'] = "Ja";
			} else if(!$rec["wbd_booking_id"] && $rec["credit_points"] > 0) {
				$rec['wbd_reported'] = "Nein";
			} else {
				$rec['wbd_reported'] = "-";
			}
		} else {
			$rec['credit_points'] = "-";
			$rec['wbd_reported'] = "-";
		}

		$rec["action"] = "";
		if ($rec["bill_id"] != -1 && $rec["bill_id"] != "-empty-") {
			$params = array("bill_id" => $rec["bill_id"],  "target_user_id" => self::$target_user_id);
			$rec["action"] .= "<a href='".self::getLinkToThis("getBill",$params)."'>". self::$get_bill_img."</a>";
		}
		if ($rec["certificate"] != -1 && $rec["certificate"] != 0) {
			$params = array("cert_id" => $rec["certificate"],  "target_user_id" => self::$target_user_id);
			$rec["action"] .= "<a href='".self::getLinkToThis("getCertificate",$params)."'>". self::$get_cert_img."</a>";
		}
		if ($rec["ref_id"] !== null) {
			$rec["link_open"] = "<a href='goto.php?target=crs_".$rec["ref_id"]."'>";
			$rec["link_close"] = "</a>";
		}
		else {
			$rec["link_open"] = "";
			$rec["link_close"] = "";
		}
		return parent::transformResultRow($rec);
	}

	public static function transformResultRowXLSX($rec) {
		global $lng;
		$no_entry = $lng->txt("gev_table_no_entry");

		$rec["fee"] = (($rec["bill_id"] != -1 || gevUserUtils::getInstance(self::$target_user_id)->paysFees())&& $rec["fee"] != -1)
					? $rec["fee"] = gevCourseUtils::formatFee($rec["fee"])
					: $rec["fee"] == "-empty-";

		if ($rec["participation_status"] == "teilgenommen") {
			$rec["status"] = self::$success_img;
		} elseif (in_array($rec["participation_status"], array("fehlt entschuldigt", "fehlt ohne Absage"))
			 ||  in_array($rec["booking_status"], array("kostenpflichtig storniert", "kostenfrei storniert"))
			) {
			$rec["status"] = self::$failed_img;
		} else {
			$rec["status"] = self::$in_progress_img;
		}

		if ($rec["begin_date"] == "0000-00-00" && $rec["end_date"] == "0000-00-00") {
			$rec["date"] = $no_entry;
		} elseif ($rec["end_date"] == "0000-00-00") {
			$dt = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$rec["date"] = $lng->txt("gev_from")." ".ilDatePresentation::formatDate($dt);
		} elseif ($rec["begin_date"] == "0000-00-00") {
			$dt = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = $lng->txt("gev_until")." ".ilDatePresentation::formatDate($dt);
		} else {
			$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
			$end = new ilDate($rec["end_date"], IL_CAL_DATE);
			$rec["date"] = ilDatePresentation::formatDate($start)." - <br/>".ilDatePresentation::formatDate($end);
		}

		$rec['credit_points'] = $rec['credit_points'] >= 0 ? $rec['credit_points'] : 0;
		$rec["wbd"] = in_array($rec["okz"], array("OKZ1", "OKZ2", "OKZ3"))
					? $lng->txt("yes")
					: $lng->txt("no");
		return parent::transformResultRowXLSX($rec);
	}

	protected static function getLinkToThis($cmd,$params) {
		global $ilCtrl;
		foreach ($params as $key => $value) {
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",  $key, $value);
		}
		$link = $ilCtrl->getLinkTargetByClass(array("ilObjPluginDispatchGUI","ilObjReportEduBioGUI"), $cmd);
		foreach ($params as $key => $value) {
			$ilCtrl->setParameterByClass("ilObjReportEduBioGUI",  $key, null);
		}
		return $link;
	}
}