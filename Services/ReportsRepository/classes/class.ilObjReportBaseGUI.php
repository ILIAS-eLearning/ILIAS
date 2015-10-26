<?php

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';

class ilObjReportBaseGUI extends ilObjPluginGUI {

	protected $gLng;
	protected $gCtrl;
	protected $gTpl;
	protected $gUser;
	protected $gLog;
	protected $gAccess;

	protected $title;
	protected $table;
	protected $query;
	protected $data;
	protected $filter;
	protected $order;

	abstract public function getType();
	abstract public function getAfterCreationCmd();
	abstract public function getStandartCmd();

	abstract protected function constructFilter();

	public function setTabs() {

	}

	protected function afterConstructor() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilDB, $ilLog, $ilAccess;	
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gUser = $ilUser;
		$this->gLog = $ilLog;
		$this->gAccess = $ilAccess;

	}
	/**
	* Besides usual report commands (exportXLS, view, ...) showMenu goes here
	*/
	public function performCommand() {
	
	}

	/**
	* render report.
	*/
	protected function render() {
	
	}
		
	protected function renderView() {
	
	}
	
	protected function renderTable() {
	
	}
	
	protected function renderExportButton() {
	
	}
	
	protected function renderUngroupedTable($data) {
	
	}

	protected function renderGroupedTable($data) {
	
	}

	protected function renderGroupHeader($data) {
	
	}

	/**
	* provide xls version of report for download.
	*/
	protected function exportXLS() {

	}

	/**
	* housekeeping the get parameters passed to ctrl
	*/
	protected function enableRelevantParametersCtrl() {

	}

	protected function disableRelevantParametersCtrl() {

	}

	/**
	* Settings menu of the report. Note that any setting query will be performed inside ilObjBaseReport.
	*/
	protected function renderSettings() {

	}
}