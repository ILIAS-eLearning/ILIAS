<?php
require_once 'Services/ReportsRepository/classes/class.ilObjReportBaseListGUI.php';
  /**
* ListGUI implementation for Report plugin. This one
* handles the presentation in container items (categories, courses, ...)
* together with the corresponfing ...Access class.
*/
class ilObjReportEmplEduBiosListGUI extends ilObjReportBaseListGUI {

	/**
	* Init type
	*/
	public function initType() {
		$this->setType("xeeb");
		parent::initType();
	}

	/**
	* Get name of gui class handling the commands
	*/
	public function getGuiClass() {
		return "ilObjReportEmplEduBiosGUI";
	}

	public function getProperties() {
		$props = array();
		$this->plugin->includeClass("class.ilObjReportEmplEduBiosAccess.php");
		if (!ilObjReportEmplEduBiosAccess::checkOnline($this->obj_id)) {
			$props[] = array("alert" => true, "property" => $this->lng->txt("status"),
			"value" => $this->lng->txt("offline"));
		}
		return $props;
	}
}