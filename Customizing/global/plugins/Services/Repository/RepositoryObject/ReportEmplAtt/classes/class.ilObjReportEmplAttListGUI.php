<?php
require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseListGUI.php';
  /**
* ListGUI implementation for Report plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*/
class ilObjReportEmplAttListGUI extends ilObjReportBaseListGUI {

	/**
	* Init type
	*/
	public function initType() {
		$this->setType("xrea");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportEmplAttGUI";
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
		$this->plugin->includeClass("class.ilObjReportEmplAttAccess.php");
		if (!ilObjReportEmplAttAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		return $props;
	}
}