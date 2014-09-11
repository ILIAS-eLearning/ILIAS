<?php

require_once("Services/Calendar/classes/class.ilDatePresentation.php");

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

		$this->data = false;
		
		$this->table_cols = array(
			array("lastname", "lastname"),
			array("firstname", "firstname"),
			array("gev_bwv_id", "bwv_id"),
			array("gev_agent_key", "position_key"),
			array("gender", "gender"),
			array("gev_org_unit_short", "org_unit"),
			array("title", "title"),
			array("gev_training_id", "custom_id"),
			//array("gev_location", "venue"),
			//array("gev_provider", "provider"),
			array("gev_learning_type", "type"),
			array("date", "date"),
			array("gev_booking_status", "booking_status"),
			array("gev_participation_status", "participation_status")
		);


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

		$cmd = $this->ctrl->getCmd();
		
		switch ($cmd) {
			case "exportxls":
				$this->exportXLS();
				//no "break;" !
			default:
				return $this->render();
		}
	}
	
	
	protected function checkPermission() {

		if( $this->user_utils->isAdmin() || $this->user_utils->isSuperior()) { 
			return;
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
		
		//export-button
		$export_btn = '<a class="submit" style="float:right;"'
					. 'href="'
					.$this->ctrl->getLinkTarget($this, "exportxls")
					.'">'
					.$this->lng->txt("gev_reports_export")
					.'</a>';


		return    $title->render()
				. $period_input->render()
				. $spacer->render()
				. $export_btn
				. $this->renderTable()
				. $export_btn
				;
	}


	public function renderTable() {
		require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate("tpl.gev_attendance_by_employee_row.html", "Services/GEV/Reports");

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table_cols as $col) {
			$table->addColumn($this->lng->txt($col[0]), $col[1]);
		}
		
		$table->setFormAction($this->ctrl->getFormAction($this, "view"));

		$data = $this->getData();

		$cnt = count($data);
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);

		$table->setData($data);
		return $table->getHTML();
	}
	



	protected function exportXLS() {
		require_once "Services/User/classes/class.ilUserUtil.php";
		require_once "Services/Excel/classes/class.ilExcelUtils.php";
		require_once "Services/Excel/classes/class.ilExcelWriterAdapter.php";
		
		$data = $this->getData();

		$adapter = new ilExcelWriterAdapter("Report.xls", true); 
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		//available formats within the sheet
		$format_bold = $workbook->addFormat(array("bold" => 1));
		$format_wrap = $workbook->addFormat();
		$format_wrap->setTextWrap();
		
		//init cols and write titles
		$colcount = 0;
		foreach ($this->table_cols as $col) {
			$worksheet->setColumn($colcount, $colcount, 30); //width
			$worksheet->writeString(0, $colcount, $this->lng->txt($col[0]), $format_bold);
			$colcount++;
		}

		//write data-rows
		$rowcount = 1;
		foreach ($data as $entry) {
			$colcount = 0;
			foreach ($this->table_cols as $col) {
				$k = $col[1];
				$v = $entry[$k];
				if ($k == 'date'){
					$v = str_replace('<nobr>', '', $v);
					$v = str_replace('</nobr>', '', $v);
				}
				$worksheet->write($rowcount, $colcount, $v, $format_wrap);
				$colcount++;
			}

			$rowcount++;
		}

		$workbook->close();		
	}




	protected function getData(){ 
		if ($this->data == false){
			$this->loadData();
		}
		return $this->data;
	}

	protected function loadData(){ 
		//set data to $this->data
		$this->data = $this->fetchData();
	}


	protected function fetchData(){ 
		//fetch retrieves the data 
		
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");

		$no_entry = $this->lng->txt("gev_table_no_entry");
		$user_utils = gevUserUtils::getInstance($this->target_user_id);
		$data = array();

		//get data
		$query =	 "SELECT usrcrs.usr_id, usrcrs.crs_id, "
					."		 usrcrs.booking_status, usrcrs.participation_status, usrcrs.okz, usrcrs.org_unit,"
					."		 usr.firstname, usr.lastname, usr.gender, usr.bwv_id, usr.position_key,"
					."		 crs.custom_id, crs.title, crs.type, crs.venue, crs.provider, crs.begin_date, crs.end_date "

 					."  FROM hist_usercoursestatus usrcrs "
					."  JOIN hist_user usr ON usr.user_id = usrcrs.usr_id AND usr.hist_historic = 0"
					."  JOIN hist_course crs ON crs.crs_id = usrcrs.crs_id AND crs.hist_historic = 0"

					."  WHERE ("
					."		(usrcrs.booking_status != '-empty-' OR usrcrs.participation_status != '-empty-')"
					."  	AND usrcrs.function NOT IN ('Trainingsbetreuer', 'Trainer')"
					."  )"
					
					. $this->queryWhen($this->start_date, $this->end_date)
					. $this->queryAllowedUsers()
					
					."  ORDER BY usr.lastname ASC";


		$res = $this->db->query($query);

		while($rec = $this->db->fetchAssoc($res)) {
			/*	
				modify record-entries here.
			*/			
			foreach ($rec as $key => $value) {
				
				if ($value == '-empty-' || $value == -1) {
					$rec[$key] = $no_entry;
					continue;
				}

				//date
				if( $rec["begin_date"] && $rec["end_date"] 
					&& ($rec["begin_date"] != '0000-00-00' && $rec["end_date"] != '0000-00-00' )
					){
					$start = new ilDate($rec["begin_date"], IL_CAL_DATE);
					$end = new ilDate($rec["end_date"], IL_CAL_DATE);
					$date = '<nobr>' .ilDatePresentation::formatPeriod($start,$end) .'</nobr>';
					//$date = ilDatePresentation::formatPeriod($start,$end);
				} else {
					$date = '-';
				}
				$rec['date'] = $date;
			}
			
			$data[] = $rec;
		}

		return $data;
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
		

		//get all users the current user is superior of:
		$allowed_user_ids = $this->user_utils->getEmployees();

//		$allowed_user_ids_str = join(',', $allowed_user_ids);
//		$query = "AND usr.user_id IN ($allowed_user_ids_str)";
		$query = " AND ".$this->db->in("usr.user_id", $allowed_user_ids, false, "integer");
		
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