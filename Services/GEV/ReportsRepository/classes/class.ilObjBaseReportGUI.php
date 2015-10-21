<?php

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';

class ilObjBaseReportGUI extends ilObjPluginGUI {

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
	abstract public function getStandartCmd() {

	}

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

	protected function exportXLS() {

	}

	protected function enableRelevantParametersCtrl() {

	}

	protected function disableRelevantParametersCtrl() {

	}

}