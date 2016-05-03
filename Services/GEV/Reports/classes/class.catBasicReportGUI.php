<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* base class for ReportGUIs 
* for Generali
*
* @author	Richard Klees <richard.klees@concepts-and-training.de>
* @author	Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportTable.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportOrder.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportQuery.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catReportQueryOn.php';
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.catFilter.php';

class catBasicReportGUI {

	public function __construct() {
		require_once("Services/Calendar/classes/class.ilDatePresentation.php");
		require_once("Services/GEV/Utils/classes/class.gevUserUtils.php");

		global $lng, $ilCtrl, $tpl, $ilUser, $ilDB, $ilLog;
		
		$this->lng = &$lng;
		$this->ctrl = &$ilCtrl;
		$this->tpl = &$tpl;
		$this->db = &$ilDB;
		$this->log = &$ilLog;
		$this->user = &$ilUser;
		$this->user_utils = gevUserUtils::getInstance($this->user->getId());

		$this->title = null;
		$this->table = null;
		$this->query = null;
		$this->data = false;
		$this->filter = null;
		$this->order = null;
	}
	

	public function executeCommand() {
		$this->checkPermission();

		$cmd = $this->ctrl->getCmd();
		$res = $this->executeCustomCommand($cmd);
		if ($res !== null) {
			return $res;
		}
		
		switch ($cmd) {
			case "exportexcel":
				$this->exportExcel();
				exit();
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
		return $this->user_utils->isAdmin() || $this->user_utils->isSuperior()
				|| $this->user_utils->hasRoleIn(array("OD-Betreuer","Admin-Ansicht"))
				|| $this->user_utils->canCancelEmployeeBookings();
	}

	
	protected function render() {
		require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");

		$spacer = new catHSpacerGUI();
		
		return    ($this->title !== null ? $this->title->render() : "")
				. ($this->filter !== null ? $this->filter->render() : "")
				. $spacer->render()
				. $this->renderView()
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
		
		$content = null;
		
		$data = $this->getData();
		
		if ($this->table->_group_by === null) {
			$content = $this->renderUngroupedTable($data);
		}
		else {
			$content = $this->renderGroupedTable($data);
		}
		
		//export-button
		$export_btn = "";
		if (count($data) > 0) {
			$export_btn = $this->renderExportButton();
		}

		return	 $export_btn
				.$content
				.$export_btn;
	}


	protected function renderExportButton() {
		$this->enableRelevantParametersCtrl();
		$export_btn = '<a class="submit exportXlsBtn"'
						. 'href="'
						.$this->ctrl->getLinkTarget($this, "exportexcel")
						.'">'
						.$this->lng->txt("gev_report_excel_export")
						.'</a>';
		$this->disableRelevantParametersCtrl();
		return $export_btn;
	}

	
	protected function renderUngroupedTable($data) {
		$table = new catTableGUI($this, "view");
		$table->setEnableTitle(false);
		$table->setTopCommands(false);
		$table->setEnableHeader(true);
		if(!$this->table->row_template_filename) {
			throw new Exception("No template defined for table ".get_class($this));
		}
		$table->setRowTemplate(
			$this->table->row_template_filename, 
			$this->table->row_template_module
		);

		$table->addColumn("", "blank", "0px", false);
		foreach ($this->table->columns as $col) {
			$table->addColumn( $col[2] ? $col[1] : $this->lng->txt($col[1])
							 , $col[0]
							 , $col[3]
							 );
		}
		
		if ($this->order !== null) {
			$table->setOrderField($this->order->getOrderField());
			$table->setOrderDirection($this->order->getOrderDirection());
		}
		
		$cnt = count($data);
		$table->setLimit($cnt);
		$table->setMaxCount($cnt);

		$external_sorting = true;
		if($this->order === null || 
			in_array($this->order->getOrderField(), 
				$this->internal_sorting_fields ? $this->internal_sorting_fields : array())
			) {
				$external_sorting = false;	
		}
		$table->setExternalSorting($external_sorting);

		if ($this->internal_sorting_numeric) {
			foreach ($this->internal_sorting_numeric as $col) {
				$table->numericOrdering($col);
			}
		}


		$table->setData($data);
		$this->enableRelevantParametersCtrl();
		$return = $table->getHtml();
		$this->disableRelevantParametersCtrl();
		return $return;
	}
	
	protected function renderGroupedTable($data) {
		$grouped = $this->groupData($data);
		$content = "";

		foreach ($grouped as $key => $rows) {
			// We know for sure there is at least one entry in the rows
			// since we created a group from it.
			$content .= $this->renderGroupHeader($rows[0]);
			$content .= $this->renderUngroupedTable($rows);
		}
		
		return $content;
	}
	
	protected function renderGroupHeader($data) {
		$tpl = new ilTemplate( $this->table->group_head_template_filename
							 , true, true
							 , $this->table->group_head_template_module
							 );

		foreach ($this->table->_group_by as $key => $conf) {
			$tpl->setVariable("VAL_".strtoupper($key), $data[$key]);
			$tpl->setVariable("TITLE_".strtoupper($key)
							 , $conf[2] ? $conf[1] : $this->lng->txt($conf[1]));
		}
		
		return $tpl->get();
	}

	protected function groupData($data) {
		$grouped = array();

		foreach ($data as $row) {
			$group_key = $this->makeGroupKey($row);
			if (!array_key_exists($group_key, $grouped)) {
				$grouped[$group_key] = array();
			}
			$grouped[$group_key][] = $row;
		}
		
		return $grouped;
	}
	
	protected function makeGroupKey($row) {
		$head = "";
		$tail = "";
		foreach ($this->table->_group_by as $key => $value) {
			$head .= strlen($row[$key])."-";
			$tail .= $row[$key];
		}
		return $head.$tail;
	}

	protected function getExcelWriter() {
		require_once './Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.spoutXLSXWriter.php';
		$workbook = new spoutXLSXWriter();
		return $workbook;
	}

	/**
	 * provide xlsx version of report for download.
	 */
	protected function exportExcel() {
		$workbook = $this->getExcelWriter();
		$sheet_name = "report";
		$workbook
			->addSheet($sheet_name)
			->setRowFormatBold();

		$header = array();
		foreach ($this->table->all_columns as $col) {
			if ($col[4]) {
				continue;
			}
			if (method_exists($this, "_process_xlsx_header") && $col[2]) {
				$header[] = $this->_process_xlsx_header($col[1]);
			}
			else {
				$header[] = $col[2] ? $col[1] : $this->lng->txt($col[1]);
			}
		}
		$workbook
			->writeRow($header)
			->setRowFormatWrap();
		foreach ($this->getData() as $entry) {
			$row = array();
			foreach ($this->table->all_columns as $col)  {
				if ($col[4]) {
					continue;
				}
				$k = $col[0];
				$v = $entry[$k];
				$method_name = '_process_xlsx_' .$k;
				if (method_exists($this, $method_name)) {
					$v = $this->$method_name($v);
				}
				$row[] = $v;
			}
			$workbook->writeRow($row);
		}

		$workbook->offerDownload("report.xlsx");
	}


	protected function queryWhere() {
		$query_part = $this->query ? $this->query->getSqlWhere() : ' TRUE ';
		$filter_part = $this->filter ? $this->filter->getSQLWhere() : ' TRUE ';
		return ' WHERE '.$filter_part.' AND '.$query_part;
	}

	protected function queryHaving() {
		if ($this->filter === null) {
			return "";
		}
		$having = $this->filter->getSQLHaving();
		if (trim($having) === "") {
			return "";
		}
		return " HAVING ".$having;
	}
	
	protected function queryOrder() {
		if ($this->order === null ||
			in_array($this->order->getOrderField(), 
				$this->internal_sorting_fields ? $this->internal_sorting_fields : array())
			) {
			return "";
		}

		
		
		return $this->order->getSQL();
	}
	
	protected function getData(){ 
		if ($this->data == false){
			$this->data = $this->fetchData();
		}
		return $this->data;
	}

	protected function fetchData() {
		if ($this->query === null) {
			throw new Exception("catBasicReportGUI::fetchData: query not defined.");
		}
		
		$query = $this->query->sql()."\n "
			   . $this->queryWhere()."\n "
			   . $this->query->sqlGroupBy()."\n"
			   . $this->queryHaving()."\n"
			   . $this->queryOrder();
		
		$res = $this->db->query($query);
		$data = array();
		
		while($rec = $this->db->fetchAssoc($res)) {
			$data[] = $this->transformResultRow($rec);
		}

		return $data;
	}
	
	protected function transformResultRow($a_row) {
		return $a_row;
	}
	
	// Helper to replace "-empty-"-entries from historizing tables
	// by gev_no_entry.
	protected function replaceEmpty($a_rec) {
		foreach ($a_rec as $key => $value) {
			if ($a_rec[$key] == "-empty-" || $a_rec[$key] == "0000-00-00" || $a_rec[$key] === null) {
				$a_rec[$key] = $this->lng->txt("gev_table_no_entry");
			}
		}
		return $a_rec;
	}

	protected function enableRelevantParametersCtrl() {
		foreach ($this->relevant_parameters as $get_parameter => $get_value) {
			$this->ctrl->setParameter($this, $get_parameter, $get_value);
		}
	}

	protected function disableRelevantParametersCtrl() {
		foreach ($this->relevant_parameters as $get_parameter => $get_value) {
			$this->ctrl->setParameter($this, $get_parameter, null);
		}
	}
}