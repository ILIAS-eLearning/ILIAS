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
	}
	
	public function executeCommand() {
		$this->checkPermission();
		
		return $this->render();
	}
	
	protected function checkPermission() {
		return $this->user->getId() == $this->target_user_id
			|| $this->target_user_utils->isEmployeeOf($this->user->getId());
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
		return "";
	}
	
	public function renderTable() {
		require_once("Services/CatUIComponents/classes/class.catTableGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate("tpl.gev_edu_bio_row.html", "Services/GEV/Desktop");
		$table->addColumn($this->lng->txt("gev_training_id"), "custom_id");
		$table->addColumn($this->lng->txt("title"), "title");
		$table->addColumn($this->lng->txt("gev_learning_type"), "type");
		$table->addColumn($this->lng->txt("date"), "date");
		$table->addColumn($this->lng->txt("gev_location"), "location");
		$table->addColumn($this->lng->txt("gev_provider"), "provider");
		$table->addColumn($this->lng->txt("il_crs_tutor"), "trainer");
		$table->addColumn($this->lng->txt("gev_points"), "points");
		$table->addColumn($this->lng->txt("gev_costs"), "costs");
		$table->addColumn($this->lng->txt("status"), "status");
		$table->addColumn($this->lng->txt("gev_wbd_relevant"), "wbd");
		$table->addColumn($this->action_img, "action");
		
		$query =	 "SELECT crs.custom_id, crs.title, crs.type, usrcrs.begin_date, usrcrs.end_date, "
					."       crs.venue, crs.provider, crs.tutor, usrcrs.credit_points, crs.fee, "
					."       usrcrs.participation_status, usrcrs.okz, usrcrs.bill_id, usrcrs.certificate"
					."  FROM hist_usercoursestatus usrcrs "
					."  JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
					."  JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"
					." WHERE usr.user_id = ".$this->db->quote($this->target_user_id, "integer")
					."   AND ".$this->db->in("usrcrs.function", array("Mitglied", "Teilnehmer", "Member"), false, "text")
					."   AND usrcrs.booking_status = 'gebucht'"
					."   AND usrcrs.hist_historic = 0 "
					."   AND ( usrcrs.end_date > ".$this->db->quote($this->start_date->get(IL_CAL_DATE), "date")
					."        OR usrcrs.end_date = '-empty-')"
					."   AND usrcrs.begin_date < ".$this->db->quote($this->end_date->get(IL_CAL_DATE), "date")
					;
		
		$res = $this->db->query($query);
		
		$no_entry = $this->lng->txt("gev_table_no_entry");
		$data = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$rec["fee"] = ($rec["bill_id"] != -1 && $rec["fee"] != -1)
						? $rec["fee"] = gevCourseUtils::formatFee($rec["fee"])
						: $rec["fee"] == "-empty-";
			$rec["status"] = ( $rec["participation_status"] == "fehlt entschuldigt" 
							|| $rec["participation_status"] == "fehlt ohne Absage")
						   ? $this->in_failed_img
						   : ( ($rec["participation_status"] == "teilgenommen")
						   	 ? $this->passed_img
						   	 : $this->in_progress_img
						   	 );

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
				$rec["action"] = "<a href='".$this->ctrl->getLinkTarget($this, "getBill")."'>"
							   . $this->get_bill_img."</a>";
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
}

?>