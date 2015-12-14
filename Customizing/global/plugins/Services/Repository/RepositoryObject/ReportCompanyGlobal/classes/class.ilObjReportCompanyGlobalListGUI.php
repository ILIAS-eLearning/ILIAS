<?php
require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseListGUI.php';

/**
* {@inheritdoc}
*/
class ilObjReportCompanyGlobalListGUI extends ilObjReportBaseListGUI {

	/**
	* Init type
	*/
	public function initType() {
		$this->setType("xrcg");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportCompanyGlobalGUI";
	}

	/**
	* Get commands
	*/
	public function initCommands() {
		return array(
			array(
				"permission" => "read",
				"cmd" => "showContent",
				"txt" => "show",
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "settings",
				"txt" => $this->lng->txt("edit"),
				"default" => false)
		);
	}

	public function getProperties() {
		$props = array();

		$this->plugin->includeClass("class.ilObjReportCompanyGlobalAccess.php");
		if (!ilObjReportCompanyGlobalAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		 
		return $props;
	}
}