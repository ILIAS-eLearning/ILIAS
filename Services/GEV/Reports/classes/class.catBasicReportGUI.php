<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* base class for ReportGUIs 
* for Generali
*
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/

class catReportTable {
	protected function __construct() {
		$this->columns = array();
		$this->row_template_filename = null;
		$this->row_template_module = null;
	}
	
	public static function create() {
		return new catReportTable();
	}
	
	public function column($a_id, $a_title, $a_sql_name = false, $a_no_lng_var = false, $a_width = "") {
		$this->columns[] = array( $a_id
								, $a_title
								, ($a_sql_name === false) ? $a_sql_name : $a_id
								, $a_no_lng_var
								, $a_width
								);
		return $this;
	}
	
	public function template($a_filename, $a_module) {
		$this->row_template_filename = $a_filename;
		$this->row_template_module = $a_module;
		return $this;
	}
}

abstract class catBasicReportGUI {

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

		$this->title = null;
		/*$this->title = array(
			'title' => '',
			'desc' => '',
			'img' => '',
			'no_lng_vars' => true
		);*/

		//$this->legend = null;
		$this->table = null;
		$this->query_from = null;
		$this->data = false;
		
		//watch out for sorting of special fields, i.e. dates shown as a period of time.
		//to avoid the ilTable-sorting, set this too true.
		//i.e. applies to: _table_nav=date:asc:
		$this->external_sorting = false;

		$this->permissions = gevReportingPermissions::getInstance($this->user->getId());

		$this->filter = null;
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
	
	/*protected function digestSearchParameter($param, $default){
		//parameters should also be passed on table-sorting
		$this->filter_params[$param] = $default;
		if(isset($_GET[$param])){
			$this->filter_params[$param] = $_GET[$param];
		}
		//post always wins
		if(isset($_POST[$param])){
			$this->filter_params[$param] = $_POST[$param];
		}
		//store for later
		$this->ctrl->setParameter($this, $param, $this->filter_params[$param]);
	}*/


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

		/*$title = new catTitleGUI(
			$this->title['title'],
			$this->title['desc'],
			$this->title['img'],
			!$this->title['no_lng_vars']
		);
		
		if ($this->legend !== null) {
			$title->setLegend($this->legend);
		}*/

		$spacer = new catHSpacerGUI();
		
		//export-button
		$export_btn = '<a class="submit exportXlsBtn"'
					. 'href="'
					.$this->ctrl->getLinkTarget($this, "exportxls")
					.'">'
					.$this->lng->txt("gev_report_exportxls")
					.'</a>';

		return    ($this->title !== null ? $this->title->render() : "")
				. ($this->filter !== null ? $this->filter->render() : "")
				//. $period_input->render($this->getAdditionalFilters())
				. $spacer->render()
				. $export_btn
				. $this->renderView()
				. $export_btn
				;
	}

	protected function renderView() {
		return $this->renderTable();
	}

	protected function renderTable() {
		if ($this->table === null) {
			throw new Exception("catBasicReport::renderTable: you need to define a table.");
		}

		require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
		
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		$table->setRowTemplate(
			$this->table->row_template_filename, 
			$this->table->row_template_module
		);

		$process = array();

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table->columns as $col) {
			$table->addColumn( $col[3] ? $col[1] : $this->lng->txt($col[1])
							 , $col[0]
							 , $col[4]
							 );
		}
		
		//$table->setFormAction($this->ctrl->getFormAction($this, "view"));

		$data = $this->getData();
/*		//process values, if necessary
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
*/
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

	protected function queryWhere() {
		if ($this->filter === null) {
			return " WHERE TRUE";
		}
		
		return " WHERE ".$this->filter->getSQL();
	}
	
	protected function queryFrom() {
		return $this->query_from;
	}

	protected function getData(){ 
		if ($this->data == false){
			$this->data = $this->fetchData();
		}
		return $this->data;
	}

	abstract protected function fetchData();
}
?>
