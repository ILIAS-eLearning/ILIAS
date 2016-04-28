<?php
require_once 'Customizing/global/plugins/Services/Cron/CronHook/ReportMaster/classes/ReportBase/class.ilObjReportBaseListGUI.php';
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