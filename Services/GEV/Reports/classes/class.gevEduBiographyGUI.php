<?php

class gevEduBiographyGUI {
	public function __construct() {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		
		global $lng, $ilCtrl, $tpl, $ilUser, $ilDB;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->db = &$ilDB;
		$this->user = $ilUser;
		$this->target_user_id = $_POST["target_user_id"]
							  ? $_POST["target_user_id"]
							  : $ilUser->getId();
		$this->start_date = $_POST["period"]["start"]["date"]
						  ? new ilDate(    $_POST["period"]["start"]["date"]["y"]
						  				  ."-".$_POST["period"]["start"]["date"]["m"]
						  				  ."-".$_POST["period"]["start"]["date"]["d"]
						  				  , IL_CAL_DATE)
						  : new ilDate(date("Y")."-01-01", IL_CAL_DATE);
		$this->end_date = $_POST["period"]["end"]["date"]
						  ? new ilDate(    $_POST["period"]["end"]["date"]["y"]
						  				  ."-".$_POST["period"]["end"]["date"]["m"]
						  				  ."-".$_POST["period"]["end"]["date"]["d"]
						  				  , IL_CAL_DATE)
						  : new ilDate(date("Y")."-12-31", IL_CAL_DATE);
						  
		if (ilDate::_after($this->start_date, $this->end_date)) {
			$this->end_date = new ilDate($this->start_date->get(IL_CAL_DATE), IL_CAL_DATE);
			$this->end_date->increment(ilDateTime::YEAR);
		}

		$this->target_user_utils = gevUserUtils::getInstance($this->target_user_id);
		
		$this->query_where = null;
		$this->query_from = null;
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
				return $this->render();
		}
	}
	
	protected function checkPermission() {
		if(    $this->user->getId() == $this->target_user_id
			|| $this->target_user_utils->isEmployeeOf($this->user->getId())) {
			return;
		}
		ilUtil::sendFailure($this->lng->txt("no_edu_bio_permission"), true);
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}
	
	public function render() {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
		require_once("Services/GEV/Desktop/classes/class.gevPeriodSelectorGUI.php");
		
		
		if ($this->user->getId() == $this->target_user_id) {
			$title = new catTitleGUI("gev_my_edu_bio", "gev_my_edu_bio_desc", "GEV_img/ico-head-edubio.png");
		}
		else {
			$title = new catTitleGUI( sprintf($this->lng->txt("gev_others_edu_bio"), $this->target_user_utils->getFullName())
									, sprintf($this->lng->txt("gev_others_edu_bio_desc"), $this->target_user_utils->getFullName())
									, "GEV_img/ico-head-edubio.png"
									, false
									);
		}
		
		$this->get_cert_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_cert.png").'" />';
		$this->get_bill_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-get_bill.png").'" />';
		$this->success_img  = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-green.png").'" />';
		$this->in_progress_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-orange.png").'" />';
		$this->failed_img = '<img src="'.ilUtil::getImagePath("GEV_img/ico-key-red.png").'" />';
		$this->action_img = '<img src="'.ilUtil::getImagePath("gev_action.png").'" />';
		
		$legend = new catLegendGUI();
		$legend->addItem($this->get_cert_img, "gev_get_certificate")
			   ->addItem($this->get_bill_img, "gev_get_bill")
			   ->addItem($this->success_img, "gev_passed")
			   ->addItem($this->in_progress_img, "gev_in_progress")
			   ->addItem($this->failed_img, "gev_failed");
		$title->setLegend($legend);
		
		$period_input = new gevPeriodSelectorGUI( $this->start_date
												, $this->end_date
												, $this->ctrl->getLinkTarget($this, "view")
												);
		
		$spacer = new catHSpacerGUI();
		
		return    $title->render()
				. $period_input->render()
				. $spacer->render()
				. $this->renderOverview()
				. $spacer->render()
				. $this->renderTable()
				;
	}
	
	public function renderOverview() {
		$user_utils = gevUserUtils::getInstance($this->target_user_id);
		$tpl = new ilTemplate("tpl.gev_edu_bio_overview.html", true, true, "Services/GEV/Reports");

		$this->renderAcademyPoints($tpl);
		
		if ($user_utils->transferPointsFromWBD()) {
			$this->renderWBDPoints($tpl);
			$tpl->setVariable("WBDPOINTSVISIBIBLE", "visible");
		}
		else {
			$tpl->setVariable("WBDPOINTSVISIBIBLE", "invisible");
			if($user_utils->transferPointsToWBD()) {
				$tpl->setVariable("WBDTRANSVISIBIBLE", "visible");
				$tpl->setCurrentBlock("wbd_transfer");
				$tpl->setVariable("TRANSFER_TITLE", $this->lng->txt("gev_wbd_transfer_on"));
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
		
		$start_date = $this->start_date->get(IL_CAL_FKT_GETDATE);
		$fy_start = new ilDate($start_date["year"]."-01-01", IL_CAL_DATE); 
		$fy_end = new ilDate($start_date["year"]."-12-31", IL_CAL_DATE);
		$fy_end->increment(ilDateTime::YEAR, 4);

		$tpl->setVariable("ACADEMY_FIVE_YEAR", ilDatePresentation::formatPeriod($fy_start, $fy_end));
	
		$query = $this->academyQuery($this->start_date, $this->end_date);
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
				.$this->queryFrom()
				.$this->queryWhere($start, $end)
				." AND usrcrs.participation_status = 'teilgenommen'"
				." AND crs.crs_id > 0" // only academy points
				;
	}
	
	protected function renderWBDPoints($tpl) {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		$user_utils = gevUserUtils::getInstance($this->target_user_id);

		$tpl->setVariable("WBD_SUM_TITLE", $this->lng->txt("gev_points_in_wbd"));
		$tpl->setVariable("WBD_SUM_CERT_PERIOD_TITLE", $this->lng->txt("gev_points_in_wbd_cert_period"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_TITLE", $this->lng->txt("gev_points_in_wbd_cert_year"));
		$tpl->setVariable("WBD_SUM_CUR_YEAR_PRED_TITLE", $this->lng->txt("gev_points_at_end_of_cert_year"));
		
		$cy_start = $user_utils->getStartOfCurrentCertificationYear();
		$cy_end = $user_utils->getStartOfCurrentCertificationYear();
		$cy_end->increment(ilDateTime::YEAR, 1);
		
		$cp_start = $user_utils->getStartOfCurrentCertificationPeriod();
		$cp_end = $user_utils->getStartOfCurrentCertificationPeriod();
		$cp_end->increment(ilDateTime::YEAR, 5);
		
		$tpl->setVariable("WBD_CERT_PERIOD", ilDatePresentation::formatPeriod($cp_start, $cp_end));
		$tpl->setVariable("WBD_CERT_YEAR", ilDatePresentation::formatPeriod($cy_start, $cy_end));
		
		$query = $this->wbdQuery($this->start_date, $this->end_date);
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
				.$this->queryFrom()
				.$this->queryWhere($cy_start, $cy_end)
				." AND usrcrs.booking_status = 'gebucht'"
				." AND ".$this->db->in("usrcrs.participation_status", array("teilgenommen", "nicht gesetzt"), false, "text")
				." AND (".$this->db->in("usrcrs.okz", array("OKZ1", "OKZ2", "OKZ3"), false, "text")
				."      OR crs.crs_id < 0 "
				."     )"
				;
		$res = $this->db->query($query);
		if ($rec = $this->db->fetchAssoc($res)) {
			$tpl->setVariable("WBD_SUM_CUR_YEAR_PRED", $rec["sum"] ? $rec["sum"] : 0);
		}
	}
	
	protected function wbdQuery(ilDate $start, ilDate $end) {
		return   "SELECT SUM(usrcrs.credit_points) sum "
				.$this->queryFrom()
				.$this->queryWhere($start, $end)
				." AND usrcrs.participation_status = 'teilgenommen'"
				." AND (".$this->db->in("usrcrs.okz", array("OKZ1", "OKZ2", "OKZ3"), false, "text")
				."      OR crs.crs_id < 0 "
				."     )"
				;
	}
	
	public function renderTable() {
		require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate("tpl.gev_edu_bio_row.html", "Services/GEV/Reports");
		$table->addColumn("", "blank", "0px", false);
		$table->addColumn($this->lng->txt("gev_training_id"), "custom_id");
		$table->addColumn($this->lng->txt("title"), "title");
		$table->addColumn($this->lng->txt("gev_learning_type"), "type");
		$table->addColumn($this->lng->txt("date"), "date", "112px");
		$table->addColumn($this->lng->txt("gev_location"), "location");
		$table->addColumn($this->lng->txt("gev_provider"), "provider");
		$table->addColumn($this->lng->txt("il_crs_tutor"), "tutor");
		$table->addColumn($this->lng->txt("gev_points"), "credit_points");
		$table->addColumn($this->lng->txt("gev_costs"), "fee");
		$table->addColumn($this->lng->txt("status"), "status");
		$table->addColumn($this->lng->txt("gev_wbd_relevant"), "wbd");
		$table->addColumn($this->action_img, "action");
		$table->setFormAction($this->ctrl->getFormAction($this, "view"));
		
		$query =	 "SELECT crs.custom_id, crs.title, crs.type, usrcrs.begin_date, usrcrs.end_date, "
					."       crs.venue, crs.provider, crs.tutor, usrcrs.credit_points, crs.fee, "
					."       usrcrs.participation_status, usrcrs.okz, usrcrs.bill_id, usrcrs.certificate, "
					."       usrcrs.booking_status "
					. $this->queryFrom()
					. $this->queryWhere($this->start_date, $this->end_date)
					;
		
		$res = $this->db->query($query);
		
		$no_entry = $this->lng->txt("gev_table_no_entry");
		$user_utils = gevUserUtils::getInstance($this->target_user_id);
		
		$data = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$rec["fee"] = (($rec["bill_id"] != -1 || $user_utils->paysFees())&& $rec["fee"] != -1)
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
			if ($rec["bill_id"] != -1) {
				$this->ctrl->setParameter($this, "bill_id", $rec["bill_id"]);
				$this->ctrl->setParameter($this, "target_user_id", $this->target_user_id);
				$rec["action"] = "<a href='".$this->ctrl->getLinkTarget($this, "getBill")."'>"
							   . $this->get_bill_img."</a>";
				$this->ctrl->clearParameters($this);
			}
			if ($rec["certificate"] != -1) {
				$this->ctrl->setParameter($this, "cert_id", $rec["certificate"]);
				$this->ctrl->setParameter($this, "target_user_id", $this->target_user_id);
				$rec["action"] .= "<a href='".$this->ctrl->getLinkTarget($this, "getCertificate")."'>"
							   . $this->get_cert_img."</a>";
				$this->ctrl->clearParameters($this);
			}
			
			foreach ($rec as $key => $value) {
				if ($value == '-empty-' || $value == -1) {
					$rec[$key] = $no_entry;
					continue;
				}
			}
			
			$data[] = $rec;
		}

		$table->setData($data);
		
		return $table->getHTML();
	}
	
	protected function getBill() {
		// check weather this bill really belongs to an edu bio record of the current user.
		$bill_id = $_GET["bill_id"];
		$res = $this->db->query( "SELECT COUNT(*) cnt"
								."  FROM hist_usercoursestatus "
								." WHERE usr_id = ".$this->db->quote($this->target_user_id, "integer")
								."   AND bill_id = ".$this->db->quote($bill_id, "integer")
								);
		if($rec = $this->db->fetchAssoc($res)) {
			if ($rec["cnt"] == 0) {
				return $this->render();
			}
		}
		
		require_once("Services/Billing/classes/class.ilBill.php");
		require_once("Services/GEV/Utils/classes/class.gevPDFBill.php");
		$bill = ilBill::getInstanceById($_GET["bill_id"]);
		$gevPDFBill = new gevPDFBill();
		$gevPDFBill->setBill($bill);
		$gevPDFBill->deliver();
		exit();
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
	
	protected function queryWhere(ilDate $start, ilDate $end) {
		if ($this->query_where === null) {
			$this->query_where =
					 " WHERE usr.user_id = ".$this->db->quote($this->target_user_id, "integer")
					."   AND ".$this->db->in("usrcrs.function", array("Mitglied", "Teilnehmer", "Member"), false, "text")
					."   AND ".$this->db->in("usrcrs.booking_status", array("gebucht", "kostenpflichtig storniert", "kostenfrei storniert"), false, "text")
					."   AND usrcrs.hist_historic = 0 "
					."   AND ( usrcrs.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
					."        OR usrcrs.end_date = '0000-00-00')"
					."   AND usrcrs.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
					;
		}
		
		return $this->query_where;
	}
	
	protected function queryFrom() {
		if ($this->query_from === null) {
			$this->query_from =
					 "  FROM hist_usercoursestatus usrcrs "
					."  JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
					."  JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0";
		}
		return $this->query_from;
	}
}

?>