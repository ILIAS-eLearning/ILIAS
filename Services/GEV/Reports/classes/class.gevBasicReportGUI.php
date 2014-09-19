<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* base class for ReportGUIs 
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

abstract class gevBasicReportGUI {

	public function __construct() {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");
		require_once("Services/GEV/Reports/classes/class.gevReportingPermissions.php");

		global $lng, $ilCtrl, $tpl, $ilUser, $ilDB;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->db = &$ilDB;
		$this->user = $ilUser;
		$this->user_utils = gevUserUtils::getInstance($this->user->getId());

		$this->title = array(
			'title' => '',
			'desc' => '',
			'img' => ''
		);
		
		$this->table_cols = array();//add arrays like this: array(translation-constant, key_in_data)
		$this->table_row_template = array(
			'filnename' => '',
			'path' => ''
		);
		$this->query_where = null;
		$this->query_from = null;
		$this->data = false;
	
		
		//watch out for sorting of special fields, i.e. dates shown as a period of time.
		//to avoid the ilTable-sorting, the this too true.
		//i.e. applies to: _table_nav=date:asc:
		$this->external_sorting = false;


		//$this->report_permissions = gevReportingPermissions::getInstance($this->user->getId());

		//date is a mandatory filter.
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

		
	}
	

	public function executeCommand() {
		$this->checkPermission();

		$cmd = $this->ctrl->getCmd();
		
		$res = $this->executeCustomCommand($cmd);
		
		if ($res !== null) {
			return $res;
		}
		
		switch ($cmd) {
			case "exportxls":
				$this->exportXLS();
				//no "break;" !
			default:
				return $this->render();
		}
	}
	
	protected function executeCustomCommand($a_cmd) {
		return null;
	}
	
	protected function checkPermission() {
		if( $this->userIsPermitted() ) { 
			return;
		}
		
		ilUtil::sendFailure($this->lng->txt("no_report_permission"), true);
		ilUtil::redirect("ilias.php?baseClass=gevDesktopGUI&cmdClass=toMyCourses");
	}

	protected function userIsPermitted () {
		return $this->user_utils->isAdmin() || $this->user_utils->isSuperior();
	}

	
	protected function render() {
		require_once("Services/CaTUIComponents/classes/class.catTitleGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
		require_once("Services/CaTUIComponents/classes/class.catLegendGUI.php");
		require_once("Services/GEV/Desktop/classes/class.gevPeriodSelectorGUI.php");
		
		//$title = new catTitleGUI("gev_rep_attendance_by_employee_title", "gev_rep_attendance_by_employee_desc", "GEV_img/ico-head-edubio.png");

		$title = new catTitleGUI(
			$this->title['title'],
			$this->title['desc'],
			$this->title['img']
		);
			
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
		$export_btn = '<a class="submit exportXlsBtn"'
					. 'href="'
					.$this->ctrl->getLinkTarget($this, "exportxls")
					.'">'
					.$this->lng->txt("gev_report_exportxls")
					.'</a>';


		return    $title->render()
				. $period_input->render()
				. $spacer->render()
				. $export_btn
				. $this->renderTable()
				. $export_btn
				;
	}


	protected function renderTable() {
		require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate(
			$this->table_row_template['filename'], 
			$this->table_row_template['path']
		);
		
		$process = array();

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table_cols as $col) {
			$table->addColumn($this->lng->txt($col[0]), $col[1]);

			//check, if there are entries to process
			//if not, skip iteration over data
			$method_name = '_process_table_' .$col[1];
			if (method_exists($this, $method_name)) {
				$process[$col[1]] = $method_name;
			}
		}
		
		$table->setFormAction($this->ctrl->getFormAction($this, "view"));

		$data = $this->getData();
		
		//process values, if necessary
		if($process){
			foreach ($data as $arpos => $entry) {
				foreach ($entry as $key => $value) {
					if (array_key_exists($key, $process)) {
						$v = $this->$process[$key]($value);
						$data[$arpos][$key] = $v;
					} 
				}
			}
		}

		$cnt = count($data);
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);
		$table->setExternalSorting($this->external_sorting);

		$table->setData($data);

		return $table->getHTML();
	}
	



	protected function exportXLS() {
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

				$method_name = '_process_xls_' .$k;
				if (method_exists($this, $method_name)) {
					$v = $this->$method_name($v);
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
			$this->data = $this->fetchData();
		}
		return $this->data;
	}

	protected function fetchData(){ 
		//fetch retrieves the data 
		die('gevBasicReportGUI::fetchData: WRONG SCOPE !');
	}

}
?>
