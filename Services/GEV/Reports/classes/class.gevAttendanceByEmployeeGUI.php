<?php

class gevAttendanceByEmployeeGUI {
	public function __construct() {
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Reports/classes/class.gevReportingPermissions.php");

		global $lng, $ilCtrl, $tpl, $ilUser, $ilDB;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->db = &$ilDB;
		$this->user = $ilUser;
		
		//$this->report_permissions = gevReportingPermissions::getInstance($this->user->getId());

		//$this->user_id = $ilUser->getId();
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

		$this->user_utils = gevUserUtils::getInstance($this->user->getId());
		
		$this->query_where = null;
		$this->query_from = null;
	}
	
	public function executeCommand() {
		$this->checkPermission();

		/*
		$cmd = $this->ctrl->getCmd();
		
		switch ($cmd) {
			case "getCertificate":
				return $this->deliverCertificate();
			case "getBill":
				return $this->getBill();
			default:
				return $this->render();
		}
		*/
		return $this->render();
	}
	
	
	protected function checkPermission() {

		if( $this->user_utils->isAdmin() ) { 
			return;
		} else {
			//is superior anywhere?
			if ($this->user_utils->getEmployees()){
				return;
			}

		}
		ilUtil::sendFailure($this->lng->txt("no_report_permission"), true);
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}
	
	public function render() {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
		require_once("Services/GEV/Desktop/classes/class.gevPeriodSelectorGUI.php");
		
		$title = new catTitleGUI("gev_rep_attendance_by_employee_title", "gev_rep_attendance_by_employee_desc", "GEV_img/ico-head-edubio.png");
		
		
		/*
		//table legend

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
		*/
		$period_input = new gevPeriodSelectorGUI( $this->start_date
												, $this->end_date
												, $this->ctrl->getLinkTarget($this, "view")
												);
		
		$spacer = new catHSpacerGUI();
		
		return    $title->render()
				. $period_input->render()
				. $spacer->render()
				//. $this->renderOverview()
				//. $spacer->render()
				. $this->renderTable()
				;
	}
	/*
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
	*/


	public function renderTable() {
		require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate("tpl.gev_attendance_by_employee_row.html", "Services/GEV/Reports");

		$table_cols = array(
			array("lastname", "lastname"),
			array("firstname", "firstname"),
			array("gev_bwv_id", "bwv_id"),
			array("gev_agent_key", "position_key"),
			array("gender", "gender"),
			array("gev_org_unit_short", "org_unit"),
			array("title", "title"),
			array("gev_training_id", "custom_id"),
			array("gev_location", "venue"),
			array("gev_provider", "provider"),
			array("gev_learning_type", "type"),
			array("gev_booking_status", "booking_status"),
			array("gev_participation_status", "participation_status")
		);

		$table->addColumn("", "blank", "0px", false);
		foreach ($table_cols as $col) {
			$table->addColumn($this->lng->txt($col[0]), $col[1]);
		}
		
		$table->setFormAction($this->ctrl->getFormAction($this, "view"));
		

		//get data
		$query =	 "SELECT usrcrs.usr_id, usrcrs.crs_id, "
					."		 usrcrs.booking_status, usrcrs.participation_status, usrcrs.okz, usrcrs.org_unit,"
					."		 usr.firstname, usr.lastname, usr.gender, usr.bwv_id, usr.position_key,"
					."		 crs.custom_id, crs.title, crs.type, crs.venue, crs.provider "

 					."  FROM hist_usercoursestatus usrcrs "
					."  JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
					."  JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"

					."  WHERE (usrcrs.booking_status != '-empty-' OR usrcrs.participation_status != '-empty-')"
					
					. $this->queryWhen($this->start_date, $this->end_date)
					. $this->queryAllowedUsers()
					
					."  ORDER BY usr.lastname ASC";


		//print $query;

		$res = $this->db->query($query);
		
		$no_entry = $this->lng->txt("gev_table_no_entry");
		$user_utils = gevUserUtils::getInstance($this->target_user_id);
		
		$data = array();
		while($rec = $this->db->fetchAssoc($res)) {
			/*	
				modify record-entries here.
			*/			

			foreach ($rec as $key => $value) {
				if ($value == '-empty-' || $value == -1) {
					$rec[$key] = $no_entry;
					continue;
				}
			}
			
			$data[] = $rec;
		}

		/*
		print '<hr><pre>';
		print_r($data);
		print '</pre><hr>';
		*/
		$cnt = count($data);
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);

		$table->setData($data);
		return $table->getHTML();
	}
	
	protected function queryWhen(ilDate $start, ilDate $end) {
		if ($this->query_when === null) {
			$this->query_when =
					 //" WHERE usr.user_id = ".$this->db->quote($this->target_user_id, "integer")
					//"  WHERE ".$this->db->in("usrcrs.function", array("Mitglied", "Teilnehmer", "Member"), false, "text")
					//."   AND ".$this->db->in("usrcrs.booking_status", array("gebucht", "kostenpflichtig storniert", "kostenfrei storniert"), false, "text")
					"   AND usrcrs.hist_historic = 0 "
					."   AND ( usrcrs.end_date >= ".$this->db->quote($start->get(IL_CAL_DATE), "date")
					."        OR usrcrs.end_date = '-empty-' OR usrcrs.end_date = '0000-00-00')"
					."   AND usrcrs.begin_date <= ".$this->db->quote($end->get(IL_CAL_DATE), "date")
					;
		}
		
		return $this->query_when;
	}
	
	protected function queryAllowedUsers() {
		

		//get org units recursively
		//$allowed_orgunits = $this->report_permissions->getOrgUnitIdsWhereUserHasRole($valid_roles, true);
		

		//get all users the current user is superior of:
		$allowed_user_ids = $this->user_utils->getEmployees();

		$allowed_user_ids_str = join(',', $allowed_user_ids);
		$query = "AND usr.user_id IN ($allowed_user_ids_str)";
		
		return $query;

	}

/*	
	protected function queryFrom() {
		if ($this->query_from === null) {
			$this->query_from =
					 "  FROM hist_usercoursestatus usrcrs "
					."  JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
					."  JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0";
		}
		return $this->query_from;
	}
*/	
}

?>